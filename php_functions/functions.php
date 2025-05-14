<?php
$start_time = microtime(true); // When the script started running
//$milliseconds2 = sprintf("%03d", ($start_time_ - floor($start_time_)) * 1000);
// $start_time = date("Y-m-d H:i:s") . '.' . $milliseconds2;
// include_once('./functions_v2.php');
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~common
function zerotone($val)
{
    if ($val == 1) {
        return 0;
    } else {
        return 1;
    }
}

function toggleico($val)
{
    if ($val == 1) {
        return "<i class=\"fa fa-times text-red\"></i>";
    } else {
        return "<i class=\"fa fa-check text-green\"></i>";
    }
}
function session_details()
{

    global $server;
    $login_url = "login";
    if (isset($server) && !empty(trim($server))) {
        $login_url = $server . "/login";
    }
    $userd = array();
    if (isset($_SESSION['o-token'])) {
        $token = $_SESSION['o-token'];
        $valid = validatetoken($token);
        if ($valid == 0) {
            header("location:$login_url");
            return null;
        } else {
            $token_user = fetchrow('o_tokens', "token='$token'", "userid");
            $userd = fetchonerow('o_users', "uid='$token_user'", "*");
        }
    } else {
        return null;
    }
    return $userd;
}

function company_settings()
{
    $company = fetchonerow('platform_settings', "uid=1", "name, logo, icon, link, company_id");
    return $company;
}

function validatetoken($token)
{
    global $fulldate;
    $token_valid = checkrowexists("o_tokens", "token='$token' AND status=1 AND expiry_date >= '$fulldate'");
    if ($token_valid == 1) {
        return 1;
    } else {
        return 0;
    }
}

function passencrypt($pass)
{
    $oursalt = crazystring(32);  //generate a random number
    $longpass = $oursalt . $pass;                          //Prepend to the password
    $hash = hash('SHA256', $longpass);

    return $hash . $oursalt;
    //save hash and salt in diffrent tables
}


function profile($sid)
{
    $rid = decurl($sid);
    $d = fetchonerow('s_staff', "uid='$rid'");
    $fname = $d['first_name'];
    $lname = $d['last_name'];

    return $fname . ' ' . $lname;
}


function username($sid)
{
    $rid = decurl($sid);
    $d = fetchonerow('s_staff', "uid='$rid'", "user_name");
    $username = $d['user_name'];

    return $username;
}


function generateRandomString($length)
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}
function generateRandomNumber($length)
{
    $characters = '0123456789';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}


function crazystring($length)
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!@#%^*()_+-~{}[];:|.<>';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

function store_event($tbl, $fld, $event_details, $status = 1)
{
    global $fulldate;
    $ses = session_details();
    $event_by = $ses['uid'] ?? 0;
    $fds = array('tbl', 'fld', 'event_details', 'event_date', 'event_by', 'status');
    $event_details = sanitizeAndEscape($event_details);
    $vals = array("$tbl", "$fld", "$event_details", "$fulldate", "$event_by", $status);
    $create = addtodb('o_events', $fds, $vals);

    //echo $create;
    if ($create == 1) {
        return 1;
    } else {
        return 0;
    }
}


function validateDate($date)
{
    // Define the formats to check
    $formats = ['Y-m-d', 'Y-m-d H:i:s'];

    // Loop through each format and check if the date is valid
    foreach ($formats as $format) {
        $d = DateTime::createFromFormat($format, $date);
        // Check if the date was created successfully and matches the input
        if ($d && $d->format($format) === $date) {
            return true; // Valid date found
        }
    }

    return false; // No valid date format matched
}

function fancydate($uudate)
{
    global $date;

    $uudate = trim($uudate);
    if (empty(validateDate($uudate))) {
        return "";
    }

    $dat = explode(" ", trim($uudate));
    $udate = $dat[0];
    $datediff = datediff($date, $udate);
    if ($datediff == 1) {
        return "<span  class='badge bg-blue'>TOMORROW</span>";
    } elseif ($datediff == 0) {
        return "<span  class='badge bg-orange'>TODAY</span>";
    } elseif ($datediff == -1) {
        return "<span class='badge bg-red-active'>YESTERDAY</span>";
    } elseif ($datediff < -1) {
        $dd = $datediff * -1;
        return "<span class='badge bg-red-active'>" . $dd . " DAYS AGO</span>";
    } else {
        return "<span class='badge'>IN $datediff DAYS</span>";
    }
}
function last_date_of_month($date)
{
    $lastDay = \DateTime::createFromFormat("Y-m-d", "$date")->format("Y-m-t");
    return $lastDay;
}
function first_date_of_month($dateStr)
{
    // Create a DateTime object from the input date string
    $date = new DateTime($dateStr);
    // Modify the date to the first day of the month
    $date->modify('first day of this month');
    // Format the date as "year-month-day"
    return $date->format('Y-m-d');
}
function datefromdatetime2($s)
{
    $dt = new DateTime($s);

    $date = $dt->format('Y-m-d');
    $time = $dt->format('H:i:s');

    return $date;
}
function validate_date($date)
{
    // Check if the date matches the format yyyy-mm-dd
    if (preg_match("/^\d{4}-\d{2}-\d{2}$/", $date)) {
        // Split the date into components
        $parts = explode("-", $date);
        $year = (int) $parts[0];
        $month = (int) $parts[1];
        $day = (int) $parts[2];

        // Use PHP's built-in checkdate function to validate the date
        return checkdate($month, $day, $year);
    } else {
        return false;
    }
}
function dayname($n)
{
    if ($n == 0) {
        return "Sun";
    } elseif ($n == 1) {
        return "Mon";
    } elseif ($n == 2) {
        return "Tue";
    } elseif ($n == 3) {
        return "Wed";
    } elseif ($n == 4) {
        return "Thur";
    } elseif ($n == 5) {
        return "Fri";
    } elseif ($n == 6) {
        return "Sat";
    } else {
        return "";
    }
}


function dateformatchange($d)
{
    $newDate = date("Y-m-d", strtotime($d));
    return $newDate;
}
function readabledate($d)
{
    $newDate = date("d-M-Y", strtotime($d));
    return $newDate;
}

function flag($flag)
{
    if ($flag > 0) {
        $flagd = fetchonerow('o_flags', "uid='$flag'", "name, color_code");
        return "<span class='font-13 font-bold' style='color: " . $flagd['color_code'] . ";'><i class='fa fa-flag'></i> " . $flagd['name'] . "</span>";
    } else {
        return "";
    }
}
function flags()
{
    //  $flagd = table_to_obj2('o_flags', "status > 0", 100,"name, color_code");
    //  return "<span class='font-13 font-bold' style='color: " . $flagd['color_code'] . ";'><i class='fa fa-flag'></i> " . $flagd['name'] . "</span>";

    $flagd = table_to_obj2('o_flags', "uid > 0", 30, "uid", array('name', 'color_code'));
    return $flagd;
}
function next_step($step)
{
    if ($step > 0) {
        $step_d = fetchonerow('o_next_steps', "uid='$step'", "name, details");
        return "<span class='label font-13 font-bold' style='background-color: "
            . $step_d['details'] . ";'>" . $step_d['name'] . "</span>";
    } else {
        return "";
    }
}

/////////////////////////////////*****************************************End of random number generator
function fetchtable($table, $category, $orderby, $dir, $limit, $fds = '*')
{
    try {
        global $con;

        $query = "SELECT $fds FROM " . $table . " WHERE " . $category . " ORDER BY " . $orderby . ' ' . $dir . " LIMIT " . $limit;
        $result = mysqli_query($con, $query);
        return $result;
    } catch (Exception $e) {
        // Handle query execution error

        // echo "Query execution error: " . $e->getMessage();
    }
}


function fetchtable_archive($table, $category, $orderby, $dir, $limit, $fds = '*')
{
    try {
        global $con1;

        $query = "SELECT $fds FROM " . $table . " WHERE " . $category . " ORDER BY " . $orderby . ' ' . $dir . " LIMIT " . $limit;
        $result = mysqli_query($con1, $query);
        return $result;
    } catch (Exception $e) {
        // Handle query execution error

        // echo "Query execution error: " . $e->getMessage();
    }
}

function company_details($company_id)      ////####################################Fetch whole table
{
    global $fulldate;
    global $db_;
    $company = array();
    $roww = fetchonerow("platform_settings", "uid=1");

    $company['uid'] = $company_id;
    $company['name'] = $roww['name'];
    $company['logo'] = $roww['logo'];
    $company['added_by'] = 1;
    $company['added_date'] = $fulldate;
    $company['db_name'] = $db_;
    $company['status'] = 1;


    return $company;

    /*  $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://www.superlender.co.ke/sl-auth/company-details',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => array('company_id' => $company_id),
    ));

    $response = curl_exec($curl);

    curl_close($curl);
    return json_decode($response, true);  */
}
function member_details($email)      ////####################################Fetch whole table
{
    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://www.superlender.co.ke/sl-auth/member-details',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => array('member_email' => '' . $email . ''),
    ));

    $response = curl_exec($curl);

    curl_close($curl);
    return json_decode($response, true);
}
function create_company_member($email, $company)
{

    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://www.superlender.co.ke/sl-auth/create-member',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => array('email' => '' . $email . '', 'company' => '' . $company . ''),
    ));

    $response = curl_exec($curl);

    curl_close($curl);
    return $response;
}
function update_company_member($email, $oldemail, $company)
{
    try {

        global $con1;

        $update = "UPDATE  members SET member_email = '$email' WHERE member_email='$oldemail' AND member_company='$company'";

        if (!mysqli_query($con1, $update)) {
            return mysqli_error($con1);
        } else {
            return 1;
        }
    } catch (Exception $e) {
        // Handle query execution error
        // echo "Query execution error: " . $e->getMessage();
    }
}


function fetchtable2($table, $category, $orderby, $dir, $fds = '*')
{
    try {

        global $con;

        $query = "SELECT $fds FROM " . $table . " WHERE " . $category . " ORDER BY " . $orderby . ' ' . $dir;

        // echo "<tr><td>".$query."</td></tr>";
        $result = mysqli_query($con, $query);

        if (!$result) {
            throw new Exception(mysqli_error($con));
        }

        return $result;
    } catch (Exception $e) {
        // Handle query execution error
        // echo "Query execution error: " . $e->getMessage();
    }
}


function fetchtableGroup($table, $category, $orderby, $dir, $groupby, $limit)
{
    try {

        global $con;
        $query = "SELECT * FROM " . $table . " WHERE " . $category . " GROUP BY " . $groupby . " ORDER BY " . $orderby . ' ' . $dir . " LIMIT " . $limit;

        // var_dump($query);
        $result = mysqli_query($con, $query);

        return $result;
    } catch (Exception $e) {
        // Handle query execution error
        // echo "Query execution error: " . $e->getMessage();
    }
}

function extractnumber($str)
{
    preg_match_all('!\d+!', $str, $matches);

    $final = implode('', $matches[0]);

    return $final;
}


function fetchrow($table, $where, $name)
{
    try {


        global $con;

        $query = "SELECT $name FROM $table WHERE ($where) ORDER BY uid DESC";
        $result = mysqli_query($con, $query);

        if (!$result) {
            throw new Exception(mysqli_error($con));
        }

        $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
        $attrequired = $row[$name];

        return $attrequired;
    } catch (Exception $e) {
        // Handle query execution error
        // echo "Query execution error: " . $e->getMessage();
    }
}


function fetchonerow($table, $where, $fds = '*')
{
    try {

        global $con;

        $query = "SELECT $fds FROM $table WHERE $where;";
        // echo "<tr><td>$query</td></tr>";
        $result = mysqli_query($con, $query);
        // var_dump($result);

        if (!$result) {
            throw new Exception(mysqli_error($con));
        }

        $row = mysqli_fetch_array($result);

        return $row;
    } catch (Exception $e) {
        // Handle query execution error
        // echo "Query execution error: " . $e->getMessage();
    }
}


function fetchonerowrich($table, $where, $orderby, $dir, $limit, $fds = '*')
{
    try {

        global $con;
        $query = "SELECT $fds FROM $table WHERE ($where) ORDER BY $orderby $dir LIMIT $limit,1";
        $result = mysqli_query($con, $query);

        if (!$result) {
            throw new Exception(mysqli_error($con));
        }

        $row = mysqli_fetch_array($result);

        return $row;
    } catch (Exception $e) {
        // Handle query execution error
        // echo "Query execution error: " . $e->getMessage();
    }
}


function fetchrandomrow($table, $where)
{
    try {

        global $con;
        $query = "SELECT * FROM $table WHERE $where ORDER BY RAND()";
        $result = mysqli_query($con, $query);

        if (!$result) {
            throw new Exception(mysqli_error($con));
        }

        $row = mysqli_fetch_array($result);

        return $row;
    } catch (Exception $e) {
        // Handle query execution error
        // echo "Query execution error: " . $e->getMessage();
    }
}

function fetchmaxid($table, $where, $fds = '*')
{
    try {

        global $con;

        $query = "SELECT $fds FROM $table WHERE $where ORDER BY uid DESC LIMIT 0,1";
        $result = mysqli_query($con, $query);

        if (!$result) {
            throw new Exception(mysqli_error($con));
        }

        $row = mysqli_fetch_array($result, MYSQLI_ASSOC);

        return $row;
    } catch (Exception $e) {
        // Handle query execution error
        // echo "Query execution error: " . $e->getMessage();
    }
}

function fetchmax($table, $where, $orderby, $fds = '*')
{
    try {

        global $con;

        $query = "SELECT $fds FROM $table WHERE $where ORDER BY $orderby DESC LIMIT 0,1";
        $result = mysqli_query($con, $query);

        if (!$result) {
            throw new Exception(mysqli_error($con));
        }

        $row = mysqli_fetch_array($result, MYSQLI_ASSOC);

        return $row;
    } catch (Exception $e) {
        // Handle query execution error
        // echo "Query execution error: " . $e->getMessage();
    }
}

function fetchminid($table, $where, $orderby = 'uid')
{
    try {

        global $con;

        $query = "SELECT * FROM $table WHERE $where ORDER BY $orderby ASC LIMIT 0,1";
        $result = mysqli_query($con, $query);

        if (!$result) {
            throw new Exception(mysqli_error($con));
        }

        $row = mysqli_fetch_array($result, MYSQLI_ASSOC);

        return $row;
    } catch (Exception $e) {
        // Handle query execution error
        // echo "Query execution error: " . $e->getMessage();
    }
}

function fetchmin($table, $where, $orderby, $fds = '*')
{
    try {

        global $con;

        $query = "SELECT $fds FROM $table WHERE $where ORDER BY $orderby ASC LIMIT 0,1";
        $result = mysqli_query($con, $query);

        if (!$result) {
            throw new Exception(mysqli_error($con));
        }

        $row = mysqli_fetch_array($result, MYSQLI_ASSOC);

        return $row;
    } catch (Exception $e) {
        // Handle query execution error
        // echo "Query execution error: " . $e->getMessage();
    }
}

function checkrowexists($table, $where)
{
    try {

        global $con;

        $query = "SELECT * FROM $table WHERE $where";
        $result = mysqli_query($con, $query);

        if (!$result) {
            throw new Exception(mysqli_error($con));
        }

        $totalrows = mysqli_num_rows($result);

        return ($totalrows > 0) ? 1 : 0;
    } catch (Exception $e) {
        // Handle query execution error
        // echo "Query execution error: " . $e->getMessage();
        return 0;
    }
}

function searchtable($table, $category, $fields, $tags, $dir, $limit)
{
    try {

        global $con;
        $query = "SELECT * FROM $table WHERE $category AND $fields LIKE '%$tags%' LIMIT $limit";
        $result = mysqli_query($con, $query);

        if (!$result) {
            throw new Exception(mysqli_error($con));
        }

        return $result;
    } catch (Exception $e) {
        // Handle query execution error
        // echo "Query execution error: " . $e->getMessage();
        return false;
    }
}

function countotal($table, $where, $fds = '*')
{
    try {

        global $con;
        $query = "SELECT $fds FROM $table WHERE $where";
        $result = mysqli_query($con, $query);

        if (!$result) {
            throw new Exception(mysqli_error($con));
        }

        $totalrows = mysqli_num_rows($result);

        return $totalrows;
    } catch (Exception $e) {
        // Handle query execution error
        // echo "Query execution error: " . $e->getMessage();
        return false;
    }
}

function countotal_withlimit($table, $where, $fds = '*', $limit)
{
    try {

        global $con;
        $query = "SELECT $fds FROM $table WHERE $where LIMIT $limit";
        $result = mysqli_query($con, $query);

        if (!$result) {
            throw new Exception(mysqli_error($con));
        }

        $totalrows = mysqli_num_rows($result);

        return $totalrows;
    } catch (Exception $e) {
        // Handle query execution error
        // echo "Query execution error: " . $e->getMessage();
        return false;
    }
}

///~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
function input_available($x)
{
    $x = rtrim($x);
    if (empty($x)) {
        return 0;
    } else {
        return 1;
    }
}
function input_exists($table, $where, $field, $value)
{
    $where = "$field='$value' && $where";
    $ch = checkrowexists($table, $where);
    return $ch;
}



function input_length($x, $l)
{
    $x = rtrim($x);
    if ((strlen($x) < $l)) {
        return 0;
    } else {
        return 1;
    }
}
function input_between($low, $high, $string)
{
    $strlen = strlen($string);
    if ($strlen >= $low && $strlen <= $high) {
        return 1;
    } else {
        return 0;
    }
}
function validate_phone($phone)
{
    global $cc;
    $county_code = $cc;
    if ($county_code > 0) {
    } else {
        $county_code = '254';
    }
    if ((strlen($phone)) == 12 && (substr($phone, 0, 3) === "$county_code")) {
        return 1;
    } else {
        return 0;
    }
}

function make_phone_valid($phone)
{
    global $cc;
    $county_code = $cc;
    $phone = trim($phone);
    $phone = str_replace([' ', '+'], '', $phone);
    if ($county_code > 0) {
    } else {
        $county_code = '254';
    }

    if ((strlen($phone)) == 12 && (substr($phone, 0, 3) == "$county_code")) {
        return $phone;
    } else {
        if (substr($phone, 0, 1) === "0") {
            $hone = ltrim($phone, "0");
            $vphone = "$county_code" . $hone;
            return $vphone;
        } else {
            return "$county_code" . $phone;
        }
    }
}
function vnozero($x)
{
    if ($x > 0) {
        return 1;
    } else {
        return 0;
    }
}
function file_avail($filesize)
{
    if ($filesize < 0.0000000000000000001) {
        return 0;
    } else {
        return 1;
    }
}

function file_type($filename, $search_array)
{
    $ext = pathinfo($filename, PATHINFO_EXTENSION);
    $ext = strtolower($ext);
    if ((!in_array("$ext", $search_array))) {
        return 0;
    } else {
        return 1;
    }
}
function file_size($x, $max)
{
    if (($x > 0) && ($x > $max)) {
        return 0;
    } else {
        return 1;
    }
}
function emailOk($emaill)
{
    if (filter_var($emaill, FILTER_VALIDATE_EMAIL)) {
        return 1;
    } else {
        return 0;
    }
}
//////////----------------Date functions
function hour_from_date($dat)
{
    $dt = DateTime::createFromFormat("Y-m-d H:i:s", $dat);
    $hours = $dt->format('H'); // '20'

    return $hours;
}
function rounduptoany($n, $x = 5)
{

    //If the original number is an integer and is a multiple of
    //the "nearest rounding number", return it without change.
    if ((intval($n) == $n) && (!is_float(intval($n) / $x))) {

        return intval($n);
    }
    //If the original number is a float or if this integer is
    //not a multiple of the "nearest rounding number", do the
    //rounding up.
    else {

        return round(($n + $x / 2) / $x) * $x;
    }
}

function timeago($startdate, $enddate)
{
    $sfdate = strtotime($startdate);
    $sldate = strtotime($enddate);
    $diff = strtotime($enddate) - strtotime($startdate);

    if ($diff < 0) {
        $diff = strtotime($startdate) - strtotime($enddate);
        $m = '-';
    } else {
        $m = '';
        //  echo "[+]";
        // $late=0; $ico='bomb.png'; $color='orange';
    }

    // immediately convert to days
    $temp = $diff / 86400; // 60 sec/min*60 min/hr*24 hr/day=86400 sec/day
    // days
    $days = floor($temp);
    $temp = 24 * ($temp - $days);
    // hours
    $hours = floor($temp);
    $temp = 60 * ($temp - $hours);
    // minutes
    $minutes = floor($temp);
    $temp = 60 * ($temp - $minutes);
    // seconds
    $seconds = floor($temp);


    $date_ = date("d M -y", strtotime($startdate));
    $time_ = date("g:i A", strtotime($startdate));

    if ($days == 0 and $hours == 0) {
        return "$minutes mins ago";
    } elseif ($days == 0 and $hours > 0) {
        return "$hours hrs ago";
    } elseif ($days == 1) {
        return "Yesterday $time_";
    } elseif ($days > 1) {
        return $date_;
    } else {
        return $date_;
    }
}

function datecompare($date1, $date2)
{
    $date1 = strtotime($date1);
    $date2 = strtotime($date2);

    $diff = $date1 - $date2;
    if ($diff > 0) /////first date is newer than second
    {
        return 1;
    } elseif ($diff < 0) ////fisrt date is older than second
    {
        return -1;
    } elseif ($diff == 0) ///date are the same
    {
        return 0;
    }
}
function getFirstDayOfWeek($date)
{
    // Convert the input date to a timestamp
    $timestamp = strtotime($date);

    // Use the 'N' format character to get the ISO-8601 numeric representation of the day of the week
    // (1 for Monday through 7 for Sunday)
    $dayOfWeek = date('N', $timestamp);

    // Calculate the number of seconds to subtract to get the first day of the week (Monday)
    $secondsToSubtract = ($dayOfWeek - 1) * 24 * 3600;

    // Calculate the timestamp of the first day of the week
    $firstDayOfWeekTimestamp = $timestamp - $secondsToSubtract;

    // Format and return the result
    return date('Y-m-d', $firstDayOfWeekTimestamp);
}
function getWeekOfMonth($date)
{
    // Convert the date string to a DateTime object
    $dateTime = new DateTime($date);

    // Get the day of the month
    $dayOfMonth = $dateTime->format('j');

    // Calculate the week of the month
    $weekOfMonth = ceil($dayOfMonth / 7);

    return $weekOfMonth;
}


function getLastDayOfWeek($date)
{
    // Get the first day of the week
    $firstDayOfWeek = getFirstDayOfWeek($date);

    // Convert the first day of the week to a timestamp
    $timestamp = strtotime($firstDayOfWeek);

    // Calculate the timestamp of the last day of the week (Sunday)
    $lastDayOfWeekTimestamp = $timestamp + 6 * 24 * 3600;

    // Format and return the result
    return date('Y-m-d', $lastDayOfWeekTimestamp);
}
function getFirstDayOfMonth($date)
{
    // Convert the input date to a timestamp
    $timestamp = strtotime($date);

    // Use the 'j' format character to get the day of the month without leading zeros
    $dayOfMonth = date('j', $timestamp);

    // Calculate the number of seconds to subtract to get the first day of the month
    $secondsToSubtract = ($dayOfMonth - 1) * 24 * 3600;

    // Calculate the timestamp of the first day of the month
    $firstDayOfMonthTimestamp = $timestamp - $secondsToSubtract;

    // Format and return the result
    return date('Y-m-d', $firstDayOfMonthTimestamp);
}
function maskString($inputString, $start, $length)
{
    // Check if start and length are within the bounds of the string
    if ($start < 0 || $length < 0 || $start + $length > strlen($inputString)) {
        // Invalid parameters
        return "Invalid parameters";
    }

    // Replace the specified substring with '*'
    $maskedString = substr_replace($inputString, str_repeat('*', $length), $start, $length);

    return $maskedString;
}
function real_loan_agent($customer_id, $loggedin_agent = 0)
{
    $cusd = fetchonerow('o_customers', "uid='$customer_id'", "added_by, current_agent, branch");
    $customer_current_agent = $cusd['current_agent'];
    $customer_added_by = $cusd['added_by'];
    $customer_branch = $cusd['branch'];

    $lo = 0;
    $co = 0;

    $group_id = fetchrow('o_user_groups', "name='Loan Officer'", "uid");
    if ($group_id > 0) {
    } else {
        $group_id = 7;
    }
    $all_branch_los = table_to_array('o_users', "user_group='$group_id' AND branch='$customer_branch' AND status=1", "100000", "uid");
    $all_branch_los_string = implode(',', $all_branch_los);
    if ($customer_current_agent > 0) {
        if ((in_array($customer_current_agent, $all_branch_los)) == 1) {

            ////--The current agent is also an LO
            $lo = $customer_current_agent;
        } else {
            ////---Current agent is not an LO
        }
    } else {
        ///----No current agent, we try the user who added the customer
        if ((in_array($customer_added_by, $all_branch_los)) == 1) {
            $lo = $customer_added_by;
        } else {
            /////-----The added by user is not an LO
        }
    }

    if ($lo == 0) {
        ///-----still 0, no current agent, no added_by
        if ($loggedin_agent > 0) {
            if ((in_array($loggedin_agent, $all_branch_los)) == 1) {
                $lo = $loggedin_agent;
            } else {
            }
        }
    }
    if ($lo == 0) {
        ///-----Check the last good agent from loans
        $loans = fetchmaxid('o_loans', "customer_id=$customer_id AND current_branch=$customer_branch AND disbursed=1 AND current_lo in ($all_branch_los_string)", "current_lo");
        $last_good_lo = $loans['current_lo'];
        if ($last_good_lo > 0) {
            $lo = $last_good_lo;
        } else {
            $lo = 0;
        }
    }

    if ($lo > 0) {
        ///----Check if they have a pair
        $pair = fetchrow('o_pairing', "lo='$lo' AND status=1", "co");
        if ($pair > 0) {
            $co = $pair;
        } else {
            $co = 0;
        }
    }

    return (array("LO" => $lo, "CO" => "$co"));
}

function dateadd($date, $ys, $mts, $dys)
{
    $newtime = strtotime($date . " + $ys years + $mts months   + $dys days");
    return date("Y-m-d", $newtime);
}

function datesub($date, $ys, $mts, $dys)
{
    $newtime = strtotime($date . " - $ys years - $mts months   - $dys days");
    return date("Y-m-d", $newtime);
}

function last_x_months($months)
{
    $d = array();
    for ($i = $months; $i >= 1; --$i) {
        $dat = date('F Y', strtotime("-$i month"));
        array_push($d, $dat);
        //echo $dat.',';
    }
    return $d;
}
function last_x_months_mysql($months)
{
    $d = array();
    for ($i = $months; $i >= 1; --$i) {
        $dat = date('Y-m', strtotime("-$i month"));
        array_push($d, $dat);
        //echo $dat.',';
    }
    return $d;
}
function real_working_date($date)
{
}
function weekOfMonth($date)
{
    // estract date parts
    list($y, $m, $d) = explode('-', date('Y-m-d', strtotime($date)));

    // current week, min 1
    $w = 1;

    // for each day since the start of the month
    for ($i = 1; $i < $d; ++$i) {
        // if that day was a sunday and is not the first day of month
        if ($i > 1 && date('w', strtotime("$y-$m-$i")) == 0) {
            // increment current week
            ++$w;
        }
    }

    // now return
    return $w;
}

function move_to_monday($date)
{
    ///------Check permission
    global $move_to_monday;
    if ($move_to_monday == 1) {

        $dayofweek = date('w', strtotime($date));
        if ($dayofweek == 0) {
            return dateadd($date, 0, 0, 1);
        } else {
            return $date;
        }
    } else {
        return $date;
    }
}


//////////----------------End of date functions
/// //////---------------File functions
function upload_file($fname, $tmpName, $upload_dir)
{
    $ext = pathinfo($fname, PATHINFO_EXTENSION);
    $nfileName = generateRandomString(25) . '.' . "$ext";

    $filePath = $upload_dir . $nfileName;

    $result = move_uploaded_file($tmpName, $filePath); //var_dump($result);
    if (!$result) {
        return 0;
    } elseif ($result) {
        return $nfileName;
    }
}
function makeThumbnails($updir, $img, $w, $h, $fname)
{
    $thumbnail_width = $w;
    $thumbnail_height = $h;
    $thumb_beforeword = "thumb";
    $ext = fileext_fetch($img);
    $arr_image_details = getimagesize("$updir" . "$img"); // pass id to thumb name
    $original_width = $arr_image_details[0];
    $original_height = $arr_image_details[1];
    if ($original_width > $original_height) {
        $new_width = $thumbnail_width;
        $new_height = intval($original_height * $new_width / $original_width);
    } else {
        $new_height = $thumbnail_height;
        $new_width = intval($original_width * $new_height / $original_height);
    }
    $dest_x = intval(($thumbnail_width - $new_width) / 2);
    $dest_y = intval(($thumbnail_height - $new_height) / 2);
    if ($arr_image_details[2] == 1) {
        $imgt = "ImageGIF";
        $imgcreatefrom = "ImageCreateFromGIF";
    }

    if ($arr_image_details[2] == 2) {
        $imgt = "ImageJPEG";
        $imgcreatefrom = "ImageCreateFromJPEG";
    }
    if ($arr_image_details[2] == 3) {
        $imgt = "ImagePNG";
        $imgcreatefrom = "ImageCreateFromPNG";
    }

    if ($imgt == "ImageJPEG") {
        $old_image = imagecreatefromjpeg("$updir" . "$img");
    }

    if ($imgt == "ImagePNG") {
        $old_image = imagecreatefrompng("$updir" . "$img");
    }

    if ($imgt == "ImageGIF") {
        $old_image = imagecreatefromgif("$updir" . "$img");
    }

    $new_image = imagecreatetruecolor($thumbnail_width, $thumbnail_height);

    imagealphablending($new_image, false);
    imagesavealpha($new_image, true);

    $transparency = imagecolorallocatealpha($new_image, 255, 255, 255, 127);
    imagefilledrectangle($new_image, 0, 0, $w, $h, $transparency);

    imagecopyresized(
        $new_image,
        $old_image,
        $dest_x,
        $dest_y,
        0,
        0,
        $new_width,
        $new_height,
        $original_width,
        $original_height
    );
    $imgt($new_image, "$updir" . "$fname" . ".$ext");
}


function fileext_fetch($filename)
{
    $ext = pathinfo($filename, PATHINFO_EXTENSION);
    return $ext;
}




/////TOtal loans for a given month
function month_loans($m)
{
    $totall = totaltable('s_loans', "month(given_date) = $m AND status in (2,4,5,6)", "loan_amount");
    return $totall;
}

function loan_state($status)
{
    if ($status > 0) {
        $state = fetchonerow('o_loan_statuses', "uid='$status'", "name, color_code");
        $status_name = "<label class='label font-14 text-uppercase label-default' style='background-color: " . $state['color_code'] . " ;'>" . $state['name'] . "</label>";
    } else {
        $status_name = "<label class='label font-14 text-uppercase label-default'>Deleted</label>";
    }
    return $status_name;
}
function loan_next_stage($loan_id)
{
    $result = array();
    $next_stage = null;
    $l = fetchonerow("o_loans", "uid='" . $loan_id . "'", "uid, product_id, loan_stage");
    $product_id = $l['product_id'];
    $loan_stage = $l['loan_stage'];
    $current_stage_order = fetchrow('o_product_stages', "stage_id='$loan_stage' AND status=1", "stage_order");
    // echo "Prod".$product_id; ///
    // echo "Current Stage $loan_stage  Order".$current_stage_order.', for loan'.$loan_id.','; ///
    if ($product_id > 0) {
        $next_stage = fetchminid('o_product_stages', "product_id='$product_id' AND status=1 AND uid != $loan_stage AND stage_order > $current_stage_order", "stage_order");
        $next_stage_id = $next_stage['stage_id'];
        //   echo "Next Stage Id".$next_stage_id; ///
        $stage_info = fetchonerow('o_loan_stages', "uid='$next_stage_id' AND status=1", "uid, name, description");
        if ($next_stage_id > 0) {
            $state = 1;
        } else {
            $state = "NO_STAGE";
        }
    } else {
        $state = 0;
    }
    $result['state'] = $state;
    $result['stage_details'] = $stage_info;


    return $result;
}
function loan_prev_stage($loan_id)
{
}

function loan_addons($loan_id)
{
    $total_addons = totaltable('o_loan_addons', "loan_id='$loan_id' AND status=1", "addon_amount");
    return $total_addons;
}

function loan_addons_archive($loan_id)
{
    $total_addons = totaltable_archive('o_loan_addons', "loan_id='$loan_id' AND status=1", "addon_amount");
    return $total_addons;
}
function loan_interest_addons($loan_id)
{
    $interest_addons = table_to_array('o_addons', "addon_category='INTEREST'", "100", "uid", "uid", "asc");
    if (sizeof($interest_addons) > 0) {
        $all_addons = implode(',', $interest_addons);
        $total_addons = totaltable('o_loan_addons', "loan_id='$loan_id' AND status=1 AND addon_id in ($all_addons)", "addon_amount");
        return $total_addons;
    } else {
        return 0;
    }
}

function loan_penalty_addons($loan_id)
{
    $interest_addons = table_to_array('o_addons', "addon_category='PENALTY'", "100", "uid", "uid", "asc");
    if (sizeof($interest_addons) > 0) {
        $all_addons = implode(',', $interest_addons);
        $total_addons = totaltable('o_loan_addons', "loan_id='$loan_id' AND status=1 AND addon_id in ($all_addons)", "addon_amount");
        return $total_addons;
    } else {
        return 0;
    }
}

function payment_break_down($product_id, $loan_id, $principal, $total_payment)
{
    $breakdown = array();
    $balance = $total_payment;
    $applied_addons = table_to_array('o_product_addons', "product_id='$product_id' AND status=1", "100", "addon_id");
    $loan_addons = table_to_obj('o_loan_addons', "loan_id='$loan_id' AND status = 1", "100", "addon_id", "addon_amount");
    $aa = implode(',', $applied_addons);
    $addons = fetchtable('o_addons', "status=1 AND uid in ($aa)", "from_day", "asc", "100", "uid, name, loan_stage, from_day");
    while ($ad = mysqli_fetch_array($addons)) {
        $addon_id = $ad['uid'];
        $name = $ad['name'];
        $loan_stage = $ad['loan_stage'];
        $from_day = $ad['from_day'];
        $loan_addon_amount = $loan_addons[$addon_id];
        if ($loan_addon_amount > 0) {
            /////------Addon Exists
            if ($balance >= $loan_addon_amount) {
                $breakdown[$name] = $loan_addon_amount;
                $balance = $balance - $loan_addon_amount;
            } else {
                $breakdown[$name] = 0;
            }
        }
    }
    ////////------Finally for principal
    if ($balance >= $principal) {
        $breakdown['Principal'] = $principal;
    } else {
        $breakdown['Principal'] = $principal - $balance;
    }
    return $breakdown;
}

function apply_loan_addon_to_Loan($addonId, $loanId, $recalculate_loan = true)
{
    global $fulldate;
    $addon = fetchonerow('o_addons', "uid='$addonId'", "amount,amount_type,addon_on, applicable_loan, paid_upfront, status, deducted_upfront, addon_category");
    $addon_on = $addon['addon_on'];
    $addon_amount = $addon['amount'];
    $applicable_loan = $addon['applicable_loan'];
    $paid_upfront = $addon['paid_upfront'];
    $addon_status = $addon['status'];
    $deducted_upfront = $addon['deducted_upfront'];
    $addon_category = $addon['addon_category'];
    if ($deducted_upfront == 1) {
        $addon_status = 2;
    } else {
        $addon_status = 1;
    }



    $loan = fetchonerow('o_loans', "uid='$loanId'", "customer_id, loan_amount, disbursed_amount,total_repayable_amount, loan_balance, period, period_units, $addon_on");
    $field_amount = $loan[$addon_on];

    if ($applicable_loan == 1) {

        ///-----Applicable to first loan, check if its first loan
        $total_loans = total_customer_loans($loan['customer_id']);
        if ($total_loans >= 1) {

            return 0;
        } else {
            ////------Continue
        }
    }


    ////-----Check addons that are applied on for first loan
    $period = intval($loan['period']);
    if (($addon['amount_type']) == 'FIXED_VALUE') {
        $addon_amount = $addon['amount'];
    } else if (($addon['amount_type']) == 'PERCENTAGE' && $addonId == 7 && $period >= 60) {
        $addon_amount_multiplier = intval($period / 30);
        $perc = round(($field_amount * ($addon['amount'] / 100)), 2);
        $addon_amount = $perc * $addon_amount_multiplier;
    } else if (($addon['amount_type']) == 'PERCENTAGE') {

        $perc = round(($field_amount * ($addon['amount'] / 100)), 2);
        $addon_amount = $perc;
    }
    // echo "AddonType => {$addon['amount_type']} AddOn Amount = $addon_amount, AddOn ID: $addonId: Field Amount: $field_amount, Loan Id: $loanId <br/>";

    /////------------------Our addon value  = $addon_amount
    ///// -----------------Save the addon to database
    $exists = checkrowexists('o_loan_addons', "addon_id='$addonId' AND loan_id='$loanId'");
    if ($exists == 1) {
        $save = updatedb('o_loan_addons', "addon_amount='$addon_amount', added_date='$fulldate'", "loan_id='$loanId' AND addon_id='$addonId'");
    } else {
        $fds = array('loan_id', 'addon_id', 'addon_amount', 'added_date', 'status');
        $vals = array("$loanId", "$addonId", "$addon_amount", "$fulldate", "$addon_status");
        $save = addtodb('o_loan_addons', $fds, $vals);
    }
    if ($save == 1 && $recalculate_loan == true) {
        recalculate_loan($loanId);
    }
    return $save;
}

function mpesa_addon($addonId, $loanId, $recalculate_loan = true)
{
    global $fulldate;

    $loan = fetchonerow('o_loans', "uid='$loanId'", "loan_amount");
    $field_amount = $loan['loan_amount'];



    ////-----Check addons that are applied on for first loan

    if ($field_amount > 1000) {
        $addon_amount = 22;
    } else {
        $addon_amount = 15;
    }

    $exists = checkrowexists('o_loan_addons', "addon_id='$addonId' AND loan_id='$loanId'");
    if ($exists == 1) {
        $save = updatedb('o_loan_addons', "addon_amount='$addon_amount', added_date='$fulldate'", "loan_id='$loanId' AND addon_id='$addonId'");
    } else {
        $fds = array('loan_id', 'addon_id', 'addon_amount', 'added_date', 'status');
        $vals = array("$loanId", "$addonId", "$addon_amount", "$fulldate", "1");
        $save = addtodb('o_loan_addons', $fds, $vals);
    }
    if ($save == 1 && $recalculate_loan == true) {
        recalculate_loan($loanId);
    }
    return $save;
}

function addon_with_amount($addonId, $loanId, $amount, $update = 1, $recalculate_loan = true)
{
    global $fulldate;

    if ($update == 1) {
        $exists = checkrowexists('o_loan_addons', "addon_id='$addonId' AND loan_id='$loanId' AND status=1");
    } else {
        $exists = 0;
    }
    if ($exists == 1) {
        if ($update == 1) {
            $save = updatedb('o_loan_addons', "addon_amount='$amount', added_date='$fulldate', status=1", "loan_id='$loanId' AND addon_id='$addonId'");
            // return  $save;
        } else {
            return "Exists";
        }
    } else {
        $fds = array('loan_id', 'addon_id', 'addon_amount', 'added_date', 'status');
        $vals = array("$loanId", "$addonId", "$amount", "$fulldate", "1");
        $save = addtodb('o_loan_addons', $fds, $vals);
    }
    if ($save == 1 && $recalculate_loan == true) {
        recalculate_loan($loanId);
    }
    return $save;
}
function addon_with_amount_update($addonId, $loanId, $amount, $recalculate_loan = true)
{
    global $fulldate;



    $save = updatedb('o_loan_addons', "addon_amount='$amount', added_date='$fulldate', status=1", "loan_id='$loanId' AND addon_id='$addonId' AND status=1");
    // return  $save;

    if ($save == 1 && $recalculate_loan == true) {
        recalculate_loan($loanId);
    }
    return $save;
}

function remove_addon($addonId, $loanId, $recalculate = 1)
{
    $remove = updatedb('o_loan_addons', "status='0'", "loan_id='$loanId' AND addon_id='$addonId'");
    if ($recalculate == 1) {
        recalculate_loan($loanId);
    }
    return $remove;
}

function mid_addons($loanId)
{
    global $date;
    global $fulldate;
    $loan = fetchonerow('o_loans', "uid='$loanId'", "*");
    $given_date = $loan['given_date'];
    $product_id = $loan['product_id'];
    $days_ago = datediff($given_date, $date);

    $addons = fetchtable('o_product_addons', "status=1 AND product_id = '$product_id'", "uid", "asc", "100", "addon_id");
    while ($a = mysqli_fetch_array($addons)) {
        $addon = $a['addon_id'];

        $addon_det = fetchonerow('o_addons', "uid='$addon'", "*");
        $loan_stage = $addon_det['loan_stage'];
        $from_day = $addon_det['from_day'];
        $to_day = $addon_det['to_day'];
        $amount = $addon_det['amount'];
        $field_value = $loan[$addon_det['addon_on']];
        $amount_type = $addon_det['amount_type'];
        //  echo "Addon $addon [$from_day -> $to_day] Days ago [$days_ago],";
        if ($days_ago >= $from_day && $days_ago <= $to_day && $loan_stage == 'DISBURSED') {
            $days_past = ($days_ago - $from_day) + 1;
            $add_addon = 1;
        } elseif ($days_ago >= $from_day && $days_ago > $to_day) {
            $days_past = ($to_day - $from_day) + 1;
            $add_addon = 1;
        } else {
            $days_past = $days_ago;
            $add_addon = 0;
        }
        // echo " Days passed [$days_past] DAys ago[$days_ago] AddAddon [$add_addon]";

        if ($to_day > $from_day) {

            // echo "Days past [$days_past] Field value [$field_value] AddOn Amount [$amount]";
            $total_v = $days_past * $amount;
            if ($amount_type == 'PERCENTAGE') {
                $total_addon = ($total_v / 100) * $field_value;
            } else {
                $total_addon = $total_v;
            }
            // echo "AddONId: $addon, Days Passed: $days_past, Days Ago: $days_ago, From day: $from_day, To day: $to_day,    Total Addon [$total_addon] for Loan [$loanId]";
            if ($total_addon > 0) {
                $exists = checkrowexists('o_loan_addons', "addon_id='$addon' AND loan_id='$loanId'");
                if ($exists == 1) {
                    $save = updatedb('o_loan_addons', "addon_amount='$total_addon', added_date='$fulldate'", "loan_id='$loanId' AND addon_id='$addon'");
                } else {
                    $fds = array('loan_id', 'addon_id', 'addon_amount', 'added_date', 'status');
                    $vals = array("$loanId", "$addon", "$total_addon", "$fulldate", "1");
                    $save = addtodb('o_loan_addons', $fds, $vals);
                }
                if ($save == 1) {
                    recalculate_loan($loanId);
                }
                return "Save addon" . $save . ",";
            } else {
                return "Total AddOn: $total_addon,";
            }
            //return $total_addon;
        }
        echo "<br/>";
    }
}



function deduction_amount($amount, $deduction_id)
{
    $deduction_d = fetchonerow('o_deductions', "uid='$deduction_id'", "amount, amount_type");
    if (($deduction_d['amount_type']) == 'FIXED_VALUE') {
        return $deduction_d['amount'];
    } else if (($deduction_d['amount_type']) == 'PERCENTAGE') {
        $perc = round(($amount * ($deduction_d['amount'] / 100)), 2);
        return $perc;
    } else {
    }
}

function addon_amount($amount, $addon_id)
{
    $add_d = fetchonerow('o_addons', "uid='$addon_id'", "amount, amount_type");
    if (($add_d['amount_type']) == 'FIXED_VALUE') {
        return $add_d['amount'];
    } else if (($add_d['amount_type']) == 'PERCENTAGE') {
        $perc = round(($amount * ($add_d['amount'] / 100)), 2);
        return $perc;
    }
}


function loan_deductions($loan_id)
{
    $total_deductions = totaltable('o_loan_deductions', "loan_id='$loan_id' AND status=1", "deduction_amount");
    return $total_deductions;
}
function loan_obj($loan_id)
{
    $l = fetchonerow("o_loans", "uid='" . $loan_id . "'", "loan_amount ,disbursed_amount ,total_repayable_amount ,total_repaid ,total_addons ,total_deductions ,given_date ,next_due_date ,final_due_date");
    $l['loan_balance'] = $l['total_repayable_amount'] - $l['total_repaid'];
    return $l;
}


function repay_schedule($loan_id)
{
    global $registration_fee_addon_id;
    global $processing_fee_addon_id;

    $l = fetchonerow("o_loans", "uid='" . $loan_id . "'", "*");
    if ($l['uid'] > 0) {

        $given_date = $l['given_date'];
        $freq_days = $l['payment_frequency'];
        $period_days = $l['period'] * $l['period_units'];

        $all_addons_kv = nested_kv("o_loan_addons", "status = 1 AND loan_id='$loan_id'", "loan_id", "addon_id", "addon_amount");

        ////----Check if total repayable will deduct upfront fees and reg fees
        $loan_addons_det = $all_addons_kv[$loan_id];
        $registration_fee = 0;
        $processing_fee = 0;
        // echo json_encode($all_addons_kv) ."<br>";
        if (intval($registration_fee_addon_id) > 0) {
            $registration_fee = doubleval($loan_addons_det[$registration_fee_addon_id]);
        }

        if (intval($processing_fee_addon_id) > 0) {
            $processing_fee = doubleval($loan_addons_det[$processing_fee_addon_id]);
        }

        ///-----------End of check if repayable will deduct upfront fees and reg fees

        $total_repayable = $l['total_repayable_amount'] - ($registration_fee + $processing_fee);
        $total_repaid = $l['total_repaid'] - ($registration_fee + $processing_fee);

        if ($freq_days > 0) {
            $instalments = floor($period_days / $freq_days);
            $instalment_amount = ceil($total_repayable / $instalments);

            $rec = "";
            for ($i = 1; $i <= $instalments; ++$i) {
                $date = dateadd($given_date, 0, 0, ($freq_days * $i));
                $amount_due = $instalment_amount * $i;
                $balance = max($amount_due - $total_repaid, 0);

                if ($total_repaid >= $amount_due) {
                    $state = "<span class='font-13 text-green'><i class='fa fa-check'></i>Repaid</span>";
                    $balance = 0;  // No balance if fully repaid
                } else {
                    $state = "<span class='font-13 text-red'><i class='fa fa-times'></i>Unpaid</span>";
                }

                $rec .= "<tr>
                            <td>$date</td>
                            <td>" . money($instalment_amount) . "</td>
                            <td>" . money($balance) . "</td>
                            <td>$state</td>
                         </tr>";
            }
        } else {
            $balance = max($total_repayable - $total_repaid, 0);

            if ($total_repaid >= $total_repayable) {
                $state = "<span class='font-13 text-green'><i class='fa fa-check'></i>Repaid</span>";
                $balance = 0;  // No balance if fully repaid
            } else {
                $state = "<span class='font-13 text-red'><i class='fa fa-times'></i>Unpaid</span>";
            }

            $rec = "<tr>
                        <td>" . $l['final_due_date'] . "</td>
                        <td>" . money($total_repayable) . "</td>
                        <td>" . money($balance) . "</td>
                        <td>$state</td>
                    </tr>";
        }

        return $rec;
    } else {
        return "Loan not found";
    }
}


function recalculate_loan($loan_id, $force_recalc = false)
{
    global $date;
    if (intval($loan_id) === 0) {
        return 1;
    }

    $l = fetchonerow("o_loans", "uid='" . $loan_id . "'", "loan_amount, disbursed_amount, final_due_date, disbursed, paid, status");
    /////////------------deductions
    /// ///---Check if loan is cleared and dont recalculate
    $cleared = $l['paid'];
    $disbursed_amount = $l['disbursed_amount'];
    $disbursed = $l['disbursed'];
    if ($cleared == 1 && $force_recalc == false) {
        return 0;
    } else {
        $deduction_total = loan_deductions($loan_id);
        /////////------------AddOn total
        $addon_total = loan_addons($loan_id);
        $interest_addons_total = loan_interest_addons($loan_id); /// For purposes of determining interest alone, we store interest on its own
        $penalty_addons_total = loan_penalty_addons($loan_id);  //// We also store penalty on its own for rate calculation
        /////////------------Total Repaid
        $repaid_total = total_repaid($loan_id);

        $total_repayable_amount = $l['loan_amount'] + $addon_total - $deduction_total;
        $loan_balance = $total_repayable_amount - $repaid_total;
        $final_due_date = $l['final_due_date'];
        $status = $l['status'];
        if ($loan_balance < 1) {
            $and_clear = " ,paid=1, status=5";
            $cleared_event = 1;
            // $loan_balance = 0;
        } else {
            // mark loan as overdue if final due date is passed & but not marked overdue
            if (new DateTime($final_due_date) < new DateTime($date) && $status == 3 && $disbursed == 1) {
                $and_clear = " ,paid=0, status=7";
                $cleared_event = 0;
            } else {
                // retain existing status
                $and_clear = "";
                $cleared_event = 0;
            }
        }

        if ($cleared_event == 1) {
            $cleared_date = ", cleared_date='$date'";
        } else {
            $cleared_date = "";
        }

        $income_earned = false_zero($repaid_total - $disbursed_amount);

        if ($interest_addons_total > 0) {
            ////--- store it ask JSON
            //  $sec = array("INTEREST_AMOUNT"=>$interest_addons_total);
            $andsec = " ,other_info = JSON_SET(
                IFNULL(other_info, '{}'),
                '$.PENALTY_AMOUNT', '$penalty_addons_total',
                '$.INTEREST_AMOUNT', '$interest_addons_total')";
        } else {
            $andsec = " ";
        }
        // echo $andsec;

        $fds = "total_addons='$addon_total', total_deductions='$deduction_total', total_repaid='$repaid_total',  total_repayable_amount='$total_repayable_amount', income_earned='$income_earned',loan_balance='$loan_balance' $and_clear $cleared_date $andsec";
        $update = updatedb('o_loans', $fds, "uid='$loan_id'");

        if ($cleared_event == 1 && $cleared == 0) {
            store_event('o_loans', $loan_id, "Loan cleared via loan recalculation");
        }

        ////////----------------Update total loans


        return $update;
    }
}

function change_loan_dates($lid, $given_date, $new_date)
{

    if ($given_date != $new_date) {
        $loan_det = fetchonerow('o_loans', "uid='$lid'", "given_date, next_due_date, final_due_date, period, period_units");
        $given_date = $loan_det['given_date'];
        $next_due_date = $loan_det['next_due_date'];
        $final_due_date = $loan_det['final_due_date'];
        $period = $loan_det['period'];
        $period_units = $loan_det['period_units'];

        $diff = datediff3($new_date, $given_date);
        $new_next_d = dateadd($next_due_date, 0, 0, $diff);

        // $final_due_d = move_to_monday(dateadd($final_due_date, 0,0, $diff));
        $final_due_d = move_to_monday(final_due_date($new_date, $period, $period_units));
        $new_dates = "NEW Dates-> Disbursed: $new_date, Next Due: $new_next_d, Final Due: $final_due_d";
        $new_dates_update = "given_date='$new_date', next_due_date='$new_next_d', final_due_date='$final_due_d'";
        $updt = updatedb('o_loans', "$new_dates_update", "uid='$lid'");
        if ($updt == 1) {
            store_event('o_loans', $lid, "Loan date changed by $diff days: $new_dates");
        }
        return $updt;
    }
    return "Same date";
}


function total_customer_loans($customer_id)
{
    global $has_archive;
    $total_customer_loans = countotal('o_loans', "customer_id='$customer_id' AND status != 0 AND disbursed=1", "uid");

    if ($has_archive == 1) {
        $total_customer_loans += countotal_archive('o_loans', "customer_id='$customer_id' AND status != 0 AND disbursed=1", "uid", "1000000");
    }
    //echo $total_customer_loans;
    $upd = updatedb('o_customers', "total_loans='$total_customer_loans'", "uid='$customer_id'");
    return $total_customer_loans;
}


///Total collection for a given month
function month_collections($m)
{
    $totall = totaltable('s_incoming_payments', "month(date_received) = $m AND status in (1,2)", "amount");
    return $totall;
}
///TOtal customers for a given month
function new_customers($m)
{
    $totall = countotal('s_users_primary', "month(added_date) = $m AND status in (2)", "uid");
    return $totall;
}
///TOtal leads for a given month
function new_leads($m)
{
    $totall = countotal('s_users_primary', "month(added_date) = $m AND status in (1)", "uid");
    return $totall;
}

function thumbnail($filename, $newsize)
{
    // File and new size
    //  $filename = 'test.jpg';
    $percent = 0.5;

    // Content type
    //  header('Content-Type: image/jpeg');

    // Get new sizes
    list($width, $height) = getimagesize($filename);
    $newwidth = $width * $percent;
    $newheight = $height * $percent;

    // Load
    $thumb = imagecreatetruecolor($newwidth, $newheight);
    $source = imagecreatefromjpeg($filename);

    // Resize
    imagecopyresized($thumb, $source, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);

    // Output
    imagejpeg($thumb);
}

function errormes($x)
{
    return "<div class='alert alert-danger'>$x</div>";
}
function sucmes($x)
{
    return "<div class='alert alert-success'>$x</div>";
}
function success($x)
{
    return "<div class='alert successbox'>$x</div>";
}
function notice($x)
{
    return "<div class='alert alert-info'>$x</div>";
}


function isyear($x)
{
    if ((strlen($x)) == 4) {
        if (is_numeric($x)) {
            return 1;
        } else {
            return 0;
        }
    } else {
        return 0;
    }
}


function addtodb($tb, $fds, $vals)
{
    try {

        global $con;

        $fields = implode(',', $fds);
        $values = implode("','", $vals);
        $values = "'$values'";

        $insertq = "INSERT into $tb ($fields) VALUES ($values)";


        if (!mysqli_query($con, $insertq)) {
            throw new Exception(mysqli_error($con));
        }
        return 1;
    } catch (Exception $e) {
        // Handle query execution error
        // echo "Query execution error: " . $e->getMessage();
        return "Query execution error: " . $e->getMessage();
    }
}


function addtodbmulti($tb, $fds, $vals)
{
    try {

        global $con;

        $fields = implode(',', $fds);
        $values = $vals;

        $insertq = "INSERT IGNORE into $tb ($fields) VALUES $values";  //echo $insertq;

        if (!mysqli_query($con, $insertq)) {
            throw new Exception(mysqli_error($con));
        }

        logupdate($tb, $insertq);
        return 1;
    } catch (Exception $e) {
        // Handle query execution error
        // echo "Query execution error: " . $e->getMessage();
        return "Query execution error: " . $e->getMessage();
    }
}


function updatedb($tb, $fds, $where)
{
    try {

        global $con;
        $updateq = "UPDATE $tb SET $fds WHERE $where";

        if (!mysqli_query($con, $updateq)) {
            throw new Exception(mysqli_error($con));
        }

        logupdate($tb, $updateq);
        return 1;
    } catch (Exception $e) {
        // Handle query execution error
        // echo "Query execution error: " . $e->getMessage();
        return "Query execution error: " . $e->getMessage();
    }
}


function notify($from, $to, $title, $content, $linkto)
{
    global $fulldate;

    $fds = array('staff_id', 'sent_date', 'source_details', 'title', 'details', 'link', 'status');
    $vals = array("$to", "$fulldate", "$from", "$title", "$content", "$linkto", "1");
    $create = addtodb('o_notifications', $fds, $vals);

    return $create;
}
function notifyUpdate($uid, $date, $heading, $content, $linkto)
{
    $updaten = updatedb('wb_notifications', "notifdate='$date',heading='$heading',content='$content',linkto='$linkto', status=0", "uid='$uid'");
    return $updaten;
}
function bmail($from, $to, $replyto, $subject, $body, $cc)
{


    $message = '<html><body>';
    $message .= "<h1>$subject</h1>";
    $message .= $body;
    $message .= '</body></html>';

    $headers = "From: $from" . "\r\n" .
        "Reply-To: $replyto";
    $headers .= "CC: $cc\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";



    $sm = mail($to, $subject, $message, $headers);
    return $sm;
}

function totaltable($table, $where, $fld)
{
    try {

        global $con;

        $q = "SELECT SUM($fld) FROM $table WHERE $where";
        $result = mysqli_query($con, $q);

        if (!$result) {
            throw new Exception(mysqli_error($con));
        }

        $row = mysqli_fetch_array($result);

        return ($row[0] > 0) ? $row[0] : 0;
    } catch (Exception $e) {
        // Handle query execution error
        // echo "Query execution error: " . $e->getMessage();
        return false;
    }
}

function totaltable_archive($table, $where, $fld)
{
    try {

        global $con1;

        $q = "SELECT SUM($fld) FROM $table WHERE $where";
        $result = mysqli_query($con1, $q);

        if (!$result) {
            throw new Exception(mysqli_error($con1));
        }

        $row = mysqli_fetch_array($result);

        return ($row[0] > 0) ? $row[0] : 0;
    } catch (Exception $e) {
        // Handle query execution error
        // echo "Query execution error: " . $e->getMessage();
        return false;
    }
}

function countotal_archive($table, $where, $fds = '*', $limit)
{
    try {

        global $con1;

        $query = "SELECT $fds FROM $table WHERE $where LIMIT $limit";
        $result = mysqli_query($con1, $query);

        if (!$result) {
            throw new Exception(mysqli_error($con1));
        }

        $totalrows = mysqli_num_rows($result);

        return $totalrows;
    } catch (Exception $e) {
        // Handle query execution error
        // echo "Query execution error: " . $e->getMessage();
        return false;
    }
}


function create_archive_token()
{
}
function get_string_between($string, $start, $end)
{
    $string = " " . $string;
    $ini = strpos($string, $start);
    if ($ini == 0)
        return "";
    $ini += strlen($start);
    $len = strpos($string, $end, $ini) - $ini;
    return substr($string, $ini, $len);
}
function extract_words_between($startstring, $endstring, $string)
{
    foreach (explode($startstring, $string) as $key => $value) {
        if (strpos($value, $endstring) !== FALSE) {
            $result[] = substr($value, 0, strpos($value, $endstring));
            ;
        }
    }
    return $result;
}

function deletefile($link)
{
    if (!unlink($link)) {
        return 0;
    } else {
        return 1;
    }
}
function plaintext($html)
{
    // remove comments and any content found in the the comment area (strip_tags only removes the actual tags).
    $plaintext = preg_replace('#<!--.*?-->#s', '', $html);

    // put a space between list items (strip_tags just removes the tags).
    $plaintext = preg_replace('#</li>#', ' </li>', $plaintext);

    // remove all script and style tags
    $plaintext = preg_replace('#<(script|style)\b[^>]*>(.*?)</(script|style)>#is', "", $plaintext);

    // remove br tags (missed by strip_tags)
    $plaintext = preg_replace("#<br[^>]*?>#", " ", $plaintext);

    // remove all remaining html
    $plaintext = strip_tags($plaintext);

    return $plaintext;
}

function makenumeric($string)
{
    $num = preg_replace("/[^0-9]/", "", $string);
    return $num;
}
function makedouble($string)
{
    $num = preg_replace("/[^0-9.]/", "", $string);
    return $num;
}
function allowedfiles()
{
    $files = array('pdf', 'ppt', 'pptx', 'doc', 'docx', 'rtf', 'xlt', 'xltx', 'zip', 'rar', 'ai', 'psd');
    return $files;
}
function allowedocs()
{
    $docs = array('pdf', 'ppt', 'pptx', 'doc', 'docx', 'rtf', 'xlt', 'xltx');
    return $docs;
}
function allowedimages()
{
    $images = array('jpg', 'jpeg', 'png');
    return $images;
}

function encurl($id)
{
    $secureId = $id * 1321;
    return $secureId;
}
function decurl($id)
{
    $originalId = $id / 1321;
    return $originalId;
}
function current_url()
{
    return $_SERVER['HTTP_HOST'];
}

function mycountry()
{
    return 'KE';
}
function currency()
{
    return 'Ksh';
}
function truecurrency($value)
{
    $num = number_format($value, 2);
    $currency = currency();
    return $currency . ' ' . $num;
}

function intodolars($amount)
{
    return "$amount";
}
function intomycur($dolars)
{
    $converted = $dolars * 100;
    return 'Ksh.' . $converted;
}
//////!!!!!!!!!!!!!!!!!!!!!!Common classes


function accepted_files($x)
{
    if ($x == 1)  /// contest post attachments
    {
        return ".jpg,.JPG,.jpeg,.JPEG.png,.PNG,.gif,.GIF.pdf,.PDF,.doc,.DOC,.docx,.DOCX, .ppt,.PPT,.pptx,.PPTX,.txt,.TXT,.psd,.PSD,.ai,.AI";
    }
}

// function sanitize_url($unsafeurl)
// {
//     $new_url = sanitize_title("$unsafeurl");
//     return $new_url;
// }



function safehtml($html)
{
    $safeHtml = addslashes($html);
    $safeHtml = htmlspecialchars($html);

    // $safeHtml = stripslashes($html);

    $safeHtml = htmlentities($safeHtml);

    return $safeHtml;
}

function htmldec($html)
{
    $unsafeHtml = html_entity_decode($html);
    return $unsafeHtml;
}

function error($x)
{
    return "<span class=\"errorbox\">$x</span>";
}


function upload_file_name($fname, $tmpName, $upload_dir)
{


    $ext = pathinfo($fname, PATHINFO_EXTENSION);
    $path_parts = pathinfo($fname);
    $name = $path_parts['filename'];

    $nfileName = safename($name) . '.' . "$ext";

    $filePath = $upload_dir . $nfileName;

    $result = move_uploaded_file($tmpName, $filePath); //var_dump($result);
    if (!$result) {
        return 0;
    } elseif ($result) {
        return $nfileName;
    }
}
function safename($string)
{
    $nstring = str_replace(' ', '_', $string);
    $datestring = date("Y-m-d_h-i-s");

    return $nstring . '-' . $datestring;
}

function save_log($content)
{
    global $fulldate;
    $fds = array('activity', 'date_created');
    $vals = array("$content", "$fulldate");
    $createlog = addtodb('s_activity_logs', $fds, $vals);

    return $createlog;
}


function payment_status($state)
{
    if ($state == 1) {
        $status = 'Added';
    } elseif ($state == 2) {
        $status = 'Verified';
    } elseif ($state == 3) {
        $status = 'Disputed';
    } elseif ($state == 4) {
        $status = 'Reversed';
    } elseif ($state == 5) {
        $status = 'Deleted';
    }

    return $status;
}
function admin_status($state)
{


    if ($state == 0) {
        $status = "<span class=\"label label-default\">Inactive</span>";
        ;
    } elseif ($state == 1) {
        $status = "<span class=\"label label-success\">Active</span>";
    } elseif ($state == 2) {
        $status = "<span class=\"label label-success\">Blocked</span>";
    } elseif ($state == 3) {
        $status = "<span class=\"label label-success\">Former</span>";
    }

    return $status;
}
function gender($gender)
{
    if ($gender == 1) {
        return 'Male';
    } elseif ($gender == 2) {
        return 'Female';
    } else {
        return 'Unspecified';
    }
}


/////////////___________Details
class credit_details
{
    var $cid;

    function __construct($cid)
    {
        $this->cid = $cid;
    }
    function total_loans($cid)
    {
        $total = countotal('o_loans', "customer_id='$cid' AND status != 0", "uid");
        return $total;
    }
    function total_loan_value()
    {
        $cid = $this->cid;
        $totall = totaltable('s_loans', "customer_id='$cid' AND status in (2,4,5,6)", "loan_amount");
        if ($totall > 0) {
        } else {
            $totall = 0;
        }
        return $totall;
    }
    function total_repayments_value()
    {
        $cid = $this->cid;
        $loan_codes = array();
        $all_loans = fetchtable('s_loans', "customer_id='$cid'", "uid", "desc", "1000", "uid");
        while ($l = mysqli_fetch_array($all_loans)) {
            array_push($loan_codes, $l['uid']);
        }

        $loans_string = implode(",", $loan_codes);
        $totall = totaltable('s_incoming_payments', "loan_id in ($loans_string) AND status =1", "amount");
        if ($totall > 0) {
        } else {
            $totall = 0;
        }
        return $totall;
    }
}

function customer_loans($cid)
{
    global $has_archive;
    $total = countotal('o_loans', "customer_id='$cid' AND status != 0 AND disbursed=1", "uid");
    if ($has_archive == 1) {
        $total += countotal_archive('o_loans', "customer_id='$cid' AND status != 0 AND disbursed=1", "uid", "1000000");
    }
    return $total;
}

////____________Pass loan code and get all details about a loan

class loan_details
{
    var $loanid = 0;
    var $total_repaid = 0;
    var $l = array();

    function __construct($loanid)
    {
        $this->loanid = $loanid;
        $l = fetchonerow('s_loans', "uid = '$loanid'");
        $total_repaid = totaltable('s_incoming_payments', "loan_id='$loanid'", "amount");
        $this->l = $l;
        $this->total_repaid = $total_repaid;
    }
    function loan_amount()
    {
        $l = $this->l;
        $loan_amount = $l['amount'];

        if ($loan_amount > 0) {
        } else {
            $loan_amount = 0;
        }

        return $loan_amount;
    }
    function loan_interest()
    {
        $l = $this->l;
        $loan_interest = $l['loan_interest'];
        if ($loan_interest > 0) {
        } else {
            $loan_interest = 0;
        }
        return $loan_interest;
    }
    function loan_late_interest()
    {
        $l = $this->l;
        $late_interest = $l['late_interest'];

        if ($late_interest > 0) {
        } else {
            $late_interest = 0;
        }
        return $late_interest;
    }
    function total_repaid()
    {
        $total_repaid = $this->total_repaid;
        if ($total_repaid > 0) {
        } else {
            $total_repaid = 0;
        }
        return $total_repaid;
    }
    function loan_balance()
    {
        $l = $this->l;
        $payable = $l['loan_total'];
        $given_date = $l['given_date'];
        $loan_amount = $l['loan_amount'];
        $loan_product = $l['loan_product'];
        $loan_interest = $l['loan_interest'];

        global $date;
        $days_passed = datediff($given_date, $date);

        ////___________Payable for Dumisha
        if ($loan_product == 4) {
            if ($days_passed <= 14) ////-----Use the 14% already there
            {
            } elseif ($days_passed > 14 && $days_passed <= 30) /////Day 15 - 30 Use 1% per day
            {
                $daily = $days_passed - 14;
                $new_added_interest = $loan_amount * ($days_passed / 100);
            } elseif ($days_passed > 30) ///Day 31 and above, use 16%
            {
                $new_added_interest = ceil($loan_amount * (16 / 100));
            }
            $payable = $loan_amount + $loan_interest + $new_added_interest;
        }


        $total_repaid = $this->total_repaid;

        $balance = $payable - $total_repaid;
        if ($balance < 0) {
            $balance == 0;
        }

        return $balance;
    }

    function last_repayment_date()
    {
        $loanid = $this->loanid;
        $last_d = fetchmax('o_incoming_payments', "loan_id='$loanid'", "date_received", "date_received");
        $last_payment_date = $last_d['date_received'];
        return $last_payment_date;
    }
}

function loan_status($action)
{
    $andfds = "";
    if ($action == 3 || $action == 4 || $action == 5 || $action == 7 || $action == 8 || $action == 9) {
        $andfds = ", disbursed='1'";
    }
    if ($action == 1 || $action == 2 || $action == 6) {
        $andfds = ", disbursed='0'";
    }
    if ($action == 5) {
        $andfds .= ", paid='1', loan_balance=0";
    } else {
        $andfds .= ", paid='0'";
    }
    if ($action == 0) {
        $andfds = ", disbursed='0', paid='0'";
    }
    return $andfds;
}
function createloan($user, $amount, $product)
{
    $userdetails = fetchonerow('s_users_primary', "uid='$user'");
    $productdetails = fetchonerow('s_loan_products', "uid='$product'");

    $first_name = $userdetails['first_name'];
    $primary_phone = $userdetails['primary_phone'];
}

function paging($url, $orderby, $dir, $offset, $rpp, $fds, $search, $box, $remaining, $where = 'uid>0')
{
    $nrpp = $offset + $rpp;

    if ($offset > 1) {
        $off = $offset - $rpp;
        echo "<button onclick=\"paging('$url','$orderby','$dir','$off','$rpp','$fds','$search','$box','$where');\" class=\"btn btn-primary\">Prev</button> &emsp;";
    } else {
        echo "<button class=\"btn btn-default disabled\">Prev</button> &emsp;";
    }
    echo $offset . '-' . $nrpp;
    if ($remaining > $rpp) {
        $off = $offset + $rpp;
        echo "&emsp;<button onclick=\"paging('$url','$orderby','$dir','$off','$rpp','$fds','$search','$box','$where');\" class=\"btn btn-primary\">Next</button>";
    } else {
        echo "&emsp;<button class=\"btn btn-default disabled\">Next</button>";
    }
}


function paging_values_hidden($where, $offset, $rpp, $orderby, $dir, $search, $func, $page_no = 1)
{
    $vals = "";
    $vals .= "<input type='text' title='where' id='_where_' value='$where'>";
    $vals .= "<input type='text' title='offset' id='_offset_' value='$offset'>";
    $vals .= "<input type='text' title='rpp' id='_rpp_' value='$rpp'>";
    $vals .= "<input type='text' title='page_no' id='_page_no_' value='$page_no'>";
    $vals .= "<input type='text' title='orderby' id='_orderby_' value='$orderby'>";
    $vals .= "<input type='text' title='dir' id='_dir_' value='$dir'>";
    $vals .= "<input type='text' title='search' id='_search_' value='$search'>";
    $vals .= "<input type='text' title='func' id='_func_' value='$func()'>";

    return $vals;
}

function paging_values_hidden2($where, $offset, $rpp, $orderby, $dir, $search, $func, $sort, $page_no = 1)
{
    $vals = "";
    $vals .= "<input type='text' title='where' id='_where_' value='$where'>";
    $vals .= "<input type='text' title='offset' id='_offset_' value='$offset'>";
    $vals .= "<input type='text' title='rpp' id='_rpp_' value='$rpp'>";
    $vals .= "<input type='text' title='page_no' id='_page_no_' value='$page_no'>";
    $vals .= "<input type='text' title='orderby' id='_orderby_' value='$orderby'>";
    $vals .= "<input type='text' title='dir' id='_dir_' value='$dir'>";
    $vals .= "<input type='text' title='search' id='_search_' value='$search'>";
    $vals .= "<input type='text' title='func' id='_func_' value='$func()'>";
    $vals .= "<input type='text' title='sort' id='_sort_' value='$sort'>";

    return $vals;
}

function paging_values_hidden3($where, $offset, $rpp, $orderby, $dir, $search, $func, $sort, $page_no = 1)
{
    $vals = "";
    $vals .= "<input type='text' title='where' id='_where_' value='$where'>";
    $vals .= "<input type='text' title='offset' id='_offset_' value='$offset'>";
    $vals .= "<input type='text' title='rpp' id='_rpp_' value='$rpp'>";
    $vals .= "<input type='text' title='page_no' id='_page_no_' value='$page_no'>";
    $vals .= "<input type='text' title='orderby' id='_orderby_' value='$orderby'>";
    $vals .= "<input type='text' title='dir' id='_dir_' value='$dir'>";
    $vals .= "<input type='text' title='search' id='_search_' value='$search'>";

    // Pass the function as a string and embed the argument dynamically
    $vals .= "<input type='text' title='func' id='_func_' value=\"$func\">";

    $vals .= "<input type='text' title='sort' id='_sort_' value='$sort'>";

    return $vals;
}

function payment_schedule($loanid)
{


    $loand = fetchonerow('s_loans', "uid='$loanid'");
    $customer_id = $loand['customer_id'];
    $given_date = $loand['given_date'];
    $due_date = $loand['due_date'];
    $loan_amount = $loand['loan_amount'];
    $loan_total = $loand['loan_total'];
    $loan_product = $loand['loan_product'];

    $prod = fetchonerow('s_loan_products', "uid='$loan_product'");
    $product_name = $prod['product_name'];
    $loan_term = $prod['loan_term'];
    $payment_frequency = $prod['payment_frequency'];
    $loan_term = $prod['loan_term'];
    if ($payment_frequency == 1) {
        $pf = 7;
    } elseif ($payment_frequency == 2) {
        $pf = 30;
    }
    ///___Fetch the payment frequncy 1.e. 7 for weekly and 30 for Monthly
    echo "<h3 class=\"p-3 mb-2 bg-dark text-white\">Shapcare Credit</h3>";
    echo "<i>Repayment Schedule</i><br/>";
    echo "<strong><h4>Loan Ref: $loanid </h4></strong>";
    if ($payment_frequency == 0) {
        /////The product does not require frequent payment
        $datef = date("d-M-Y", strtotime($due_date));
        echo "Pay <strong>$loan_total</strong> not later than  <strong>$datef</strong> ";
    } else {
        $days = floor($loan_term / $pf);
        $periodic_pay = ceil($loan_total / $days);
        for ($i = 1; $i <= $days; ++$i) {
            $day = $i * $pf;
            $date = dateadd($given_date, 0, 0, $day);
            $datef = date("d-M-Y", strtotime($date));
            echo "Pay <strong>.$periodic_pay</strong> &emsp; By <strong>$datef</strong><br/>";
        }
    }
}



///__________Function to check customer balance
function customer_balance($cid)
{
    $last_loan = fetchmaxid('s_loans', "customer_id='$cid' AND status in (2,5,6)", "uid");
    $lid = $last_loan['uid'];
    if ($lid > 0) {
        $loan = new loan_details($lid);
        // $amount_paid = $loan -> total_repaid;
        // $last_payment_date = $loan -> last_repayment_date();
        $balance = $loan->loan_balance();
        return $balance;
    } else {
        return 0;
    }
}



function sms_service($loan_id, $instant = 0)
{
    global $fulldate;
    global $date;
    $loan_d = fetchonerow('o_loans', "uid='$loan_id'", "uid, customer_id, given_date ,account_number, product_id, status");
    if ($loan_d['uid'] > 0) {
        $customer_id = $loan_d['customer_id'];
        $account_number = $loan_d['account_number'];
        $product_id = $loan_d['product_id'];
        $given_date = $loan_d['given_date'];
        $loan_status = $loan_d['status'];
        $loan_day = datediff($given_date, $date);


        $reminders = fetchonerow('o_product_reminders', "product_id='$product_id' AND loan_day='$loan_day' AND status=1", "uid, loan_status, message_body");
        $r_loan_status = $reminders['loan_status'];

        if ($reminders['uid'] > 0 && $r_loan_status == $loan_status) {
            ////////------------We have a matching reminder message
            $message_ = $reminders['message_body'];
            $conv = convert_message($message_, 0);
            return "Send $conv ";
        } else {
            return "No matching message for status loan: $loan_status, day $loan_day, product $product_id";
        }
    } else {
        return "Loan ID is already deleted";
    }
}

/*
$pay_state = 'PARTIAL_PAYMENT';
product_notify($product_id, 0, $pay_state, 0, $latest_loan_id, $account_number);
*/
function product_notify($product_id, $loan_day, $custom_event, $loan_status, $loan_id, $mobile_number)
{
    $message = fetchonerow('o_product_reminders', "(product_id='$product_id' OR product_id='0') AND loan_day='$loan_day' AND custom_event='$custom_event' AND (loan_status='$loan_status') AND status=1", "uid, message_body");
    $message_uid = $message['uid'];
    if ($message_uid > 0) {
        $message_body = $message['message_body'];
        $message_body_conv = convert_message($message_body, $loan_id);
        $q = queue_message($message_body_conv, $mobile_number);
    }
}
function convert_message($message, $loan_id)
{
    global  $sms_by_firstname;
    $loans = fetchonerow('o_loans', "uid='$loan_id'", "*");
    $customers = fetchonerow('o_customers', "uid='" . $loans['customer_id'] . "'", "*");
    $variables = extract_words_between('{', '}', $message);
    if (sizeof($variables) > 0) {
        foreach ($variables as $value) {
            $rec = explode('.', $value);
            $table = $rec[0];
            $field = $rec[1];

            if ('o_' . $table == 'o_loans') {
                $final_value = $loans[$field];
            } else if ('o_' . $table == 'o_customers') {


                $final_value = $customers[$field];
                // check if contains full_name so that we grap the firstname only
                if ($field == 'full_name' && strpos($final_value, ' ') !== false) {
                    $final_value = trim(explode(' ', $final_value)[0]);
                }
            }

            $message = str_replace("$table.$field", $final_value, $message);
        }
        ///----Remove quotes
        $message = str_replace('{', '', $message);
        $message = str_replace('}', '', $message);
        return $message;
    } else {
        return $message;
    }
}

function convert_message_offline($message, $loans, $customers)
{
    // $loans = fetchonerow('o_loans',"uid='$loan_id'","*");
    // $customers = fetchonerow('o_customers',"uid='".$loans['customer_id']."'","*");

    $variables = extract_words_between('{', '}', $message);
    if (sizeof($variables) > 0) {
        foreach ($variables as $value) {
            $rec = explode('.', $value);
            $table = $rec[0];
            $field = $rec[1];

            if ('o_' . $table == 'o_loans') {
                $final_value = $loans[$field];
            } else if ('o_' . $table == 'o_customers') {
                $final_value = $customers[$field];
                // check if contains full_name so that we grap the firstname only
                if ($field == 'full_name' && strpos($final_value, ' ') !== false) {
                    $final_value = trim(explode(' ', $final_value)[0]);
                }
            }

            $message = str_replace("$table.$field", $final_value, $message);
        }
        ///----Remove quotes
        $message = str_replace('{', '', $message);
        $message = str_replace('}', '', $message);
        return $message;
    } else {
        return $message;
    }
}
function queue_message($message, $phone, $created_by = 0, $source_tbl = '', $source_record = 0, $status = 1)
{
    global $fulldate;
    $fds = array("phone", "message_body", "queued_date", "created_by", "source_tbl", "source_record", "status");
    $vals = array("$phone", "$message", "$fulldate", "$created_by", "$source_tbl", "$source_record", "$status");
    $create = addtodb("o_sms_outgoing", $fds, $vals);
    return $create;
}


function queue_bulk_messages($messages, $batchSize = 1000)
{
    global $con; // Assuming $con is your database connection
    global $fulldate;

    $totalInserted = 0;

    // Split the messages into chunks of the specified batch size
    $batches = array_chunk($messages, $batchSize);


    // echo "Total batches: " . count($batches) . "<br>";

    foreach ($batches as $batch) {

        // echo "Processing batch of " . count($batch) . " rows <br>";

        $values = [];

        foreach ($batch as $msg) {
            // Escape and prepare each field for insertion
            $phone = mysqli_real_escape_string($con, $msg['phone']);
            $message_body = mysqli_real_escape_string($con, $msg['message_body']);
            $message_type = $msg['message_type'] ? $msg['message_type'] : 'PERSONALIZED';
            $queued_date = $msg['queued_date'] ?? $fulldate;
            $created_by = intval($msg['created_by'] ?? 0);
            $source_tbl = mysqli_real_escape_string($con, $msg['source_tbl'] ?? '');
            $source_record = intval($msg['source_record'] ?? 0);
            $status = intval($msg['status'] ?? 1);

            $values[] = "('$phone', '$message_body', '$message_type', '$queued_date', $created_by, '$source_tbl', $source_record, $status)";
        }

        // Build and execute the bulk insert query for the current batch
        $query = "INSERT INTO o_sms_outgoing (phone, message_body, message_type, queued_date, created_by, source_tbl, source_record, status) VALUES " . implode(", ", $values);
        $result = mysqli_query($con, $query);

        if (!$result) {
            // Handle error if needed, such as rolling back
            die("Error in bulk insert for batch: " . mysqli_error($con));
        }

        // echo "Inserted $batchSize rows in this batch <br>";

        // Count the inserted rows in this batch
        $totalInserted += mysqli_affected_rows($con);
    }

    return $totalInserted;  // Return the total number of inserted rows across all batches
}


function datediff3($startdate, $enddate) ///////////////////plain date
{
    $sfdate = strtotime($startdate);
    $sldate = strtotime($enddate);
    $diff = strtotime($enddate) - strtotime($startdate);

    if ($diff < 0) {
        $diff = strtotime($startdate) - strtotime($enddate);
        $m = '-';
    } else {
        $m = '';
        //  echo "[+]";
        // $late=0; $ico='bomb.png'; $color='orange';
    }

    // immediately convert to days
    $temp = $diff / 86400; // 60 sec/min*60 min/hr*24 hr/day=86400 sec/day
    // days
    $days = floor($temp);
    $temp = 24 * ($temp - $days);
    // hours
    $hours = floor($temp);
    $temp = 60 * ($temp - $hours);
    // minutes
    $minutes = floor($temp);
    $temp = 60 * ($temp - $minutes);
    // seconds
    $seconds = floor($temp);


    //return "$days*$hours*$minutes";

    return intval($days);
}

function datediff($startdate, $enddate) ///////////////////plain date
{
    $sfdate = strtotime($startdate);
    $sldate = strtotime($enddate);
    $diff = strtotime($enddate) - strtotime($startdate);

    if ($diff < 0) {
        $diff = strtotime($startdate) - strtotime($enddate);
        $m = '-';
    } else {
        $m = '';
        //  echo "[+]";
        // $late=0; $ico='bomb.png'; $color='orange';
    }

    // immediately convert to days
    $temp = $diff / 86400; // 60 sec/min*60 min/hr*24 hr/day=86400 sec/day
    // days
    $days = floor($temp);

    /*$temp = 24 * ($temp - $days);

    // hours
    $hours = floor($temp);

    $temp = 60 * ($temp - $hours);

    // minutes
    $minutes = floor($temp);

    $temp = 60 * ($temp - $minutes);
    // seconds

    $seconds = floor($temp);


    //return "$days*$hours*$minutes";
    */

    return intval($m . $days);
}
function timeDiff($datetime1, $datetime2)
{
    // Convert datetime strings to DateTime objects
    $date1 = new DateTime($datetime1);
    $date2 = new DateTime($datetime2);

    // Calculate the difference in seconds
    $interval = $date1->diff($date2);
    $differenceInSeconds = $interval->s + ($interval->i * 60) + ($interval->h * 3600) + ($interval->d * 86400) + ($interval->m * 2629746) + ($interval->y * 31556952);

    return $differenceInSeconds;
}

function isDateInRange($checkDate, $startDate, $endDate)
{
    // Convert input dates to timestamps
    $startTimestamp = strtotime($startDate);
    $endTimestamp = strtotime($endDate);
    $checkTimestamp = strtotime($checkDate);

    // Check if the check date is between the start and end dates
    return ($startTimestamp <= $checkTimestamp && $checkTimestamp <= $endTimestamp);
}

function date_greater($first, $last)
{
    $curdate = strtotime($first);
    $mydate = strtotime($last);

    if ($curdate > $mydate) {
        return $mydate;
    } else {
        return 0;
    }
}


function money($num)
{
    return number_format($num, 2, ".", ",");
}


function generateToken($userid, $device_id, $browser_name, $IPAddress, $OS)
{
    global $fulldate;
    global $one_session; ////Login to only one session

    $userid = intval($userid);

    // echo "User ID: $userid <br> Device ID: $device_id <br> Browser: $browser_name <br> IP: $IPAddress <br> OS: $OS <br> Date: $fulldate <br>";

    $token_expiry = dateadd($fulldate, 0, 0, 30); ///one month
    /////Remove other tokens for the user
    if ($one_session == 1) {
        $cleartokens = updatedb('o_tokens', "status=2, expiry_date='$fulldate'", "userid=$userid AND status=1");
    }
    /// Create new token
    $token = generateRandomString(64);

    // echo "Token: $token";
    $fds = array("userid", "token", "creation_date", "expiry_date", "device_id", "browsername", "IPAddress", "OS", "status");
    $vals = array($userid, "$token", "$fulldate", "$token_expiry", "$device_id", "$browser_name", "$IPAddress", "$OS", "1");

    // echo "vals";
    // var_dump($vals);

    $create = addtodb("o_tokens", $fds, $vals);
    if ($create == 1) {
        /// return token
        return $token;
    } else {
        return 0;
    }
}

function arrow_back($backto, $title)
{
    return "<a style='margin-right: 15px;' href='$backto' title='Back to $backto' class='text-blue font-16'>
    <i class='fa fa-reply'></i> Back to $title</a>";
}
function status($state)
{
    if ($state == 0) {
        return "<label class='label label-danger'> Inactive</label>";
    } else if ($state == 1) {
        return "<label class='label label-success'> Active</label>";
    }
}
function yesno($state)
{
    if ($state == 0) {
        return "NO";
    } else {
        return "YES";
    }
}



function deduction_exists($did, $pid)
{
    $deduction_exists = checkrowexists("o_product_deductions", "deduction_id='$did' AND product_id='$pid' AND status = 1");
    if ($deduction_exists == 1) {
        return "<a onclick=\"product_deduction_save($pid, $did, 'REMOVE')\" title='Click to Remove' class=\"text-success pointer\"><i class=\"fa fa-check\"></i> Added </a>";
    } else {
        return "<a onclick=\"product_deduction_save($pid, $did, 'ADD')\" title='Click to Add' class=\"text-primary pointer\"><i class=\"fa fa-times-circle\"></i> Not Added </a>";
    }
}

function addon_exists($aid, $pid)
{
    $deduction_exists = checkrowexists("o_product_addons", "addon_id='$aid' AND product_id='$pid' AND status > 0");
    if ($deduction_exists == 1) {
        return "<a onclick=\"product_addon_save($pid, $aid, 'REMOVE')\" title='Click to Remove' class=\"text-success pointer\"><i class=\"fa fa-check\"></i> Added </a>";
    } else {
        return "<a onclick=\"product_addon_save($pid, $aid, 'ADD')\" title='Click to Add' class=\"text-primary pointer\"><i class=\"fa fa-times-circle\"></i> Not Added </a>";
    }
}

function stage_exists($did, $pid)
{
    $stage_exists = checkrowexists("o_product_stages", "stage_id='$did' AND product_id='$pid' AND status = 1");
    if ($stage_exists == 1) {
        return "<a onclick=\"product_stage_save($pid, $did, 'REMOVE')\" title='Click to Remove' class=\"text-success pointer\"><i class=\"fa fa-check\"></i> Added </a>";
    } else {
        return "<a onclick=\"product_stage_save($pid, $did, 'ADD')\" title='Click to Add' class=\"text-primary pointer\"><i class=\"fa fa-times-circle\"></i> Not Added </a>";
    }
}




///////////----------------Loan Calculations

function total_instalments($period, $period_units, $payment_frequency)
{
    if ($payment_frequency > 0) {
        $total_instalments = round((($period * $period_units) / $payment_frequency), 0);
    } else {
        $total_instalments = 1;
    }
    return $total_instalments;
}

function final_due_date($given_date, $period, $period_units)
{
    $total_days = $period * $period_units;
    $final_day = dateadd($given_date, 0, 0, $total_days);
    return $final_day;
}

function next_due_date($given_date, $period, $period_units, $payment_frequency)
{
    $total_days = $period * $period_units;

    if ($payment_frequency > 0) {
        $next_due = dateadd($given_date, 0, 0, $payment_frequency);
    } else {
        $next_due = dateadd($given_date, 0, 0, $total_days);
    }
    return $next_due;
}

function total_repaid($loan_id)
{
    $total_pay = totaltable('o_incoming_payments', "loan_id='$loan_id' AND status=1", "amount");
    return $total_pay;
}

function total_repaid_archive($loan_id)
{
    $total_pay = totaltable_archive('o_incoming_payments', "loan_id='$loan_id' AND status=1", "amount");
    return $total_pay;
}

function loan_balance($loan_id)
{

    // handle case of loan_id = 0
    if (intval($loan_id) === 0) {
        return 0;
    }

    $loan = fetchonerow("o_loans", "uid='" . $loan_id . "'", "total_repayable_amount");

    $repayable_amount = $loan['total_repayable_amount'];
    // a search for a load id which does not exist
    if ($repayable_amount === null) {
        return 0;
    }

    $repaid = total_repaid($loan_id);

    $balance = $repayable_amount - $repaid;
    updatedb("o_loans", "loan_balance = $balance, total_repaid = $repaid", "uid=$loan_id AND status > 0");
    return $balance;
}

/* function loan_balance($loan_id)
{
    global $date;
    $repaid = total_repaid($loan_id);
    $loan =  fetchonerow("o_loans", "uid='" . $loan_id . "'", "total_repayable_amount");
    $repayable_amount = $loan['total_repayable_amount'];

    $balance = $repayable_amount - $repaid;
    updatedb("o_loans", "loan_balance=$balance", "uid=$loan_id AND status > 0");


    if ($balance <= 0) {
        updatedb('o_loans', "status='5', paid=1", "uid='$loan_id'");
    } else {
        $row = fetchonerow("o_loans", "uid = $loan_id", "final_due_date");
        $final_due_date = $row['final_due_date'] ?? '0000-00-00';
        // $total_repaid = doubleval($row['total_repaid']);

        if (new DateTime($date) > new DateTime($final_due_date)) {
            updatedb('o_loans', "status='7', paid=0", "uid='$loan_id'");
        } else {
            // if ($total_repaid > 0) {
            //     updatedb('o_loans', "status='4', paid=0", "uid='$loan_id'");
            // } else {
            //     updatedb('o_loans', "status='3', paid=0", "uid='$loan_id'");
            // }

            updatedb('o_loans', "status='3', paid=0", "uid='$loan_id'");
        }
    }

    return $balance;
}
*/

/// //////////////=============End of loan calculations
function logupdate($table, $query)
{
    /* global $fulldate;
    $userd = session_details();
    $user = $userd['uid'];
    //////Save the log in the o_logs_update table
    $fds = array('tbl_','query_','change_by','change_date','status');
    $vals = array("$table", "".addslashes($query)."","$user","$fulldate","1");
    $addtologs = addtodb('o_changelog',$fds, $vals);
   // echo "[[$addtologs]]";  */
}
function leading_zero($number)
{
    if ($number < 10) {
        return '0' . $number;
    } else {
        return $number;
    }
}
function month_name($month_number)
{
    $dateObj = DateTime::createFromFormat('!m', $month_number);
    $monthName = $dateObj->format('F'); // March
    return $monthName;
}
function send_sms_bulk($mobile_number, $message, $linkId = 0)
{
    global $fulldate;
    global $sms_provider;

    if ($sms_provider == 'DIGIVAS') {
        return send_via_digivas($mobile_number, $message, $linkId);
        /*
        global $digivas_configs;
        $client_id = $digivas_configs['clientId'];
        $product_id = $digivas_configs['productId'];
        $bearer = $digivas_configs['Bearer'];

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.digivas.co.ke/vas/api/Bulk_SMS',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS =>'{
        "unique_ref":"'.$linkId.'",
        "clientId":"'.$client_id.'",
        "dlrEndpoint":"https://example.com/test",
        "productId":"'.$product_id.'",
        "msisdn":"'.$mobile_number.'",
        "message":"'.$message.'"
        }',
            CURLOPT_HTTPHEADER => array(
                'Authorization: Bearer '.$bearer.'',
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);

        $data = json_decode($response, true);
        $credits = $data['credits'];


        curl_close($curl);
        ////----Returning balance
        return $credits;
        */
    } else {
        ///////---------Default is Africa's talking
        $fmessage = urlencode($message);
        $curl = curl_init();
        $bulk_code = fetchrow('o_sms_settings', "property_name='BULK_CODE'", "property_value");
        $username = fetchrow('o_sms_settings', "property_name='USERNAME'", "property_value");
        $apiKey = fetchrow('o_sms_settings', "property_name='AFT_BULK_KEY'", "property_value");

        if (input_length($bulk_code, 3) == 1 && input_length($username, 3) == 1) {
            // $apiKey = 'cc1e8a7a26e73da5414d2d4a1cdc7927bfbf0bda9876d3f883e70d0b1a2e4f30';

            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://api.africastalking.com/version1/messaging',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 10,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => "username=$username&from=$bulk_code&message=$fmessage&to=$mobile_number",
                CURLOPT_HTTPHEADER => array(
                    'apiKey: ' . $apiKey,
                    'Content-Type: application/x-www-form-urlencoded',
                    'Accept: application/json'
                ),
            ));

            $response = curl_exec($curl);

            curl_close($curl);
            return $response;
        } else {
            return "NO SETTINGS AVAILABLE";
        }
    }
}
function bulk_sms_balance()
{
    $curl = curl_init();

    $username = fetchrow('o_sms_settings', "property_name='USERNAME'", "property_value");
    $apiKey = fetchrow('o_sms_settings', "property_name='AFT_BULK_KEY'", "property_value");

    if (input_length($username, 3) == 1) {
        //  $apiKey = 'cc1e8a7a26e73da5414d2d4a1cdc7927bfbf0bda9876d3f883e70d0b1a2e4f30';


        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.africastalking.com/version1/user?username=' . $username,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'apiKey:' . $apiKey
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return $response;
    } else {
        return "NO  SETTINGS";
    }
}

function send_sms_interactive($mobile_number, $message, $linkId = 0)
{

    global $fulldate;
    $curl = curl_init();
    $bulk_code = fetchrow('o_sms_settings', "property_name='SHORT_CODE'", "property_value");
    $username = fetchrow('o_sms_settings', "property_name='SHORT_CODE_USERNAME'", "property_value");
    $apiKey = fetchrow('o_sms_settings', "property_name='AFT_2WAY_KEY'", "property_value");
    $keyword = fetchrow('o_sms_settings', "property_name='AFT_2WAY_KEYWORD'", "property_value");
    if (input_available($keyword) == 0) {
        $keyword = $username;
    }

    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://content.africastalking.com/version1/messaging',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => array('username' => '' . $username . '', 'to' => '' . $mobile_number . '', 'message' => '' . $message . '', 'from' => '' . $bulk_code . '', 'bulkSMSMode ' => '0', 'keyword' => '' . $keyword . '', 'linkId' => '' . $linkId . ''),
        CURLOPT_HTTPHEADER => array(
            'Apikey: ' . $apiKey . ''
        ),
    ));

    $response = curl_exec($curl);

    curl_close($curl);

    return $response;
}

function send_sms_interactive2($mobile_number, $message, $linkId = 0)
{

    global $fulldate;
    $curl = curl_init();
    $bulk_code = fetchrow('o_sms_settings', "property_name='SHORT_CODE'", "property_value");
    $username = fetchrow('o_sms_settings', "property_name='SHORT_CODE_USERNAME'", "property_value");
    $apiKey = fetchrow('o_sms_settings', "property_name='AFT_2WAY_KEY'", "property_value");
    $keyword = fetchrow('o_sms_settings', "property_name='AFT_2WAY_KEYWORD'", "property_value");
    if (input_available($keyword) == 0) {
        $keyword = $username;
    }

    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://api.africastalking.com/version1/messaging',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => array('username' => '' . $username . '', 'to' => '' . $mobile_number . '', 'message' => '' . $message . '', 'from' => '' . $bulk_code . '', 'bulkSMSMode ' => '0', 'keyword' => '' . $keyword . '', 'linkId' => '' . $linkId . ''),
        CURLOPT_HTTPHEADER => array(
            'Apikey: ' . $apiKey . ''
        ),
    ));

    $response = curl_exec($curl);

    curl_close($curl);

    return $response;
}

function send_money($msisdn, $amount, $loan_id = 0)
{

    global $fulldate;
    global $server2;
    global $api_server;
    global $sl_key;

    if ((input_length($api_server, 5)) == 0) {
        $server = $server2;
    } else {
        $server = $api_server;
    }
    $platform = company_settings();
    $company_id = $platform['company_id'];
    if ($loan_id > 0) {
        ////////-------------If loan ID is provided, check if loan has already been disbursed
        $loan_state = fetchonerow('o_loans', "uid='$loan_id'", "disburse_state, customer_id");
        $disbursed_state = $loan_state['disburse_state'];
        $customer_id = $loan_state['customer_id'];

        if ($disbursed_state != 'NONE') {
            return "Loan already disbursed!";
        } else {
            // $upd = updatedb('o_loans',"disburse_state='INITIATED'","uid='$loan_id'");
            ///---Proceed
        }
    }
    //echo $server;
    //die();
    $consumerKey = fetchrow('o_mpesa_configs', "uid='4'", "property_value");
    $consumerSecret = fetchrow('o_mpesa_configs', "uid='5'", "property_value");

    $headers = ['Content-Type:application/json; charset=utf8'];
    $url = 'https://api.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    curl_setopt($curl, CURLOPT_HEADER, false);

    curl_setopt($curl, CURLOPT_USERPWD, $consumerKey . ':' . $consumerSecret);
    $result = curl_exec($curl);
    $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $result = json_decode($result);
    $access_token = $result->access_token;


    $url = 'https://api.safaricom.co.ke/mpesa/b2c/v3/paymentrequest'; //url b2c v3

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type:application/json', 'Authorization:Bearer ' . $access_token)); //setting custom header

    $InitiatorName = fetchrow('o_mpesa_configs', "uid='7'", "property_value");
    $SecurityCredential_enc = fetchrow('o_mpesa_configs', "uid='3'", "property_value"); /// Its encrypted now
    $SecurityCredential = decryptStringSecure($SecurityCredential_enc, $sl_key);
    $Amount = $amount;
    $PartyA = fetchrow('o_mpesa_configs', "uid='1'", "property_value");
    $PartyB = $msisdn;
    $Remarks = "L$loan_id";
    $QueueTimeOutURL = $server . '/apis/mpesa_queue_timeout';
    $ResultURL = $server . '/apis/b2c-notice?c=' . $company_id . '&r=' . $loan_id;
    $Occasion = '';
    $OriginatorConversationID = uuid_gen();

    if ($loan_id > 0) {
        updatedb('o_loans', "transaction_date='$fulldate', disburse_state='INITIATED', disbursed=1", "uid='$loan_id'");
        $total_loans = total_customer_loans($customer_id);
    }

    $curl_post_data = array(
        //Fill in the request parameters with valid values
        'OriginatorConversationID' => $OriginatorConversationID,
        'InitiatorName' => $InitiatorName, //This is the credential/username used to authenticate the transaction request.
        'SecurityCredential' => $SecurityCredential, //Base64 encoded string of the B2C short code and password,
        'CommandID' => 'PromotionPayment', //Unique command for each transaction type
        'Amount' => $Amount, //The amount being transacted
        'PartyA' => $PartyA, //Organization’s shortcode initiating the transaction.
        'PartyB' => $PartyB, //Phone number receiving the transaction
        'Remarks' => $Remarks, //Comments that are sent along with the transaction.
        'QueueTimeOutURL' => $QueueTimeOutURL, //The timeout end-point that receives a timeout response.
        'ResultURL' => $ResultURL, //The end-point that receives the response of the transaction
        'Occasion' => $Occasion //optional
    );

    $data_string = json_encode($curl_post_data);

    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);

    $curl_response = curl_exec($curl);
    print_r($curl_response . $ResultURL);

    $json = $curl_response;
    $j = json_decode($curl_response);
    $state = $j->ResponseCode;
    $desc = $j->ResponseDescription;
    $errorMessage = $j->errorMessage;

    if ($state == '0') {
        //  $more_comment = $current_comment . "<br/>Loan processing started on $fulldate  ";
        //$update_loan = updatedb('s_loans', "disburse_state='', comments='$more_comment'", "uid='$uid'");
        if ($loan_id > 0) {
            updatedb('o_loans', "disburse_state='INITIATED', disbursed=1", "uid='$loan_id'");
        }
    } else {

        //  $more_comment = $current_comment . "<br/>Error! $errorMessage $fulldate ";
        //  $update_loan = updatedb('s_loans', "response_code='$state', comments='$more_comment'", "uid='$uid'");
        //  echo $update_loan;
        if ($loan_id > 0) {
            updatedb('o_loans', "disburse_state='FAILED', disbursed=0", "uid='$loan_id'");
        }
    }
    if ($loan_id > 0) {
        if (input_length($errorMessage, 5) == 1) {
            $errors = ",Errors: $errorMessage";
        } else {
            $errors = "No errors";
        }
        store_event('o_loans', $loan_id, "API Request started on $fulldate with result-> State: $state, ConversationID: $OriginatorConversationID, Desc: $desc, $errors. Result sent to $ResultURL");
    }
    return "$state,  $desc, $errorMessage";
}

function b2b($from, $to, $amount)
{
    global $fulldate;
    global $server2;
    global $api_server;

    if ((input_length($api_server, 5)) == 0) {
        $server = $server2;
    } else {
        $server = $api_server;
    }

    $properties = array();
    $configs = fetchtable('o_mpesa_configs', "status=1", "uid", "asc", "100", "property_name, property_value");
    while ($co = mysqli_fetch_array($configs)) {
        $pname = $co['property_name'];
        $pvalue = $co['property_value'];
        $properties[$pname] = $pvalue;
    }
    $consumerKey = $properties['b2c_consumerKey'];
    $consumerSecret = $properties['b2c_consumerSecret'];
    $InitiatorName = $properties['b2c_InitiatorName'];
    $SecurityCredential = $properties['SecurityCredential'];

    $headers = ['Content-Type:application/json; charset=utf8'];
    $url = 'https://api.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    curl_setopt($curl, CURLOPT_HEADER, false);

    curl_setopt($curl, CURLOPT_USERPWD, $consumerKey . ':' . $consumerSecret);
    $result = curl_exec($curl);
    $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $result = json_decode($result);
    $access_token = $result->access_token;
    // echo 'The access token is: ' . $access_token;


    /////---------------------------------------------------------------New
    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://api.safaricom.co.ke/mpesa/b2b/v1/paymentrequest',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => '{
    "Initiator": "TENAKATAB2BAPI",
    "SecurityCredential": "kQEzZ/PtFfHxboxB1dGnsNz/NN6MPd4lMJgjzeF1t22+d+7gUjWRqyb9v79zPzVkO7rbtIDlOzXfOgG/Jn1dzXEOkc7ACCC4/vBsf2pXU/AvDIsomxmzArleOcoOwOr64qd432+Mne6fkg9TQSpD9I1GtdzZ6s5liN4dl03VWz4QRcqu3GWqDOa6mzJsnnOjgQuhbO88AqPjvEmDPVh147tDsQ4hiyAEo0VQk44e2mYcOdpDZNv0wRULGsoTLHpIms0WXwxfblUrv/QIds25X2NJg8H9TTPvEjouiz9H+d2sy7FB9n4+T2E/DfvSrRSgiGuNfuS+1UBwbzR5jnF5+g==",
    "CommandID": "BusinessBuyGoods",
    "SenderIdentifierType": "4",
    "RecieverIdentifierType": "2",
    "Amount": "' . $amount . '",
    "PartyA": "' . $from . '",
    "PartyB": "' . $to . '",
    "AccountReference": "1234",
    "Remarks": "Business",
    "QueueTimeOutURL": "https://tenakata.superlender.co.ke/apis/b2b-notice",
    "ResultURL": "https://tenakata.superlender.co.ke/apis/b2b-notice"
}',
        CURLOPT_HTTPHEADER => array(
            'Authorization: Bearer ' . $access_token . '',
            'Content-Type: application/json',
            'Cookie: visid_incap_2742146=vq5iSKAMT8WfVLKKU8Qg5Uu7zGUAAAAAQUIPAAAAAACGgz+aeg9w97OywzZju444'
        ),
    ));

    $response = curl_exec($curl);

    curl_close($curl);

    return $response;
    //////////////////////----------------------------
}

function b2b_v2($from, $to, $amount)
{
    global $fulldate;
    global $server2;
    global $api_server;

    // Step 1: Determine which server to use
    $server = (input_length($api_server, 5) == 0) ? $server2 : $api_server;

    // Step 2: Fetch MPESA configurations from the database
    $properties = array();
    $configs = fetchtable('o_mpesa_configs', "status=1", "uid", "asc", "100", "property_name, property_value");
    while ($co = mysqli_fetch_array($configs)) {
        $pname = $co['property_name'];
        $pvalue = $co['property_value'];
        $properties[$pname] = $pvalue;
    }

    // Ensure required properties are present
    if (!isset($properties['b2c_consumerKey'], $properties['b2c_consumerSecret'], $properties['b2c_InitiatorName'], $properties['SecurityCredential'])) {
        echo "Required MPESA properties are missing.";
        return false;
    }

    // Step 3: Generate Access Token
    $consumerKey = $properties['b2c_consumerKey'];
    $consumerSecret = $properties['b2c_consumerSecret'];
    $headers = ['Content-Type:application/json; charset=utf8'];
    $url = 'https://api.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HEADER, false);
    curl_setopt($curl, CURLOPT_USERPWD, $consumerKey . ':' . $consumerSecret);
    $result = curl_exec($curl);

    if (curl_errno($curl)) {
        echo 'cURL error while fetching access token: ' . curl_error($curl);
        curl_close($curl);
        return false;
    }

    $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);

    if ($status !== 200) {
        echo ("Failed to fetch access token. HTTP Status: $status. Response: $result");
        return false;
    }

    $result = json_decode($result);
    $access_token = $result->access_token ?? false;
    if (!$access_token) {
        echo ("Access token not found in the response.");
        return false;
    }

    // Step 4: Prepare the B2B request body
    $InitiatorName = $properties['b2c_InitiatorName'];
    $SecurityCredential = $properties['SecurityCredential'];
    $ResultURL = $server . '/apis/b2b-notice';
    $QueueTimeOutURL = $server . '/apis/b2b-notice';

    $body = [
        "Initiator" => $InitiatorName,
        "SecurityCredential" => $SecurityCredential,
        "CommandID" => "BusinessBuyGoods",
        "SenderIdentifierType" => "4",
        "RecieverIdentifierType" => "2",
        "Amount" => $amount,
        "PartyA" => $from,
        "PartyB" => $to,
        "AccountReference" => "1234",
        "Remarks" => "Business",
        "QueueTimeOutURL" => "$QueueTimeOutURL",
        "ResultURL" => "$ResultURL"
    ];

    // Step 5: Send the B2B request
    $url = 'https://api.safaricom.co.ke/mpesa/b2b/v1/paymentrequest';
    $headers = [
        'Authorization: Bearer ' . $access_token,
        'Content-Type: application/json'
    ];

    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => json_encode($body),
        CURLOPT_HTTPHEADER => $headers,
    ]);

    $response = curl_exec($curl);
    $err = curl_error($curl);

    if ($err) {
        echo ("cURL Error: " . $err);
        curl_close($curl);
        return false;
    }

    // $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    // curl_close($curl);

    // if ($status !== 200 && $status !== 201) {
    //     echo("B2B Request failed. HTTP Status: $status. Response: $response");
    //     return false;
    // }

    return $response;
}


function total_archive_loans($cid)
{
    $total_loans = totaltable_archive('o_loans', "customer_id=$cid AND disbursed=1 AND paid=1 AND status!=0", "uid");
    return $total_loans;
}

function login_to_archives($userid, $token)
{
    try {

        global $con2, $fulldate;

        $token_expiry = dateadd($fulldate, 0, 0, 30); // One month
        $fds = array("userid", "token", "creation_date", "expiry_date", "device_id", "browsername", "IPAddress", "OS", "status");
        $vals = array($userid, "$token", "$fulldate", "$token_expiry", "0", "0", "0", "0", "1");

        $fields = implode(',', $fds);
        $values = implode("','", array_map(function ($val) use ($con2) {
            return mysqli_real_escape_string($con2, trim($val));
        }, $vals));
        $values = "'$values'";

        $insertq = "INSERT INTO o_tokens ($fields) VALUES ($values)";

        if (!mysqli_query($con2, $insertq)) {
            throw new Exception(mysqli_error($con2));
        } else {
            $_SESSION['o-token'] = $token;
            return 1;
        }
    } catch (Exception $e) {
        // Handle query execution error
        // echo "Query execution error: " . $e->getMessage();
        return false;
    }
}


function queue_money($msisdn, $customer_id, $amount, $loan_id, $added_by)
{
    /////----Add loan to queue
    global $fulldate;

    $update_ = updatedb('o_loans', "status=2", "uid='$loan_id'");
    $fds = array('loan_id', 'amount', 'added_date', 'trials', 'status');
    $vals = array("$loan_id", "$amount", "$fulldate", '0', '1');
    $queue = addtodb('o_mpesa_queues', $fds, $vals);

    return $queue;
}

function send_stk($phone, $amount, $account = "")
{
    if ($amount > 5) {
        $amount = floor($amount);
    } else {

        return errormes("Amount is invalid");
    }
    if (validate_phone($phone) != 1) {
        return errormes("Phone is invalid");
    }

    $properties = array();
    $configs = fetchtable('o_mpesa_configs', "status=1", "uid", "asc", "100", "property_name,property_value");
    while ($co = mysqli_fetch_array($configs)) {
        $pname = $co['property_name'];
        $pvalue = $co['property_value'];
        $properties[$pname] = $pvalue;

        // echo $pvalue;
    }

    // return fetchrow('o_badges',"uid=1","uid").'kkk';


    // return "";
    $consumerKey = $properties['c2b_consumerKey'];
    $consumerSecret = $properties['c2b_consumerSecret'];
    //return;

    $shortcode = $properties['C2B_shortcode'];
    $Passkey = $properties['c2b_passkey'];
    $InitiatorName = $properties['c2b_InitiatorName'];
    $BusinessShortCode = $properties['c2b_parent'];
    if (input_available($BusinessShortCode) == 0) {
        $BusinessShortCode = $shortcode;
    }
    $c2b_type = $properties['c2b_type'];
    if ($c2b_type == 'TILL') {
        $ttype = 'CustomerBuyGoodsOnline';
    } else {
        $ttype = 'CustomerPayBillOnline';
    }

    $headers = ['Content-Type:application/json; charset=utf8'];
    $url = 'https://api.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    curl_setopt($curl, CURLOPT_HEADER, false);

    curl_setopt($curl, CURLOPT_USERPWD, $consumerKey . ':' . $consumerSecret);
    $result = curl_exec($curl);
    $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $result = json_decode($result);
    $access_token = $result->access_token;
    // echo 'The access token is: '.$access_token;

    $url = 'https://api.safaricom.co.ke/mpesa/stkpush/v1/processrequest'; //sktpush url
    // $BusinessShortCode = '7027754'; //shortcode
    // $Passkey = '342ccd64b04e612ea4630fbe2dfe02fd6938bf6c2eafbee5dee35963144a628c'; //passkey
    //  $InitiatorName = '' . JazaAPI . '';
    $Timestamp = '20' . date("ymdhis"); //timestamp
    $Password = base64_encode($BusinessShortCode . $Passkey . $Timestamp); //password encoded Base64



    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type:application/json', 'Authorization:Bearer ' . $access_token)); //setting custom header


    $curl_post_data = array(
        //Fill in the request parameters with valid values
        'BusinessShortCode' => $BusinessShortCode, //HQ/HO/Paybill.
        'InitiatorName' => $InitiatorName, //This is the credential/username used to authenticate the transaction request.
        'Password' => $Password, //This is generated by base64 encoding BusinessShortcode, Passkey and Timestamp.
        'Timestamp' => $Timestamp, //The timestamp of the transaction in the format yyyymmddhhiiss.
        'TransactionType' => $ttype, //The transaction type to be used for this request.
        'Amount' => $amount, //The amount to be transacted.
        'PartyA' => $phone, //The MSISDN sending the funds.
        'PartyB' => $shortcode, //The organization shortcode receiving the funds Till/Paybill
        'PhoneNumber' => $phone, //The MSISDN sending the funds.
        'CallBackURL' => 'https://www.superlender.co.ke/', //The url to where logs from M-Pesa will be sent to.
        'AccountReference' => $phone, //Used with M-Pesa PayBills.
        'TransactionDesc' => 'Pay' //A description of the transaction.
    );

    $data_string = json_encode($curl_post_data);

    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);

    $curl_response = curl_exec($curl);
    return $curl_response;
}

function reverse_b2c()
{
}
function reverse_c2b()
{
    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://api.safaricom.co.ke/mpesa/reversal/v1/request',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => '{
    "Initiator": "TENAKATAWEBAPI",
    "SecurityCredential": "BzFLlGkhDG5tIZrH8f2u5iAf9gZvQYbs+NxbC8tZPe/5V5iFiFijSKpUWzyGfLRe6UM3vUZK/rioSvsAAETXLqYDxEIw4w9fxLMeOcSHNRHyzrzv3fJvfN7gUeXYnQJ+98dgP5bULekun4ZxrLbNIOchxekj9SclA7MO9VaSYXYS2w6ozvWdrt7zPrnwfwE3p7H1R1XkyD+cW/IBP+T1zCEyp4JT7MrUV9ma3cnbZWrbsTF+CXLKfYPbzOMDMvSoDqXm1ZMgYylSoAQhIfY6/h9oS9VdJjnonn8Y9c1xNqhNwdnXl33YCnpZig6SDN+JVDDcfz8wiDhg3Q6xbJHiQA==",
    "CommandID": "TransactionReversal",
    "TransactionID": "RK47E7E7QLLN",
    "Amount": "20.00",
    "ReceiverParty": "3033631",
    "RecieverIdentifierType": "11",
    "ResultURL": "https://tenakata.superlender.co.ke",
    "QueueTimeOutURL": "https://tenakata.superlender.co.ke",
    "Remarks": "Test",
    "Occasion": "No oc"
}',
        CURLOPT_HTTPHEADER => array(
            'Authorization: Bearer <Access-Token>',
            'Content-Type: application/json'
        ),
    ));

    $response = curl_exec($curl);

    curl_close($curl);
    echo $response;
}
function query_transaction_status()
{
}

function permission($user_id, $tbl, $rec, $act)
{
    ///-----Possible custom keywords
    /// --- APPROVE, REJECT, RESEND, CANCEL, BLOCK, UNBLOCK
    $acts = array('general_', 'create_', 'read_', 'update_', 'delete_');
    $andrec = "";
    if ($rec > 0) {
        $andrec = "AND rec='$rec'";
    } else {
        $andrec = "AND rec='0'";
    }
    $user_group = fetchrow('o_users', "uid='$user_id'", "user_group");
    if ($user_group == 1) {
        return 1;
    } else {
        if (in_array($act, $acts)) {
            return checkrowexists('o_permissions', "(group_id='$user_group' OR user_id='$user_id') AND tbl='$tbl' $andrec AND $act='1' AND status=1");
        } else {
            //echo "(group_id='$user_group' OR user_id='$user_id') AND tbl='$tbl' $andrec AND custom_action='$act' AND status=1,";
            return checkrowexists('o_permissions', "(group_id='$user_group' OR user_id='$user_id') AND tbl='$tbl' $andrec AND custom_action='$act' AND status=1");
        }
    }
}



function table_to_obj($tbl, $where, $limit, $key, $value, $orderBy = "uid", $dir = 'asc')
{
    $obj = array();
    $o_t = fetchtable($tbl, $where, $orderBy, $dir, "0,$limit", "$key ,$value");
    while ($l = mysqli_fetch_array($o_t)) {
        $k = $l[$key];
        $v = $l[$value];
        $obj[$k] = $v;
    }
    return $obj;
}

function table_to_obj2($tbl, $where, $limit, $key, $val_arr)
{
    $obj = array();
    $csv = implode(", ", $val_arr);
    $o_t = fetchtable($tbl, $where, $key, "asc", "0,$limit", "$key ,$csv");
    while ($l = mysqli_fetch_array($o_t)) {
        $k = $l[$key];

        $nested_arr = array();
        foreach ($val_arr as $val) {
            $nested_arr[$val] = $l[$val];
            ;
        }

        $obj[$k] = $nested_arr;
    }
    return $obj;
}


function nested_kv($tbl, $where, $fd1, $fd2, $fd3)
{
    $obj = array();
    $o_t = fetchtable2($tbl, $where, $fd1, "asc", "$fd1, $fd2, $fd3");

    while ($l = mysqli_fetch_array($o_t)) {
        $k = $l[$fd1];

        $nested_arr = array();
        $nested_key = $l[$fd2];
        $nested_key_val = $l[$fd3];

        $nested_arr[$nested_key] = $nested_key_val;

        if (isset($obj[$k])) {
            $obj[$k][$nested_key] = $nested_key_val;
        } else {
            $obj[$k] = $nested_arr;
        }
    }
    return $obj;
}

function table_to_obj_order($tbl, $where, $orderby, $dir, $limit, $key, $value)
{
    $obj = array();
    $o_t = fetchtable($tbl, $where, $orderby, $dir, "0,$limit", "$key ,$value");
    while ($l = mysqli_fetch_array($o_t)) {
        $k = $l[$key];
        $v = $l[$value];
        $obj[$k] = $v;
    }
    return $obj;
}

function table_to_obj_order2($tbl, $where, $orderby, $dir, $limit, $key, $val_arr)
{
    $obj = array();
    $csv = implode(", ", $val_arr);
    $o_t = fetchtable($tbl, $where, $orderby, $dir, "0,$limit", "$key ,$csv");
    while ($l = mysqli_fetch_array($o_t)) {
        $k = $l[$key];

        $nested_arr = array();
        foreach ($val_arr as $val) {
            $nested_arr[$val] = $l[$val];
            ;
        }

        $obj[$k] = $nested_arr;
    }
    return $obj;
}

function table_to_array($tbl, $where, $limit, $fld, $orderby = 'uid', $dir = 'asc')
{
    $res_array = array();
    $recs = fetchtable($tbl, $where, $orderby, $dir, "$limit", "$fld");
    while ($r = mysqli_fetch_array($recs)) {
        $value = $r[$fld];
        array_push($res_array, $value);
    }
    return $res_array;
}
function table_to_array_order($tbl, $where, $orderby, $dir, $limit, $fld)
{
    $res_array = array();
    $recs = fetchtable($tbl, $where, $orderby, $dir, "0,$limit", "$fld");
    while ($r = mysqli_fetch_array($recs)) {
        $value = $r[$fld];
        array_push($res_array, $value);
    }
    return $res_array;
}

function obj2string($obj)
{
    $t = "<table class='table table-bordered bg-gray-light table-hover table-striped'>";
    foreach ($obj as $k => $v) {
        $t .= "<tr><td>$k:</td><td class='font-bold'>$v</td></tr>";
    }

    $t .= "</table>";
    return $t;
}

function mtd_target($amount)
{
    global $date;


    $d = explode('-', $date);
    $month_days = cal_days_in_month(CAL_GREGORIAN, $d[1], $d[0]);
    $remaining_weekends = remainingWeekends($date);
    $month_days = intval($month_days);
    $this_day = intval($d[2]);



    // $current_amount = round(($amount * ($this_day / $month_days)), 2);
    // $current_amount =     (round($current_amount / 0.05, 0)) * 0.05;
    // echo "($this_day, $remaining_weekends, $month_days)";

    $current_amount = round(((($this_day + $remaining_weekends) / $month_days) * $amount), 2);


    if ($current_amount > $amount) {
        $final_amount = $amount;
    } else {
        $final_amount = $current_amount;
    }
    return ceil($final_amount / 1000) * 1000;
}

function mtd_target2($monthly_target, $working_days = 20, $theday = 'DEFAULT')
{
    // Get today's date
    if ($theday === 'DEFAULT') {
        $today = new DateTime();
    } elseif (is_string($theday)) {
        $today = new DateTime($theday);
    } elseif ($theday instanceof DateTime) {
        $today = $theday;
    } else {
        throw new InvalidArgumentException("The parameter 'theday' must be a DateTime object or a valid date string.");
    }

    // Clone the date to manipulate without affecting the original
    $currentDate = clone $today;

    // Determine the day of the week (1 = Monday, 7 = Sunday)
    $dayOfWeek = $currentDate->format('N');

    // If today is Saturday (6) or Sunday (7), adjust to last Friday
    if ($dayOfWeek >= 6) {
        $daysToSubtract = $dayOfWeek - 5; // 1 day for Saturday, 2 days for Sunday
        $currentDate->modify("-$daysToSubtract days");
    }

    // Get the first day of the current month
    $firstDayOfMonth = new DateTime($currentDate->format('Y-m-01'));

    // Initialize working days counter
    $workingDaysCount = 0;

    // Iterate manually from first day to current day (inclusive)
    $checkDate = clone $firstDayOfMonth;
    while ($checkDate <= $currentDate) {
        $checkDayOfWeek = (int) $checkDate->format('N');
        if ($checkDayOfWeek < 6) { // Monday to Friday are considered working days
            $workingDaysCount++;
        }
        $checkDate->modify('+1 day');
    }

    // Calculate daily target
    $daily_target = $monthly_target / $working_days;

    // Calculate MTD target
    $mtd = $daily_target * $workingDaysCount;

    return $mtd;
}
function remainingWeekends($inputDate)
{
    // Convert input string to a DateTime object
    $date = new DateTime($inputDate);

    // Get the last day of the month
    $lastDayOfMonth = $date->format('t');

    // Initialize a counter for weekends
    $weekendCount = 0;

    // Loop through each day from the input date to the end of the month
    for ($day = $date->format('j'); $day <= $lastDayOfMonth; $day++) {
        // Check if the current day is a weekend (Saturday or Sunday)
        $currentDay = new DateTime($date->format('Y-m') . '-' . $day);
        $dayOfWeek = $currentDay->format('N'); // 1 (Monday) through 7 (Sunday)

        if ($dayOfWeek == 6 || $dayOfWeek == 7) {
            $weekendCount++;
        }
    }

    return $weekendCount;
}


function false_zero($num)
{
    if ($num > 0) {
        return $num;
    } else {
        return 0;
    }
}
function force_to($amount, $max)
{
    if ($amount > $max) {
        return $max;
    } else {
        return $amount;
    }
}

function obj_add($array, $key, $value)   ////////--------When given an object holding totals add new keys or sum
{
    ///-----Check if key and Value Exists
    if ((input_available($key)) == 1 && (input_available($value)) == 1) {
        if (array_key_exists($key, $array)) {
            $current_value = $array[$key];
            $array[$key] = $current_value + $value;
        } else {
            $array[$key] = $value;
        }
    }
    return $array;
}
function roundDown($number, $decimals = 2)
{
    $factor = pow(10, $decimals);
    return floor($number * $factor) / $factor;
}

function obj_add_unique($array, $key, $value)   ////////--------When given an object holding totals add new keys or sum
{
    ///-----Check if key and Value Exists
    if ((input_available($key)) == 1 && (input_available($value)) == 1) {
        if (!array_key_exists($key, $array)) {
            $array[$key] = $value;
        }
    }
    return $array;
}

function obj_sum($obj)
{
    $total = 0;
    foreach ($obj as $key => $value) {
        $total = $total + $value;
    }
    return $total;
}

function obj_add_nest($array, $key1, $key2, $value)   ////////--------When given an object holding totals add new keys or sum
{
    ///-----Check if key and Value Exists
    if ((input_available($key1)) == 1 && (input_available($key2)) == 1 && (input_available($value)) == 1) {
        if ($array[$key1][$key2] > 0) {
            $current_value = $array[$key1][$key2];
            $new_total = $value + $current_value;
            $array[$key1][$key2] = $new_total;
        } else {
            $array[$key1][$key2] = $value;
        }
    }
    return $array;
}

// $result = give_loan($cid, $cust['primary_product'], $text, 'TEXT', false);
function give_loan($customer_id, $product_id, $amount, $application_mode, $force_recalc = true)
{
    global $date;
    global $fulldate;
    global $autopicked_payment_days;
    ////----------Check customer details
    $customer_d = fetchonerow('o_customers', "uid='$customer_id'", "uid, full_name, primary_mobile, branch, loan_limit, status");
    $cust_id = $customer_d['uid'];
    $status = $customer_d['status'];
    $loan_limit = $customer_d['loan_limit'];
    $primary_mobile = $customer_d['primary_mobile'];
    $branch = $customer_d['branch'];

    if ($cust_id < 1) {
        return "Customer does not exists";
    }

    ////---------Check Account Details
    ////---Has loan
    $total_loans_taken = countotal_withlimit('o_loans', "customer_id = $customer_id AND disbursed = 1 AND status!=0", "uid", "1000");

    $current_loan = fetchonerow('o_loans', "customer_id='$customer_id' AND disbursed=1 AND paid=0 AND status!=0", "uid, loan_balance");
    if ($current_loan['uid'] > 0) {
        return "You have an outstanding loan of " . $current_loan['loan_balance'] . "";
    }

    /////
    $pending_loan = fetchonerow('o_loans', "customer_id='$customer_id' AND status in (1,2)", "uid, loan_amount");
    if ($pending_loan['uid'] > 0) {
        return "You have a loan pending approval " . $pending_loan['loan_amount'] . "";
    }
    //echo "[$customer_id]";
    /////

    if ($status != 1) {
        return "User is not active";
    }
    if ($loan_limit < 10) {
        return "Customer has no Limit";
    }

    ////----------Check product details
    $prod = fetchonerow('o_loan_products', "uid='$product_id'", "uid, name, period, period_units, min_amount, max_amount,automatic_disburse, status, pay_frequency");
    $period = $prod['period'];
    $period_units = $prod['period_units'];
    $min_amount = $prod['min_amount'];
    $automatic_disburse = $prod['automatic_disburse'];
    $pay_frequency = $prod['pay_frequency'];
    $payment_breakdown = $prod['payment_breakdown'];
    $total_instalments = total_instalments($period, $period_units, $pay_frequency);         //////Calculated from product

    if ($prod['uid'] < 1) {
        return "Product does not exist";
    }
    if ($prod['status'] != 1) {
        return "Product not active";
    }

    if ($amount < $prod['min_amount'] || $amount > $prod['max_amount']) {
        return "Product allows amount between " . $prod['min_amount'] . " and " . $prod['max_amount'] . "";
    }


    //// ----- Check Limit
    if ($loan_limit < $amount) {
        return "Your loan Limit is $loan_limit";
    }
    ////------Check if there is a pending loan
    $pending = fetchonerow('o_loans', "customer_id='$customer_d' AND disbursed=0 AND paid=0 AND status in (1,2)", "uid, loan_amount");
    if ($pending['uid'] > 0) {
        return "You have a pending loan of " . $pending['loan_amount'] . "";
    }

    $deno = denomination_okey($product_id, $amount);

    if ($deno[0] == 0) {
        return "Denomination not valid. Use multiples of " . $deno[1];
    }


    ////-----All is good, create a Loan
    $total_instalments_paid = 0.00;  /////Initialization
    $current_instalment = 1;         ////Initialization
    $given_date = $date;         ////Initialization
    $next_due_date = next_due_date($given_date, $period, $period_units, $pay_frequency);         ////Calculated from product
    $final_due_date = final_due_date($given_date, $period, $period_units);         ////Calculated from product
    $transaction_date = $fulldate;         ////Initialization
    $added_date = $fulldate;
    $added_by = 1;
    $real_agents = real_loan_agent($customer_id, 0);
    $current_lo = $real_agents['LO'] ? $real_agents['LO'] : 0;
    $current_co = $real_agents['CO'] ? $real_agents['CO'] : 0;
    // $current_lo = 0;
    // $current_co = 0;
    $loan_stage_d = fetchminid('o_product_stages', "product_id='$product_id' AND status=1", "stage_order, uid");
    $loan_stage = $loan_stage_d['stage_id'];


    ////--------------New check, look for upfront deductions
    //////--------Check all upfront deduction fees
    $upfront_deducts = fetchtable('o_addons', "deducted_upfront=1", "uid", "asc", "10", "uid, amount, amount_type, applicable_loan");
    $total_upfront_deducts = 0;
    while ($upd = mysqli_fetch_array($upfront_deducts)) {
        $aid = $upd['uid'];
        $product_addon_ = fetchrow('o_product_addons', "addon_id='$aid' AND status=2 AND product_id='$product_id'", "uid");
        if ($product_addon_ > 0) {
            $upfront_addon = $upd['uid'];
            $applicable_loan = $upd['applicable_loan'];
            $amount_ = $upd['amount'];
            $amount_type = $upd['amount_type'];

            if ($amount_type == 'FIXED_VALUE') {
                $a_amount = $amount_;
            } else {
                $a_amount = $amount * ($amount_ / 100);
            }


            if ($applicable_loan == 0) {
                $total_upfront_deducts += $a_amount;
            } else {
                if ($total_loans_taken < $applicable_loan) {
                    $total_upfront_deducts += $a_amount;
                }
            }
        }
    }
    /// -------------END New check, look for upfront deductions
    ///
    /// -------------CHECK UPFRONT PAYMENTS
    $upfronts = fetchtable('o_addons', "paid_upfront=1", "uid", "asc", "10", "uid, amount, amount_type, applicable_loan");
    $total_upfront = 0;
    while ($up = mysqli_fetch_array($upfronts)) {
        $aid = $up['uid'];
        $product_addon = fetchrow('o_product_addons', "addon_id='$aid' AND status=1 AND product_id='$product_id'", "uid");
        if ($product_addon > 0) {
            $upfront_addon = $up['uid'];
            $applicable_loan = $up['applicable_loan'];
            $amount_ = $up['amount'];
            $amount_type = $up['amount_type'];

            if ($amount_type == 'FIXED_VALUE') {
                $a_amount = $amount_;
            } else {
                $a_amount = $amount * ($amount_ / 100);
            }


            if ($applicable_loan == 0) {
                $total_upfront += $a_amount;
            } else {
                if ($total_loans_taken < $applicable_loan) {
                    $total_upfront += $a_amount;
                }
            }
        }
    }

    $week_ago = datesub($date, 0, 0, $autopicked_payment_days ? $autopicked_payment_days : 90);
    $upfront_q = "(mobile_number='$primary_mobile' OR customer_id = $customer_id) AND loan_id=0 AND payment_category in (0, 1, 2, 4) AND status=1 AND payment_date >= '$week_ago'";
    $total_paid = totaltable('o_incoming_payments', "$upfront_q", "amount");

    if ($total_upfront > $total_paid) {
        $total_upfront_balance = false_zero($total_upfront - $total_paid);
        return "An upfront fee of $total_upfront_balance is needed";
    }

    /// --------------END OF CHECK UPFRONT PAYMENTS

    $enc_phone = hash('sha256', $primary_mobile);
    $disbursed_amount = $amount - $total_upfront_deducts;

    $fds = array('customer_id', 'account_number', 'enc_phone', 'product_id', 'loan_amount', 'disbursed_amount', 'total_repayable_amount', 'total_repaid', 'loan_balance', 'period', 'period_units', 'payment_frequency', 'payment_breakdown', 'total_instalments', 'total_instalments_paid', 'current_instalment', 'given_date', 'next_due_date', 'final_due_date', 'added_by', 'current_lo', 'current_co', 'current_branch', 'added_date', 'loan_stage', 'application_mode', 'status');
    $vals = array("$customer_id", "$primary_mobile", "$enc_phone", "$product_id", "$amount", "$disbursed_amount", "$amount", "0", "$amount", "$period", "$period_units", "$pay_frequency", "$payment_breakdown", "$total_instalments", "$total_instalments_paid", "$current_instalment", "$given_date", "" . move_to_monday($next_due_date) . "", "" . move_to_monday($final_due_date) . "", "$added_by", "$current_lo", "$current_co", "$branch", "$added_date", "$loan_stage", "$application_mode", "1");
    $create = addtodb('o_loans', $fds, $vals);
    // updatedb("o_customers", "primary_product = $product_id", "uid = $customer_id");
    if ($create == 1) {

        // updatedb("o_customers", "total_loans = '$total_loans'", "uid = $customer_id");
        $created_loan = fetchmax('o_loans', "customer_id='$customer_id' AND product_id='$product_id'", "uid", "uid");
        $loan_id = $created_loan['uid'];
        ////////-----------Add Automatic AddOns
        $addons = fetchtable('o_product_addons', "product_id='$product_id' AND status=1", "addon_id", "asc", "20", "addon_id");
        while ($addon = mysqli_fetch_array($addons)) {

            $addon_id = $addon['addon_id'];
            $automatic = fetchrow('o_addons', "uid='$addon_id' AND from_day = 0", "automatic");
            if ($automatic == 1) {
                apply_loan_addon_to_Loan($addon_id, $loan_id, false);
            }
        }

        ////-----Mark payments for processing
        $upd = updatedb('o_incoming_payments', "loan_id='$loan_id'", "$upfront_q");

        if ($force_recalc) {
            recalculate_loan($loan_id);
        }

        return $loan_id;
    } else {
        return 'Unable to Create Loan';
    }
}
function move_image_to_api($img, $delete_local = 1)
{
    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => 'http://137.184.203.250/userdocs/',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => array('sendimage' => new CURLFILE($img)),
    ));

    $response = curl_exec($curl);

    curl_close($curl);
    if ($response == '1') {
        ///---Success, delete
        if ($delete_local == 1) {
            if (!unlink($img)) {
                echo ("moved and deleted local");
            } else {
                echo ("moved but not deleted");
            }
        } else {
            return "Success";
        }
    } else {
        return $response;
    }
}
function UrlState($url)
{
    $url = $url;
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HEADER, true);    // we want headers
    curl_setopt($ch, CURLOPT_NOBODY, true);    // we don't need body
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $output = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return $httpcode;
}

function locateImageServer($img)
{
    global $image_server1;
    global $image_server2;

    // Check if the image has a full URL
    if (substr(trim($img), 0, 4) == 'http') {
        return $img;
    } elseif (filter_var($img, FILTER_VALIDATE_URL)) {
        return $img;
    } else {
        // The image is not a full URL, construct it dynamically
        $state = UrlState("$image_server2$img");
        if ($state == 200) {
            return "$image_server2$img";
        } else {
            return "$image_server1$img";
        }
    }
}

function denomination_okey($product_id, $amount)
{
    $prod = fetchrow('o_key_values', "tbl='o_loan_products' AND record='$product_id' AND key_='MULTIPLES_OF'", "value_");
    if ($prod > 1) {
        $mult = $prod;
    } else {
        $mult = 1;
    }
    $div = $amount % $mult;
    if ($div == 0) {
        $divi[0] = 1;
        $divi[1] = $mult;
    } else {
        $divi[0] = 0;
        $divi[1] = $mult;
    }
    return $divi;
}

function update_limit($cus_id, $new_amount, $comment)
{
    global $fulldate;
    $fds = array('customer_uid', 'amount', 'given_date', 'given_by', 'comments', 'status');
    $vals = array("$cus_id", "$new_amount", "$fulldate", "0", $comment, "1");
    $create = addtodb('o_customer_limits', $fds, $vals);
    if ($create == 1) {
        $update_cust = updatedb('o_customers', "loan_limit='$new_amount'", "uid='$cus_id'");
        // echo "Sucess($update_cust)";
        store_event('o_customers', $cus_id, "Limit updated  to  $new_amount on $fulldate");
    }
    return $create;
}
function time_elapsed_string($datetime)
{
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    if ($diff->y > 0) {
        return $diff->y . 'y ago';
    }
    if ($diff->m > 0) {
        return $diff->m . 'm ago';
    }
    if ($diff->d > 0) {
        return $diff->d . 'd ago';
    }
    if ($diff->h > 0) {
        return $diff->h . 'h ago';
    }
    if ($diff->i > 0) {
        return $diff->i . 'm ago';
    }
    if ($diff->s > 0) {
        return $diff->s . 's ago';
    }

    return '0s ago';
}


function height($val)
{
    return $val * 2 + 50;
}

function getProviderByMSISDN($msisdn)
{

    $msisdn = trim($msisdn);

    $saf_prefixes = [
        '25470',
        '25471',
        '25472',
        '25474',
        '25479',
        '25411'
    ];

    $ke_airtel_prefixes = [
        '25473',
        '25475',
        '25478',
        '25410'
    ];

    $ug_mtn_prefixes = [
        '25631',
        '25639',
        '25678',
        '25676',
        '25677'
    ];

    $ug_airtel_prefixes = [
        '25620',
        '25670',
        '25675',
        '25674'
    ];

    $msisdn_prefix = substr($msisdn, 0, 5); // Get the first 5 digits of the MSISDN

    if (in_array($msisdn_prefix, $saf_prefixes)) {
        return 'KE_SAF';
    } elseif (in_array($msisdn_prefix, $ke_airtel_prefixes)) {
        return 'KE_AIRTEL';
    } elseif (in_array($msisdn_prefix, $ug_mtn_prefixes)) {
        return 'UG_MTN';
    } elseif (in_array($msisdn_prefix, $ug_airtel_prefixes)) {
        return 'UG_AIRTEL';
    } else {
        return 'KE_SAF';
    }
}

function sanitizeAndEscape($value, $connection = null)
{
    try {

        global $con;

        $trimmedValue = trim($value) ?? '';
        $escapedValue = mysqli_real_escape_string($con, $trimmedValue);
        return $escapedValue;
    } catch (Exception $e) {
        // Handle query execution error
        // echo "Query execution error: " . $e->getMessage();
    }
}

function addDaysToDate($originalDate, $daysToAdd)
{
    // Create a DateTime object from the original date
    $date = new DateTime($originalDate);

    // Add the specified number of days
    $date->modify("+$daysToAdd days");

    // Check if the resulting day is Sunday (0 in the format 'w')
    if ($date->format('w') === '0') {
        $date->modify('+1 day'); // Add 1 more day if it's Sunday
    }

    // Return the updated date
    return $date->format('Y-m-d'); // Change the format as needed
}

function extractTimeFromDate($datetimeString)
{
    // Create a DateTime object from the input string
    $dateTime = new DateTime($datetimeString);

    // Format the DateTime object to extract the time part (HH:MM:SS)
    $timePart = $dateTime->format('H:i:s');

    return $timePart;
}
function after_script($product_id, $key)
{
    $prod = fetchrow('o_loan_products', "uid=$product_id", "after_save_script");
    $scr = json_decode($prod, true);
    $script = $scr[$key];
    if ((input_length($script, 5))) {
        return $script;
    } else {
        return 0;
    }
}

function countXInY($x, $y)
{
    if ($x == 0) {
        // To avoid division by zero
        return 0;
    }

    // Calculate how many times x goes into y as a whole number
    $count = floor($y / $x);

    return $count;
}

function getMSISDNProviderUID($msisdn)
{

    global $cc;
    $uid = 0;

    $msisdn = trim($msisdn);
    $saf_prefixes = [
        '25470',
        '25471',
        '25472',
        '25474',
        '25479',
        '25411'
    ];

    $ke_airtel_prefixes = [
        '25473',
        '25475',
        '25478',
        '25410'
    ];

    $ug_mtn_prefixes = [
        '25631',
        '25639',
        '25678',
        '25676',
        '25677'
    ];

    $ug_airtel_prefixes = [
        '25620',
        '25670',
        '25675',
        '25674'
    ];

    $msisdn_prefix = substr($msisdn, 0, 5); // Get the first 5 digits of the MSISDN
    if (in_array($msisdn_prefix, $saf_prefixes)) {
        $uid = 1; // safaricom kenya
    } elseif (in_array($msisdn_prefix, $ke_airtel_prefixes)) {
        $uid = 2; // airtel kenya
    } elseif (in_array($msisdn_prefix, $ug_airtel_prefixes)) {
        $uid = 3; // airtel ug
    } elseif (in_array($msisdn_prefix, $ug_mtn_prefixes)) {
        $uid = 4; // mtn ug
    } else {
        // defaults
        if ($cc != 256) {
            $uid = 1; // safaricom kenya
        }
    }

    if ($cc != 256 && $uid != 1) {
        $uid = 1; // safaricom kenya
    }

    return $uid;
}

function getMSISDNProvider($provder_uid)
{
    $provider = "";

    if ($provder_uid === 1) {
        $provider = 'KE_SAF';
    } elseif ($provder_uid === 2) {
        $provider = 'KE_AIRTEL';
    } elseif ($provder_uid === 3) {
        $provider = 'UG_AIRTEL';
    } elseif ($provder_uid === 4) {
        $provider = 'UG_MTN';
    }

    return $provider;
}


function isGoogleMapsUrlValid($url)
{
    // Add any additional checks for a Google Maps URL pattern if needed
    if (
        !(filter_var($url, FILTER_VALIDATE_URL)) &&
        strpos($url, 'maps') !== 0
    ) {
        return 0;
    }

    return 1;
}
function hideMiddleDigits($inputString)
{
    // Check if the string has at least 3 digits
    if (preg_match('/\d{3}/', $inputString)) {
        // Get the length of the string
        $length = strlen($inputString);


        // Calculate the starting index for replacing the middle digits
        $startIndex = floor(($length - 1) / 2);

        // Replace the middle digits with asterisks
        $result = substr_replace($inputString, '***', $startIndex, 3);

        return $result;
    } else {
        // If the string doesn't have at least 3 digits, return the original string
        return $inputString;
    }
}

function resizeAndCompressImage($imagePath, $maxWidth = 1080, $quality = 85)
{
    // Get the original dimensions of the image
    list($originalWidth, $originalHeight) = getimagesize($imagePath);
    $pathInfo = pathinfo($imagePath);
    $imageExtension = strtolower($pathInfo['extension']);

    // Validate extension
    if (!in_array($imageExtension, ['jpg', 'jpeg', 'png'])) {
        return "Wrong extension";
    }

    // No resizing needed if both dimensions are smaller than the max width
    if ($originalWidth <= $maxWidth && $originalHeight <= $maxWidth) {
        return 1;
    }

    // Determine new dimensions
    if ($originalWidth > $originalHeight) {
        $ar = $originalHeight / $originalWidth;
        $newWidth = $maxWidth;
        $newHeight = round($maxWidth * $ar);
    } elseif ($originalHeight > $originalWidth) {
        $ar = $originalWidth / $originalHeight;
        $newHeight = $maxWidth;
        $newWidth = round($maxWidth * $ar);
    } else { // Square image case
        $newWidth = $newHeight = $maxWidth;
    }

    // Create a new true color image with the calculated dimensions
    $resizedImage = imagecreatetruecolor($newWidth, $newHeight);

    // Load the original image
    switch ($imageExtension) {
        case 'jpg':
        case 'jpeg':
            $originalImage = imagecreatefromjpeg($imagePath);
            break;
        case 'png':
            $originalImage = imagecreatefrompng($imagePath);
            // Preserve transparency for PNG
            imagealphablending($resizedImage, false);
            imagesavealpha($resizedImage, true);
            break;
        default:
            return "Unsupported image type";
    }

    // Resize the image
    imagecopyresampled($resizedImage, $originalImage, 0, 0, 0, 0, $newWidth, $newHeight, $originalWidth, $originalHeight);

    // Save the resized and compressed image
    switch ($imageExtension) {
        case 'jpg':
        case 'jpeg':
            imagejpeg($resizedImage, $imagePath, $quality); // uses the specified quality level (0-100)
            break;
        case 'png':
            imagepng($resizedImage, $imagePath, 9); // highest compression level (0-9)
            break;
    }

    // Free up memory
    imagedestroy($originalImage);
    imagedestroy($resizedImage);

    return 1; // Success
}


function branch_permissions($userd, $type_ = 'o_loans')
{
    //////-This is a reusable function to check if a user can view all branches, some branches or their branches only
    ///
    ///
    $read_all = permission($userd['uid'], 'o_loans', "0", "read_");

    if ($read_all == 1) {
        $anduserbranch = $andloanbranch = $andbranch = $andpaybranch = "";
    } else {
        $user_branch = $userd['branch'];
        $anduserbranch = " AND branch='$user_branch'";
        $andloanbranch = " AND current_branch='$user_branch'";
        $andbranch = " AND uid = '$user_branch'";
        $andpaybranch = " AND branch_id = '$user_branch'";

        //////-----Check users who view multiple branches
        $staff_branches = table_to_array('o_staff_branches', "agent=" . $userd['uid'] . " AND status=1", "1000", "branch", "uid", "asc");
        if (sizeof($staff_branches) > 0) {
            ///------Staff has been set to view multiple branches
            array_push($staff_branches, $userd['branch']);
            $staff_branches_list = implode(",", $staff_branches);
            $anduserbranch = " AND branch in ($staff_branches_list)";
            $andloanbranch = " AND current_branch in ($staff_branches_list)";
            $andbranch = " AND uid in ($staff_branches_list)";
            $andpaybranch = " AND branch_id in ($staff_branches_list)";
        }
    }


    if ($type_ == 'o_customers') {
        return $anduserbranch;
    } elseif ($type_ == 'o_branches') {
        return $andbranch;
    } elseif ($type_ == 'o_incoming_payments') {
        return $andpaybranch;
    } else {
        return $andloanbranch;
    }
}


function getBranchCondition($userd, $tbl, $branch_field = 'current_branch', $sec_output_key = 'branchLoanCondition', $rec = 0, $permission_field = 'read_')
{
    $read_all = permission($userd['uid'], "$tbl", $rec, "$permission_field");

    if ($read_all == 1) {
        return "";
    } else {
        $user_branch = $userd['branch'];
        $anduserbranch = " AND branch='$user_branch'";
        $sec_output_val = " AND $branch_field='$user_branch'";

        $staff_branches = table_to_array('o_staff_branches', "agent=" . $userd['uid'] . " AND status=1", "1000", "branch", "uid", "asc");
        if (sizeof($staff_branches) > 0) {
            array_push($staff_branches, $userd['branch']);
            $staff_branches_list = implode(",", $staff_branches);
            $anduserbranch = " AND branch in ($staff_branches_list)";
            $sec_output_val = " AND $branch_field in ($staff_branches_list)";
        }

        // return associative array for branchUserCondition and sec_output_key 
        return array('branchUserCondition' => $anduserbranch, "$sec_output_key" => $sec_output_val);
    }
}


function noRowSpan($n)
{
    return "<tr><td colspan='$n'><i>No Records Found</i></td></tr>";
}

function hasPendingLoan($customer_id)
{
    // do a direct query to check if the customer has a pending loan
    $count = totaltable('o_loans', "customer_id=$customer_id' AND status in (1,2,3,4,7,8,9,10)", "uid");
    return $count > 0 ? 1 : 0;
}
function session_variables($action, $key, $val = null)
{
    $session_variable = $_SESSION['session_variables'];
    if ($action == 'ADD') {
        $session_variable[$key] = $val;
        $_SESSION['session_variables'] = $session_variable;
        $session_variable = $_SESSION['session_variables'];
        if ($session_variable[$key] == $val) {
            return 1;
        } else {
            return 0;
        }
    } else {
        $val = $session_variable[$key];
        return $val;
    }
}


function convertTimeTo24Hrs($t)
{
    $ot = $mt = trim($t);
    $lastTwo = substr($mt, -2);
    $firstTwo = substr($mt, 0, 2);
    $intFirstTwo = intval($firstTwo);

    if ($lastTwo == "PM" && $intFirstTwo < 12) {
        $intFirstTwo += 12;
        $mt = str_replace($firstTwo, $intFirstTwo, $mt);
        $mt = str_replace($lastTwo, "", $mt);
    } else if ($lastTwo == "PM" && $intFirstTwo >= 12) {
        $mt = str_replace($lastTwo, "", $mt);
    } else if ($lastTwo == "AM" && $intFirstTwo < 12) {
        $mt = str_replace($lastTwo, "", $mt);
    } else {
        if ($lastTwo == "AM" && $intFirstTwo == 12) {
            $intFirstTwo -= 12;
            $mt = str_replace($lastTwo, "", $mt);
            $mt = str_replace($firstTwo, "0" . $intFirstTwo, $mt);
        }
    }

    $t = str_replace($ot, $mt, $t);
    return $t;
}

function convertTimeTo12Hrs($t)
{
    $ot = $mt = trim($t);
    $firstTwo = substr($mt, 0, 2);
    $intFirstTwo = (int) $firstTwo;
    $timeZeroIndex = substr($mt, 0, 1);

    if ($intFirstTwo == 0) {
        $intFirstTwo += 12;
        $mt = str_replace($firstTwo, $intFirstTwo, $mt) . "AM";
    } else if ($intFirstTwo < 12 && $intFirstTwo != 0 && $timeZeroIndex != 0) {
        $mt = $mt . "AM";
    } else if ($intFirstTwo < 12 && $intFirstTwo != 0 && $timeZeroIndex == 0) {
        $mt = str_replace("0", "", $mt) . "AM";
    } else if ($intFirstTwo > 12) {
        $intFirstTwo -= 12;
        $mt = str_replace($firstTwo, $intFirstTwo, $mt) . "PM";
    } else {
        if ($intFirstTwo == 12) {
            $mt = $mt . "PM";
        }
    }

    $t = str_replace($ot, $mt, $t);
    return $t;
}

// generates uuid version 4
function uuid_gen()
{
    return sprintf(
        '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        // 32 bits for "time_low"
        mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),

        // 16 bits for "time_mid"
        mt_rand(0, 0xffff),

        // 16 bits for "time_hi_and_version",
        // four most significant bits holds version number 4
        mt_rand(0, 0x0fff) | 0x4000,

        // 16 bits, 8 bits for "clk_seq_hi_res",
        // 8 bits for "clk_seq_low",
        // two most significant bits holds zero and one for variant DCE1.1
        mt_rand(0, 0x3fff) | 0x8000,

        // 48 bits for "node"
        mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0xffff)
    );
}

function make_call()
{
    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://voice.africastalking.com/call',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => 'username=tenakata&from=%2B254709924483&to=%2B254716330450&clientRequestId=ririr',
        CURLOPT_HTTPHEADER => array(
            'Accept: application/json',
            'Content-Type: application/x-www-form-urlencoded',
            'apiKey: atsk_f3156ef8f2b40312a859088887d74a657975bf6631d484a3b7f8b04d7b3866528ce09f92'
        ),
    ));

    $response = curl_exec($curl);

    curl_close($curl);
    return $response;
}

function extractUUIDv4FromString($input)
{
    $pattern = '/[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}/i';
    if (preg_match($pattern, $input, $matches)) {
        return $matches[0];
    }
    return null;
}
function loan_deductions_archive($loan_id)
{
    $total_deductions = totaltable_archive('o_loan_deductions', "loan_id='$loan_id' AND status=1", "deduction_amount");
    return $total_deductions;
}

function boldenVal($val)
{
    return "<b>$val</b>";
}

function generateThumbnailUrl($imageUrl)
{
    // Parse the URL to get the path and file name
    $urlParts = parse_url($imageUrl);
    $path = $urlParts['path'];

    // Check if 'thumb_' is already in the file name
    if (strpos(basename($path), 'thumb_') === 0) {
        // Return the original URL if it already contains 'thumb_'
        return $imageUrl;
    }

    // Add 'thumb_' to the file name
    $directory = rtrim(dirname($path), '/');
    $newPath = $directory . '/thumb_' . basename($path);

    // Rebuild the URL with the modified path
    return (isset($urlParts['scheme']) ? "{$urlParts['scheme']}:" : '') .
        (isset($urlParts['host']) ? "//{$urlParts['host']}" : '') .
        $newPath;
}

function fetchAFTBulkSMSConfigs()
{
    try {
        // connection
        global $con;

        // Define the specific properties needed for Africa's Talking Bulk SMS
        $properties = ['BULK_CODE', 'USERNAME', 'AFT_BULK_KEY'];

        // Prepare the list of property names for the IN clause
        $propertyList = "'" . implode("','", $properties) . "'";

        // Construct the SQL query
        $query = "SELECT property_name, property_value FROM o_sms_settings WHERE property_name IN ($propertyList)";

        // Execute the query
        $result = mysqli_query($con, $query);

        if (!$result) {
            throw new Exception(mysqli_error($con));
        }

        // Process the results into an associative array
        $values = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $values[$row['property_name']] = $row['property_value'];
        }

        // Extract specific credentials
        return [
            'bulk_code' => $values['BULK_CODE'] ?? null,
            'username' => $values['USERNAME'] ?? null,
            'api_key' => $values['AFT_BULK_KEY'] ?? null,
        ];
    } catch (Exception $e) {
        // Handle query execution error
        // Optionally log the error or rethrow the exception
        // echo "Query execution error: " . $e->getMessage();
        return [
            'bulk_code' => null,
            'username' => null,
            'api_key' => null,
        ];
    }
}

function createFileIfNotExists($filePath)
{
    // Check if the file exists
    if (!file_exists($filePath)) {
        // Attempt to create the file
        $file = fopen($filePath, 'w');

        // Check if the file was created successfully
        if ($file) {
            fclose($file);  // Close the file handle
            return true;  // File was created
        } else {
            return false;  // Error creating the file
        }
    }

    return true;  // File already exists
}

function updateSmsBalance(): int
{

    global $sms_provider;
    $balance = null;
    if ($sms_provider == 'BONGASMS') {
        // Fetch the SMS balance response
        $response = bongasms_bal();

        // Extract the numeric balance from the response
        $balance = $response['sms_credits'] ?? null;

    } else {

        // ======Defaults to AFRICASTALKING

        // Fetch the SMS balance response
        $response = bulk_sms_balance();

        // Extract and clean the numeric balance from the response
        $balance = preg_replace("/[^0-9\.]/", "", $response);
    }

    if ($balance === null) {
        return 0;
    }

    // Update the database with the new balance
    updatedb('o_summaries', "value_='$balance'", "name='SMS_BALANCE'");
    return $balance;
}


function encryptStringSecure($plaintext, $password)
{
    /**
     * Encrypts a given plaintext using the specified password.
     *
     * @param string $plaintext The data to be encrypted.
     * @param string $password  The secret key to use for encryption.
     *
     * @return string The encrypted string (Base64-encoded).
     */

    // Choose a secure cipher and mode (e.g. AES-256-CBC)
    $method = 'AES-256-CBC';

    // Generate an IV (Initialization Vector)
    $ivLen = openssl_cipher_iv_length($method);
    $iv = openssl_random_pseudo_bytes($ivLen);

    // Encrypt the plaintext
    $ciphertextRaw = openssl_encrypt(
        $plaintext,
        $method,
        $password,
        OPENSSL_RAW_DATA,
        $iv
    );

    // Create an HMAC for integrity checking (optional but recommended)
    $hmac = hash_hmac('sha256', $ciphertextRaw, $password, true);

    // Combine IV + HMAC + ciphertext, then Base64-encode
    $encrypted = base64_encode($iv . $hmac . $ciphertextRaw);

    return $encrypted;
}


function decryptStringSecure($encrypted, $password)
{
    /**
     * Decrypts a previously encrypted string using the specified password.
     *
     * @param string $encrypted Base64-encoded string containing IV + HMAC + ciphertext.
     * @param string $password  The secret key used for encryption.
     *
     * @return string|false The decrypted string on success, or false on failure.
     */

    $method = 'AES-256-CBC';

    // Decode from Base64
    $decoded = base64_decode($encrypted);

    // Retrieve the IV length for this cipher
    $ivLen = openssl_cipher_iv_length($method);

    // Extract IV
    $iv = substr($decoded, 0, $ivLen);

    // Extract HMAC
    $hmac = substr($decoded, $ivLen, 32);

    // Extract the raw ciphertext
    $ciphertextRaw = substr($decoded, $ivLen + 32);

    // Perform the decryption
    $decrypted = openssl_decrypt(
        $ciphertextRaw,
        $method,
        $password,
        OPENSSL_RAW_DATA,
        $iv
    );

    // Verify HMAC for data integrity
    $calculatedHmac = hash_hmac('sha256', $ciphertextRaw, $password, true);
    if (!hash_equals($hmac, $calculatedHmac)) {
        // HMAC validation failed — the data may have been tampered with
        return generateRandomString(20);
    }

    return $decrypted;
}

function moneyCurrency()
{
    global $cc;
    if ($cc == 256) {
        return 'UGX';
    } else {
        return 'KES';
    }
}

/* 
    Function to get the details of a flag
    @param $flag: The flag to get details for
    @return array: An array containing the flag name and color
*/

function get_flag_details($flag)
{
    // Fetch flag data from the table
    $flagd = table_to_obj2('o_flags', "uid > 0", "1000", "uid", array("name", "color_code"));

    // Check if the flag exists in the fetched data
    if (isset($flagd[$flag])) {
        $flag_arr = $flagd[$flag];
        return [
            'name' => $flag_arr['name'],
            'color' => $flag_arr['color_code']
        ];
    } else {
        // Return default or null values if flag not found
        return [
            'name' => null,
            'color' => null
        ];
    }
}

function cc_token($staff_uid)
{
    $call_token = session_variables('READ', "call_token");
    if (input_length($call_token, 10) == 1) {
        ///---Token exists
        //echo "set,";
        return $call_token;
    } else {
        // echo "Not set";
        ///---Create token

        $property_array = array();
        $sms_settings = fetchtable('o_sms_settings', "status=1", "uid", "asc", "100");
        while ($ss = mysqli_fetch_array($sms_settings)) {
            $pname = $ss['property_name'];
            $pvalue = $ss['property_value'];
            $property_array[$pname] = $pvalue;

            //  echo $pname.$pvalue;
        }


        $username = $property_array['AFT_VOICE_USERNAME'];
        $phone_number = '+' . $property_array['AFT_VOICE_NUMBER'];
        $api_key = $property_array['AFT_VOICE_APIKEY'];

       //  echo "$username, $phone_number, $api_key..";
        $curl = curl_init();

        // Create the JSON payload with the PHP variables
        $post_fields = json_encode([
            "username" => "$username",
            "clientName" => "$staff_uid",
            "phoneNumber" => "$phone_number"
        ]);

        //var_dump($post_fields);

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://webrtc.africastalking.com/capability-token/request',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $post_fields,
            CURLOPT_HTTPHEADER => array(
                'apiKey: ' . $api_key,
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);
        echo $response;

        if (curl_errno($curl)) {
            return 'Error:' . curl_error($curl);
            // return  0;
        } else {
            // return $response;
        }

        curl_close($curl);
        $response = json_decode($response, true);  // Decodes as an associative array
        $token = $response['token'];

        $token_set = session_variables('ADD', "call_token", $token);
        if ($token_set == 1) {
            return $token;
        } else {
            return 0;
        }
    }
}

function has_passkey($username)
{
    if (empty(trim($username))) {
        return false;
    }

    $curl = curl_init();
    global $passkey_api_base_url;
    global $sl_api_key;

    curl_setopt_array($curl, [
        CURLOPT_URL => "$passkey_api_base_url/has-passkey/" . urlencode(strtolower($username)),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 60,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_HTTPHEADER => [
            "x-api-key: $sl_api_key"
        ],
    ]);

    $response = curl_exec($curl);
    curl_close($curl);

    $result = json_decode($response, true);
    return $result['hasPasskey'] ?? false;
}

function delete_passkey($passkeyId)
{

    if (empty(trim($passkeyId))) {
        return false;
    }

    $curl = curl_init();
    global $passkey_api_base_url;
    global $sl_api_key;

    curl_setopt_array($curl, [
        CURLOPT_URL => "$passkey_api_base_url/passkeys/" . urlencode(strtolower($passkeyId)),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 60,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'DELETE',
        CURLOPT_HTTPHEADER => [
            "x-api-key: $sl_api_key"
        ],
    ]);

    $response = curl_exec($curl);
    curl_close($curl);
    return json_decode($response, true);
}


function isWithinWorkingHours(
): bool {

    $allowedStartTime = "05:30AM";
    $allowedEndTime = "05:30PM";

    try {
        $start = new DateTime($allowedStartTime);
        $end = new DateTime($allowedEndTime);
        $currentTime = new DateTime('now');
    } catch (Exception $e) {
        throw new InvalidArgumentException("Invalid time format. Use 'HH:MMAM/PM' or 'HH:MM' (24-hour).");
    }
    // Check if current time is within the allowed window
    return ($currentTime >= $start && $currentTime <= $end);
}

/**
 * Handles SKIP_B2C_VALIDATION flag update in customer record
 * 
 * @param int $customer_id The customer ID to update
 * @param int $with_ni_validation Flag (1 = set SKIP_B2C_VALIDATION to 0, otherwise set to 1)
 * @return int Number of affected rows (1 if successful, 0 if no rows were updated)
 */
function handleSkipB2CValidation($customer_id, $with_ni_validation)
{
    global $con;

    $skip_value = $with_ni_validation == 1 ? 0 : 1;

    $mark_platinum_query = "UPDATE o_customers 
         SET other_info = JSON_SET(
             IFNULL(other_info, '{}'), 
             '$.SKIP_B2C_VALIDATION', $skip_value 
         ) WHERE uid = $customer_id";

    mysqli_query($con, $mark_platinum_query);
    return enforceInteger(mysqli_affected_rows($con));
}

function enforceInteger($val)
{
    return is_numeric($val) ? intval($val) : 0;
}


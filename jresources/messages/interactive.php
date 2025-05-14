<?php
session_start();
include_once '../../configs/20200902.php';
$_SESSION['db_name'] = $db_;
include_once '../../php_functions/functions.php';
include_once '../../configs/conn.inc';
$userd = session_details();

$offset_ = $_POST['offset'];
$rpp_ = $_POST['rpp'];
$limit = "$offset_, $rpp_";
$page_no = $_POST['page_no'];
$orderby = $_POST['orderby'];
$dir = $_POST['dir'];
$sender_phone = $_POST['phone_no'];
//$sender_phone = '254716330450';
$customer = decurl($_POST['cid']);

//-----------------------------Reused Query

//default or all interactions
$o_conversations = fetchtable(
    'o_sms_interaction',
    "sender_phone='$sender_phone' AND status > 0",
    "$orderby",
    "$dir",
    "$limit",
    'message, transdate, direction'
);

///----------Paging Option
$alltotal = countotal('o_sms_interaction', "sender_phone='$sender_phone' AND status > 0");

$cust = fetchonerow(
    'o_customers',
    "uid = '$customer' AND status > 0",
    'full_name'
);

$cust_name = $cust['full_name'];

$company = fetchonerow('platform_settings', 'uid=1', 'name');
$company_name = $company['name'];

if ($alltotal > 0) {
    $row = '';
    while ($i = mysqli_fetch_array($o_conversations)) {
        $message = $i['message'];
        $direction = $i['direction'];
        $transdate = $i['transdate'];
        if (isset($transdate)) {
            $fancy_date = fancydate($transdate);
        }else {
            $fancy_date = " ";
        }

        $status_display = "
            <span class='text-success'>
                <i class='fa fa-check'></i>
            </span>";
        

        if ($direction == 2) {
            $row =
                $row .
                "<div class='direct-chat-msg'>
                    <div class='direct-chat-infos clearfix'>
                      <span class='direct-chat-name pull-left'>" .
                $company_name .
                "</span>
                      <span class='direct-chat-timestamp pull-right'>" .
                      $status_display . " " . $transdate . " ". $fancy_date.
                "</span>
                    </div>
                    <i class='fa fa-university direct-chat-img img-circle' style='font-size: 32px;' aria-hidden='true'></i>
                    <div class='direct-chat-text'>
                      " .
                $message .
                "
                    </div>
                  </div>
            ";
        } else {
            $row =
                $row .
                "
            <div class='direct-chat-msg right'>
                    <div class='direct-chat-infos clearfix'>
                      <span class='direct-chat-name pull-right'>" .
                $cust_name .
                "</span>
                      <span class='direct-chat-timestamp pull-left'>" .
                      $transdate.
                      "  ".
                      fancydate($transdate) .
                "</span>
                    </div>
                    <i class='fa fa-user direct-chat-img img-circle border' aria-hidden='true' style='font-size: 32px;'></i>
                    <div class='direct-chat-text' style='background-color: #e3ebef !important; border: 1px solid #e3ebef !important;'>
                        " .
                $message .
                "
                    </div>
                  </div>
            ";
        }
        //////------Paging Variable ---
        //$page_total = $page_total + 1;
        /////=======Paging Variable ---
    }
} else {
    $row = "<tr><td colspan='7'><i>No Records Found</i></td></tr>";
}
?>


<!-- Conversations are loaded here -->
<div>
    <?php echo $row .
        "<tr style='display: none;'><td><input type='hidden' id='_alltotal_' value='$alltotal'></td></tr>"; ?>
</div>

<?php include_once '../../configs/close_connection.inc'; ?>
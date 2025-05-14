<?php
session_start();
include_once ("../../php_functions/functions.php");
include_once ("../../configs/conn.inc");

$userd = session_details();

$branch = $_POST['branch'];
$file_name = $_FILES['target_customers']['name'];
$file_size = $_FILES['target_customers']['size'];
$file_tmp = $_FILES['target_customers']['tmp_name'];


$userd = session_details();
$pairing  = permission($userd['uid'],'o_pairing',"0","create_");
if($pairing == 0) {
    die(errormes("You don't have permission pair"));
    exit();
}




$upload_location = '../../pairing_/';

//die("Error occurred");
if($userd == null){
    die(errormes("Your session is invalid. Please re-login"));
    exit();
}

$lo_list_array = table_to_array('o_users',"user_group=7 AND status=1","100000","uid");
$co_list_array = table_to_array('o_users',"user_group in (7, 8) AND status=1","100000","uid");


$upload_pairs = permission($userd['uid'],'o_pairing',"0","update_");
if($upload_pairs != 1){
   // die(errormes("You don't have permission to update loan pairs"));
   // exit();
}

if($branch > 0){}
else{
    die(errormes("Branch is required"));
    exit();
}

$branch_agents_array = table_to_array('o_users',"branch='$branch' AND status=1","100000","uid");


$allowed_formats = "csv, CSV";
$allowed_formats_array = explode(",", $allowed_formats);

// echo "file_size => $file_size";
if($file_size > 0){
    if((file_type($file_name, $allowed_formats_array)) == 0){
        die(errormes("This file format is not allowed. Only $allowed_formats "));
        exit();
    }

}
else{
    die(errormes("File not attached or has invalid size"));
    exit();
}

$handle = fopen($file_tmp, "r");

$upload = upload_file($file_name,$file_tmp,$upload_location);
if($upload === 0)
{
    echo errormes("Error uploading file, please retry");
    exit();
}


$open = fopen("../../pairing_/".$upload, "r");
$data = fgetcsv($open, 10000000, ",");
//$data = fgetcsv($handle);

$all_branch_loans_array = table_to_array('o_loans',"current_branch='$branch'","10000000","uid");
$all_branch_loan_customers_array = table_to_obj('o_loans',"current_branch='$branch'","10000000","uid","customer_id");
$pairs = table_to_obj('o_pairing',"status=1","10000000","lo","co");

$total_records = 0;
$total_updated = 0;
$mass_logging = "";

///---------Fix bad uploads
$has_errors = 0;

$rowNum = 2;
while(($data = fgetcsv($open, 100000, ",")) !== FALSE){
    $lo_name = trim($data[7]);
    $lo_code = intval(trim($data[8]));
    $co_name = trim($data[9]);
    $co_code = intval(trim($data[10]));

   // echo "$lo_code--$co_code <br/>";

    if(!in_array($lo_code, $lo_list_array)){
        echo errormes("$lo_name ($lo_code) is not LO");
        $has_errors = 1;
        break;

    }
    if(!in_array($co_code, $co_list_array)){
        echo errormes("$co_name ($co_code) is not a CO");
        $has_errors = 1;
        break;

    }
   if(!in_array($lo_code, $branch_agents_array)){
       echo errormes("$lo_name ($lo_code) is not in the branch");
       $has_errors = 1;
       break;

   }
    if(!in_array($co_code, $branch_agents_array)){
        echo errormes("$co_name ($co_code) is not in the branch");
        $has_errors = 1;
        break;

    }


    /////////--------LO is not LO, CO is not CO
    ////////---------LO and CO are not in the branch
    ///////----------LO and CO are not a pair

    if($ignore_pairs != 1) {
        if ($co_code != $pairs[$lo_code]) {
            // echo "CSV upload has this wrong pair $lo_code-$co_code on column number <br/>";
            echo "csv has wrong pair $lo_code-$co_code on row number $rowNum.<br/>";
            echo errormes("$co_name ($co_code) and $lo_name ($lo_code) is not a pair");
            $has_errors = 1;
            break;
        }
    }

    $rowNum++;
}

if($has_errors == 1){
    die(errormes("Document has issues that require fixing"));
    exit();
}
$customer_los_array = array();
$customers_cos_array = array();
$mass_logging2 = "";

$has_errors = 0;
$open = fopen("../../pairing_/".$upload, "r");
$data = fgetcsv($open, 10000000, ",");

while (($data = fgetcsv($open, 100000, ",")) !== FALSE){

     $loan_id = trim($data[0]);
     $phone = trim($data[2]);
     $branch = trim($data[3]);
     $lo_name = trim($data[7]);
     $lo_code = trim($data[8]);
     $co_name = trim($data[9]);
     $co_code = trim($data[10]);

    // echo "$lo_code-$co_code <br/>";




    // echo $loan_id.":".$lo_code.','.$co_code.'<br/>';
   if($loan_id > 0){
    $total_records = $total_records + 1;
   }
     $loan_in_branch = in_array($loan_id, $all_branch_loans_array);
     if($loan_id > 0 && $lo_code > 0 && $co_code > 0 && $loan_in_branch == 1){
        ///----Update LO, CO
    
        $upd = updatedb('o_loans',"current_lo='$lo_code', current_co='$co_code'","uid='$loan_id'");
        if($upd == 1){
            $customer_id = $all_branch_loan_customers_array[$loan_id];
          // echo "jjd";
           // store_event('o_loans',"$loan_id","LO and CO updated via upload to $lo_name($lo_code) and $co_name($co_code) respectively");
           $mess = "LO and CO updated via upload to $lo_name($lo_code) and $co_name($co_code) respectively";
           //---Too expensive
           $mass_logging = $mass_logging . ',("o_loans","'.$loan_id.'","'.$mess.'","'.$fulldate.'","'.$userd['uid'].'","1")';

            ////-----Also update the customer LO
            if($customer_id > 0){
                $upd = updatedb('o_customers',"current_agent='$lo_code'","uid='$customer_id'");
                if($upd){
                    $mass2 = "Current agent changed to $lo_name($lo_code)";
                    $mass_logging2 = $mass_logging2 . ',("o_customers","'.$customer_id.'","'.$mass2.'","'.$fulldate.'","'.$userd['uid'].'","1")';

                }
            }
            $total_updated = $total_updated + $upd;
            ////----Log the customer update
        }
        else{
           // echo "$upd";
        }

     }
}
//echo $mass_logging;
$fds = array('tbl','fld','event_details','event_date','event_by','status');
$log = addtodbmulti('o_events', $fds, ltrim($mass_logging, ","));
$log2 = addtodbmulti('o_events', $fds, ltrim($mass_logging2, ","));
//echo $log;

////////////-------------------

echo sucmes("Complete: $total_updated out of $total_records updated., With errors $has_errors Please refresh the page...");
$proceed = 1;


//echo errormes(makeThumbnails($upload_location, "7UpkJa8zGa.jpg",50,50,"ddd.jpg"));



?>
<script>
    if('<?php echo $proceed; ?>'){
        setTimeout(function (){
          //  reload();
        }, 5000);
       // upload_list('<?php echo encurl($rec); ?>','EDIT');
    }
</script>

<?php
session_start();
include_once '../../configs/20200902.php';
include_once ("../../php_functions/functions.php");
include_once ("../../configs/conn.inc");

$userd = session_details();
$category = $_POST['type_'];
$file_name = $_FILES['file_']['name'];
$file_size = $_FILES['file_']['size'];
$file_tmp = $_FILES['file_']['tmp_name'];

if (isset($_POST['agree'])) {
    // Checkbox was checked
  $lock = 1;
} else {
   $lock = 0;
}

//echo errormes("Lock $lock");

$upload_location = '../../pairing_/';

$upload_perm  = permission($userd['uid'],'o_documents',"0","create_");
if($upload_perm == 0) {
    exit(errormes("You don't have permission to upload a file"));
}

$allowed_formats = "csv";
$allowed_formats_array = explode(",", $allowed_formats);
if ($file_size > 0) {
    if ((file_type($file_name, $allowed_formats_array)) == 0) {
        exit(errormes("This file format is not allowed. Only $allowed_formats "));
    }
} else {
    exit(errormes("File not attached or has invalid size"));
}

$upload = upload_file($file_name,$file_tmp,$upload_location);
if($upload === 0)
{
    exit(errormes("Error uploading file, please retry"));
}

$open = fopen("../../pairing_/".$upload, "r");
$data = fgetcsv($open, 10000000, ",");

$cats = array('CC','FA','EDC','IDC');
if(!in_array($category, $cats)){
    die(errormes("Please select a category e.g. CC, FA, EDC"));
}
$agent_names = table_to_obj('o_users',"uid > 0","10000","uid","name");
$sec = array("LOCK_ALLOCATION" => "1");
$total_loans = $total_updated = 0;
$mass_logging = "";
while(($data = fgetcsv($open, 100000, ",")) !== FALSE) {
    $loan_id = trim($data[0]);
    $collector_id = trim($data[1]);
    
    if($loan_id > 0 && $collector_id > 0){
        $sec_ = addslashes(json_encode($sec));
        $upd = updatedb('o_loans',"current_agent='$collector_id', allocation='$category', other_info = JSON_SET(IFNULL(other_info, '{}'), '$.LOCK_ALLOCATION', $lock)","uid='$loan_id'");

       // echo $upd."[$loan_id]";

        $total_loans+=1;
        $total_updated+=$upd;
        $agent_name = $agent_names[$collector_id];
        $mess = "Collector updated to $agent_name($collector_id) and allocation to $category";
        //---Too expensive
        $mass_logging = $mass_logging . ',("o_loans","'.$loan_id.'","'.$mess.'","'.$fulldate.'","'.$userd['uid'].'","1")';
        /////-----Log mass
    }
}

if($total_updated > 1){
    $proceed = 1;
}

echo sucmes("$total_updated/$total_loans updated successfully. Please refresh the page");
$fds = array('tbl','fld','event_details','event_date','event_by','status');
$log = addtodbmulti('o_events', $fds, ltrim($mass_logging, ","));

///////------------End of validation
?>
<script>

    if('<?php echo $proceed; ?>'){
        modal_hide();
        setTimeout(function () {
              //  reload();
        },3000);
    }
</script>


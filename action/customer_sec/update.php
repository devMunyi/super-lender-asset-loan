<?php
session_start();
include_once ("../../php_functions/functions.php");
include_once ("../../configs/conn.inc");

$userd = session_details();
$tbl = $_POST['tbl'];
$record = decurl($_POST['record']);
$key_ = sanitizeAndEscape($_POST['key_'], $con);
$value_ = sanitizeAndEscape($_POST['value_'], $con);
$added_by = $userd['uid'];
$added_date = $fulldate;
$status = $_POST['status'];
$recid = $_POST['recid'];


if((input_available($key_)) == 0)
{
    exit(errormes("Name is required"));
}
if((input_available($value_)) == 0)
{
    exit(errormes("Value is required"));
}

if((input_available($tbl)) == 0)
{
    exit(errormes("Table is not selected"));

}
if($record > 0){

    ////////-------------Check of table and record exists
    $rec_exists = checkrowexists("$tbl","uid='$record'");
    if($rec_exists == 0){
        if($rec_exists == 1){
            exit(errormes("Table, Record can not be found"));
        }
    }
    ////////-------Check if record exists
    $exists = checkrowexists('o_key_values',"tbl='$tbl' AND record='$record' AND key_='$key_' AND uid =!".decurl($recid));
    if($exists == 1){
        exit(errormes("Record already exists"));
    }
}
else{
    exit(errormes("Record not selected"));
}


$update_flds = " key_='$key_', value_='$value_', added_by = '$added_by'";
$create = updatedb('o_key_values',$update_flds,"uid=".decurl($recid));
if($create == 1)
{
    echo sucmes('Record Updated Successfully');
    $proceed = 1;

}
else
{
    echo errormes('Unable to Save Record');
}

?>
<script>
    if("<?php echo $proceed; ?>"){
        setTimeout(function () {
            reload();
        },300);
        //other_list('o_customers','<?php //  echo $_POST['record']; ?>','EDIT');
        //  clear_form('other_frm');
    }
</script>

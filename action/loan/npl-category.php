<?php
session_start();
include_once ("../../php_functions/functions.php");
include_once ("../../configs/conn.inc");

$loan_id = intval($_POST['loan_id']);
$npl_category = intval($_POST['npl_category']);


$userd = session_details();
if($userd == null){
    exit(errormes("Your session is invalid. Please re-login"));
}


if($loan_id > 0){
    $npl_category_name = fetchrow('o_npl_categories',"uid=$npl_category","title");
    $upd = updatedb('o_loans',"npl_uid=$npl_category","uid=$loan_id");
    if($upd == 1){
        $event = "Loan tagged with [$npl_category_name ($npl_category)] by [".$userd['name']."(".$userd['email'].")]";
        store_event('o_loans', $loan_id,"$event");
        if($npl_category > 0) {
            echo sucmes("Success Adding Tag: $npl_category_name");
        }
        else{
            echo sucmes("Success Removing Tag");
        }

        $proceed = 1;
    }
    else{
        if($npl_category > 0) {
            echo errormes("Error Adding Tag: $npl_category_name");
        }
        else{
            echo errormes("Error Removing Tag");
        }
    }
}else{
    echo errormes("Loan ID Missing!");
}

?>
<script>
    if ('<?php echo $proceed; ?>' === "1") {
        setTimeout(function() {
            gotourl("loans?loan=<?php echo encurl($loan_id); ?>");
        }, 1500);
    }
</script>
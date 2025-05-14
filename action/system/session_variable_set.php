<?php
session_start();
include_once ("../../php_functions/functions.php");
include_once ("../../php_functions/secondary-functions.php");
include_once ("../../configs/conn.inc");

$action = $_POST['action'];
$key = $_POST['key_'];
$value = $_POST['value_'];
$reload = $_POST['reload'];

$session = $_SESSION['session_variables'];

if($action == 'ADD'){
    $res = session_variables('ADD',$key, $value);
    if($res == 1){
        echo sucmes("Success");
    }
    else{
        echo errormes("Error occurred");
    }
}
else{
    echo errormes('Action required');
}


///////------------End of validation
?>
<script>

    if('<?php echo $reload; ?>'){
        modal_hide();
        setTimeout(function () {
            reload();
        },2000);
    }
</script>


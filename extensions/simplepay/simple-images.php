<?php
session_start();
$_SESSION['db_name'] = 'main_db';
include_once ("../configs/conn.inc");
include_once ("../php_functions/functions.php");
require_once('../php_functions/AfricasTalkingGateway.php');


?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Page Title</title>
    </head>
    <body style="background: black;">


<?php

$images = fetchtable('o_documents',"status=1 AND category=1","uid","desc","100,600","uid, stored_address");
while($i = mysqli_fetch_array($images)){
    $image = $i['stored_address'];
    $thumb = "thumb_$image";
    echo "<img src=\"../uploads_/$thumb\" class='img'>";
}
?>
<h1>This is a Heading</h1>
<p>This is a paragraph.</p>

    </body>
</html>

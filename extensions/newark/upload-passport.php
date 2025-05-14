<?php
session_start();
include_once ("../php_functions/functions.php");
include_once ("../configs/conn.inc");

$passports = fetchtable('o_customers',"flag=1","uid","asc","10000000","uid, national_id");
while($p = mysqli_fetch_array($passports)){
    $uid = $p['uid'];
    $national_id = $p['national_id'];

    if($uid > 0 && $national_id > 10){
        echo "INSERT INTO o_documents (title, description, category, added_by, added_date, tbl, rec, stored_address, status)
VALUES ('Passport Photo', 'Uploaded',1, 1, NOW(), 'o_customers', $uid, 'passport_$national_id.jpg', 1); <br/>";

        echo "INSERT INTO o_documents (title, description, category, added_by, added_date, tbl, rec, stored_address, status)
VALUES ('ID Front', 'Uploaded',2, 1, NOW(), 'o_customers', $uid, 'id_front_$national_id.jpg', 1); <br/>";

        echo "INSERT INTO o_documents (title, description, category, added_by, added_date, tbl, rec, stored_address, status)
VALUES ('ID Front', 'Uploaded',3, 1, NOW(), 'o_customers', $uid, 'id_back_$national_id.jpg', 1); <br/>";
    }

}
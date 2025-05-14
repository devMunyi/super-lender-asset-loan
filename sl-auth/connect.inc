<?php
error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED & ~E_WARNING);
date_default_timezone_set("Africa/Nairobi");

$hostname1 = 'localhost';
$dbname1   = 'lender_companies'; // Your database name.
$username1 = 'admin';
$password1 = 'Wesr54609_847*u';


$con1=mysqli_connect($hostname1,$username1,$password1,$dbname1);
if(mysqli_connect_errno())
{
    printf('Unable to connect to auth server');
    exit();
}

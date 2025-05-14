<?php
session_start();
include_once ("../php_functions/functions.php");
include_once ("../configs/conn.inc");

//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);

$products =  table_to_obj('o_loan_products', 'uid > 0', 1000, 'uid','name');
$branches =  table_to_obj('o_branches', 'uid > 0', 1000,'name','uid');
$groups =  table_to_obj('o_user_groups', 'uid > 0', 1000,'name','uid');

// Specify the path to your CSV file
$csvFile = 'Team-Login-details.csv';

// Open the CSV file for reading
$file = fopen($csvFile, 'r');

// Check if the file is successfully opened
if ($file !== false) {
    // Loop through each row in the CSV file
    while (($data = fgetcsv($file)) !== false) {
        // $data is an array containing the values of the current row

        // Extract values from the array
        $name = $data[0]; // Replace 0 with the index of your column
        $group = trim($data[1]);
        if($group == 'LO') {
            $user_group = 7;
        }
        else if($group == 'CO'){
            $user_group =  8;
        }
        else if($group == 'TL'){
            $user_group = 5;
        }
        else if($group == 'FCA'){
            $user_group = 13;
        }
        else if($group == 'CC'){
            $user_group = 12;
        }
        else{
            $user_group = 0;
        }

        $email = $data[2];
        $branch = trim($data[3]);  $branch_id = $branches[$branch];
        $pass = $data[4];
        // Add more variables as needed
        $phone = random_int(100000, 99999999);

        //////-----------End of validation
        $epass = passencrypt($pass);
        $hash = substr($epass, 0, 64);
        $salt = substr($epass, 64, 96);



        if(emailOk($email) == 1) {
            $fds = array('name', 'email', 'phone', 'national_id', 'join_date', 'pass1', 'user_group', 'tag', 'pair', 'branch', 'company', 'status');
            $vals = array("$name", "$email", "$phone", "$phone", "$fulldate", "$hash", "$user_group", "", "0", "$branch_id", "5", "1");

            echo json_encode($vals) . '<br/>';
              $create = addtodb('o_users',$fds,$vals);
            if($create == 1)
            {
                $userid = fetchrow('o_users', "email='$email'", "uid");
                $fdss = array('user', 'pass');
                $valss = array("$userid", "$salt");
                $savesalt = addtodb('o_passes', $fdss, $valss);
                echo sucmes('Record Created Successfully');
                $proceed = 1;
                $last_staff = fetchmax('o_users',"email='$email'","uid","uid");
                $sid = $last_staff['uid'];


            }

        }

    }

    // Close the file handle
    fclose($file);
} else {
    // Handle file opening error
    echo 'Error opening file';
}
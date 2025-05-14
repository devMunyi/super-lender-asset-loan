<?php
session_start();
include_once ("../php_functions/functions.php");
include_once ("../php_functions/secondary-functions.php");
include_once ("../configs/conn.inc");

// Call the function
//sendDailyReport();

$xid = $_GET['rid'];
$report_type = $_GET['report_type'];
if($report_type == 'PROGRESS'){
    $thedate = datesub($date, 0, 0, 1);
    $title = "COB Report";
}
else{
    $thedate = $date;
    $title = "Targets Report";
}

if($xid > 0 && $xid < 12){

}
else{
    die();
}


function sendEmail($to, $subject, $message, $cc1, $cc2) {
    global $xid;
    $currentDate = date("j-M-Y");
    $smtp_server = "smtppro.zoho.com";
    $smtp_port = 465; // Use 587 for TLS
    $username = "reports@simplepay.capital";
    $password = "fhV3_yjf";

    $socket = fsockopen("ssl://$smtp_server", $smtp_port, $errno, $errstr, 30);

    if (!$socket) {
        echo "Could not connect to SMTP server: $errstr ($errno)\n";
        return false;
    }

    // Read server response
    function getResponse($socket) {
        $response = "";
        while ($str = fgets($socket, 515)) {
            $response .= $str;
            if (substr($str, 3, 1) == " ") break;
        }
        return $response;
    }

    // Send command to SMTP server
    function sendCommand($socket, $command) {
        fwrite($socket, $command . "\r\n");
        return getResponse($socket);
    }

    // SMTP Authentication
    getResponse($socket); // Get server hello
    sendCommand($socket, "EHLO your-domain.com");
    sendCommand($socket, "AUTH LOGIN");
    sendCommand($socket, base64_encode($username));
    sendCommand($socket, base64_encode($password));



    ////////////-----Message
    ///


    // HTML content for the email
    $html_message = '
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Daily Performance Report</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                background-color: #f7f7f7;
                margin: 0;
                padding: 0;
            }
            .email-container {
                width: 100%;
                max-width: 600px;
                margin: 0 auto;
                background-color: #ffffff;
                padding: 20px;
                border-radius: 8px;
            }
            .header {
                display: flex;
                align-items: center;
                justify-content: space-between;
                padding-bottom: 20px;
            }
            .header img {
                height: 40px;
            }
            .header h1 {
                font-size: 24px;
                color: #333;
            }
         
            .title {
                text-align: center;
                font-size: 22px;
                color: #333;
                margin-bottom: 10px;
            }
            .description {
                text-align: center;
                font-size: 14px;
                color: #666;
                margin-bottom: 20px;
            }
            table {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 20px;
            }
            th, td {
                padding: 12px;
                text-align: center;
                border: 1px solid #ddd;
            }
            th {
                background-color: #003366;
                color: #ffffff;
                font-weight: bold;
            }
            td {
                background-color: #f9f9f9;
            }
            tfoot td {
                background-color: #e9ecef;
                font-weight: bold;
            }
            .footer {
                text-align: center;
                font-size: 12px;
                color: #999;
            }
               .bg-red{
            background-color: #ff7f7f !important;
            }
        </style>
    </head>
    <body>
    <div class="email-container">
        <!-- Header -->
        <div class="header">
            <img src="https://4f87d4cd.spke.pages.dev/logo.png"  alt="Company Logo">
        </div>

        <!-- Title -->
        <div class="title">
            ' .$subject.' - '.$currentDate.'
        </div>

        <!-- Description -->
        <div class="description">
           Please review the performance below
        </div>
        '.$message.'

        <!-- Table -->
        

        <!-- Footer -->
        <div class="footer">
            <p>Do not forward this email to any unauthorized persons or persons outside the organization.</p>
        </div>
    </div>
    </body>
    </html>';
    ///
    ///////////----End of message

   //



    // Sending email
    /*
    sendCommand($socket, "MAIL FROM:<$username>");
    sendCommand($socket, "RCPT TO:<$to>");
    sendCommand($socket, "DATA");

    $headers = "From:  Simplepay\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "To: <$to>\r\n";
    $headers .= "Subject: $subject\r\n\r\n";
    $headers .= "Reply-To: operations@simplepay.capital" . "\r\n";
   // $headers .= "CC: operations@simplepay.capital, ngaramajonah@gmail.com, njerimwangi.fx@gmail.com" . "\r\n"; // Multiple CCs

    sendCommand($socket, $headers . $html_message . "\r\n.");
    sendCommand($socket, "QUIT");


    fclose($socket);
    echo "Email sent successfully!";
    */

    //////////////----Send new


    // Sending email
    sendCommand($socket, "MAIL FROM:<$username>");
    sendCommand($socket, "RCPT TO:<$to>");
    sendCommand($socket, "RCPT TO:<$cc1>");
    sendCommand($socket, "RCPT TO:<$cc2>");
    sendCommand($socket, "DATA");

    $headers = "From: $username\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "To: <$to>, <$cc1>, <$cc2>\r\n";
    $headers .= "Subject: $subject\r\n\r\n";

   // sendCommand($socket, "Subject: $subject\r\nFrom: $username\r\nTo: $to\r\n\r\n$message\r\n.");
    sendCommand($socket, $headers . $html_message . "\r\n.");
    sendCommand($socket, "QUIT");

    fclose($socket);
    echo "Email sent successfully!";



}




//sendEmail("ngaramajonah@gmail.com", "Hello Email", "Custom Message ...","truweb@icthub.co.ke");


//die();



?>


<?php

///---Fetch all regions and branches
$regions = table_to_obj('o_regions',"status=1","100","uid","name");
$region_branches = array();
foreach ($regions as $rid => $rname) {
    $branches = table_to_array('o_branches',"region_id=$rid","100","uid","uid","asc");
   $region_branches[$rid] = $branches;
}

foreach ($regions as $rid => $rname) {
    echo "$rname <br>";


    $userd = array('uid' => 1, 'branch' => 1);

    $end_date = $date;
    $start_date = first_date_of_month($end_date);


// Create DateTime objects
    $start = new DateTime($start_date);
    $end = new DateTime($end_date);

// Calculate the difference in months
    $diff = $start->diff($end);
    $months = ($diff->y * 12) + $diff->m;  // Total months difference
    if ($diff->d > 0) {
        $months++;  // Add one more month if there's a partial month
    }

    $branches_list = implode(',', $region_branches[$rid]);

    $branch_disbursements = array();
    $branch_targets = array();

    $branch_disb_targets = table_to_obj('o_targets', "target_type='DISBURSEMENTS' AND target_group='BRANCH' AND status=1", "1000", "group_id", "amount");
    $branch_names = table_to_obj('o_branches', "uid > 0 AND uid in ($branches_list) AND status=1", "10000", "uid", "name");

    $loans = fetchtable('o_loans', "given_date BETWEEN '$start_date' AND '$end_date' AND disbursed=1 AND status!=0 AND current_branch in ($branches_list)", "uid", "asc", 1000000000, "uid, loan_amount, current_branch");
    while ($l = mysqli_fetch_array($loans)) {
        $loan_amount = $l['loan_amount'];
        $branch_id = $l['current_branch'];
        $branch_disbursements = obj_add($branch_disbursements, $branch_id, $loan_amount);
    }

    $dat = '
<table class="tablex">
    <thead>
    <tr>
        <th>Branch</th>
        <th>EOM Target</th>
        <th>Daily Target</th>
        <th>MTD Target</th>
        <th>MTD Actual</th>
        <th>Variance</th>
        <th class="pull-right">Rate %</th>
    </tr>
    </thead>
    <tbody>';
    $monthly_target_t = $monthly_target = $daily_target_t = $daily_target =
    $mtd_target_t = $mtd_target =
    $progress_t = $progress =
    $deficit_t = $deficit = 0;


    foreach ($branch_names as $bid => $bname) {
        // Calculate monthly target, considering the date range
        $monthly_target = $branch_disb_targets[$bid];
        if ($months > 1) {
            $monthly_target = $monthly_target * $months;
        }

        // Calculate progress, MTD target, and deficit
        $progress = $branch_disbursements[$bid];
        $mtd_target = mtd_target2($monthly_target, 20, $thedate);
        $deficit = false_zero($mtd_target - $progress);
        $rate = round(((false_zero($progress) / false_zero($mtd_target)) * 100), 2);
        $daily_target = $monthly_target / 20;

        // Total calculations for footer
        $monthly_target_t += $monthly_target;
        $daily_target_t += $daily_target;
        $mtd_target_t += $mtd_target;
        $progress_t += $progress;
        $deficit_t += $deficit;
         $color = '';
        ////----Color code for poor performance only for progress
        if($report_type == 'PROGRESS'){
            if($rate < 90){
                $color = 'bg-red';
            }
            else{
                $color = '';
            }
        }

        $dat .= "<tr class='$color'>
                    <td class='$color'>$bname</td>
                    <td class='$color'>" . money($monthly_target) . "</td>
                    <td class='$color'>" . money($daily_target) . "</td>
                    <td class='$color'>" . money($mtd_target) . "</td>
                    <td class='$color'>" . money($progress) . "</td>
                    <td class='$color'>" . money($deficit) . "</td>
                    <td class='$color'><span class=\"label bg-gray disabled pull-right text-black font-16 font-bold\">$rate%</span></td>
                  </tr>";
    }
    $rate_a = roundDown((($progress_t / $mtd_target_t) * 100), 2);

    $dat .= "
    </tbody>
    <tfoot class=\"bg-gray font-bold\">
    <tr>
        <th>Total</th>
        <th>" . money($monthly_target_t) . "</th>
        <th>" . money($mtd_target_t) . "</th>
        <th>" . money($daily_target_t) . "</th>
        <th>" . money($progress_t) . "</th>
        <th>" . money($deficit_t) . "</th>
        <th><span class=\"label bg-purple-gradient pull-right font-16 font-bold\">$rate_a %</span></th>
    </tr>
    </tfoot>
</table>";

   // echo "$dat"; operations.marketmodel@simplepay.capital
   // $toemail = "region".$xid."staff@simplepay.capital";
    $toemail = "truweb@icthub.co.ke";
   if($rid == $xid) {
       echo sendEmail("$toemail", "$title - $rname", "$dat","operations.marketmodel@simplepay.capital","ngaramajonah@gmail.com");
   }
}
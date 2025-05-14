<?php
session_start();
include_once '../configs/20200902.php';

$company = $_GET['c'];
$req_id = $_GET['r'];
$data  = file_get_contents('php://input');

$logFile = 'log-out.txt';
$log = fopen($logFile,"a");
fwrite($log, $data.'Company-'.$company."->".date('Y-m-d H:i:s')."\n");
fclose($log);
$result = json_decode(trim($data), true);

$ResultCode = $result['Result']['ResultCode'];
$ResultDesc = $result['Result']['ResultDesc'];
$TransactionID = $result['Result']['TransactionID'];
$TransactionDetails = $result['Result']['ResultParameters']['ResultParameter'];

//var_dump($TransactionDetails[0]);

foreach ($TransactionDetails as $key => $value) {
    if($value['Key'] == 'TransactionAmount')
    {
        $TransactionAmount = $value['Value'];
    }
    if($value['Key'] == 'B2CUtilityAccountAvailableFunds')
    {
        $B2CUtilityAccountAvailableFunds = $value['Value'];
    }
    if($value['Key'] == 'ReceiverPartyPublicName')
    {
        $ReceiverPartyPublicName = $value['Value'];
    }
}

$user_d = explode('-',$ReceiverPartyPublicName);
$mobile = trim($user_d[0]);
$name = trim($user_d[1]);

if($company > 0) {

    include_once("../php_functions/functions.php");
    include_once("../configs/auth.inc");
    $company_d = company_details($company);
    if ($company_d['uid'] > 0) {

        $db = $company_d['db_name'];
        $_SESSION['db_name'] = $db;
        include_once("../configs/conn.inc");
///////-------------------------End of get company details

        if ($req_id > 0 && $ResultCode == '0') {
            ////////-----------Its a Loan, lets update it
            $loan_det = fetchonerow('o_loans',"uid='$req_id'","given_date, next_due_date, final_due_date");
            $given_date = $loan_det['given_date'];
            $next_due_date = $loan_det['next_due_date'];
            $final_due_date = $loan_det['final_due_date'];

            if($given_date != $date){
                $diff = datediff3($date, $given_date);
                $new_next_d = dateadd($next_due_date, 0,0, $diff);
                $final_due_d = move_to_monday(dateadd($final_due_date, 0,0, $diff));
                $new_dates = "NEW Dates-> Disbursed: $date, Next Due: $new_next_d, Final Due: $final_due_d";
                $new_dates_update = ", given_date='$date', next_due_date='$next_due_date', final_due_date='$final_due_date'";

            }
            else{
                $new_dates = "";
                $new_dates_update = "";
            }

            $save = updatedb('o_loans', "transaction_code='$TransactionID', disburse_state='DELIVERED', disbursed=1, status=3 $new_dates_update", "uid='$req_id'");
            store_event('o_loans', $req_id, "Success: on $fulldate , M-Pesa delivered the funds with message ($TransactionDetails). Transcode ($TransactionID): $new_dates");
        } else if ($req_id > 0 && $ResultCode != '0') {
            $save = updatedb('o_loans', "transaction_code='$TransactionID', disburse_state='FAILED', disbursed=0", "uid='$req_id'");
            store_event('o_loans', $req_id, "Error: on $fulldate , M-Pesa was unable to deliver the funds with message ($TransactionDetails). Transcode ($TransactionID) $data ");

        }

///---------Lets update the balance
        if($B2CUtilityAccountAvailableFunds > 0) {
            updatedb('o_summaries', "value_='$B2CUtilityAccountAvailableFunds', last_update='$fulldate'", "uid=1");
        }
    }
}
else{
    ///------Probably a suspense payment
}




include_once("../configs/close_connection.inc");


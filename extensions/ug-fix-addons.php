<?php
session_start();
include_once("../php_functions/functions.php");
include_once("../configs/conn.inc");

$sql = "SELECT max(ip.payment_date) as payment_date, l.given_date, l.uid as loan_id, l.loan_amount, la.addon_id, la.addon_amount, la.uid as loan_addon_uid FROM o_incoming_payments ip left join o_loans l ON l.uid = ip.loan_id LEFT JOIN o_loan_addons la ON l.uid = la.loan_id WHERE ip.loan_id IN (24542, 27087, 40032, 41489, 41918, 45321, 45591, 48519, 49752, 49759, 50117, 52310, 
    53374, 53466, 55197, 55791, 55890, 56106, 57548, 57604, 58568, 58709, 60022, 60656, 
    61909, 62158, 62400, 63228, 63555, 65379, 66170, 66703, 67024, 67188, 67659, 68724, 
    69022, 69364, 69739, 71170, 71607, 76427) AND (l.loan_balance <= -1500 OR l.loan_balance > 1) AND l.status = 5 AND la.addon_id = 7 group by ip.loan_id";

$result = mysqli_query($con, $sql);

$addon_okay = 0;
$addon_not_okay = 0;
$addon_wrongly_added = 0;
$payment_date_less_than_given_date = 0;
while ($row = mysqli_fetch_array($result)) {

    $max_pid = $row['max_pid'];
    $payment_date = $row['payment_date'];
    $given_date = $row['given_date'];
    $loan_amount = $row['loan_amount'];
    $addon_id = $row['addon_id'];
    $addon_amount = $row['addon_amount'];
    $loan_addon_uid = $row['loan_addon_uid'];

    if ($payment_date > $given_date) {
        // overall days passed
        $passed_days = datediff3($payment_date, $given_date);

        // days above 14 days
        if ($passed_days > 14) {
            $days_above_14 = $passed_days - 14;
            // calculate the interest
            $interest = ($days_above_14 / 100) * $loan_amount;

            if($interest != $addon_amount){
                echo " ADDON NOT OKAY => Loan ID: " . $row['loan_id'] . ", Interest: $interest, Addon Amount: $addon_amount, Payment Date: $payment_date, Given Date: $given_date,  loan_addon_uid: $loan_addon_uid<br/>";
                $addon_not_okay++;

                // update the addon amount
                updatedb('o_loan_addons', "addon_amount='$interest'", "uid='$loan_addon_uid'");

                // force recalculate the loan
                recalculate_loan($row['loan_id'], true);
            }else{
                echo "Addon OKAY => Loan ID: " . $row['loan_id'] . ", Interest: $interest, Addon Amount: $addon_amount, Payment Date: $payment_date, Given Date: $given_date <br/>";
                $addon_okay++;
            }
        } else {
            echo "Addon wrongly added => Loan ID: " . $row['loan_id'] . ", Payment Date: $payment_date, Given Date: $given_date <br/>";
            $addon_wrongly_added++;
        }
    }else {
        echo "Payment Date less than Given Date => Loan ID: " . $row['loan_id'] . ", Payment Date: $payment_date, Given Date: $given_date <br/>";
        $payment_date_less_than_given_date++;
    }
}


echo "<br>Addon OKAY: $addon_okay<br>";
echo "Addon NOT OKAY: $addon_not_okay<br>";
echo "Addon WRONGLY ADDED: $addon_wrongly_added<br>";
echo "Payment Date less than Given Date: $payment_date_less_than_given_date<br>";

// close the connection
mysqli_close($con);

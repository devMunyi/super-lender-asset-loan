<?php
session_start();

include_once ("../configs/20200902.php");
$_SESSION['db_name'] = $db_;
include_once ("../configs/conn.inc");
include_once ("../php_functions/functions.php");



// Define the query
$sql = "
    SELECT
        l.uid AS loan_uid,
        l.final_due_date AS final_due_date,
        p.uid AS payment_uid,
        p.payment_date
    FROM
        o_loans l
    LEFT JOIN (
        SELECT
            loan_id,
            uid,
            payment_date
        FROM
            o_incoming_payments
        WHERE
            (loan_id, payment_date) IN (
                SELECT
                    loan_id,
                    MAX(payment_date)
                FROM
                    o_incoming_payments
                GROUP BY
                    loan_id
            )
    ) p ON l.uid = p.loan_id
    WHERE
        l.paid = 1 AND l.cleared_date = '0000-00-00' LIMIT 200000;
";

// Execute the query
$result = $con->query($sql);

if ($result->num_rows > 0) {
    // Output data of each row
    while ($row = $result->fetch_assoc()) {


        $loan_id = $row["loan_uid"];
        $payment_uid = $row["payment_uid"];
        $loan_final_due_date  = $row['final_due_date'];

        if($payment_uid > 0){
            $payment_date = $row["payment_date"];
        }
        else{
            $payment_date = $loan_final_due_date;
        }

      //  echo "Loan UID: " . $row["loan_uid"] . " - Payment UID: " . $row["payment_uid"] . " - Payment Date: " . $row["payment_date"];
        $update = updatedb('o_loans',"cleared_date='$payment_date'","uid='$loan_id'");

        echo "Loan: $loan_id, Payment_id: $payment_uid, Cleared Date: $payment_date, Final_due_date: $loan_final_due_date,Update: $update<br/>";
        $payment_date = "0000-00-00";
        $loan_id = $payment_uid = 0;
    }
} else {
    echo "0 results";
}


//////----------------------Adds
?>

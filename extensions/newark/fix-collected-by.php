<?php
session_start();
include_once ("../configs/conn.inc");
include_once ("../php_functions/functions.php");


// select all payments whose transaction starts with SP-
$rpp = intval($_GET['rpp'] ?? 10);

$coll_by = "SELECT distinct(loan_id)
FROM o_incoming_payments ip
INNER JOIN o_loans l ON l.uid = ip.loan_id
WHERE ip.loan_id > 0
  AND (ip.collected_by != l.current_co AND ip.collected_by != l.current_agent)
  AND collected_by != 0
ORDER BY ip.uid DESC
LIMIT $rpp";
$payments = mysqli_query($con, $coll_by);

$loanUids = [];
while ($payment = mysqli_fetch_assoc($payments)) {
    $loanUids[] = $payment['loan_id'];
}

$loanUidsStr = implode(",", $loanUids);

$currentAgents = table_to_obj("o_loans", "uid IN ($loanUidsStr)", "$rpp", "uid", "current_agent");

$currentCOs = table_to_obj("o_loans", "uid IN ($loanUidsStr)", "$rpp", "uid", "current_co");




// loop $loanUid and update collected_by
$skipped = 0;
$updated_count = 0;
foreach ($loanUids as $loanUid) {
    $currentAgent = $currentAgents[$loanUid] ? $currentAgents[$loanUid] : 0;
    $currentCO = $currentCOs[$loanUid] ? $currentCOs[$loanUid] : 0;
    $collected_by = $currentAgent ? $currentAgent : $currentCO;
    echo "Loan ID: $loanUid, Collected by: $collected_by <br>";
    if($collected_by == 0){
        $skipped++;
        continue;
    }

    $resp  = updatedb('o_incoming_payments', "collected_by='$collected_by'", "loan_id='$loanUid' AND collected_by = 0");
    if ($resp) {
        $updated_count++;
    }
}


echo "Updated $updated_count records. Skipped $skipped records";

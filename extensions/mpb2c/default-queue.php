<?php
//session_start();
include_once('../../configs/20200902.php');
include_once("../../php_functions/functions.php");
include_once("../../configs/conn.inc");
require(__DIR__ . '/../../vendor/autoload.php'); // must be imported for rmq.php to work
require("../../php_functions/rmqUtils.php");

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

$connection = new AMQPStreamConnection($RMQ_HOST, $RMQ_PORT, $RMQ_USERNAME, $RMQ_PASS);
$channel = $connection->channel();
$rpp = intval($_GET['rpp'] ?? 10);
$rpp = $rpp > 0 ? $rpp : 10;
// $past_date = datesub($date, 0, 0, 5);
// subtract 20 minutes from $fulldate
$past_date = subtractMinutesFromDt($fulldate, 20);

try {
    $queueType = getQueueTypeFromFilePath(__FILE__);
    if (empty($queueType)) {
        echo "Queue Type not set!<br/>";
        return;
    }

    $queueName = getQueueName($queueType);
    if (empty($queueName)) {
        echo "Queue Name not set!<br/>";
        return;
    }

    $channel->queue_declare($queueName, false, true, false, false);

    $retryMinutes = getRetryMinutes($queueType);
    if (empty($retryMinutes)) {
        echo "Retry Minutes not set!<br/>";
        return;
    }

    $retryLimit = getRetryLimit($queueType);
    if (empty($retryLimit)) {
        echo "Retry Limit not set!<br/>";
        return;
    }

    $past_minutes_dt = subtractMinutesFromDt($fulldate, $retryMinutes);
    if (empty($past_minutes_dt)) {
        echo "Past Minutes Datetime not set!<br/>";
        return;
    }

    echo "Queue Type: $queueType, Queue Name: $queueName<br/>";
    echo "Retry Minutes: $retryMinutes, Retry Limit: $retryLimit<br/>";
    echo "Past Minutes DT: $past_minutes_dt<br/>";
    echo "RPP: $rpp<br/>";
    echo "Days Ago 1: $past_date<br/>";

    $query = "SELECT * FROM o_mpesa_queues WHERE status = 1 AND trials < '$retryLimit' AND added_date >= '$past_date 00:00:00' AND queue_type='$queueType' AND retry_time <= '$past_minutes_dt' ORDER BY uid ASC LIMIT $rpp";
    echo "SQL: $query<br/>";

    $queues = mysqli_query($con, $query);
    while ($q = mysqli_fetch_array($queues)) {
        $bal = doubleval(fetchrow('o_summaries', "uid=1", "value_"));
        $b2c_bal = doubleval($bal - 300); // offset arbitrary transaction fee

        $uid = $q['uid'];
        $loan_id = intval($q['loan_id']);
        $q_amount = doubleval($q['amount']);

        if ($b2c_bal < $q_amount) {
            echo "Insufficient balance, $b2c_bal < $q_amount<br/>";
            continue;
        }

        echo "Start Loan $loan_id, Q $uid<br/>";
        if (b2CRmqIsSet()) {
            $processed = updatedb('o_mpesa_queues', "sent_date='$fulldate', feedbackcode='Processed', status=2", "uid='$uid'");
            if ($processed == 1) {
                $loan_d = fetchonerow('o_loans', "uid='$loan_id'", "loan_amount, disbursed_amount, given_date, disburse_state, status");
                $loan_amount = $loan_d['loan_amount'];
                $disbursed_amount = $loan_d['disbursed_amount'];
                $given_date = $loan_d['given_date'];
                $time_ago = datediff($given_date, $date);
                $disburse_state = $loan_d['disburse_state'];
                $status = $loan_d['status'];

                $send_ = 1;
                if ($disbursed_amount != $q_amount) {
                    $send_ = 0;
                    $feedback = "Amounts Mismatch ($loan_amount, $q_amount)";
                }
                if ($status != 2 || $disburse_state != 'NONE') {
                    $send_ = 0;
                    $feedback = "Already Processed ($disburse_state) or wrong status ($status)";
                }
                if ($time_ago >= 4) {
                    $send_ = 0;
                    $feedback = "Loan too old ($time_ago)";
                }

                echo "Feedback: $feedback<br/>";
                echo "Send: $send_<br/>";

                if ($send_ == 1) {
                    $msg = new AMQPMessage($loan_id, ['delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT]);
                    $channel->basic_publish($msg, '', $queueName);
                    $feedback = generateQueueFeedback($queueType);
                }
            }

            store_event('o_loans', $loan_id, "Automatic Loan Processing: State: $send_, Feedback: $feedback");
        } else {
            echo "Requeuing service unavailable<br/>";
            break;
        }
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "<br/>";
} finally {
    // Ensure the channel and connection are closed
    $channel->close();
    $connection->close();
    echo "Done<br/>";
    include_once("../../configs/close_connection.inc");
}

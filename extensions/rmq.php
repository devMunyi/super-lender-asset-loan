<?php

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

// calling script will parse $queueName and $msgID
$connection = new AMQPStreamConnection($RMQ_HOST, $RMQ_PORT, $RMQ_USERNAME, $RMQ_PASS);
$channel = $connection->channel();

// declare durable queue
$channel->queue_declare($queueName, false, true, false, false);

// create persistent message
$msg = new AMQPMessage($msgID, ['delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT]);
$channel->basic_publish($msg, '', $queueName);

$channel->close();
$connection->close();
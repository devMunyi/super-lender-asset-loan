<?php


/**
 * The name of the queues.
 */

class QueueName {
    const B2CDEFQ = 'b2cDEFQueue'; // default queue
    const B2CLBALQ = 'b2cLBALQueue'; // low balance queue
    const B2CFLDQ = 'b2cFLDQueue'; // failed attempt queue
    const B2CMSDQ = 'b2cMSDQueue'; // missed queue
    const CAMPAIGNSMSQ = 'campaignSMSQueue'; // campaign sms queue
}

/**
 * The type of the queues.
 */
class QueueType {
    const DEFAULT = 'DEF'; // type default queue
    const LOW_BAL = 'LBAL'; // type low balance queue
    const FAILED = 'FLD'; // type failed attempt queue
    const MISSED = 'MSD'; // type missed queue
}

/**
 * The retry minutes for the queues.
 */
class QueueRetryMinutes {
    const DEFAULT = 1; // retry minutes for default queue
    const LOW_BAL = 1; // retry minutes for low balance queue
    const FAILED = 1; // retry minutes for failed attempt queue
    const MISSED = 1; // retry minutes for missed queue
}

/**
 * The retry limit for the queues.
 */
class QueueRetryLimit {
    const DEFAULT = 3; // retry limit for default queue
    const LOW_BAL = 3; // retry limit for low balance queue
    const FAILED = 3; // retry limit for failed attempt queue
    const MISSED = 3; // retry limit for missed queue
}

/**
 * The select limit for the queues.
 */
class QRowSelectLimit {
    const DEFAULT = 7; // select limit for default queue
    const LOW_BAL = 5; // select limit for low balance queue
    const FAILED = 1; // select limit for failed attempt queue
    const MISSED = 1; // select limit for missed queue
}

/**
 * Get the queue select limit for a given queue type.
 *
 * @param string $queueType The type of queue (e.g., 'DEF', 'LBAL', 'FLD', 'MSD').
 * @return int|null The select limit for the given queue type, or null if the type is unknown.
 */
function getQRowSelectLimit(string $queueType): ?int {
    // Define the select limit for each queue type
    $selectLimits = [
        'DEF' => QRowSelectLimit::DEFAULT,
        'LBAL' => QRowSelectLimit::LOW_BAL,
        'FLD' => QRowSelectLimit::FAILED,
        'MSD' => QRowSelectLimit::MISSED,
    ];

    // Return the select limit or a default value if the type is unknown
    return $selectLimits[$queueType] ?? null;
}


/**
 * Get the queue type for a given queue name.
 *
 * @param string $queueName The name of the queue (e.g., 'b2cDEFQueue', 'b2cLBALQueue', 'b2cFLDQueue', 'b2cMSDQueue').
 * @return string|null The queue type for the given name, or null if the name is unknown.
 */

function getQueueType(string $queueName): ?string {
    switch ($queueName) {
        case QueueName::B2CDEFQ:
            return QueueType::DEFAULT;
        case QueueName::B2CLBALQ:
            return QueueType::LOW_BAL;
        case QueueName::B2CFLDQ:
            return QueueType::FAILED;
        case QueueName::B2CMSDQ:
            return QueueType::MISSED;
        default:
            return null;
    }
}

/**
 * Get all queue names.
 *
 * @return array An array of all queue names.
 */

function getAllQueueNames(): array {
    return [
        QueueName::B2CDEFQ,
        QueueName::B2CLBALQ,
        QueueName::B2CFLDQ,
        QueueName::B2CMSDQ
    ];
}

/**
 * Get the queue name for a given queue type.
 *
 * @param string $queueType The type of queue (e.g., 'DEF', 'LBAL', 'FLD', 'MSD').
 * @return string|null The queue name for the given type, or null if the type is unknown.
 */

function getQueueName(string $queueType): ?string {
    switch ($queueType) {
        case QueueType::DEFAULT:
            return QueueName::B2CDEFQ;
        case QueueType::LOW_BAL:
            return QueueName::B2CLBALQ;
        case QueueType::FAILED:
            return QueueName::B2CFLDQ;
        case QueueType::MISSED:
            return QueueName::B2CMSDQ;
        default:
            return null;
    }
}

/**
 * Subtracts minutes from a given date string and returns the result in the same format.
 *
 * @param string $date The date in the format 'Y-m-d H:i:s'.
 * @param int $minutes The number of minutes to subtract.
 * @return string The resulting date in the format 'Y-m-d H:i:s'.
 */
function subtractMinutesFromDt(string $dt, int $minutes): ?string {
    // Create a DateTime object from the given date
    $dateTime = DateTime::createFromFormat('Y-m-d H:i:s', $dt);

    if (!$dateTime) {
        return null;
    }

    // Subtract the minutes
    $interval = new DateInterval("PT{$minutes}M");
    $dateTime->sub($interval);

    // Return the date in the same format
    return $dateTime->format('Y-m-d H:i:s');
}


/**
 * Generates feedback based on the queue type.
 *
 * @param string $queueType The type of queue (e.g., 'DEF', 'LBAL', 'FLD', 'MSD').
 * @return string The feedback message.
 */
function generateQueueFeedback(string $queueType): string {
    // Define the feedback messages for each queue type
    $feedbackMessages = [
        'DEF' => 'Default queued Loan posted for processing.',
        'LBAL' => 'Low balance queued Loan posted for processing.',
        'FLD' => 'Failed queued Loan posted for processing.',
        'MSD' => 'Missed queued Loan posted for processing.',
    ];
  
    // Return the feedback message or a default message if the type is unknown
    return $feedbackMessages[$queueType] ?? 'Unknown queue type. Loan posted for processing.';
  }


/**
 * Get the queue type from a given file path.
 *
 * @param string $filePath The file path to extract the queue type from.
 * @return string|null The queue type extracted from the file path, or null if not found.
 */

function getQueueTypeFromFilePath(string $filePath): ?string {
    // Extract the filename from the given file path
    $filename = basename($filePath);

    // Map filenames to their corresponding queue types
    $queueTypeMap = [
        'default-queue.php' => 'DEF', // Default queue
        'failed-queue.php' => 'FLD',  // Failed queue
        'low-bal-queue.php' => 'LBAL', // Low balance queue
        'missed-queue.php' => 'MSD',  // Missed queue
        
    ];

    // Return the queue type if it exists, or null as the missed
    return $queueTypeMap[$filename] ?? null;
}

/**
 * Get the retry minutes for a given queue type.
 *
 * @param string $queueType The type of queue (e.g., 'DEF', 'LBAL', 'FLD', 'MSD').
 * @return int|null The retry minutes for the given queue type, or null if the type is unknown.
 */
function getRetryMinutes(string $queueType): ?int {
    // Define the retry minutes for each queue type
    $retryMinutes = [
        'DEF' => QueueRetryMinutes::DEFAULT,
        'LBAL' => QueueRetryMinutes::LOW_BAL,
        'FLD' => QueueRetryMinutes::FAILED,
        'MSD' => QueueRetryMinutes::MISSED,
    ];

    // Return the retry minutes or a default value if the type is unknown
    return $retryMinutes[$queueType] ?? null;
}

/** 
    * Get the retry limit for a given queue type.
    *
    * @param string $queueType The type of queue (e.g., 'DEF', 'LBAL', 'FLD', 'MSD').
    * @return int|null The retry limit for the given queue type, or null if the type is unknown.
*/

function getRetryLimit(string $queueType): ?int {
    // Define the trial limit for each queue type
    $trialLimits = [
        'DEF' => QueueRetryLimit::DEFAULT,
        'LBAL' => QueueRetryLimit::LOW_BAL,
        'FLD' => QueueRetryLimit::FAILED,
        'MSD' => QueueRetryLimit::MISSED,
    ];

    // Return the trial limit or a default value if the type is unknown
    return $trialLimits[$queueType] ?? null;
}

/**
 * Check if the B2C RMQ is set.
 *
 * @return bool True if the B2C RMQ is set, false otherwise.
 */
function b2CRmqIsSet(): bool {
    global $B2C_RMQ_IS_SET;
    return isset($B2C_RMQ_IS_SET) && intval($B2C_RMQ_IS_SET) == 1;
}


/**
 * Check if the SMS RMQ is set.
 *
 * @return bool True if the SMS RMQ is set, false otherwise.
 */

function smsRmqIsSet(): bool {
    global $SMS_RMQ_IS_SET;
    return isset($SMS_RMQ_IS_SET) && intval($SMS_RMQ_IS_SET) == 1;
}

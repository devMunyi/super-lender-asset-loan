const mysql = require('mysql2/promise');
const axios = require('axios');

// MySQL Connection Config
const dbConfig = {
  host: 'localhost',
  user: 'username',
  password: 'password',
  database: 'your_database',
};

// Function to fetch unsent SMS messages
async function fetchUnsentMessages(limit) {
  const connection = await mysql.createConnection(dbConfig);
  try {
    const [rows] = await connection.execute(
      'SELECT uid, phone, message_body FROM o_sms_outgoing WHERE status = 1 LIMIT ?',
      [limit]
    );
    return rows;
  } catch (error) {
    console.error('Error fetching unsent messages:', error);
    throw error;
  } finally {
    await connection.end();
  }
}

// Function to send SMS messages using Africastalking API
async function sendSMSBulk(phone, message) {
  try {
    const bulkCode = 'YOUR_BULK_CODE';
    const username = 'YOUR_USERNAME';
    const apiKey = 'YOUR_API_KEY';
    const url = 'https://api.africastalking.com/version1/messaging';

    const response = await axios.post(url, {
      username,
      from: bulkCode,
      message,
      to: phone,
    }, {
      headers: {
        'apiKey': apiKey,
        'Content-Type': 'application/x-www-form-urlencoded',
        'Accept': 'application/json',
      },
    });

    return response.data;
  } catch (error) {
    console.error('Error sending SMS:', error);
    throw error;
  }
}

// Main function to send SMS messages
async function sendSMS(limit) {
  try {
    const unsentMessages = await fetchUnsentMessages(limit);

    const sendingPromises = unsentMessages.map(async (message) => {
      const { uid, phone, message_body } = message;
      const response = await sendSMSBulk(phone, message_body);
      
      // Update the status in the database
      const connection = await mysql.createConnection(dbConfig);
      await connection.execute(
        'UPDATE o_sms_outgoing SET status = 2, sent_date = NOW() WHERE uid = ?',
        [uid]
      );
      await connection.end();

      return response;
    });

    await Promise.all(sendingPromises);

    console.log('All SMS messages sent successfully.');
  } catch (error) {
    console.error('Error sending SMS messages:', error);
  }
}

// Set the limit for the number of messages to send per minute
const limit = 5000; // this should take less than a minute to send all messages

// Call the main function to start sending SMS messages
sendSMS(limit);

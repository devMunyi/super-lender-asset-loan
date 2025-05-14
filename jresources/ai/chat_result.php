<?php
session_start();
include_once ("../../php_functions/functions.php");
include_once ("../../php_functions/secondary-functions.php");
include_once ("../../configs/conn.inc");

$userd = session_details();
$userid = $userd["uid"];
if ($userd == null) {
    die(errormes("Your session is invalid. Please re-login"));
    exit();
}

if(isset($_SESSION['session_id'])){
    $session_id = $_SESSION['session_id'];
}
else{
    $session_id = generateRandomString(15);
    $_SESSION['session_id'] = $session_id;
}


$q = addslashes($_POST['q']);



echo "<div class=\"row\">";
echo "<div class=\"col-sm-4\"></div>";

echo "<div class=\"direct-chat-msg right col-sm-8\">
                  <div class=\"direct-chat-primary clearfix\">
                    <span class=\"direct-chat-name pull-right\">You</span>
                    <span class=\"direct-chat-timestamp pull-left\">$full_date</span>
                  </div>
                  <!-- /.direct-chat-info -->
                  <img class=\"direct-chat-img\" src=\"custom_icons/profile.png\" alt=\"Message User Image\"><!-- /.direct-chat-img -->
                  <div class=\"direct-chat-text\">
                    $q
                  </div>
                  <!-- /.direct-chat-text -->
                </div>";
echo "</div>";

$fds = array('user_id','session_id','ask_date','question','status');
$vals = array("$userid","$session_id","$fulldate","$q","1");
$create = addtodb('o_ai_conversations', $fds, $vals);
if($create == 1){
  $last_conv = fetchmaxid('o_ai_conversations',"user_id='$userid' AND session_id='$session_id'","uid");
  $conversation_id = $last_conv["uid"];
}
else{
    echo errormes("An error occurred. Please try again".$create);
    die();
}

///------
$message = "Convert this question into a mysql query. Only return the query without explanations or extra content. just pure mysql query:";
$question = "$q?.";
$schema_instructions = "Here is the table structure:

Table: o_customers
Fields:
-uid (INT, PRIMARY KEY): Unique identifier for each customer
-full_name
-primary_mobile: Phone number
-email_address
-physical_address
-geolocation
-national_id (VARCHAR)
-gender: M for Make, F for Female
-dob: Date of Birth
-added_by (INT, FOREIGN KEY): References o_users.uid
-current_agent(INT, FOREIGN KEY): References o_users.uid
-added_date(Datetime) The datetime it was added
-branch(INT, FOREIGN KEY) References o_branches.uid
-primary_product(INT, FOREIGN KEY) References o_loan_products.uid
-loan_limit(DOUBLE, FOREIGN KEY)  Maximum amount client can borrow
-sec_data (JSON) Stores data in key value pairs, Keys are numbers referencing o_form_fields.uid
-badge_id: (INT, FOREIGN KEY) References o_badges.uid
-status: (INT): 1-Active, 2-Blocked, 3-Lead

Table: o_users (Stores staff accounts)
Fields:
- uid (INT, PRIMARY KEY):
- name (VARCHAR): Name of staff
- email
- phone
- national_id
- join_date
- login_trials: How many times they attempted to login without success
- User_group (INT, FOREIGN KEY): References o_user_groups.uid
- branch (INT, FOREIGN KEY): References o_branches.uid
- status (INT, FOREIGN KEY): References o_staff_statuses.uid

Table: o_branches
Fields:
- uid (INT, PRIMARY KEY)
- name
- status (1-active)

Table: o_loan_products
Fields:
- uid (INT)
- name 
- description
- period (INT): how long
- period_units (int):
- min_amount: Least amount you can borrow
- max_amount: Maximum amount you can borrow
- automatic_disburse(INT): Whether loan is disbursed automatically. 1-Yes, 2-No
- status(INT): 1-Active, 0-Inactive

Table: o_badges
Fields:
- uid
- title
- description

Table: o_user_groups
Fields:
- uid
- name
- description
- status(INT) 1-Active, 0-Deleted

Table: o_loans
Fields:
- uid(INT, PRIMARY KEY)
- customer_id(INT, FOREIGN KEY): References o_users.uid
- group_id(INT, FOREIGN KEY): References o_customer_groups.uid
- account_number(VARCHAR): The phone number that received the funds
- product_id(INT, FOREIGN KEY): References o_loan_products.uid
- loan_amount(Double): Referring to the principal 
- disbursed_amount(Double): Referring to the amount that was disbursed 
- total_repayable_amount(Double): Referring to the total amount that is expected to be paid. Principal+Interest+Other Charges
- total_repaid(Double): Referring to the total amount that has paid towards the loan
- loan_balance(Double): Referring to balance of the loan
- income_earned(Double): How much has been earned from the loan
- given_date(Date): The date the loan was disbursed
- final_due_date(Date): The due date of the loan
- cleared_date(Date): The date the loan was cleared
- added_by (INT, FOREIGN KEY): References o_users.uid
- current_agent(INT, FOREIGN KEY): References o_users.uid, this is current collector
- current_lo(INT, FOREIGN KEY): References o_users.uid, the current loan officer
- current_co(INT, FOREIGN KEY): References o_users.uid, the current collections officer
- allocation(VARCHAR): Where is allocated currently e.g. BRANCH, CALL CENTRE etc
- current_branch(INT, FOREIGN KEY): References o_branches.uid
- loan_stage(INT, FOREIGN KEY): References o_loan_stages.uid
- disbursed(INT): whether loan is disbursed. 1-Yes, 0-No
- paid(INT): Whether loan is paid. 1-Yes, 0-No
- status(INT, FOREIGN KEY): References o_loan_statuses.uid /// By default, select loans in status(3,5,7) or disbursed, cleared, overdue

Table: o_loan_statuses
fields:
 - uid (INT, PRIMARY KEY)
 - name (VARCHAR)  /// Defaulter is another name for Overdue
 
Table: o_loan_stages
Fields:
 - uid(INT, PRIMARY KEY)
 - name (VARCHAR)
 
Table: o_incoming_payments (Payments made by customer)
Fields:
- uid(INT, PRIMARY KEY)
- customer_id(INT, FOREIGN KEY): References o_customers.uid
- branch_id(INT, FOREIGN KEY): References o_branches.uid
- mobile_number(VARCHAR): Payer phone number
- amount(DOUBLE): Payment amount
- transaction_code(VARCHAR): or Mpesa code
- loan_id(INT, FOREIGN KEY): References o_loans.uid
- loan_balance(Double): loan balance after this payment
- payment_date(DATE): when payment was mad
- added_by(INT, FOREIGN KEY): References o_users.uid
- collected_by(INT, FOREIGN KEY): References o_users.uid
- record_method(VARCHAR): 
- status(INT) 1-Active, 0-Deleted
 
Table: o_customer_conversations (For storing interactions)
Fields:
 - uid(INT, PRIMARY KEY)
 - customer_id(INT, FOREIGN KEY): References o_customers.uid
 - branch(INT, FOREIGN KEY): References o_branches.uid
 - agent_id(INT, FOREIGN KEY): References o_users.uid
 - loan_id(INT, FOREIGN KEY): References o_loans.uid
 - transcript(TEXT): the interaction/conversation details
 - conversation_method(INT, FOREIGN KEY): References o_conversation_methods.uid
 - conversation_date(DATETIME)
 - next_interaction(DATETIME): Next time scheduled to interact with client again, the due date of the interaction
 - promised_amount(DOUBLE)
 - conversation_purpose(INT, FOREIGN KEY): References o_conversation_purpose.uid
 - flag(INT, FOREIGN KEY): References o_flags.uid
 
Table: o_flags
Fields:
- uid(INT, PRIMARY KEY)
- name(VARCHAR)

Table: o_conversation_methods
Fields:
- uid(INT, PRIMARY KEY)
- name(VARCHAR)

Table: o_conversation_purpose
Fields:
- uid(INT, PRIMARY KEY)
- name(VARCHAR): PTP is short form for promise to pay

Table: o_sms_outgoing
Fields:
- uid(INT, PRIMARY KEY)
- phone(VARCHAR, FOREIGN KEY): References o_customers.primary_mobile
- message_body(TEXT)
- queued_date(DATETIME): When it was sent
- Status(INT) 1-Waiting, 2-Sent, 0-Deleted
 
Please obey the following rules:
- In the query, only select specified fields above, don't select * 
- Always return SELECT query only, no UPDATE, INSERT, DELETE,DROP, ALTER etc drop 
- Unless it's a referenced or joined table, don't select records where status=0 if such a field exists. That is a soft deleted record
- Unless specified, return a maximum of 10000 records and pick date range of 3 months
- For foreign keys, always join referenced table and display the fields specified namely: o_customers.full_name, o_branches.name, o_users.name, o_loan_products.name, o_badges.title, o_user_groups.name, o_loan_statuses.name, o_loan_stages.name,
o_flags.name, o_conversation_methods.name, o_conversation_purpose.name
-When returning numbers, add thousands separators
-When returning decimals, round-off to 2 decimal places
-For rates, return with a percentage sign at the end
=Do not return
-Be very accurate with queries
-When a query is requested on loans, don't return rejected or reversed loans unless specified
";

$full_prompt = "$message \n $question \n $schema_instructions";



// Example usage
$generatedSQL = json_encode(generateSQLQuery($full_prompt));
$data = json_decode($generatedSQL, true);
// Extract the content field
$content = $data['choices'][0]['message']['content'];
$content_full = $content;

// Remove non-SQL parts (backticks and "sql")
$content = str_replace(['```sql', '```'], '', $content);

// Trim whitespace or newlines from the content
$content = trim($content);

//echo $content;
////------Save the result in the DB
$ansdate=date('Y-m-d H:i:s');
$upd = updatedb('o_ai_conversations',"answer_date='$ansdate', full_response='$content_full', sql_response='$content'","uid='$conversation_id'");
////------End of save

if (isSelectQueryOnly($content)) {
    //echo "This query is a valid SELECT statement.";
} else {
    die(errormes("An error occurred, Invalid dataq"));
}

// Output the cleaned SQL content
$result = mysqli_query($con_a, $content);

if (!$result) {

    die(errormes("AI Failed to return a valid response, refine your question or click submit again."));
}

// Get the column names
$columns = [];
if ($result->num_rows > 0) {
    $columns = array_keys(mysqli_fetch_assoc($result));
    mysqli_data_seek($result, 0); // Reset pointer after fetching column names
}


// Output the extracted content

//echo generateSQLQuery($full_prompt);


///////------AI Respose
echo "<div class=\"row\">";

echo "<div class=\"direct-chat-msg  small-shadow col-sm-11\">
                  <div class=\"direct-chat-info clearfix\">
                    <span class=\"direct-chat-name pull-left\">AI</span>
                    <span class=\"direct-chat-timestamp pull-right\">$full_date</span>
                  </div>
                  <!-- /.direct-chat-info -->
                  <img class=\"direct-chat-img\" src=\"custom_icons/robo.png\" alt=\"Message User Image\"><!-- /.direct-chat-img -->
                  <div class=\"direct-chat-text\" style='background-color: white;'>";
         ?>
              <div class="scroll-hor bg-gray-light font-16">


                     <table id="userTable" class="table-striped tablex gentable_" style="width:100%">
        <thead>
            <tr>
                <?php foreach ($columns as $column): ?>
                    <th><?php echo ucfirst($column); ?></th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <?php
            // Fetch and display rows
            $totals = array_fill(0, count($columns), 0); // Array to store column totals
            while ($row = mysqli_fetch_assoc($result)) {
                echo '<tr>';
                $i = 0;
                foreach ($row as $cell) {
                    echo '<td>' . htmlspecialchars($cell) . '</td>';
                    if (is_numeric($cell)) {
                        $totals[$i] += $cell; // Calculate total for numeric columns
                    }
                    $i++;
                }
                echo '</tr>';
            }
            ?>
        </tbody>
        <tfoot>
            <tr>
                <?php foreach ($totals as $total): ?>
                    <th><?php echo is_numeric($total) ? $total : ''; ?></th>
                <?php endforeach; ?>
            </tr>
        </tfoot>
    </table>

                  </div>
               <br/>
            <div class="graph_area">
                <div class="row">

               <a href="#" onclick="generate_ai_graph('bar'); return false;" class="text-bold col-lg-4 font-16 text-blue"><i class="fa fa-bar-chart"></i> Generate Bar Chart</a>

                <a href="#" onclick="generate_ai_graph('line')" class="text-bold font-16 text-purple col-lg-4"><i class="fa fa-line-chart"></i> Generate Line Chart</a>

                <a href="#" onclick="generate_ai_graph('doughnut')" class="text-bold font-16 text-green col-lg-4"><i class="fa fa-pie-chart"></i> Generate Pie Chart</a>
                </div>
                <div style="display: none;" id="canva_">
                  <canvas  id="myChart" width="400" height="200"></canvas>
                </div>
                  <!-- /.direct-chat-text -->
            </div>
                </div>
</div>


<div class=\"col-sm-1\"></div>
</div>


<script>
    $(document).ready(function() {
        $('#userTable').DataTable({
            dom: 'Bfrtip',
            buttons: [
                'copy', 'csv', 'excel', 'pdf', 'print'
            ],
            "pageLength": 10, // Default number of records to display
            "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
        });
    });
</script>

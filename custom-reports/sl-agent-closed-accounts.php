<?php
$lo_bal = array();
$lo_disbursed = array();
$lo_repaid = array();
$lo_bal_loans = array();
$lo_repayable = array();

$hreport = $_GET['hreport'];
$hid = $_GET['hid'];

$url_ = "?hreport=$hreport&hid=$hid";

?>
    <div class="row">
        <div class="col-lg-3">
            <a href="<?php echo $url_; ?>&ag=12" class="btn btn-primary bg-purple-gradient">CC</a>
            <a href="<?php echo $url_; ?>&ag=13" class="btn btn-primary bg-purple-gradient">FA</a>
            <a href="<?php echo $url_; ?>&ag=21" class="btn btn-primary bg-purple-gradient">IDC</a>
            <a href="<?php echo $url_; ?>&ag=14" class="btn btn-primary bg-purple-gradient">EDC</a>
            <ol class="list-group">


            <?php
            if(isset($_GET['ag'])){
                $g = $_GET['ag'];
                $users = fetchtable('o_users',"status=1 AND user_group=$g","name","asc","1000","uid, name");
                while($u = mysqli_fetch_array($users)){
                    $uuid = $u['uid'];
                    $uname = $u['name'];
                    echo "<li class=\"list-group-item\">
                    <a class=\"text-blue\" href=\"$url_&ag=$g&agent=$uuid\">$uname</a>
                </li>";
                }
            }
            ?>
            </ol>
        </div>

        <div class="col-lg-9">
            <?php
            $agent_id = $userd['uid'];
            $a_name = $userd['name'];

            if(isset($_GET['agent'])){
                $agent_id = $_GET['agent'];
                $a_name = fetchrow('o_users',"uid='$agent_id'","name");
            }

            ?>
            <h4><?php echo $a_name; ?></h4>

            <table id="example2" class="table table-condensed table-striped table-bordered">
                <thead>
                <tr><th>Customer Name</th><th>Phone</th><th>Branch</th>  <th>Due Date</th><th>Closed Date</th><th>Payment Amount</th></tr>
                </thead>
                <tbody>
                <?php
                $loans_array = array();
                $last_payment_date = array();
                $agent_amount = array();
                $total_amount = 0;
                $branches = table_to_obj('o_branches',"uid > 0","10000","uid","name");
                $payments = fetchtable('o_incoming_payments',"payment_date BETWEEN '$start_date' AND '$end_date' AND status=1 AND collected_by = '$agent_id'","uid","asc","1000000","uid, loan_id, payment_date, amount");
                while($p = mysqli_fetch_array($payments)){
                    $loan_id = $p['loan_id'];
                    $payment_date = $p['payment_date'];
                    $amount = $p['amount'];

                    $last_payment_date[$loan_id] = $payment_date;
                    $agent_amount = obj_add($agent_amount, $loan_id, $amount);

                    array_push($loans_array, $loan_id);

                }
                $loans_string = implode(',', $loans_array);
                $customers_array = table_to_array('o_loans',"uid in ($loans_string)","100000","customer_id");
                $customer_list = implode(',', $customers_array);

                $customer_names = table_to_obj('o_customers',"uid in ($customer_list)","100000","uid","full_name");

                $loans = fetchtable('o_loans',"uid in ($loans_string) AND disbursed=1 AND paid=1 AND status!=0 AND current_agent='$agent_id'","uid","desc","1000000","uid, customer_id, account_number, final_due_date, current_branch, customer_id");
                while($l = mysqli_fetch_array($loans)){
                    $uid = $l['uid'];
                    $phone_number = $l['account_number'];
                    $final_due_date = $l['final_due_date'];
                    $current_branch = $l['current_branch'];
                    $customer = $l['customer_id'];

                    $branch_name = $branches[$current_branch];
                    $aamount =$agent_amount[$uid];

                    $total_amount+=$aamount;

                    $closed_date = $last_payment_date[$uid];
                    $customer_name = $customer_names[$customer];

                    echo "<tr><td>$customer_name</td><td>$phone_number</td><td>$branch_name</td>  <td>$final_due_date</td><td>$closed_date</td><td>".money($aamount)."</td></tr>";
                }
                ?>
                </tbody>
                <tfoot>
                <tr><th>--</th><th>--</th><th>--</th>  <th>--</th><th>--</th><th><?php echo money($total_amount); ?></th></tr>
                </tfoot>
            </table>
        </div>


    </div>



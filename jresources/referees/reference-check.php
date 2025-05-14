<?php
session_start();
include_once("../../php_functions/functions.php");
include_once("../../configs/conn.inc");

$reference_number =  make_phone_valid(sanitizeAndEscape($_POST['reference_phone'], $con));

if ((validate_phone($reference_number)) == 1) {
    $o_customer_referees_ = fetchtable('o_customer_referees', "mobile_no = '$reference_number' AND status = 1", "uid", "asc", "0,100", "uid, customer_id, id_no, referee_name, physical_address, email_address, relationship, status");
    $o_customer_alternative_ = fetchtable('o_customer_contacts', "value = '$reference_number' AND status = 1", "uid", "asc", "0,100", "uid, customer_id, contact_type, status ");
    $o_customer_guarantors_ = fetchtable('o_customer_guarantors', "mobile_no = '$reference_number' AND status = 1", "uid", "asc", "0,100", "uid, customer_id, national_id, guarantor_name, physical_address, relationship, status");

    $o_customers = fetchtable('o_customers', "primary_mobile = '$reference_number'", "uid", "asc", "0,100", "uid, full_name, primary_mobile, branch");

    
    $entry = 0;

    $branches = table_to_obj('o_branches',"uid > 0","1000","uid","name");
    $loan_statuses = table_to_obj('o_loan_statuses',"uid > 0","1000","uid","name");

    if ((mysqli_num_rows($o_customer_referees_)) > 0) {

        echo "<table class='table table-striped'>";
        echo "<thead><tr><th>Referee Name</th> <th>Referee Relationship</th><th>Customer Name</th><th>Branch</th><th>Latest Loan</th></tr></thead>";
        while ($t = mysqli_fetch_array($o_customer_referees_)) {
            $uid = $t['uid'];
            $referee_name = $t['referee_name'];
            $referee_relationship = $t['relationship'];
            $customer_id = $t['customer_id'];
            $customer_d = fetchonerow('o_customers', "uid='$customer_id'", "full_name, primary_mobile, branch");
            $relationship_name = fetchrow('o_customer_referee_relationships', "uid='$referee_relationship'", "name");
            $customer_name = $customer_d['full_name'];
            $primary_mobile = $customer_d['primary_mobile'];

            $branch = $customer_d['branch'];
            $branch_name = $branches[$branch];
            $latest_loan = fetchmaxid('o_loans',"customer_id='$customer_id' AND status!=0", "status");

            $latest_loan_status = $loan_statuses[$latest_loan['status']];

            echo "<tr><td>$referee_name</td><td>$relationship_name</td><td>$customer_name ($primary_mobile)</td><td>$branch_name</td><td class='text-purple'><b>$latest_loan_status</b></td></tr>";
        }
        echo "</table>";

        $entry = 1;
    }
    
    if ((mysqli_num_rows($o_customer_alternative_)) > 0) {
        echo "<table class='table table-striped'>";
        echo "<thead><tr><th>Contact Type</th><th>Customer Name</th><th>Branch</th><th>Latest Loan</th></tr></thead>";
        while ($t = mysqli_fetch_array($o_customer_alternative_)) {
            $contact_type = $t['contact_type'];
            $customer_id = $t['customer_id'];
            $customer_d = fetchonerow('o_customers', "uid='$customer_id'", "full_name, primary_mobile, branch");
            $contact_type_name = fetchrow('o_contact_types', "uid='$contact_type'", "name");
            $customer_name = $customer_d['full_name'];
            $primary_mobile = $customer_d['primary_mobile'];

            $branch = $customer_d['branch'];
            $branch_name = $branches[$branch];
            $latest_loan = fetchmaxid('o_loans',"customer_id='$customer_id' AND status!=0", "status");

            $latest_loan_status = $loan_statuses[$latest_loan['status']];

            echo "<tr><td>$contact_type_name ($reference_number)</td><td>$customer_name ($primary_mobile)</td><td>$branch_name</td><td class='text-purple'><b>$latest_loan_status</b></td></tr>";
        }
        echo "</table>";

        $entry = 1;
    } 
    
    if((mysqli_num_rows($o_customer_guarantors_)) > 0) {

        echo "<table class='table table-striped'>";
        echo "<thead><tr><th>Guarantor Name</th> <th>Guarantor Relationship</th><th>Customer Name</th><th>Branch</th><th>Latest Loan</th></tr></thead>";
        while ($t = mysqli_fetch_array($o_customer_guarantors_)) {
            $uid = $t['uid'];
            $guarantor_name = $t['guarantor_name'];
            $guarantor_relationship = $t['relationship'];
            $customer_id = $t['customer_id'];
            $customer_d = fetchonerow('o_customers', "uid='$customer_id'", "full_name, primary_mobile, branch");
            $relationship_name = fetchrow('o_customer_guarantor_relationships', "uid='$guarantor_relationship'", "name");
            $customer_name = $customer_d['full_name'];
            $primary_mobile = $customer_d['primary_mobile'];

            $branch = $customer_d['branch'];
            $branch_name = $branches[$branch];
            $latest_loan = fetchmaxid('o_loans',"customer_id='$customer_id' AND status!=0", "status");

            $latest_loan_status = $loan_statuses[$latest_loan['status']];

            echo "<tr><td>$guarantor_name</td><td>$relationship_name</td><td>$customer_name ($primary_mobile)</td><td>$branch_name</td><td class='text-purple'><b>$latest_loan_status</b></td></tr>";
        }
        echo "</table>";

        $entry = 1;

    } 

    if((mysqli_num_rows($o_customers)) > 0) {
        echo "<table class='table table-striped'>";
        echo "<thead><tr><th>Customer Name</th><th>Branch</th><th>Latest Loan</th></tr></thead>";
        while ($t = mysqli_fetch_array($o_customers)) {
            $uid = $t['uid'];
            $customer_name = $t['full_name'];
            $primary_mobile = $t['primary_mobile'];
            $branch = $t['branch'];
            $branch_name = $branches[$branch];
            $latest_loan = fetchmaxid('o_loans',"customer_id='$uid' AND status!=0", "status");

            $latest_loan_status = $loan_statuses[$latest_loan['status']];

            echo "<tr><td>$customer_name ($primary_mobile)</td><td>$branch_name</td><td class='text-purple'><b>$latest_loan_status</b></td></tr>";
        }
        echo "</table>";

        $entry = 1;
    }

    // not found in any of the tables
    if($entry == 0){
        echo "<h4><i>No results found</i></h4>";
    }

} else {
    echo errormes("Enter valid phone");
}


mysqli_close($con);
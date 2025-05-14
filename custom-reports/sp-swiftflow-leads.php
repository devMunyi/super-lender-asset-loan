<?php

// enable error reporting
// error_reporting(E_ALL);

$branch_id = $_GET['branch'] ?? 0;
if (is_numeric($branch_id) && $branch_id > 0) {
    $prsedBranch = "&branch=" . decurl($branch_id);
} else {
    $prsedBranch = "";
}

$curl = curl_init();
curl_setopt_array($curl, array(
    CURLOPT_URL => "https://swiftflow-backend.spcl.one/api/leads?start=$start_date&end=$end_date$prsedBranch",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'GET',
    CURLOPT_HTTPHEADER => array(
        "Authorization: Bearer $swiftflow_token"
    ),
));

$response = curl_exec($curl);

curl_close($curl);

$leads = json_decode($response, true);
?>


<div class="col-sm-12">
    <table id="example2" class="table table-condensed table-striped table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Branch</th>
                <th>Lead Name</th>
                <th>Phone Number</th>
                <th>Agent</th>
                <th>AddedAt</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $total_counter = 0; 
            foreach ($leads as $lead) {
                $total_counter++;
                $lead_name = $lead['leadName'];
                $phone_number = make_phone_valid($lead['leadPhoneNumber']);
                $branch = $lead['agentBranch'];
                $agent = $lead['agentName'];
                $added_at = date('Y-m-d h:i:s A', strtotime($lead['createdAt'])); 

                echo "<tr><td>$total_counter</td><td>$branch</td><td>$lead_name</td><td>$phone_number</td><td>$agent</td><td>$added_at</td></tr>";
            }
            
            ?>
        </tbody>

        <tfoot>
            <tr>
                <th>#</th>
                <th>--</th>
                <th>--</th>
                <th>--</th>
                <th>--</th>
                <th>--</th>
            </tr>
        </tfoot>
    </table>


</div>

<?php

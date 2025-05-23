<?php
session_start();
include_once("../../php_functions/functions.php");
include_once("../../configs/conn.inc");

$loan_id = $_GET['loan_id'];
if($loan_id > 0){
    $loan_d = fetchonerow('o_loans',"uid='".decurl($loan_id)."'","uid, customer_id");
    $customer_id = $loan_d['customer_id'];
}
else{
    echo "<i>Loan ID is invalid</i>";
    die();
}


?>

<table class="table-bordered font-14 table table-hover">
    <thead><tr><th>Name</th><th>Type</th><th>Status</th><th>Action</th></tr></thead>
    <tbody>
    <?php
    $o_collateral_ = fetchtable('o_collateral',"customer_id='$customer_id' AND status!=0", "uid", "desc", "0,10", "uid ,category ,title ,description ,money_value ,status, loan_id ");
    $collateral_total = countotal('o_collateral',"customer_id='$customer_id' AND status!=0");

    $category_names = table_to_obj('o_asset_categories', "uid > 0", "1000", "uid", "name");
    if($collateral_total > 0){
        while($k = mysqli_fetch_array($o_collateral_))
        {
            $uid = $k['uid'];
            $collateral = encurl($uid);
            $category_name = $category_names[$category];
            $title = $k['title'];
            $description = $k['description'];
            $money_value = $k['money_value'];
            $status = $k['status'];
            if($status == 1){
                $state = "<span class=\"text-orange\"><i class=\"fa fa-info\"></i> Not Used </span>";
                $act = "<button onclick=\"loan_collateral_action($loan_id, $collateral, 2)\" class='btn btn-sm btn-success'><i class='fa fa-check'></i> Apply</button>";
            }
            else if($status == 2){
                if($k['loan_id'] == decurl($loan_id)){
                    $act = "<button onclick=\"loan_collateral_action($loan_id, $collateral, 1)\" class='btn btn-sm btn-primary'><i class='fa fa-link'></i> Release</button>";
                    $state = "<span class=\"text-success\"><i class=\"fa fa-check\"></i> Applied </span>";
                }else{
                    $act = "<button onclick=\"loan_collateral_action($loan_id, $collateral, 2)\" class='btn btn-sm btn-success'><i class='fa fa-check'></i> Apply</button>";
                    $state = "<span class=\"text-muted\"><i class=\"fa fa-ban\"></i> Used </span>";

                }


            }

            echo "<tr><td>$title</td><td>$category_name</td><td>$state</td><td>$act</td> </tr>";
        }

    }else{
        echo "<tr><td colspan='8'><i>No Records Found</i></td></tr>";
    }

    include_once ("../../configs/close_connection.inc");
    ?>
    
    </tbody>


</table>
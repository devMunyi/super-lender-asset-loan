<?php
session_start();
include_once("../../php_functions/functions.php");
include_once("../../configs/conn.inc");
$userd = session_details();

$loan_id = $_GET['loan_id'];

$l = fetchonerow("o_loans","uid='".decurl($loan_id)."'","product_id, loan_stage");
?>


<table style="width: 100%; text-align: center" class="table table-striped font-18">

    <?php
    //////-----------Stages
    $o_product_stages_ = fetchtable('o_product_stages', "product_id=" . $l['product_id'] . " AND status=1", "stage_order, uid", "asc", "0,100", "uid ,stage_id, is_final_stage ");

    $loans_stage_names = table_to_obj('o_loan_stages', "uid > 0", "10000000", "uid", 'name');
    if (mysqli_num_rows($o_product_stages_) > 0) {
        $st = 1;
        while ($b = mysqli_fetch_array($o_product_stages_)) {
            $uid = $b['uid'];
            $stage_id = $b['stage_id'];
            $stage_name = $loans_stage_names[$stage_id];
            $is_final_stage = $b['is_final_stage'];

            if($is_final_stage == 1){
                $dismark = "<a title='Money disbursal stage' class='text-red'><i class='fa fa-bolt'></i></a>";
            }
            else{
               $dismark = "";
            }
            if ($l['loan_stage'] == $stage_id) {
                ///-----------Check permissions to change stage
                $stage_action_permission  = permission($userd['uid'],'o_loan_stages',"$stage_id","general_");
                $enc_loan_stage = encurl($l['loan_stage']);

                if($stage_action_permission == 1) {
                    $action1 = "<button onclick=\"modal_view('/jresources/loans/loan_stage_approve','loan_id=$loan_id&stage_id=$enc_loan_stage','Approve to Next Stage')\" class='btn btn-success btn-sm'><i class='fa fa-check'></i>Approve</button>";
                    $action2 = "<button onclick=\"change_loan_statusV2($loan_id, 6, 'Reject this loan')\" class='btn btn-danger btn-sm'><i class='fa fa-times'></i> Reject</button>";
                }
                echo "<tr class='font-18 font-bold text-green'><td colspan='2'><span class='badge bg-green-gradient'>$st</span> $stage_name (Current) <br/><div class='inaction'> $action1 $action2</div></td></tr>";
            } else {
                echo "<tr><td colspan='2'><span class='badge'>$st</span> $stage_name $dismark</td></tr>";
            }

            $st = $st + 1;
            if($is_final_stage == 1){
                break;
            }
            else{
                echo "<tr><td  colspan='2'><i class='fa fa-arrow-down'></i></td></tr>";
            }
        }
    } else {
        echo "<tr><td class='text-orange font-14 font-bold'><i class='fa fa-info-circle'></i> This product has no product stages</td></tr>";
    }
    include_once ("../../configs/close_connection.inc");
    ?>
</table>

<script>
    $(function() {
        $('.btn').dblclick(false);
    });
</script>
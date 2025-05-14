<?php
session_start();
include_once("../../php_functions/functions.php");
include_once("../../configs/conn.inc");
/////----------Session Check
 $userd = session_details();
$customer_id = $_POST['customer_id'];
$change_pair  = permission($userd['uid'],'o_pairing',"0","update_");
if($change_pair == 0){
    die(errormes("You do not have permission to change agent"));
    exit();
}


if ($userd == null) {
    die(errormes("Your session is invalid. Please re-login"));
    exit();
}
$cust_d = fetchonerow('o_customers',"uid='$customer_id'","current_agent, branch");
//var_dump($cust_d);
$current_agent = $cust_d['current_agent'];
$current_branch = $cust_d['branch'];


?>
<div class="alert alert-info">
    When you change the customer agent, all the loans belonging to the customer will be changed too
</div>
<form class="form-horizontal" onsubmit="return false;" method="post">
    <div class="box-body">
        <div class="form-group">
            <input type="hidden" id="cid" value="<?php echo $customer_id; ?>">
            <label for="agent_id" class="col-sm-3 control-label">Select an LO</label>

            <div class="col-sm-9">
                <select class="form-control" id="agent_id">
                    <option value="0">--Unspecified</option>
                    <?php

                    $and_user_group = " AND user_group = 7 ";
                    if(isset($allowed_agent_groups) && count($allowed_agent_groups) > 0){
                      $and_user_group_ = implode(",",$allowed_agent_groups);
                        $and_user_group = " AND user_group IN ($and_user_group_) ";
                    }
                    $users = fetchtable('o_users',"status=1 $and_user_group AND branch='$current_branch'","name","asc","1000","uid, name, email");
                    while($u = mysqli_fetch_array($users)){
                        $uid = $u['uid'];
                        $name = $u['name'];
                        $email = $u['email'];

                        if($current_agent == $uid){
                            $selected = "SELECTED";
                        }
                        else{
                            $selected = "";
                        }

                        echo "<option $selected value='$uid'> $name [$uid]</option>";
                    }
                    ?>
                </select>
            </div>
        </div>
        <div class="form-group">
            <div class="col-sm-9">
                <button class="btn btn-success" onclick="update_agent('<?php echo $customer_id; ?>');"> Update </button>
            </div>
        </div>

    </div>
</form>



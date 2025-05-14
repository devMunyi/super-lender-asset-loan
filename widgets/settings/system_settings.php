<?php
session_start();
include_once("../../php_functions/functions.php");
include_once("../../configs/conn.inc");

/////----------Session Check
$userd = session_details();
if ($userd == null) {
    die(errormes("Your session is invalid. Please re-login"));
    exit();
}
/////---------End of session check
?>

<h3>System Settings</h3>
<div class="row">
    <div class="col-md-10">
        <table class="table table-bordered table-condensed">

            <thead>
                <tr>
                    <th>Company ID</th>
                    <th>Name</th>
                    <th>Logo</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                //query platform settings table
                $sys = fetchonerow("platform_settings", "uid = 1", "*");
                $name = $sys['name'];
                $logo = $sys['logo'];
                $company_id = $sys['company_id'];
                ?>
                <tr>
                    <td><?php echo $company_id; ?></td>
                    <td><?php echo $name; ?></td>
                    <td><img src="dist/img/<?php echo $logo; ?>" height="100px" /></td>
                    <td><span class="btn btn-xs alert-primary" onclick="modal_view('/forms/system_settings_edit.php','','Edit Settings')"><i class="fa fa-edit"> </i><span>Edit</span></span></td>
                </tr>
            </tbody>

        </table>
    </div>

    <div class="col-md-10 well">
        <table style='background-color: #2c3e50; color:aliceblue' class="table table-bordered table-condensed">

            <!-- <thead></thead> -->
            <tbody>
                <!-- <tr></tr> -->

                <?php if ($cc == 256) { ?>
                    <tr>
                        <th>AIRTEL B2C Balance</th>
                        <td><?php echo money(fetchrow('o_summaries', "uid=5", "value_")); ?></td>
                        <td><a class="btn btn-primary btn-sm" onclick="modal_view('/forms/airtel-b2c-balance-change.php','','Edit Airtel B2C Balance')"> Update</a></td>
                    </tr>
                    <tr>
                        <th>MTN B2C Balance</th>
                        <td><?php echo money(fetchrow('o_summaries', "uid=4", "value_")); ?></td>
                        <td><a class="btn btn-primary btn-sm" onclick="modal_view('/forms/mtn-b2c-balance-change.php','','Edit MTN B2C Balance')"> Update</a></td>
                    </tr>
                <?php } else { ?>
                    <tr>
                        <th>B2C Balance</th>
                        <td><?php echo money(fetchrow('o_summaries', "uid=1", "value_")); ?></td>
                        <td><a class="btn btn-primary btn-sm" onclick="modal_view('/forms/b2c-balance-change.php','','Edit B2C Balance')"> Update</a></td>
                    </tr>
                <?php } ?>

                <tr>
                    <th>SMS Balance</th>
                    <td><?php echo money(fetchrow('o_summaries', "uid=3", "value_")); ?></td>
                    <td><a class="btn btn-primary btn-sm" onclick="modal_view('/forms/sms-balance-change.php','','Edit SMS Balance')"> Update</a></td>
                </tr>

                <?php if ($cc == 254) { ?>
                    <tr>
                        <th>B2C PIN</th>
                        <td>********</td>
                        <td><a class="btn btn-primary btn-sm" onclick="modal_view('/forms/security_credential.php','','Edit Mpesa Password')"> Update</a></td>
                    </tr>

                    <tr>
                        <th>B2B</th>
                        <th></th>
                        <td><a class="btn btn-primary btn-sm" onclick="modal_view('/forms/b2b-transfer','','Make Transfer')">Transfer</a></td>
                    </tr>
                <?php } ?>

            </tbody>

        </table>
    </div>
</div>
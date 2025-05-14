<?php
session_start();
include_once '../../configs/20200902.php';
$_SESSION['db_name'] = $db_;
include_once '../../php_functions/functions.php';
include_once '../../configs/conn.inc';


$userd = session_details();
if($userd == null){
    exit(errormes("Your session is invalid. Please re-login"));
}

$offset_ = $_POST['offset'];
$rpp_ = $_POST['rpp'];
$limit = "$offset_, $rpp_";
$page_no = $_POST['page_no'];
$orderby = $_POST['orderby'];
$dir = $_POST['dir'];
$customer = decurl($_POST['cid']);
$phone_no = $_POST['phone_no'];

//-----------------------------Reused Query

//default or all interactions
$o_cust_broadcasts = fetchtable(
    'o_sms_outgoing',
    "phone='$phone_no' AND status > 0",
    "$orderby",
    "$dir",
    "$limit",
    'message_body, sent_date, status'
);

///----------Paging Option
$alltotal = countotal('o_sms_outgoing', "phone='$phone_no' AND status > 0");

$cust = fetchonerow('platform_settings', 'uid=1', 'name');
$company_name = $cust['name'];

if ($alltotal > 0) {
    $row = '';
    while ($msg = mysqli_fetch_array($o_cust_broadcasts)) {
        $message_body = $msg['message_body'];
        $sent_date = $msg['sent_date'];
        // $sent_date = '2023-03-15';

        if (isset($sent_date)) {
            $fancy_date = fancydate($sent_date);
        }else {
            $fancy_date = " ";
        }

        $status = $msg['status'];

        // message sent status
        if($status == 2){
            $status_display = "
            <span class='text-success' title='message already sent'>
                <i class='fa fa-check'></i>
            </span>";
            $classed = 'message sent';
        }else {
        // message queued status
        $status_display = "
        <span class='text-grey' style='font-size: 14px'>
            <i class='fa fa-clock-o'></i>
        </span>";
        $classed = 'message queued';
        }

        $row =
            $row .
            "<div class='direct-chat-msg'>
                    <div class='direct-chat-infos clearfix'>
                      <span class='direct-chat-name pull-left'>" .
            $company_name .
            "</span>
                      <span class='direct-chat-timestamp pull-right' title='".$classed."'>" .
                      $status_display . " " . $sent_date . " ". $fancy_date. "</span>
                    </div>
                    <i class='fa fa-university direct-chat-img img-circle' style='font-size: 32px;' aria-hidden='true'></i>
                    <div class='direct-chat-text'>
                      " .
            $message_body .
            "
                    </div>
                  </div>
            ";

        //////------Paging Variable ---
        //$page_total = $page_total + 1;
        /////=======Paging Variable ---
    }
} else {
    $row = "<tr><td colspan='7'><i>No Records Found</i></td></tr>";
}
?>


<!-- Conversations are loaded here -->
<div class="">
  <?php echo $row .
      "<tr style='display: none;'><td><input type='hidden' id='_alltotal_' value='$alltotal'></td></tr>"; ?>
</div>

<?php 
$permi = permission($userd["uid"], "o_sms_outgoing", "0", "create_");
if($permi == 1){ ?>
<div class="pull-right">
    <button class="btn btn-primary bg-blue-gradient" onclick="modal_view('/forms/broadcasts_add_form.php','customer_id=<?php echo $customer; ?>','Send New Message to Client'); modal_show();  "><i class="fa fa-plus"></i> New Message</button>
</div>

<?php } ?>

<?php include_once '../../configs/close_connection.inc'; ?>

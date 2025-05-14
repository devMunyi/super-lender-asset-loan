<?php
session_start();
include_once("php_functions/functions.php");
include_once("configs/conn.inc");
include_once("php_functions/authenticator.php");

$company = company_settings();
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?php echo $company['name']; ?> | Campaign</title>
    <!-- Tell the browser to be responsive to screen width -->
    <?php
    include_once('header_includes.php');
    ?>
</head>

<body class="hold-transition skin-purple sidebar-mini">
    <div class="wrapper">

        <?php
        include_once('header.php');
        ?>
        <!-- Left side column. contains the logo and sidebar -->
        <?php
        include_once('menu.php');
        ?>

        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">
            <!-- Content Header (Page header) -->
            <!-- Main content -->
            <?php
            $view_campaigns = permission($userd['uid'], 'o_campaigns', "0", "general_");
            if ($view_campaigns != 1) {
                die(errormes("You don't have permission to view broadcasts"));
                exit();
            }

            if (isset($_GET['campaign'])) {
            ?>
                <div class="_details">
                    <?php include_once "widgets/campaign-details.php"; ?>
                </div>
            <?php
            } elseif (isset($_GET['campaign-add-edit'])) {
            ?>
                <div class="_form">
                    <?php include_once "forms/campaign-add-edit.php"; ?>
                </div>
            <?php } else { ?>
                <div class="_list">
                    <?php include_once "widgets/campaign-list.php"; ?>
                </div>
            <?php } ?>
            <!-- /.content -->
        </div>

        <!-- /.content-wrapper -->
        <?php
        include_once("footer.php");
        ?>


        <!-- Control Sidebar -->

        <!-- /.control-sidebar -->
        <!-- Add the sidebar's background. This div must be placed
         immediately after the control sidebar -->
        <div class="control-sidebar-bg"></div>
    </div>
    <!-- ./wrapper -->

    <?php
    include_once("footer_includes.php");
    ?>
    <script>
        $(function() {


            ////////-------------Doc Load Other Function
            if (document.getElementById('example1')) {
                campaign_list();
                pager('#example1');
            }
            let camp_status = '<?php echo $camp_status ?>';

            if (camp_status == 1) {
                pager("#example2");
            } else if (camp_status == 2) {
                $("#example2").remove();
                $("#inactive_campaign").html("<p class =\"font-14 text-red alert\" style='text-align:center'><b>No audience for inactive campaign</b></p>");
            }

            if ('<?php echo $message_list; ?>') {
                campaign_message_list('<?php echo $message_list; ?>', 'EDIT');
            }

            /////-----SMS count checker
            $('#description').on('input', function() {
                const maxChars = <?php echo $max_sms_char ? $max_sms_char : 0; ?>;
                const charCount = $(this).val().length;

                // Update the character count display
                $('#char_count').text(`Characters: ${charCount}/${maxChars}`);

                // Disable or enable the button based on the character count
                if (charCount >= maxChars) {
                    // $('#btn_campaign_msg').prop('disabled', true);
                } else {
                    // $('#btn_campaign_msg').prop('disabled', false);
                }
            });


        })
    </script>
</body>

</html>
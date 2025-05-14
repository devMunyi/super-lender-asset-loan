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
  <title><?php echo $company['name']; ?> | Customers</title>
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
      if (isset($_GET['type'])) {
        $view = $_GET['type'];
      } else {
        $view = 'Customer';
      }
      if (isset($_GET['customer']) || isset($_GET['customern'])) {
        $view = 'customer-details'
          ?>
        <div class="_details">
          <?php
          include_once('widgets/customer-details.php');
          ?>
        </div>

        <?php
      } elseif (isset($_GET['customer-add-edit'])) {
        ?>
        <div class="_form">
          <?php
          include_once('forms/customer-add-edit-form.php');
          ?>
        </div>
      <?php } else { ?>
        <div class="_list">
          <?php include_once('widgets/customer-list.php'); ?>
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
    $(function () {
      let orderby = '<?php echo $_GET['orderby']; ?>';
      let dir = '<?php echo $_GET['dir']; ?>';
      if (!orderby) {
        orderby = 'uid';
      }
      if (!dir) {
        dir = 'desc';
      }


      if (document.getElementById('example1')) {
        customer_list();
        pager('#example1');
      }

      ////////-------------Doc Load Other Function

      if ('<?php echo $contact_list; ?>') {
        contact_list('<?php echo $contact_list; ?>', 'EDIT');
      }

      if ('<?php echo $guarantor_list; ?>') {
        guarantor_list('<?php echo $guarantor_list; ?>', 'EDIT');
      }

      if ('<?php echo $referee_list; ?>') {
        referee_list('<?php echo $referee_list; ?>', 'EDIT');
      }
      if ('<?php echo $collateral_list; ?>') {
        collateral_list('<?php echo $collateral_list; ?>', 'EDIT');
      }
      if ('<?php echo $upload_list; ?>') {
        upload_list('<?php echo $upload_list; ?>', 'EDIT');
      }

      ;
      const currentURL = window.location.href;

      if ('<?php echo $geo_textarea_display ?>' == 'none') {
        getGeolocation().then(locationURL => {
          const current_geolocation = locationURL?.trim();
          $('#geolocation').val(current_geolocation);

        }).catch(error => {
          const feed = "<div class='alert alert-danger'>" + error + "</div>";
          // feedback("DEFAULT", "TOAST", ".feedback_", feed, "4");
        })
      } else if (currentURL.includes("bio")) {
        const initial_geolocation = $('#initial_geolocation').val()?.trim();
        getGeolocation().then(locationURL => {
          const current_geolocation = locationURL?.trim();

          if ('<?php echo $geo_textarea_display; ?>' == 'none') {
            $('#geolocation').val(locationURL?.trim());
          } else if ('<?php echo $geo_textarea_display; ?>' == 'block') {
            // $('#geolocation').val(locationURL?.trim());
          }

        }).catch(error => {
          const feed = "<div class='alert alert-danger'>" + error + "</div>";
          // feedback("DEFAULT", "TOAST", ".feedback_", feed, "4");
        })
      }
      //}

      /////-----Enforce geo location
      let geo_enforced = '<?php echo $geo_location_enforced; ?>';
      if (geo_enforced == 1) {
        window.onload = checkLocationServices;
      }

      /////-----End of enforce geo location

    });
    ////////------------Function to check Geo location
    function checkLocationServices() {
      if ("geolocation" in navigator) {
        navigator.geolocation.getCurrentPosition(
          function (position) {
            document.getElementById("locationStatus").innerText = "Location services are enabled.";
          },
          function (error) {
            $('#locationbox').fadeIn('fast');
            if (error.code === error.PERMISSION_DENIED) {
              document.getElementById("locationStatus").innerText = "Your location is disabled";
              document.getElementById("enableButton").style.display = "inline";
              $('#bio_form').css('display', 'none');
            } else {
              document.getElementById("locationStatus").innerText = "Unable to determine location status.";
            }
          }
        );
      } else {
        $('#locationbox').fadeIn('fast');
        document.getElementById("locationStatus").innerText = "Geolocation is not supported by this browser.";
      }
    }

    function showInstructions() {
      alert("Please enable location by clicking the location icon in the address bar and then refresh the page");
    }
    ///////Function to check Geo location



    ///===== interactions tab date picker functions

    const encCustomerID = $('#cid')?.val()?.trim();
    $('#period_')?.daterangepicker({
      autoUpdateInput: false,
      opens: 'left'
    }, function (start, end, label) {
      console.log("A new date selection was made: " + start.format('YYYY-MM-DD') + ' to ' + end.format('YYYY-MM-DD'));
      $('#c_start_d').val(start.format('YYYY-MM-DD'));
      $('#c_end_d').val(end.format('YYYY-MM-DD'));

      interactions_filter();
    });

    $('#ni_period_')?.daterangepicker({
      autoUpdateInput: false,
      opens: 'left'
    }, function (start, end, label) {
      console.log("A new date selection was made: " + start.format('YYYY-MM-DD') + ' to ' + end.format('YYYY-MM-DD'));
      $('#ni_start_d').val(start.format('YYYY-MM-DD'));
      $('#ni_end_d').val(end.format('YYYY-MM-DD'));


      interactions_filter();
    });

    ///==== End of interactions tab date picker functions
  </script>

  <script>
    if ("<?php echo isset($_GET['other']) ?>") {

      // Helper function to get input value as a number
      function getInputValue(id) {
        const value = parseInt(document.getElementById(id).value, 10);
        return isNaN(value) || value <= 0 ? 0 : value;
      }

      // Helper function to set the result in the DOM
      function setResult(id, value) {
        const num = parseInt(value, 10);
        const element = document.getElementById(id);
        element.value = Math.max(0, isNaN(num) ? 0 : num);
      }

      function calculateWeeklyExpenses() {
        const monthlyRent = getInputValue("in_27");
        let weeklyRent = monthlyRent > 0 ? monthlyRent / 4 : 0;
        const weeklyUtilities = getInputValue("in_28");
        const weeklyTransport = getInputValue('in_31');
        const weeklyExpenses = weeklyRent + weeklyUtilities + weeklyTransport;
        setResult("in_25", weeklyExpenses);
        return weeklyExpenses;
      }

      function calculateProfits() {
        // Get input values
        const weeklySales = getInputValue("in_22");
        const weeklyStockSpend = getInputValue("in_23");
        const weeklyExpenses = getInputValue("in_25"); // Get the current expenses value

        // Calculate gross profit (Sales - Stock Spend)
        const weeklyGrossProfit = weeklySales - weeklyStockSpend;
        setResult("in_24", weeklyGrossProfit);

        // Calculate net profit (Gross Profit - Expenses)
        const weeklyNetProfit = weeklyGrossProfit - weeklyExpenses;
        setResult("in_26", weeklyNetProfit);
      }

      // Initialize all calculations
      function updateAllCalculations() {
        calculateWeeklyExpenses();
        calculateProfits();
      }

      // Attach event listeners
      document.getElementById("in_22").addEventListener("input", updateAllCalculations);
      document.getElementById("in_23").addEventListener("input", updateAllCalculations);
      document.getElementById("in_27").addEventListener("input", updateAllCalculations);
      document.getElementById("in_28").addEventListener("input", updateAllCalculations);
      document.getElementById("in_31").addEventListener("input", updateAllCalculations);

      // Initial calculation on page load
      updateAllCalculations();
    }
  </script>

</body>

</html>
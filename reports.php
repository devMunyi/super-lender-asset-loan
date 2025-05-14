<?php

// set memory limit
ini_set('memory_limit', '-1');

// enable error reporting
error_reporting(E_ALL);

session_start();

include_once("php_functions/functions.php");
include_once("configs/conn.inc");
if ($has_archive == 1) {
    include_once("configs/archive_conn.php");
}

$company = company_settings();
$kpi_measured = table_to_obj('o_user_groups', "uid>0", "1000", "uid", "kpi_measured");
$staff_groups = table_to_obj('o_users', "uid>0", "100000", "uid", "user_group");
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?php echo $company['name']; ?> | Reports</title>
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

        $downloadPermi = 1;
        if(isset($restrict_report_download)  && $restrict_report_download == 1){
            $downloadPermi = permission($userd['uid'], 'o_reports', "0", "DOWNLOAD");
        }


        // extract user_group
        $user_group = $userd['user_group'] ?? 0;
        if (isset($_GET['regreport'])) {
            $regional_report = $_GET['regreport'];
            $region = 0;

            if (isset($_GET['region'])) {
                $region = intval($_GET['region'] ?? 0);
            }

            $andRegbranches = "";
            $andRegbranches_loan = "";
            $andRegbranches_pay = "";
            $andRegion = "";
            if ($region > 0) {
                $region_d = decurl($region);
                $reg_branches = table_to_array('o_branches', "region_id=$region_d", "1000", "uid", "uid", "asc");
                if (sizeof($reg_branches) > 0) {
                    $reg_branches_list = implode(",", $reg_branches);
                    $andRegbranches = " AND uid in ($reg_branches_list)";
                    $andRegbranches_loan = " AND current_branch in ($reg_branches_list)";
                    $andRegbranches_pay = " AND branch_id in ($reg_branches_list)";
                }

                $andRegionUid = " AND uid=$region_d";
                $andRegion = " AND region_id=$region_d";
            }
        } else {
            ///////////---------------Permissions
            $read_all = permission($userd['uid'], 'o_loans', "0", "read_");
            $user_branch = $userd['branch'];

            if ($read_all == 1) {
                $anduserbranch = $andbranch_loan = "";
            } else {

                if (isset($_GET['branch']) && $_GET['branch'] > 0) {
                    $user_branch = decurl($_GET['branch']);
                }

                $anduserbranch = " AND branch='$user_branch'";
                $andbranch_loan = " AND current_branch='$user_branch'";

                //////-----Check users who view multiple branches
                $staff_branches = table_to_array('o_staff_branches', "agent=" . $userd['uid'] . " AND status=1", "1000", "branch", "uid", "asc");
                if (sizeof($staff_branches) > 0) {
                    ///------Staff has been set to view multiple branches
                    if (intval($userd['branch']) > 0) {
                        array_push($staff_branches, $userd['branch']);
                    }

                    // if array contains 0 remove it
                    if (($key = array_search(0, $staff_branches)) !== false) {
                        unset($staff_branches[$key]);
                    }

                    $staff_branches_list = implode(",", $staff_branches);
                    $anduserbranch = " AND branch in ($staff_branches_list)";
                    $andbranch_loan = " AND current_branch in ($staff_branches_list)";
                }
            }
        }

        ////-----Branches to SEE
        $read_b = permission($userd['uid'], 'o_branches', "0", "read_"); /////Can view all branches
        if ($read_b == 1) {
            ////-----Read all
            $andbranch = "";
            $can_read_all_branches = 1;
        } else {
            ///-----Read My Branch
            $andbranch = " AND uid = '$user_branch'";
            $can_read_all_branches = 0;
            //////-----Check users who view multiple branches
            $staff_branches = table_to_array('o_staff_branches', "agent=" . $userd['uid'] . " AND status=1", "1000", "branch", "uid", "asc");
            if (sizeof($staff_branches) > 0) {
                ///------Staff has been set to view multiple branches
                array_push($staff_branches, $userd['branch']);
                $staff_branches_list = implode(",", $staff_branches);
                $andbranch = " AND uid in ($staff_branches_list)";
            }
        }

        ////------Other GET variables
        $other_get = "";
        $ag = $_GET['ag'];
        $agent = $_GET['agent'];
        if (isset($_GET['ag'])) {
            $other_get .= "&ag=$ag";
        }
        if (isset($_GET['agent'])) {
            $other_get .= "&agent=$agent";
        }
        if (isset($_GET['type'])) {
            $type = $_GET['type'];
            $other_get .= "&type=$type";
        }

        $andLoanProductId = "";
        $andLoanProductIdWithTableAlias = "";
        $andCustomerPrimaryProduct = "";
        $andCustomerPrimaryProductWithTableAlias = "";

        if (isset($_GET["product"]) && enforceInteger($_GET["product"]) > 0) {
            $selected_product_id = decurl(intval($_GET["product"]));
            $andLoanProductId = " AND product_id = $selected_product_id ";
            $andLoanProductIdWithTableAlias = " AND l.product_id = $selected_product_id ";

            $andCustomerPrimaryProduct = " AND primary_product = $selected_product_id ";
            $andCustomerPrimaryProductWithTableAlias = " AND c.primary_product = $selected_product_id ";
        }

        /// ------End of other GET variables
        
        $branches = table_to_obj('o_branches', "status=1", "1000", "uid", "name");
        $staff_branch = table_to_obj('o_users', "status=1", "1000", "uid", "branch");


        ?>

        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">
            <!-- Content Header (Page header) -->


            <!-- Main content -->


            <?php
            /////----------Reports View Permission
            $view_reports = permission($userd['uid'], 'o_reports', "0", "read_"); /////Can view reports
            if ($view_reports == 1) {
                ?>
                <section class="content-header">
                    <h1>
                        Reports
                        <small>List</small>
                    </h1>
                    <ol class="breadcrumb">
                        <li><a href="index"><i class="fa fa-dashboard"></i> Home</a></li>
                        <li class="active"><?php echo $branches[$userd['branch']]; ?></li>
                    </ol>

                    <div class="container">
                        <div class="row">
                            <div class="report-search-container">
                                <?php

                                if (empty($_SERVER['QUERY_STRING'])) { ?>
                                    <span class="report-search-container_span">
                                        <input type='text' id='search_report' onkeyup='search_reports()' class='form-control'
                                            placeholder='Search Reports...'>
                                    </span>

                                <?php } ?>

                                <section class="report-search-container_section">
                                    <a href="reports?add-edit" class="btn btn-success"><i
                                            class="fa fa-plus pull-right"></i>New Report</a>
                                    <a href='pairing' class='btn btn-danger'> <i class='fa fa-link pull-right'></i> Pairing
                                        V1</a>
                                    <a href='pairing-v2' class='btn btn-primary'> <i class='fa fa-link pull-right'></i>
                                        Pairing V2</a>
                                    <section>
                            </div>
                        </div>
                    </div>
                </section>
                <section class="content">

                    <div class="">

                        <?php



                        //////////////-------------------Process the report dates
                        $loan_statuses = table_to_obj('o_loan_statuses', "status=1", "100", "uid", "name");
                        if (isset($_GET['report']) || isset($_GET['system']) || isset($_GET['hreport']) || isset($_GET['regreport'])) {
                            $rep_id = $_GET['report'];
                            $report_type = 'CUSTOM';
                            if (isset($_GET['system'])) {
                                $rep_id = $_GET['system'];
                                $report_type = 'SYSTEM';
                            } elseif (isset($_GET['hreport'])) {
                                $report_type = 'HREPORT';
                                $rep_id = $_GET['hreport'];
                            } elseif (isset($_GET['regreport'])) {
                                $report_type = 'REGREPORT';
                                $rep_id = $_GET['regreport'];
                            }
                            if (intval($rep_id) > 0) {
                                $x = fetchonerow("o_reports", "uid='" . decurl($rep_id) . "'", "*");
                                $uid = $x['uid'];
                                $title = $x['title'];
                                $description = $x['description'];
                                $row_query = $x['row_query'];
                                $branch_query = $x['branch_query'];
                                $added_by = $x['added_by'];
                                $added_date = $x['added_date'];
                                $viewable_by = $x['viewable_by'];
                            }
                            if (isset($_GET['hreport'])) {
                                $hid = $_GET['hid'];
                                $hurl = $_GET['hreport'];
                                $x = fetchonerow("o_reports_custom", "uid='" . decurl($hid) . "' OR report_url='$hurl'", "title");
                                $title = $x['title'];
                            }
                            if (isset($_GET['regreport'])) {
                                $hid = $_GET['hid'];
                                $hurl = $_GET['regreport'];
                                $x = fetchonerow("o_reports_custom", "uid='" . decurl($hid) . "' OR report_url='$hurl'", "title");
                                $title = $x['title'];
                            }
                            if ($show_mtd_reports == 'TRUE') {
                                $start_date = "$thisyear-$thismonth-01";
                            } else {
                                $start_date = datesub($date, 0, 0, 1);
                            }
                            $end_date = $date;
                            if (isset($_GET['from'])) {
                                $start_date = $_GET['from'];
                            }
                            if (isset($_GET['to'])) {
                                $end_date = $_GET['to'];
                            }
                            if (isset($_GET['branch'])) {
                                $branch_ = $_GET['branch'];
                            }
                            if (isset($_GET['region'])) {
                                $region_ = $_GET['region'];
                            }

                            if ($lock_report_date_range == 1) {
                                $report_length = intval(datediff3($start_date, $end_date));
                                if ($report_length > 31) {
                                    $end_date = $start_date + 30;

                                    echo errormes("You can only select 1 month date range");
                                }
                            }
                            /////-----
                    

                            ////////////-----------------Process report branch
                            if (!isset($_GET['regreport'])) {
                                // region filter
                                if ($region_ > 0 && $branch_ == 0) {
                                    $region_d = $region = decurl($region_);
                                    $andRegion = " AND uid = $region_d";
                                    $andRegionUid = " AND uid = $region_d";
                                    $reg_branches_arr = table_to_array('o_branches', "region_id=$region_d", "1000", "uid", "uid", "asc");
                                    $region_branches = implode(",", $reg_branches_arr);
                                    $andbranch1 = " AND uid IN ($region_branches)";
                                    $andbranch_loan = " AND current_branch IN ($region_branches)";
                                    $andbranch_pay = " AND branch_id IN ($region_branches)";
                                    $andbranch_client = " AND branch IN ($region_branches)";
                                    $andbranch = " AND uid IN ($region_branches)";
                                } else if ($branch_ > 0) {
                                    // branch filter
                                    $branch_d = decurl($branch_);
                                    $andbranch1 = " AND uid = $branch_d";
                                    $andbranch_loan = " AND current_branch = $branch_d";
                                    $andbranch_pay = " AND branch_id = $branch_d";
                                    $andbranch_client = " AND branch = $branch_d";
                                    if ($can_read_all_branches == 1) {
                                        $andbranchx = $branch_d;
                                    } else {
                                        $andbranchx = $user_branch;
                                    }
                                } else {
                                    if ($can_read_all_branches == 1) {
                                        $andbranch1 = $_andbranch1 = "";
                                        $andbranch_loan = $_andbranch_loan = "";
                                        $andbranch_pay = $_andbranch_pay = "";
                                        $andbranch_client = $_andbranch_client = "";
                                        $branch_d = decurl($branch_);
                                        $andbranchx = $branch_d;
                                    } else {
                                        $andbranch1 = " AND uid = $user_branch";
                                        $andbranch_loan = " AND current_branch = $user_branch";
                                        $andbranch_pay = " AND branch_id = $user_branch";
                                        $andbranch_client = " AND branch = $user_branch";

                                        //////-----Check users who view multiple branches
                                        $staff_branches = table_to_array('o_staff_branches', "agent=" . $userd['uid'] . " AND status=1", "1000", "branch", "uid", "asc");
                                        if (sizeof($staff_branches) > 0) {
                                            ///------Staff has been set to view multiple branches
                                            array_push($staff_branches, $userd['branch']);
                                            $staff_branches_list = implode(",", $staff_branches);
                                            $andbranch1 = " AND uid in ($staff_branches_list)";
                                            $andbranch_loan = " AND current_branch in ($staff_branches_list)";
                                            $andbranch_pay = " AND branch_id in ($staff_branches_list)";
                                            $andbranch_client = " AND branch in ($staff_branches_list)";
                                        }

                                        $_andbranch1 = "";
                                        $_andbranch_loan = "";
                                        $_andbranch_pay = "";
                                        $_andbranch_client = "";

                                        $andbranchx = $user_branch;
                                    }
                                }
                            }







                            //////-------------------------End of process report branch
                    


                            ?>
                            <div class="">
                                <div class="box box-primary">
                                    <div class="box-title well">
                                        <div class="report-filters-container">
                                            <div>
                                                <h4 class="font-bold"><a href="reports"> <i class="fa fa-arrow-up"></i> </a>
                                                    <?php echo $title; ?></h4>
                                            </div>
                                            <div>
                                                <table>
                                                    <tr>
                                                        <td class="report-filters-container_table_tr_td">
                                                            <?php if ($has_regions == "TRUE") {
                                                                ?>
                                                                <select id="region_" class="form-control top-select" onchange="">
                                                                    <option value="0">All Regions</option>
                                                                    <?php
                                                                    $regions = fetchtable('o_regions', "status=1", "name", "asc", "1000", "uid, name");
                                                                    while ($r = mysqli_fetch_array($regions)) {
                                                                        $rid = encurl($r['uid']);
                                                                        $rname = $r['name'];
                                                                        if ($_GET['region'] == $rid) {
                                                                            $rsel = "selected";
                                                                        } else {
                                                                            $rsel = "";
                                                                        }
                                                                        echo "<option value='$rid' $rsel>$rname</option>";
                                                                    }
                                                                    ?>
                                                                </select>
                                                            <?php } ?>

                                                            <?php if (!isset($_GET['regreport'])) { ?>
                                                                <select id="branch_" class="form-control top-select" onchange="">
                                                                    <option value="0">All Branches</option>
                                                                    <?php
                                                                    $branches = fetchtable('o_branches', "status=1 $andbranch", "name", "asc", "1000", "uid, name");
                                                                    while ($b = mysqli_fetch_array($branches)) {
                                                                        $bid = encurl($b['uid']);
                                                                        $bname = $b['name'];
                                                                        if ($branch_ == $bid) {
                                                                            $bsel = "selected";
                                                                        } else {
                                                                            $bsel = "";
                                                                        }
                                                                        echo "<option value='$bid' $bsel>$bname</option>";
                                                                    }
                                                                    ?>
                                                                </select>
                                                            <?php } ?>

                                                            <?php if ($product_filter_required == "TRUE") {
                                                                ?>
                                                                <select id="product_" class="form-control top-select" onchange="">
                                                                    <option value="0">All Products</option>
                                                                    <?php
                                                                    $products = fetchtable('o_loan_products', "status=1", "name", "asc", "1000", "uid, name");
                                                                    while ($p = mysqli_fetch_array($products)) {
                                                                        $pid = encurl($p['uid']);
                                                                        $pname = $p['name'];
                                                                        $p_sel = ($_GET['product'] == $pid) ? "selected" : "";
                                                                        echo "<option value='$pid' $p_sel>$pname</option>";
                                                                    }
                                                                    ?>
                                                                </select>
                                                            <?php } ?>

                                                        </td>

                                                    </tr>
                                                </table>
                                            </div>

                                            <div class="report-filters-container__div">
                                                <div>

                                                    From <input type="date" title="From" class="btn btn-default bg-gray"
                                                        value="<?php echo $start_date; ?>" id="from_date">
                                                </div>
                                                <div>
                                                    To <input type="date" class="btn btn-default bg-gray" title="To"
                                                        id="to_date" value="<?php echo $end_date; ?>"> </div>
                                            </div>


                                            <div class="report-filters-container__div">
                                                <div><button class="btn btn-primary"
                                                        onclick="run_reports('<?php echo $rep_id; ?>','<?php echo $report_type; ?>','<?php echo $other_get; ?>');">
                                                        RUN <i class="fa fa-arrow-right"></i></button></div>

                                                <div><a class="btn btn-default" href="?add-edit=<?php echo $rep_id; ?>"> <i
                                                            class="fa fa-edit"></i></a></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="box-body scroll-hor">
                                        <table class="table table-condensed table-striped" id="example3">
                                            <?php
                                            if (isset($_GET['report'])) {
                                                $rep_id = $_GET['report'];
                                                if ($can_read_all_branches == 1) {
                                                    if (intval($branch_) > 0) {
                                                        $sql = "$branch_query";
                                                    } else {
                                                        $sql = "$row_query";
                                                    }
                                                } else {
                                                    $sql = "$branch_query";
                                                }


                                                $sql = str_replace('{{{start_date}}}', "'" . $start_date . "'", $sql);
                                                $sql = str_replace('{{{end_date}}}', "'" . $end_date . "'", $sql);

                                                // handle branch query by removing any spacing double or single between equal sign and {{{branch}}}
                                                if (strpos($sql, '{{{branch}}}') !== false) {
                                                    if (count($staff_branches) > 0 && intval($_GET['branch']) == 0) {
                                                        $sql = preg_replace('/\s*=\s*{{{branch}}}/', '={{{branch}}} ', $sql);

                                                        $sql = str_replace('={{{branch}}}', " IN ($staff_branches_list) ", $sql);
                                                    } else {
                                                        $sql = str_replace('{{{branch}}}', "'" . $andbranchx . "'", $sql);
                                                    }
                                                }

                                                // echo "andbranchx => $andbranchx <br>";
                                    

                                                // $sql = str_replace('{{{branch_loan}}}', "'" . $andbranch_loan . "'", $sql);
                                                // $sql = str_replace('{{{branch_payment}}}', "'" . $andbranch_pay . "'", $sql);
                                                // $sql = str_replace('{{{branch_client}}}', "'" . $andbranch_client . "'", $sql);
                                    
                                                echo "<div style='display: none;'>$sql</div>";

                                                // echo "query======= => $sql<br>";
                                                try {

                                                    $result = mysqli_query($con, $sql);
                                                    $result1 = mysqli_query($con, $sql);
                                                } catch (Exception $e) {
                                                    echo $e->getMessage();
                                                }


                                                $loop = 0;
                                                ///////------Write table heads
                                                $obj = mysqli_fetch_array($result);
                                                $times = 0;
                                                foreach ($obj as $field => $value) {
                                                    $times += 1;
                                                    $head .= "<tr>";
                                                    if (!is_numeric($field)) {
                                                        $head_ .= "<th>" . $field . "</th>";
                                                    } /////This is not a real key. Only Mysql knows what it is
                                                    $head .= "</tr>";
                                                }

                                                echo '<thead>' . $head_ . '</thead>';
                                                /*
                                                $display_ok = ($group_uid == 5 || $read_all == 1 || $read_b == 1) && $report_type == 3963;

                                                if ($display_ok) {
                                                    $repaid_totals = $repayable_totals = $balance_totals = $amount_disbursed_totals = $interest_totals = 0;
                                                }
                                           */

                                                while ($row = mysqli_fetch_array($result1)) {
                                                    $rec .= "<tr>";
                                                    foreach ($row as $r => $v) {
                                                        //echo "Key=" . $r . ", Value=" . $v;
                                    
                                                        if (!is_numeric($r)) {
                                                            $rec .= "<td>" . $v . "</td>";
                                                        } /////This is not a real key. Only Mysql knows what it is
                                    

                                                    }
                                                    /*
                                                    if ($display_ok) {
                                                        $repayable = $row['Amount_Repayable'];
                                                        $total_repaid = $row['Amount_Repaid'];
                                                        $balance = $row['Balance'];
                                                        $amount_disbursed = $row['Amount_Disbursed'];
                                                        $interest = $row['Interest'];

                                                        $repayable_totals += $repayable;
                                                        $repaid_totals += $total_repaid;
                                                        $balance_totals += $balance;
                                                        $amount_disbursed_totals += $amount_disbursed;
                                                        $interest_totals += $interest;
                                                    } */

                                                    $rec .= "</tr>";
                                                    echo $rec;
                                                    $rec = "";
                                                    $loop = $loop + 1;
                                                }
                                            } elseif (isset($_GET['hreport'])) {
                                            } else {
                                                ///
                                            }
                                            ////-------System Report cut
                                    
                                            ///--------System report cut
                                            ?>
                                        </table>
                                        <?php
                                        if (isset($_GET['hreport']) || isset($_GET['regreport'])) {
                                            $report_name = $_GET['hreport'] ?? $_GET['regreport'];
                                            include_once "custom-reports/$report_name";
                                        }
                                        ?>

                                    </div>
                                </div>
                            </div>
                            <?php
                        } elseif (isset($_GET['system'])) {
                            $sys = $_GET['system'];
                            //echo "djjd";
                        } elseif (isset($_GET['add-edit'])) {
                            ?>
                            <?php
                            if ($userd['user_group'] == 1) {
                                ?>
                                <div class="">
                                    <!-- /.box -->
                                    <?php
                                    include_once "forms/report-add-edit.php";
                                    ?>
                                </div>
                                <?php
                            } else {
                                echo errormes("You don't have permission to add/edit reports");
                            }
                        } else {
                            ?>
                            <div class="mt-4">
                                <div class="report-cards-container">
                                    <?php




                                    ////-----------Block of reports list
                                    ////////----------------hibrid reports
                                    $hibrid = fetchtable('o_reports_custom', "status=1", "uid", "asc", "1000", "*");
                                    while ($hi = mysqli_fetch_array($hibrid)) {

                                        $huid = $hi['uid'];
                                        $tit = $hi['title'];
                                        $qsKey = 'hreport';

                                        // check if title contains region
                                        if (strpos($tit, 'Region') !== false) {
                                            $qsKey = 'regreport';
                                        }

                                        $descr = $hi['description'];
                                        $report_url = $hi['report_url'];
                                        /*    echo "<div class=\"col-md-3\"> <a href=\"?$qsKey=$report_url&hid=" . encurl($huid) . "\">
                                
                                    <div class=\"box box-success\">
                                        <div class=\"box-header with-border\">
                                            <h3 class=\"box-title font-bold\">$tit</h3>

                                            <!-- /.box-tools -->
                                        </div>
                                        <!-- /.box-header -->
                                        <div class=\"box-body\">
                                            $descr
                                        </div>
                                        <!-- /.box-body -->
                                    </div>
                                    <!-- /.box -->
                               
                            </a> </div>";   */

                                        echo " <a href=\"?$qsKey=$report_url&hid=" . encurl($huid) . "\"><div class=\"masonry-item\" title='$descr'>
                                        <div class=\"masonry-item-header\">
                                            <span class=\"custom-badge\">$huid</span>
                                            <span class=\"fw-bold\"><h4 class='font-bold text-black text-control' style='width: 250px;'>$tit</h4></span>
                                        </div>
                                        <p style='width: 250px;' class='font-14 text-primary text-control'>$descr</p>
                                    </div> </a>";
                                    }


                                    $o_reports_ = fetchtable('o_reports', "status=1", "uid", "asc", "0,100", "uid ,title ,description ,row_query ,added_by ,added_date ,viewable_by ");
                                    if ((mysqli_num_rows($o_reports_)) == 0) {
                                        echo "<h3 class='font-italic'>No reports defined</h3>";
                                    } else {
                                        while ($x = mysqli_fetch_array($o_reports_)) {
                                            $uid = $x['uid'];
                                            $title = $x['title'];
                                            $description = $x['description'];
                                            $row_query = $x['row_query'];
                                            $added_by = $x['added_by'];
                                            $added_date = $x['added_date'];
                                            $viewable_by = trim($x['viewable_by'] ?? '');
                                            if (strlen($viewable_by) > 0) {
                                                $viewable_by = explode(",", $viewable_by);
                                            } else {
                                                $viewable_by = [];
                                            }


                                            $user_uid = $userd['uid'] ?? 0;
                                            if (in_array($user_group, $viewable_by) || count($viewable_by) < 1 || in_array($user_uid, $viewable_by)) {
                                                /*    echo "<div class=\"col-md-3\"> <a href=\"?report=" . encurl($uid) . "\">
                                
                                    <div class=\"box box-success\">
                                        <div class=\"box-header with-border\">
                                            <h3 class=\"box-title font-bold\">$title</h3>

                                            <!-- /.box-tools -->
                                        </div>
                                        <!-- /.box-header -->
                                        <div class=\"box-body\">
                                            $description
                                        </div>
                                        <!-- /.box-body -->
                                    </div>
                                    <!-- /.box -->
                               
                            </a> </div>";  */




                                                echo " <a href=\"?report=" . encurl($uid) . "\"><div class=\"masonry-item\" style=\"background: #fffee2;\" title='$description'>
                                        <div class=\"masonry-item-header\" >
                                            <span class=\"custom-badge\">$uid</span>
                                            <span class=\"fw-bold\"><h4 class='font-bold text-black text-control' style='width: 250px;'>$title</h4></span>
                                        </div>
                                        <p class='font-14 text-primary text-control' style='width: 250px;'>$description</p>
                                    </div> </a>";
                                            }
                                        }
                                    }

                                    /// -----------Block of report list
                                    ?>

                                </div>
                            </div>
                            <?php
                        }








                        ?>
                        <!-- /.col -->

                    </div>
                </section>

            <?php } else {
                echo errormes("You don't have permission to view this page");
            } ?>

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
        $(document).ready(function () {
            const downloadPermi = '<?php echo $downloadPermi; ?>';
            const buttons = downloadPermi === "1"
                ? ['copy', 'csv', 'excel', 'pdf', 'print']
                : [];

            $('#example2')?.DataTable({
                dom: 'Bfrtip',
                pageLength: 20,
                buttons: buttons
            });



            var additionalOptions = {
                dom: 'Bfrtips',
                buttons: [
                    'copy', 'csv', 'excel', 'pdf', 'print'
                ]
            };
            const dataTableOptions = $.extend({}, additionalOptions, {
                // Your DataTables configuration options go here
                footerCallback: function (row, data, start, end, display) {
                    const api = this.api();

                    // Check if footer exists; if not, create it
                    let footer = $('#example3 tfoot');
                    if (footer.length === 0) {
                        footer = $('<tfoot></tfoot>').appendTo('#example3');
                        api.columns().every(function (index) {
                            footer.append("<th style='padding-left:0.5rem; padding-right:0.5rem;'></th>");
                        });
                    }

                    // Iterate over each footer cell
                    footer.find('th').each(function (index) {
                        if (index > 0) { // Skip the first column if needed
                            // Calculate the total for the column
                            const total = api.column(index, {
                                search: 'applied'
                            })
                                .data()
                                .reduce((sum, value) => {
                                    const num = parseFloat(value.replace(/,/g, ''));
                                    if (value.trim() !== '' && !isNaN(num) && value.includes('.') && num > 0 && num <= 100000000) {
                                        return sum + num;
                                    }
                                    return sum;
                                }, 0);


                            // Format the total
                            const formattedTotal = total !== 0 ?
                                (isNaN(total) ? '' : total.toLocaleString('en-US', {
                                    minimumFractionDigits: 2,
                                    maximumFractionDigits: 2
                                })) :
                                '---';

                            // Update the footer cell
                            $(this).text(formattedTotal);

                            // Add class 'text-center' if formattedTotal is empty
                            if (formattedTotal === '' || formattedTotal === '---') {
                                $(this).addClass('text-center');
                            }
                        } else {
                            $(this).text('Total'); // Optionally label the first column
                        }
                    });
                }
            });


            var dataTable = $('#example3')?.DataTable(dataTableOptions);

            // Recalculate totals on table redraw (after filtering)
            dataTable?.on('draw.dt', function () {
                dataTable.footerCallback();
            });
        });


        function search_reports() {
            // Get the input value
            const query = document.getElementById('search_report').value.toLowerCase();

            // Get all masonry items
            const items = document.querySelectorAll('.masonry-item');

            // Loop through each item and check if it contains the search query
            items.forEach(item => {
                // Get the text content of the item (you can modify this selector to be more specific if needed)
                const text = item.textContent.toLowerCase();

                // Check if the item text includes the search query
                if (text.includes(query)) {
                    item.style.display = ''; // Show the item
                } else {
                    item.style.display = 'none'; // Hide the item
                }
            });
        }


    </script>
</body>

</html>
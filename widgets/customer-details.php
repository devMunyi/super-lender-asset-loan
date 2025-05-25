<section class="content-header">
    <?php
    $agent_id = $userd['uid'] ?? 0; // $userd will come from the header script
    if (isset($_GET['customer'])) {
        $customer_ = $_GET['customer'];
        $customer_id = decurl($customer_);
        $cust = fetchonerow('o_customers', "uid='$customer_id'");
    } elseif (isset($_GET['customern'])) {
        $customer_ = $_GET['customern'];
        $cust = fetchonerow('o_customers', "primary_mobile='$customer_'");
    }

    $product_id = $cust['primary_product'] ?? 0;

    $town = fetchrow('o_towns', "uid='" . $cust['town'] . "'", "name");
    $added_by = fetchrow('o_users', "uid='" . $cust['added_by'] . "'", "name");
    $phone_ = $cust['primary_mobile'];

    $phone_provider = $cust['phone_number_provider'] ? $cust['phone_number_provider'] : "";

    if (intval($phone_provider) > 0) {
        $phone_provider = " (" . fetchrow('o_telecomms', "uid = $phone_provider", "name") . ")";
    }

    $branch = fetchrow('o_branches', "uid='" . $cust['branch'] . "'", "name");
    $product = fetchrow('o_loan_products', "uid='" . $cust['primary_product'] . "'", "name");
    $state = fetchonerow('o_customer_statuses', "code='" . $cust['status'] . "'", "name, color");

    $group_name = "";
    if ($group_loans > 0) {
        $customer_group_id = fetchrow('o_group_members', "status=1 AND customer_id='$customer_id'", "group_id");
        if ($customer_group_id > 0) {
            $gid = encurl($customer_group_id);
            $group_name = "<a href='groups?group=$gid' <b><i class='fa fa-angle-double-right'></i> " . fetchrow('o_customer_groups', "uid='$customer_group_id'", "group_name") . "</b></a>";
        }
    }


    $status = "<span class='label " . $state['color'] . "'>" . $state['name'] . "</span>";
    if ($cust['current_agent']) {
        $current_lo = fetchrow('o_users', "uid='" . $cust['current_agent'] . "'", "name");
        $pair_agent = fetchrow('o_pairing', "lo='" . $cust['current_agent'] . "'", "co");
        if ($pair_agent > 0) {
            $current_co = fetchrow('o_users', "uid='" . $pair_agent . "'", "name");
        } else {
            $current_co = "<i>Unspecified</i>";
        }
    } else {
        $current_lo = "<i>Unspecified</i>";
        $current_co = "<i>Unspecified</i>";
    }



    $passport_photo = fetchrow('o_documents', "category='1' AND tbl='o_customers' AND rec=$customer_id AND status=1", "stored_address");
    if (!$passport_photo) {
        $profile = "";
    } else {

        $img = locateImageServer($passport_photo);
        $profile = "<img src='$img' class='img-bordered' width='100%'>";
    }

    if (isset($_GET['type'])) {
        $view = $_GET['type'];
    } else {
        $view = 'Customer';
    }
    ?>
    <h1>
        <?php echo $view; ?> Details
        <small><?php echo $cust['full_name']; ?></small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="index"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active"><?php echo $cust['full_name']; ?></li>
    </ol>
</section>
<section class="content">
    <div class="row">
        <div class="col-xs-12">
            <div class="nav-tabs-custom">
                <ul class="nav nav-tabs nav-justified font-16">
                    <li class="nav-item nav-100 active"><a href="#tab_1" data-toggle="tab" aria-expanded="false"><i
                                class="fa fa-info"></i> Bio Info</a></li>
                    <li class="nav-item nav-100"><a href="#tab_2"
                            onclick="loadContacts('<?php echo $customer_; ?>', '<?php echo $cust['primary_mobile']; ?>')"
                            data-toggle="tab" aria-expanded="false"><i class="fa fa-phone"></i> Contact Info</a></li>
                    <li class="nav-item nav-100"><a href="#tab_3" onclick="account_info('<?php echo $customer_; ?>');"
                            data-toggle="tab" aria-expanded="false"><i class="fa fa-money"></i> Account Info</a></li>
                    <li class="nav-item nav-100"><a href="#tab_11"
                            onclick="customerAccountStatement('<?php echo $customer_; ?>');" data-toggle="tab"
                            aria-expanded="false"><i class="fa fa-list"></i> Statement</a></li>
                    <?php
                    if ($display_guarantors_tab == 1) {
                        ?>
                        <li class="nav-item nav-100"><a href="#tab_10"
                                onclick="loadGuarantors('<?php echo $customer_; ?>'); convert_to_hyperlinks();"
                                data-toggle="tab" aria-expanded="false"><i class="fa fa-users"></i> Guarantors</a></li>
                    <?php }
                    ?>

                    <li class="nav-item nav-100"><a href="#tab_4"
                            onclick="loadReferees('<?php echo $customer_; ?>'); convert_to_hyperlinks();"
                            data-toggle="tab" aria-expanded="false"><i class="fa fa-users"></i> Referees</a></li>
                    <li class="nav-item nav-100"><a href="#tab_5" onclick="loadCollateral('<?php echo $customer_; ?>')"
                            data-toggle="tab" aria-expanded="false"><i class="fa fa-tag"></i> Collateral</a></li>
                    <li class="nav-item nav-100"><a href="#tab_7" onclick="loadUploads('<?php echo $customer_; ?>')"
                            data-toggle="tab" aria-expanded="false"><i class="fa fa-cloud-upload"></i> Uploads</a></li>
                    <li class="nav-item nav-100"><a href="#tab_6"
                            onclick="loadCustomerEvents('<?php echo $customer_; ?>')" data-toggle="tab"
                            aria-expanded="false"><i class="fa fa-clock-o"></i> Events</a></li>
                    <li class="nav-item nav-100"><a href="#tab_8"
                            onclick="interactions_load('<?php echo $customer_; ?>','#comments_list')" data-toggle="tab"
                            aria-expanded="false"><i class="fa fa-comments-o"></i> Interactions</a></li>

                    <li class="nav-item nav-100"><a href="#tab_9" onclick="loadMessages()" data-toggle="tab"
                            aria-expanded="false"><i class="fa fa-comments"></i> Messages</a></li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane active" id="tab_1">
                        <div class="row">
                            <div style="margin-top: 25px;" class="col-md-2">
                                <?php echo $profile; ?>
                            </div>
                            <div class="col-md-7">
                                <?php
                                if ($limit_maker_checker == 1) {
                                    $last_limit = fetchmax('o_customer_limits', "customer_uid='$customer_id' AND status!=0", "uid", "uid, amount, status, comments");
                                    $last_state = $last_limit['status'];
                                    $limit_amount = $last_limit['amount'];
                                    $limit_id = $last_limit['uid'];
                                    $comments = $last_limit['comments'];

                                    $approve_perm = permission($userd['uid'], 'o_customer_limits', "0", "general_");
                                    if ($approve_perm == 1) {
                                        $act = "<button onclick=\"give_limit_approval($limit_id, 'APPROVE')\" class='btn btn-sm btn-success'>Approve</button> <button onclick=\"give_limit_approval($limit_id, 'REJECT')\" class='btn btn-danger btn-sm'>Reject</button>";
                                    }

                                    if ($last_state == 2) {
                                        echo "<div class=\"alert bg-purple-gradient\">Customer has a new limit of <b>$limit_amount</b> with comments <span class='text-black font-italic'> '$comments' </span> awaiting approval $act</div>";
                                    }
                                    if ($last_state == 3) {
                                        $approve_perm2 = permission($userd['uid'], 'o_customer_limits', "0", "SHARP_INCREMENT");
                                        if ($approve_perm2 == 1) {
                                            $act = "<button onclick=\"give_limit_approval($limit_id, 'APPROVE')\" class='btn btn-sm btn-success'>Approve</button> <button onclick=\"give_limit_approval($limit_id, 'REJECT')\" class='btn btn-danger btn-sm'>Reject</button>";
                                        }
                                        echo "<div class=\"alert bg-red-gradient\"><i class='fa fa-warning'></i> ATTENTION! for New Limit: <b>$limit_amount</b>.   <span class='text-black font-italic'> '$comments' </span>.  $act</div>";
                                    }
                                }

                                $last_lim = fetchmax('o_customer_limits', "customer_uid='$customer_id'", "uid", "uid, amount, status, date(given_date) as given_date");
                                $last_limit_id = $last_lim['uid'];
                                $last_amount = $last_lim['amount'];
                                $last_status = $last_lim['status'];
                                $given_date = $last_lim['given_date'];
                                if ($last_status == 0 && $last_limit_id > 0) {
                                    echo "<div class=\"alert bg-danger\">Last limit of <b>$last_amount</b> was rejected" . fancydate($given_date) . "</div>";
                                } else if ($last_status == 1) {
                                    echo "<div class=\"alert bg-success\">Last limit of <b>$last_amount</b> was given " . fancydate($given_date) . "</div>";
                                }



                                $badge_id = $cust['badge_id'];
                                if ($badge_id > 0) {
                                    $badge_details = fetchonerow('o_badges', "uid='$badge_id'", "icon, title, description");
                                    $icon = $badge_details['icon'];
                                    $badge_title = $badge_details['title'];
                                    $badge_description = $badge_details['description'];
                                    $badge_det = "<a class='cust-b pull' title='$badge_title: $badge_description'><img class='badge_img' height='24px'  src=\"badges/$icon\"/></a>";
                                    $badge_form_action = "<i class=\'fa fa-pencil\'></i> Change Customer Tag";
                                } else {
                                    $badge_form_action = "<i class=\'fa fa-plus\'></i> Add Customer Tag";
                                    $badge_det = "";
                                }
                                // echo $badge_id;
                                ?>
                                <h3>Primary Information</h3>
                                <table class="table-bordered font-14 table table-hover">
                                    <tr>
                                        <td class="text-bold">UID</td>
                                        <td><?php echo decurl($customer_); ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-bold">Full Name</td>
                                        <td class="font-18 font-bold">
                                            <?php echo $cust['full_name'] . ' ' . $badge_det; ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-bold">National ID</td>
                                        <td class="font-18 font-bold"><?php echo $cust['national_id']; ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-bold">Primary Mobile</td>
                                        <td class="font-18 font-bold">
                                            <?php echo $cust['primary_mobile'] . $phone_provider; ?>
                                        </td>
                                    </tr>

                                    <tr style="display: none;">
                                        <td class="text-bold">Email</td>
                                        <td><?php echo $cust['email_address']; ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-bold">Physical Address</td>
                                        <td><?php echo $cust['physical_address']; ?> <br /><?php echo $town; ?></td>
                                    </tr>
                                    <tr>

                                        <td class="text-bold">Location Map</td>
                                        <td>
                                            <?php
                                            if (
                                                (strpos($cust['geolocation'], 'https://maps.app.goo.gl/') === 0) ||
                                                (strpos($cust['geolocation'], 'https://maps.apple.com/') === 0)
                                            ) {
                                                $loc_url = $cust["geolocation"];
                                                echo "<a href=\"$loc_url\" target=\"_blank\" rel=\"noopener noreferrer\">$loc_url</a>";
                                            } elseif (isset($cust['geolocation']) && isGoogleMapsUrlValid($cust['geolocation']) == 1) {
                                                ?>
                                                <div class="mapouter">
                                                    <div class="gmap_canvas"><iframe
                                                            src="<?php echo $cust['geolocation']; ?>&amp;t=&amp;z=13&amp;ie=UTF8&amp;iwloc=&amp;output=embed"
                                                            frameborder="0" scrolling="no"
                                                            style="width: 100%; height: 100%;" loading="lazy">></iframe>
                                                        <style>
                                                            .mapouter {
                                                                position: relative;
                                                                height: 100%;
                                                                width: 100%;
                                                                background: #fff;
                                                            }
                                                        </style>
                                                        <style>
                                                            .gmap_canvas {
                                                                overflow: hidden;
                                                                height: 100%;
                                                                width: 100%
                                                            }

                                                            .gmap_canvas iframe {
                                                                position: relative;
                                                                z-index: 2
                                                            }
                                                        </style>
                                                    </div>
                                                </div>
                                            <?php }
                                            ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-bold">Gender</td>
                                        <td><?php echo $cust['gender']; ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-bold">DOB</td>
                                        <td><?php echo $cust['dob']; ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-bold">Added By</td>
                                        <td><?php echo $added_by; ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-bold">Current LO</td>
                                        <td><?php echo $current_lo; ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-bold">Current CO</td>
                                        <td><?php echo $current_co; ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-bold">Current Limit</td>
                                        <td class="font-bold"><?php echo money($cust['loan_limit']); ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-bold">Date Added</td>
                                        <td><?php echo $cust['added_date']; ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-bold">Branch</td>
                                        <td><?php echo $branch;
                                        echo "$group_name"; ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-bold">Product</td>
                                        <td><?php echo $product; ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-bold">Total Loans</td>
                                        <td><?php echo $cust['total_loans']; ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-bold">Status</td>
                                        <td><?php echo $status; ?></td>
                                    </tr>

                                </table>
                                <h3>Other Information</h3>
                                <table class="table table-bordered font-14 table-hover no-right-click">
                                    <?php

                                    $sec = $cust['sec_data'];
                                    $sec_obj = (json_decode($sec, true));

                                    $product_forms = table_to_array('o_product_forms', "product_id = '$product_id'", "100", "form_id");
                                    if (sizeof($product_forms) > 0) {
                                        $form_list = implode(',', $product_forms);
                                        $and_form = " AND uid in ($form_list)";
                                    } else {
                                        $form_list = "AND uid > 0 AND product_id = 0";
                                        $and_form = "AND uid > 0 AND product_id = 0";
                                    }

                                    $forms = fetchtable('o_forms', "status=1 $and_form", "form_name", "asc", "100");
                                    while ($f = mysqli_fetch_array($forms)) {
                                        $form_name = $f['form_name'];

                                        echo "<tr><td colspan='2'><span class='text-uppercase text-purple font-16'>$form_name</span></td></th>";

                                        $form_id = $f['uid'];

                                        $fields = array();
                                        $otherf = fetchtable('o_form_fields', "status=1 AND tbl='o_customers' AND form_id='$form_id'", "uid", "asc", "1000");
                                        while ($o = mysqli_fetch_array($otherf)) {
                                            $field_id = $o['uid'];
                                            $field_name = $o['field_name'];
                                            $field_type = $o['field_type'];
                                            $field_p = $sec_obj['' . $field_id . ''];
                                            ////--------For select fields, keys are saved instead of values
                                    

                                            echo "<tr><td class='text-bold'><span class='text-muted font-italic'>$field_id</span> $field_name</td><td>$field_p</td></tr>";
                                        }
                                    }
                                    ?>
                                </table>
                            </div>
                            <div class="col-md-3">
                                <table class="table">
                                    <tr>
                                        <td><a href="customers?customer-add-edit=<?php echo $customer_; ?>&bio"
                                                class="btn btn-primary btn-block  btn-md grid-width-10"><i
                                                    class="fa fa-pencil"></i> Update Profile</a></td>
                                    </tr>

                                    <?php
                                    if ($cust['status'] == 1) {
                                        ?>
                                        <tr>
                                            <td><a href="assets" class="btn btn-success btn-block btn-md"
                                                    onclick=""><i
                                                        class="fa fa-plus"></i> Give a Loan</a></td>
                                        </tr>
                                        <!-- <tr><td><a href="loans?loan-add-edit&customer=<?php echo $customer_ ?>" class="btn btn-success btn-block btn-md"><i class="fa fa-plus"></i> Give a Loan</a></td></tr> -->
                                        <?php
                                    }
                                    ?>
                                    <tr>
                                        <td><button onclick="update_limit_popup('<?php echo $customer_id; ?>','EDIT')"
                                                class="btn btn-block bg-black-active btn-md"><i
                                                    class="fa  fa-check-circle"></i> Update Limit</button></td>
                                    </tr>
                                    <tr>
                                        <td><button onclick="interactions_popup(<?php echo $customer_; ?>)"
                                                class="btn btn-block bg-orange-active btn-md"><i
                                                    class="fa  fa-comments-o"></i> Interactions</button></td>
                                    </tr>

                                    <tr>
                                        <td>
                                            <select class="btn btn-block btn-md btn-default" id="sel_status"
                                                onchange="change_customer_statusV2('<?php echo $_GET['customer']; ?>')">
                                                <?php
                                                $o_statuses_ = fetchtable('o_customer_statuses', "status > 0 AND code IN(1, 2, 3)", "name", "asc", "0,10", "uid, code ,name ");
                                                while ($s = mysqli_fetch_array($o_statuses_)) {
                                                    $code = $s['code'];
                                                    $name = $s['name'];

                                                    $selected = $cust['status'] == $code ? "selected" : "";

                                                    echo "<option value='$code' $selected>$name</option>";
                                                }
                                                ?>
                                            </select>
                                        </td>
                                    </tr>

                                    <?php

                                    // if ($cust['status'] == 1) {
                                    //     echo "<tr><td><button onclick=\"change_customer_status('$customer_id', 2, 'Block Customer');\" class=\"btn btn-block bg-red-active btn-md\"><i class=\"fa  fa-\"></i> BlackList/Block</button></td></tr>";
                                    // } else if ($cust['status'] == 3) {
                                    //     echo "<tr><td><button onclick=\"change_customer_status('$customer_id', 1, 'Convert lead to customer');\" class=\"btn btn-block bg-olive-active btn-md\"><i class=\"fa  fa-stop\"></i> Convert to Customer</button></td></tr>";
                                    // } else if ($cust['status'] == 2) {
                                    //     echo "<tr><td><button onclick=\"change_customer_status('$customer_id', 1, 'Whitelist/UnBlock Customer');\" class=\"btn btn-block bg-gray-light btn-md\"><i class=\"fa  fa-stop\"></i> Whitelist/UnBlock</button></td></tr>";
                                    // }
                                    
                                    $change_pair = permission($userd['uid'], 'o_pairing', "0", "update_");

                                    if ($change_pair == 1) {
                                        echo "<tr><td><button onclick=\"change_customer_agent('$customer_id')\" class=\"btn btn-block bg-maroon-gradient btn-md\"><i class=\"fa  fa-user-circle-o\"></i> Change Agent</button></td></tr>";
                                    }
                                    ?>

                                    <tr>
                                        <td>
                                            <button style="background: #800080; color: #FFF;"
                                                class="btn btn-default btn-block btn-md"
                                                onclick="modal_view('/forms/customer-add-edit-badge.php','customer_id=<?php echo $customer_; ?>','<?php echo $badge_form_action; ?>'); modal_show();">
                                                <?php echo $badge_id > 0 ? '<i class=\'fa fa-pencil\'></i>' : '<i class=\'fa fa-plus\'></i>'; ?>
                                                <?php echo $badge_form_action; ?>
                                            </button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <button style="background: #7e273f; color: #FFF;"
                                                class="btn btn-default btn-block btn-md" onclick="alert('Change PIN');">
                                                <i class="fa fa-mobile"></i> Reset PIN
                                            </button>
                                        </td>
                                    </tr>


                                </table>
                            </div>
                        </div>
                    </div>
                    <!-- /.tab-pane -->
                    <div class="tab-pane" id="tab_2">
                        <div class="row">
                            <div class="col-md-2">
                                <span class="info-box-icon"><i class="fa fa-phone"></i></span>
                            </div>
                            <div class="col-md-7" id="contacts_placeholder">
                                <i>Loading contacts...</i>
                            </div>
                            <div class="col-md-3">
                                <table class="table">
                                    <tr>
                                        <td><a href="customers?customer-add-edit=<?php echo $customer_; ?>&contact"
                                                class="btn btn-success btn-block  btn-md"><i class="fa  fa-plus"></i>
                                                Add/Edit Contact</a></td>
                                    </tr>
                                    <tr style="display: none;">
                                        <td><button class="btn btn-primary btn-block btn-md"><i
                                                    class="fa  fa-pencil"></i> Edit Contact</button></td>
                                    </tr>
                                    <tr style="display: none;">
                                        <td><button class="btn btn-danger btn-block btn-md"><i class="fa  fa-times"></i>
                                                Remove Contact</button></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    <!-- /.tab-pane -->
                    <div class="tab-pane" id="tab_3">
                        <div class="row">

                            <div class="col-md-6">
                                <div id="account_info" class="scroll-hor">
                                    Loading Loans...
                                </div>
                                <div id="account_info_archives" class="scroll-hor well text-black">
                                    <?php
                                    if ($has_archive == 1) {
                                        echo "<a href='#' class='btn bg-gray-active' onclick=\"load_archive_loans($customer_); return false;\"><i class='fa fa-file-zip-o'></i> Check from archives</a>";
                                    }
                                    ?>

                                </div>
                            </div>
                            <div class="col-md-6">
                                <div id="payments_info" class="scroll-hor">
                                    Loading payments...
                                </div>
                                <div id="payments_info_archives" class="scroll-hor well text-black">

                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- /.tab-pane -->
                    <div class="tab-pane" id="tab_11">
                        <div class="row">


                            <div class="col-md-8 col-md-offset-2">
                                <div id="customer_account_statement" class="scroll-hor">
                                    Loading Statement...
                                </div>
                            </div>

                            <div class="col-md-2">
                                <table class="table">
                                    <tr>
                                        <td>
                                            <a href='customer_statement?cid=<?php echo $customer_; ?>'
                                                class="btn btn-default" target="_blank"><i class="fa fa-print"></i>
                                                Generate Print</a>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- /.tab-pane -->
                    <div class="tab-pane" id="tab_4">
                        <div class="row">
                            <div class="col-md-2">
                                <span class="info-box-icon"><i class="fa fa-users"></i></span>
                            </div>
                            <div class="col-md-7">
                                <table class="table-bordered font-14 table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th style="display: none">ID No.</th>
                                            <th>Mobile No.</th>
                                            <th style="display: none;">Email</th>
                                            <th>Physical Address</th>
                                            <th>Relationship</th>
                                        </tr>
                                    </thead>
                                    <tbody id="referees_placeholder">
                                        <tr>
                                            <td colspan="6"><i>Loading referees...</i></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="col-md-3">
                                <table class="table">
                                    <tr>
                                        <td><a href="customers?customer-add-edit=<?php echo $customer_; ?>&referees"
                                                class="btn btn-success btn-block btn-md"><i class="fa fa-plus"></i>
                                                Add/Edit Referee</a></td>
                                    </tr>
                                    <tr style="display: none;">
                                        <td><button class="btn btn-primary btn-block  btn-md grid-width-10"><i
                                                    class="fa fa-pencil"></i> Update Referee</button></td>
                                    </tr>
                                    <tr style="display: none">
                                        <td><button class="btn btn-danger btn-block btn-md"><i class="fa fa-times"></i>
                                                Remove Referee</button></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- /.tab-pane -->
                    <div class="tab-pane" id="tab_10">
                        <div class="row">
                            <div class="col-md-2">
                                <span class="info-box-icon"><i class="fa fa-users"></i></span>
                            </div>
                            <div class="col-md-7">
                                <table class="table-bordered font-14 table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>ID No.</th>
                                            <th>Mobile No.</th>
                                            <th>Amount Guaranteed</th>
                                            <th>Relationship</th>
                                        </tr>
                                    </thead>
                                    <tbody id="guarantors_placeholder">
                                        <tr>
                                            <td colspan="5"><i>Loading guarantors...</i></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="col-md-3">
                                <table class="table">
                                    <tr>
                                        <td><a href="customers?customer-add-edit=<?php echo $customer_; ?>&guarantors"
                                                class="btn btn-success btn-block btn-md"><i class="fa fa-plus"></i>
                                                Add/Edit Guarantor</a></td>
                                    </tr>
                                    <tr style="display: none;">
                                        <td><button class="btn btn-primary btn-block  btn-md grid-width-10"><i
                                                    class="fa fa-pencil"></i> Update Guarantor</button></td>
                                    </tr>
                                    <tr style="display: none">
                                        <td><button class="btn btn-danger btn-block btn-md"><i class="fa fa-times"></i>
                                                Remove Guarantor</button></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- /.tab-pane -->
                    <div class="tab-pane" id="tab_5">
                        <div class="row">
                            <div class="col-md-2">
                                <span class="info-box-icon"><i class="fa fa-tag"></i></span>
                            </div>
                            <div class="col-md-8">
                                <table class="table-bordered font-14 table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Category</th>
                                            <th>Title</th>
                                            <th>Description</th>
                                            <th>Current Worth</th>
                                            <th>Ref Number</th>
                                            <th>File Number</th>
                                            <th>Added Date</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody id="collateral_placeholder">
                                        <tr>
                                            <td colspan="8"><i>Loading collateral...</i></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="col-md-2">
                                <table class="table">
                                    <tr>
                                        <td><a href="customers?customer-add-edit=<?php echo $customer_; ?>&collateral"
                                                class="btn btn-success btn-block btn-md"><i class="fa fa-plus"></i>
                                                Add/Edit Collateral</a></td>
                                    </tr>
                                    <tr style="display: none;">
                                        <td><button class="btn btn-primary btn-block  btn-md grid-width-10"><i
                                                    class="fa fa-pencil"></i> Update Collateral</button></td>
                                    </tr>
                                    <tr style="display: none;">
                                        <td><button class="btn btn-danger btn-block btn-md"><i class="fa fa-times"></i>
                                                Remove Collateral</button></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    <!-- /.tab-pane -->
                    <div class="tab-pane" id="tab_6">
                        <div class="row">
                            <div class="col-md-2">
                                <span class="info-box-icon"><i class="fa fa-clock-o"></i></span>
                            </div>
                            <div class="col-md-10">
                                <table class="table-bordered font-14 table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Event</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody id="customer_events_placeholder">
                                        <tr>
                                            <td colspan="2"><i>Loading events...</i></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                        </div>
                    </div>
                    <div class="tab-pane" id="tab_7">
                        <div class="row">
                            <div class="col-md-2">
                                <span class="info-box-icon"><i class="fa fa-cloud-upload"></i></span>
                            </div>
                            <div class="col-md-8" id="upload_list">
                                <i>Loading uploads...</i>
                            </div>
                            <div class="col-md-2">
                                <table class="table">
                                    <tr>
                                        <td><a href="customers?customer-add-edit=<?php echo $customer_; ?>&uploads"
                                                class="btn btn-success btn-block btn-md"><i class="fa fa-plus"></i>
                                                Upload/Edit File</a></td>
                                    </tr>
                                    <tr style="display: none;">
                                        <td><button class="btn btn-primary btn-block  btn-md grid-width-10"><i
                                                    class="fa fa-pencil"></i> Update File</button></td>
                                    </tr>
                                    <tr style="display: none;">
                                        <td><button class="btn btn-danger btn-block btn-md"><i class="fa fa-times"></i>
                                                Delete File</button></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane" id="tab_8">
                        <div class="row">
                            <div class="col-md-2">
                                <span class="info-box-icon"><i class="fa fa-comments-o"></i></span>
                            </div>

                            <div class="col-md-10">
                                <div class="box">
                                    <div class="box-header bg-info">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <h3 class="box-title font-16">
                                                    <a title="Clear Filters"
                                                        class="btn font-16 btn-md bg-navy text-bold"
                                                        href="javascript:void(0)" onclick="resetPagination()"><i
                                                            class="fa fa-refresh"></i> All</a>
                                                    <a class="btn font-16 btn-md bg-blue-gradient text-bold"
                                                        href="javascript:void(0)" title="My PTPs"
                                                        onclick="select_item('#agent_','<?php echo $agent_id; ?>');interactions_filter();"><i
                                                            class="fa fa-user-circle-o"></i> My</a>
                                                    <input type="hidden" id="agent_" value="0">

                                                    <a class="btn font-16 btn-md bg-maroon-gradient text-bold"
                                                        href="javascript:void(0)"
                                                        onclick="select_item('#interaction_outcome','6'); interactions_filter();"><i
                                                            class="fa fa-thumbs-up"></i> PTPs</a>

                                                    <select
                                                        class="btn font-16 btn-default btn-md btn-default text-bold top-select"
                                                        id="interaction_outcome" onchange="interactions_filter()">
                                                        <option value="0"> All Outcomes</option>
                                                        <?php
                                                        $out = fetchtable('o_flags', "status=1", "name", "asc", "100", "uid, name");
                                                        while ($o = mysqli_fetch_array($out)) {
                                                            $oid = $o['uid'];
                                                            $name = $o['name'];

                                                            echo "<option value='$oid'>$name</option>";
                                                        }
                                                        ?>
                                                    </select>

                                                    <select
                                                        class="btn font-14 btn-default btn-md btn-default text-bold top-select"
                                                        id="interaction_method" onchange="interactions_filter()">
                                                        <option value="0">All Methods</option>
                                                        <?php
                                                        $o_conversation_methods = fetchtable('o_conversation_methods', "status=1", "uid", "asc", "100", "*");
                                                        while ($cov = mysqli_fetch_array($o_conversation_methods)) {
                                                            $cid = $cov['uid'];
                                                            $cname = $cov['name'];
                                                            $cdetails = $cov['details'];
                                                            //  echo " <a class=\"btn font-16 btn-md btn-default text-black text-bold\" href=\"#\" onclick=\"face_to_face_interactions('sort_1')\"><i class=\"$cdetails\"></i> $cname</a>";
                                                            echo "<option value='$cid'>$cname</option>";
                                                        }
                                                        ?>
                                                    </select>



                                                    <button onclick="interactions_filter('duetoday', '')"
                                                        class="btn bg-purple-gradient"><i class="fa fa-bell"></i> Due
                                                        Today</button>
                                                    <button onclick="interactions_filter('', 'overdue')"
                                                        class="btn bg-blue-gradient"><i class="fa fa-binoculars"></i>
                                                        Overdue</button>

                                                    <input style="width: 135px" type="text" name="daterange"
                                                        class="btn btn-default" id="period_"
                                                        title="Filter with interaction date (Day interaction was added)"
                                                        value="Interaction date" />
                                                    <input type="hidden" id="c_start_d"> <input type="hidden"
                                                        id="c_end_d">

                                                    <input style="width: 135px;" type="text" name="daterange"
                                                        class="btn btn-default" id="ni_period_"
                                                        title="Filter with interaction due date (Next interaction)"
                                                        value="Next date" />
                                                    <input type="hidden" id="ni_start_d"> <input type="hidden"
                                                        id="ni_end_d">

                                                </h3>

                                                <button class="btn btn-success pull-right btn-float"
                                                    onclick="modal_view('/forms/interaction_add_form','customer_id=<?php echo $customer_id; ?>','New Interaction'); modal_show();"><i
                                                        class="fa fa-plus"></i> New</button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="box-body">
                                        <div class="col-md-10" id="comments_list">
                                            Loading ...
                                        </div>
                                    </div>
                                    <div id="pagination"></div>
                                    <div style="display: none">
                                        <?php
                                        $paging_values = paging_values_hidden3('uid > 0', 0, 10, 'uid', 'desc', '', "interactions_load('$customer_','#comments_list')", 'default_sort');
                                        echo $paging_values;
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>


                    <!-- Messages -->
                    <div class="tab-pane" id="tab_9">
                        <div class="row">
                            <div class="col-xs-2 font-14">
                                <ol class="list-group">
                                    <li class="list-group-item" style="cursor: pointer" id="broadcasts">
                                        <span class="text-navy"><i class="fa fa-bullhorn" aria-hidden="true"></i>
                                            Broadcasts</span>
                                    </li>
                                    <li class="list-group-item" style="cursor: pointer" id="interactive">
                                        <span class="text-navy"><i class="fa fa-reply-all" aria-hidden="true"></i>
                                            Interactive</span>
                                    </li>
                                </ol>
                            </div>
                            <div class="col-xs-10">

                                <!-- /.box -->

                                <div class="box">
                                    <div class="box-header bg-info">
                                        <span style="display: flex; justify-content: center; gap: 3px;"
                                            class="font-14 text-bold" id="total_results_"></span>
                                    </div>
                                    <!-- /.box-header -->

                                    <div class="row" style="display: flex; justify-content: center;">
                                        <div class="col-xs-10 offset-xs-1">
                                            <div class="box-body" id="messages_list">
                                                <!-- load messages via ajax call -->
                                            </div>

                                            <div id="pagination"></div>
                                        </div>
                                    </div>
                                    <!-- /.box-body -->
                                </div>
                                <!-- /.box -->
                            </div>
                            <!-- /.col -->
                        </div>

                        <?php echo "<div style='display: none;'>" .
                            paging_values_hidden(
                                'uid > 0',
                                0,
                                10,
                                'uid',
                                'desc',
                                '',
                                'loadMessages',
                                1
                            ) .
                            '</div>'; ?>

                        <input type="hidden" value="<?php echo $customer_; ?>" id="cust_reuse_id_msgs_tab">
                        <input type="hidden" value="<?php echo $phone_; ?>" id="cust_phone_reuse_msgs_tab">
                    </div>
                </div>
                <!-- /.tab-content -->
            </div>
        </div>
    </div>
</section>
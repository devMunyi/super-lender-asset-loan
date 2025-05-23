<section class="content-header">
    <h1>
        <?php echo arrow_back('customers', 'Customers'); ?>
        <?php
        $cid = $_GET['customer-add-edit'];
        $geo_textarea_display = "none";
        if ($cid > 0) {
            $cust = fetchonerow('o_customers', "uid='" . decurl($cid) . "'");
            $customer_id = $_GET['customer-add-edit'];
            $_SESSION['update-customer-btn'] = 1;

            if ($group_loans == 1) {
                $customer_group = fetchrow('o_group_members', "customer_id='" . decurl($cid) . "' AND status=1", "group_id");
                $group_display = 'All';
            } else {
                $customer_group = 0;
                $group_display = 'None';
            }

            echo "Customer <small>Edit</small> <span class='text-green text-bold'>" . $cust['full_name'] . "</span> <a title='Back to customer' class='font-16' href=\"customers?customer=$cid\"><i class='fa fa-arrow-circle-up'></i></a>";
            $act = "<span class='text-orange'><i class='fa fa-edit'></i>Edit</span>";
            if ($cust['gender'] == 'M') {
                $male_checked = 'CHECKED';
            } elseif ($cust['gender'] == 'F') {
                $female_checked = 'CHECKED';
            }

            if (strlen(trim($cust["geolocation"])) > 30) {
            } else {
                $geo_textarea_display = "block";
            }
        } else {
            $cust = array();
            $customer_id = "";
            echo "Customer <small>Add</small>";
            $act = "<span class='text-green'><i class='fa fa-edit'></i>Add</span>";
            $btn_action = "new-customer-btn";
            $_SESSION['update-customer-btn'] = 0;
        }
        ?>

    </h1>
    <ol class="breadcrumb">
        <li><a href="index"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Customer</li>
    </ol>
</section>
<section class="content">
    <div class="row">
        <div class="col-xs-12">

            <!-- /.box -->

            <div class="box">

                <!-- /.box-header -->
                <div class="row">
                    <div class="col-sm-2">
                        <?php
                        $edit = $_GET['customer-add-edit'];
                        if ($edit > 0) {

                            ////---------Check if user has edit permission
                            $update_addon = permission($userd['uid'], 'o_customers', "0", "update_");
                            if ($update_addon != 1) {
                                die(errormes("You don't have permission to edit customer"));
                                exit();
                            }

                        ?>

                            <ul class="list-group">
                                <li class="list-group-item"><a href="customers?customer-add-edit=<?php echo $customer_id; ?>&bio" aria-expanded="false"><i class="fa fa-info"></i> Bio Info</a></li>
                                <li class="list-group-item"><a href="customers?customer-add-edit=<?php echo $customer_id; ?>&contact" aria-expanded="false"><i class="fa fa-phone"></i> Contact Info</a>
                                </li>
                                <li class="list-group-item"><a href="customers?customer-add-edit=<?php echo $customer_id; ?>&referees" aria-expanded="false"><i class="fa fa-users"></i> Referees</a></li>
                                <li class="list-group-item"><a href="customers?customer-add-edit=<?php echo $customer_id; ?>&collateral" aria-expanded="false"><i class="fa fa-tag"></i> Collateral</a></li>
                                <li class="list-group-item"><a href="customers?customer-add-edit=<?php echo $customer_id; ?>&uploads" aria-expanded="false"><i class="fa fa-cloud-upload"></i> Uploads</a></li>
                                <li class="list-group-item"><a href="customers?customer-add-edit=<?php echo $customer_id; ?>&other" aria-expanded="false"><i class="fa fa-info-circle"></i> Other</a></li>


                            </ul>
                        <?php
                        } else {
                            $update_addon = permission($userd['uid'], 'o_customers', "0", "create_");
                            if ($update_addon != 1) {
                                die(errormes("You don't have permission to add customer"));
                                exit();
                            }
                        }
                        ?>
                    </div>
                    <div class="col-sm-6">
                        <!-- /.box-header -->
                        <!-- form start -->
                        <?php
                        if (isset($_GET['contact'])) {
                            $contact_id = $_GET['contact'];
                            if ($contact_id > 0) {
                                echo "<h3>Edit Contact";
                                $k = fetchonerow("o_customer_contacts", "uid=" . decurl($contact_id), "contact_type, value");
                                $contact_type = $k['contact_type'];
                                $value = $k['value'];
                            } else {
                                echo "<h3>Add Additional Contacts";
                            }
                        ?>

                            <a class="btn-outline-black pull-right" href="customers?customer-add-edit=<?php echo $cid; ?>&referees">Skip <i class="fa fa-angle-double-right"></i></a><a href="customers?customer-add-edit=<?php echo $cid; ?>&contact" class="btn-outline-black pull-right">New <i class="fa fa-plus"></i></a>

                            </h3>
                            <form class="form-horizontal" onsubmit="return false;" id="contact_" method="post">
                                <div class="box-body">
                                    <div class="form-group">
                                        <label for="contact_value" class="col-sm-3 control-label">Type</label>

                                        <div class="col-sm-9">
                                            <select class="form-control" id="contact_type">
                                                <option value="0">--Select One</option>
                                                <?php
                                                $o_contact_types_ = fetchtable('o_contact_types', "status=1", "uid", "desc", "0,10", "uid ,name ");
                                                while ($t = mysqli_fetch_array($o_contact_types_)) {
                                                    $uid = $t['uid'];
                                                    $name = $t['name'];
                                                    if ($contact_type == $uid) {
                                                        $selected_t = "SELECTED";
                                                    } else {
                                                        $selected_t = "";
                                                    }
                                                    echo "<option $selected_t value='$uid'>$name</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label for="contact_value" class="col-sm-3 control-label">Details</label>

                                        <div class="col-sm-9">
                                            <textarea class="form-control" id="contact_value"><?php echo $value; ?></textarea>
                                        </div>
                                    </div>


                                    <div class="col-sm-3"></div>
                                    <div class="col-sm-9">
                                        <div class="box-footer">
                                            <br />
                                            <button type="submit" class="btn btn-lg btn-flat btn-default">Cancel</button>
                                            <button type="submit" class="btn btn-success btn-flat bg-green-gradient btn-lg pull-right" onclick="customer_save_additional_contact('<?php echo $cid; ?>','<?php echo $contact_id; ?>');">
                                                Save
                                            </button>
                                        </div>
                                    </div>

                                </div>
                                <!-- /.box-body -->

                                <!-- /.box-footer -->
                            </form>
                        <?php
                        } elseif (isset($_GET['referees'])) {

                            $ref_id = $_GET['referees'];
                            if ($ref_id > 0) {
                                $l = fetchonerow("o_customer_referees", "uid=" . decurl($ref_id), "*");
                                $relationship = $l['relationship'];
                                echo " <h3>Edit Referee";
                            } else {
                                echo " <h3>New Referee";
                            }
                        ?>
                            <a class="btn-outline-black pull-right" href="customers?customer-add-edit=<?php echo $cid; ?>&collateral">Skip <i class="fa fa-angle-double-right"></i></a><a href="customers?customer-add-edit=<?php echo $cid; ?>&referees" class="btn-outline-black pull-right">New <i class="fa fa-plus"></i></a></h3>
                            <form class="form-horizontal" id="ref_form" onsubmit="return false;" method="post">
                                <div class="box-body">
                                    <div class="form-group">
                                        <input type="hidden" id="ref_id" value="<?php echo $ref_id; ?>">
                                        <label for="full_name" class="col-sm-3 control-label">Full Name</label>

                                        <div class="col-sm-9">
                                            <input type="text" class="form-control" value="<?php echo $l['referee_name'] ?>" id="full_name" placeholder="First Middle Last">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="email_" class="col-sm-3 control-label">Email</label>

                                        <div class="col-sm-9">
                                            <input type="text" value="<?php echo $l['email_address'];  ?>" class="form-control" autocomplete="OFF" id="email_" placeholder="user@email.com">
                                        </div>
                                    </div>
                                    <div class="form-group" style="display: none;">
                                        <label for="national_id" class="col-sm-3 control-label">National ID</label>

                                        <div class="col-sm-9">
                                            <input type="text" class="form-control" id="national_id" value="<?php echo  $l['id_no']; ?>" placeholder="8 Characters">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="phone_number" class="col-sm-3 control-label">Phone Number</label>

                                        <div class="col-sm-9">
                                            <input type="text" class="form-control" id="phone_number" value="<?php echo  $l['mobile_no']; ?>" placeholder="07...">
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label for="phone_provider" class="col-sm-3 control-label"></label>

                                        <div class="col-sm-9">
                                            <input type="text" class="form-control" id="phone_number" value="<?php echo  $l['mobile_no']; ?>" placeholder="07...">
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label for="main_address" class="col-sm-3 control-label">Home Address</label>
                                        <div class="col-sm-9">
                                            <textarea class="form-control" id="main_address" placeholder=""><?php echo  $l['physical_address']; ?></textarea>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label for="relationship" class="col-sm-3 control-label">Relationship</label>

                                        <div class="col-sm-9">
                                            <select class="form-control" id="relationship">
                                                <option value="0">--Select One</option>
                                                <?php

                                                $recs = fetchtable('o_customer_referee_relationships', "status=1", "name", "desc", "100", "uid ,name");
                                                while ($r = mysqli_fetch_array($recs)) {
                                                    $uid = $r['uid'];
                                                    $name = $r['name'];

                                                    if ($uid == $relationship) {
                                                        $selected_ref = "SELECTED";
                                                    } else {
                                                        $selected_ref = "";
                                                    }
                                                    echo "<option $selected_ref value=\"$uid\">$name</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-sm-3"></div>
                                    <div class="col-sm-9">
                                        <div class="box-footer">
                                            <br />
                                            <button type="submit" class="btn btn-lg btn-default">Cancel</button>
                                            <button type="submit" class="btn btn-success bg-green-gradient btn-lg pull-right" onclick="customer_add_referee('<?php echo $cid; ?>');">Save
                                            </button>
                                        </div>
                                    </div>

                                </div>
                                <!-- /.box-body -->

                                <!-- /.box-footer -->
                            </form>
                        <?php
                        } else if (isset($_GET['collateral'])) {
                        ?>
                            <?php
                            $collateral_id = $_GET['collateral'];
                            if ($collateral_id > 0) {
                                echo "<h3>Edit Collateral";
                                $c = fetchonerow("o_collateral", "uid=" . decurl($collateral_id), "*");
                            } else {
                                echo "<h3>Add Additional Collateral";
                            }
                            ?>

                            <a class="btn-outline-black pull-right" href="customers?customer-add-edit=<?php echo $cid; ?>&uploads">Skip <i class="fa fa-angle-double-right"></i></a><a href="customers?customer-add-edit=<?php echo $cid; ?>&collateral" class="btn-outline-black pull-right">New <i class="fa fa-plus"></i></a>

                            </h3>
                            <form class="form-horizontal" onsubmit="return false;" method="post">
                                <div class="box-body">
                                    <div class="form-group">
                                        <input type="hidden" id="col_id" value="<?php echo $collateral_id; ?>">
                                        <label for="title" class="col-sm-3 control-label">Title</label>

                                        <div class="col-sm-9">
                                            <input type="text" class="form-control" value="<?php echo $c['title'] ?>" id="title" placeholder="">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="money_value" class="col-sm-3 control-label">Money Value
                                            (Ksh)</label>

                                        <div class="col-sm-9">
                                            <input type="number" class="form-control" id="money_value" value="<?php echo $c['money_value'] ?>" placeholder="">
                                        </div>
                                    </div>
                                    <div class="form-group" style="display: none;">
                                        <label for="reference_number" class="col-sm-3 control-label">Reference
                                            Number</label>

                                        <div class="col-sm-9">
                                            <input type="text" class="form-control" value="<?php echo $c['doc_reference_no'] ?>" id="reference_number">
                                        </div>
                                    </div>
                                    <div class="form-group" style="display: none;">
                                        <label for="physical_file_number" class="col-sm-3 control-label">Physical File
                                            Number</label>

                                        <div class="col-sm-9">
                                            <input type="text" class="form-control" value="<?php echo $c['filling_reference_no'] ?>" id="physical_file_number" />
                                        </div>
                                    </div>
                                    <div class="form-group" style="display: none;">
                                        <label for="digital_file_number" class="col-sm-3 control-label">Digital File
                                            Number</label>

                                        <div class="col-sm-9">
                                            <input type="text" class="form-control" value="<?php echo $c['document_scan_address']; ?>" id="digital_file_number">
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label for="description" class="col-sm-3 control-label">Description</label>

                                        <div class="col-sm-9">
                                            <textarea class="form-control" id="description"><?php echo $c['description'] ?></textarea>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label for="category" class="col-sm-3 control-label">Type</label>

                                        <div class="col-sm-9">
                                            <select class="form-control" id="category">
                                                <option value="0">--Select One</option>
                                                <?php

                                                $recs = fetchtable('o_asset_categories', "status=1", "name", "asc", "100", "uid ,name");
                                                while ($r = mysqli_fetch_array($recs)) {
                                                    $uid = $r['uid'];
                                                    $name = $r['name'];

                                                    if ($uid == $c['category']) {
                                                        $selected_category = "SELECTED";
                                                    } else {
                                                        $selected_category = "";
                                                    }
                                                    echo "<option $selected_category value=\"$uid\">$name</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-sm-3"></div>
                                    <div class="col-sm-9">
                                        <div class="box-footer">
                                            <br />
                                            <button type="submit" class="btn btn-lg btn-default">Cancel</button>
                                            <button type="submit" class="btn btn-success btn-lg pull-right" onclick="customer_add_collateral('<?php echo $cid; ?>');">Save
                                            </button>
                                        </div>
                                    </div>

                                </div>
                                <!-- /.box-body -->

                                <!-- /.box-footer -->
                            </form>
                        <?php
                        } else if (isset($_GET['other'])) {
                        ?>

                            <?php
                            $other_id = $_GET['other'];
                            if ($other_id > 0) {
                                echo "<h3>Edit Other";
                                $o = fetchonerow("o_key_values", "uid=" . decurl($other_id), "*");
                            } else {
                                echo "<h3>Other Info";
                            }
                            ?>
                            <a class="btn-outline-black pull-right" href="customers?customer=<?php echo $cid; ?>&uploads">Finish <i class="fa fa-check-circle"></i></a>
                            </h3>
                            <form class="form-horizontal" id="other_frm" onsubmit="return false;" method="post">
                                <div class="box-body">



                                    <!-- /.box-body -->

                                    <!-- /.box-footer -->

                                    <?php
                                    /////----------------Existing fields
                                    $sec = $cust['sec_data'];
                                    $sec_obj = (json_decode($sec, true));
                                    /////----------Forms
                                    $fields = array();
                                    $total_fields = 0;
                                    $forms = fetchtable('o_forms', "status=1", "uid", "asc", "100");
                                    while ($f = mysqli_fetch_array($forms)) {
                                        $form_name = $f['form_name'];
                                        echo "<div class=\"box box-solid border\">";
                                        echo "<div class=\"box-title\">";
                                        echo "<h4 class='text-uppercase text-center alert'>$form_name</h4>";
                                        echo "</div>";
                                        $form_id = $f['uid'];
                                        echo "<div class=\"box-body\">";

                                        $otherf = fetchtable('o_form_fields', "status=1 AND tbl='o_customers' AND form_id='$form_id'", "uid", "asc", "1000");
                                        while ($o = mysqli_fetch_array($otherf)) {
                                            $total_fields = $total_fields + 1;
                                            $field_id = $o['uid'];
                                            //////////-----------Existing value
                                            $current_saved_value = $sec_obj[$field_id];

                                            $field_name = $o['field_name'];
                                            $field_type = $o['field_type'];
                                            $field_options = $o['field_options'];
                                            $caption_ = $o['caption_'];
                                            $arr = json_decode($field_options, true);
                                            array_push($fields, $field_id);
                                            if ($field_type == 'SELECT') {
                                                $option = "<option>$caption_</option>";
                                                // sort list based on value alphabetically
                                                uasort($arr, function ($a, $b) {
                                                    return strcmp($a, $b);
                                                });
                                                foreach ($arr as $key => $value) {
                                                    if ($current_saved_value == $value) {
                                                        $selected_val = "SELECTED";
                                                    } else {
                                                        $selected_val = "";
                                                    }
                                                    $option .= "<option $selected_val value='$value'>$value</option>";
                                                }

                                                $in = "<select class='form-control' id=\"in_$field_id\"> $option</select>";
                                            } elseif ($field_type == 'NUMBER') {
                                                $in = "<input class='form-control' value='$current_saved_value' placeholder='$caption_' id=\"in_$field_id\" type=\"$field_type\">";
                                            } else if ($field_type == 'TEXTAREA') {
                                                $in = "<textarea class='form-control' placeholder='$caption_' id=\"in_$field_id\">$current_saved_value</textarea>";
                                            } else if ($field_type == 'DATE') {
                                                $in = "<input class='form-control' value='$current_saved_value' placeholder='$caption_' id=\"in_$field_id\" type=\"date\">";
                                            } else {
                                                $in = "<input class='form-control' placeholder='$caption_' value='$current_saved_value' id=\"in_$field_id\" type=\"$field_type\">";
                                            }
                                            $field_options = $o['field_options'];
                                            echo "<div class=\"form-group\">  
                        <label for=\"$field_id\" class=\"col-sm-3 control-label\">$field_name</label>
                        <div class=\"col-sm-9\">
                            $in
                        </div>
                    </div>";
                                        }
                                        echo "</div></div>";
                                    }
                                    if ($total_fields > 0) {
                                    ?>
                                        <textarea style="display: none;" id="other_fields"><?php echo implode(',', $fields);  ?></textarea>
                                        <div class="col-sm-3"></div>
                                        <div class="col-sm-9">
                                            <div class="box-footer">
                                                <br />
                                                <button type="submit" class="btn btn-lg btn-default">Cancel</button>
                                                <button type="submit" class="btn btn-success btn-lg pull-right" onclick="save_other('o_customers','<?php echo $cid; ?>');">Save
                                                </button>
                                            </div>
                                        </div>
                                </div>
                            <?php
                                    } else {
                                        echo "<div class=\"col-sm-12\"><h5 class='font-italic'> No custom fields configured. Please talk to the admin</h5></div>";
                                    }
                            ?>
                            </form>
                        <?php
                        } else if (isset($_GET['uploads'])) {
                        ?>
                            <h3>Upload File <a class="btn-outline-black pull-right" href="customers?customer-add-edit=<?php echo $cid; ?>&other">Skip <i class="fa fa-angle-double-right"></i></a><a href="customers?customer-add-edit=<?php echo $cid; ?>&uploads" class="btn-outline-black pull-right">New <i class="fa fa-plus"></i></a></h3>
                            <form class="form-horizontal" id="doc-upload" method="POST" action="action/files/customer_upload_file" enctype="multipart/form-data">
                                <div class="box-body">
                                    <div class="form-group">
                                        <input type="hidden" value="o_customers" name="tbl">
                                        <input type="hidden" value="<?php echo decurl($cid); ?>" name="rec">
                                        <label for="title" class="col-sm-3 control-label">Title</label>

                                        <div class="col-sm-9">
                                            <input type="text" class="form-control" value="<?php echo $cust['name'] ?>" id="title" name="title" placeholder="Document Title">
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label for="description" class="col-sm-3 control-label">Description</label>

                                        <div class="col-sm-9">
                                            <textarea class="form-control" id="description" name="description"></textarea>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label for="group_" class="col-sm-3 control-label">Type</label>

                                        <div class="col-sm-9">
                                            <select class="form-control" name="type_" id="type_">
                                                <option value="0">--Select One</option>
                                                <?php
                                                $o_customer_document_categories_ = fetchtable('o_customer_document_categories', "uid>0", "uid", "desc", "0,10", "uid ,name ");
                                                while ($c = mysqli_fetch_array($o_customer_document_categories_)) {
                                                    $uid = $c['uid'];
                                                    $name = $c['name'];
                                                    echo "<option value=\"$uid\">$name</option>";
                                                }

                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group">

                                        <label for="file_" class="col-sm-3 control-label">File</label>

                                        <div class="col-sm-9">
                                            <input type="file" class="form-control" id="file_" name="file_">
                                        </div>
                                    </div>
                                    <div class="form-group">

                                        <label for="reference_number" class="col-sm-3 control-label">Reference
                                            Number</label>

                                        <div class="col-sm-9">
                                            <input type="text" class="form-control" value="<?php echo $cust['name'] ?>" id="reference_number" name="reference_number">
                                        </div>
                                    </div>
                                    <div class="form-group">

                                        <label for="make_thumbnail" class="col-sm-3 control-label">Make a
                                            Thumbnail</label>

                                        <div class="col-sm-9">
                                            <label> <input type="checkbox" value="1" CHECKED id="make_thumbnail" name="make_thumbnail"> Yes</label>
                                        </div>
                                    </div>


                                    <div class="col-sm-3"></div>
                                    <div class="col-sm-9">
                                        <div class="box-footer">
                                            <br />
                                            <div class="prgress">
                                                <div class="messagedoc-upload" id="message"></div>
                                                <div class="progressdoc-upload" id="progress">
                                                    <div class="bardo-upload" id="bar"></div>
                                                    <br />
                                                    <div class="percentdoc-upload" id="percent"></div>
                                                </div>
                                            </div>

                                            <button type="submit" class="btn btn-lg btn-default">Cancel</button>
                                            <?php
                                            if ($upload_external == 1) {
                                            ?>
                                                <button id="submit-btn" type="submit" class="btn btn-success btn-lg pull-right" onclick="customerFileUpload();">Submit
                                                </button>
                                            <?php
                                            } else {
                                            ?>
                                                <button id="doc-upload" type="submit" class="btn btn-success btn-lg pull-right" onclick="formready('doc-upload');">Upload
                                                </button>
                                            <?php
                                            }
                                            ?>
                                        </div>

                                    </div>

                                </div>
                                <!-- /.box-body -->

                                <!-- /.box-footer -->
                            </form>
                        <?php
                        } else {

                        ?>
                            <h3><?php echo $act; ?> Bio Information</h3>
                            <form class="form-horizontal" onsubmit="return false;" method="post">
                                <div class="box-body">
                                    <div class="form-group">

                                        <label for="full_name" class="col-sm-3 control-label">Full Name</label>

                                        <div class="col-sm-9">
                                            <input type="text" class="form-control" value="<?php echo $cust['full_name'] ?>" id="full_name" placeholder="First Middle Last">
                                        </div>
                                    </div>
                                    <div class="form-group" style="display: none;">
                                        <label for="email_" class="col-sm-3 control-label">Email</label>

                                        <div class="col-sm-9">
                                            <input type="email" class="form-control" value="<?php echo $cust['email_address']; ?>" autocomplete="OFF" id="email_" placeholder="user@email.com">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="national_id" class="col-sm-3 control-label">National ID</label>

                                        <div class="col-sm-9">
                                            <input type="text" class="form-control" value="<?php echo $cust['national_id']; ?>" id="national_id" placeholder="8 Characters">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="phone_number" class="col-sm-3 control-label">Phone Number</label>

                                        <div class="col-sm-9">
                                            <input type="text" class="form-control" value="<?php echo $cust['primary_mobile']; ?>" id="phone_number" placeholder="07...">
                                        </div>
                                    </div>

                                    <div class="form-group" style="display: <?php echo $cc === 256 ? 'block' : 'none'; ?>;">
                                        <label for="phone_number_provider" class="col-sm-3 control-label">Phone Number Provider</label>

                                        <div class="col-sm-9">
                                            <select class="form-control" id="phone_number_provider">
                                                <option value="0">--Select One</option>
                                                <?php

                                                $o_telecomms_ = fetchtable('o_telecomms', "uid > 0 AND country_code = '$cc'", "name", "asc", "0,100", "uid, name");
                                                while ($e = mysqli_fetch_array($o_telecomms_)) {
                                                    $uid = $e['uid'];
                                                    $name = $e['name'];

                                                    if (($cust['phone_number_provider']) == $uid) {
                                                        $selected_st = 'SELECTED';
                                                    } else {
                                                        $selected_st = '';
                                                    }
                                                    echo "<option $selected_st value=\"$uid\">$name</option>";
                                                }

                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="dob" class="col-sm-3 control-label">DOB</label>

                                        <div class="col-sm-9">
                                            <input type="date" value="<?php echo $cust['dob']; ?>" class="form-control" id="dob">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="gender" class="col-sm-3 control-label">Gender</label>

                                        <div class="col-sm-4">
                                            <label><input type="radio" <?php echo $male_checked; ?> name="gender" value="M"> Male</label>
                                        </div>
                                        <div class="col-sm-4">
                                            <label><input type="radio" <?php echo $female_checked; ?> name="gender" value="F"> Female</label>
                                        </div>

                                    </div>
                                    <div class="form-group">
                                        <label for="main_address" class="col-sm-3 control-label">Home Address</label>

                                        <div class="col-sm-9">
                                            <textarea class="form-control" id="main_address" placeholder="123 Street"><?php echo $cust['physical_address']; ?>
                                        </textarea>
                                        </div>
                                    </div>

                                    <div class="form-group" style="display: <?php echo $geo_textarea_display; ?>;">
                                        <label for="main_address" class="col-sm-3 control-label">Location Map</label>
                                        <div class="col-sm-9">
                                            <textarea class="form-control" id="initial_geolocation" style="display: none;"><?php echo  $cust['geolocation']; ?></textarea><br />
                                            <textarea class="form-control" id="geolocation" style="display: <?php echo $geo_textarea_display; ?>;"><?php echo  $cust['geolocation']; ?></textarea>
                                        </div>
                                    </div>

                                    <div class="form-group" style="display: none;">
                                        <label for="town_" class="col-sm-3 control-label">Town</label>

                                        <div class="col-sm-9">
                                            <select class="form-control" id="town_">
                                                <option value="0">--Select One</option>
                                                <?php
                                                $o_towns_ = fetchtable('o_towns', "status=1", "uid", "desc", "0,10", "uid ,name ");
                                                while ($i = mysqli_fetch_array($o_towns_)) {
                                                    $uid = $i['uid'];
                                                    $name = $i['name'];
                                                    if (($cust['town']) == $uid) {
                                                        $selected = 'SELECTED';
                                                    } else {
                                                        $selected = '';
                                                    }
                                                    echo "<option $selected value=\"$uid\">$name</option>";
                                                }

                                                ?>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label for="group_" class="col-sm-3 control-label">Product</label>

                                        <div class="col-sm-9">
                                            <select class="form-control" id="primary_product">
                                                <option value="0">--Select One</option>
                                                <?php
                                                $o_loan_products_ = fetchtable('o_loan_products', "status=1", "uid", "desc", "0,100", "uid ,name ");
                                                while ($y = mysqli_fetch_array($o_loan_products_)) {
                                                    $uid = $y['uid'];
                                                    $name = $y['name'];

                                                    if (($cust['primary_product']) == $uid) {
                                                        $selected_ = 'SELECTED';
                                                    } else {
                                                        $selected_ = '';
                                                    }
                                                    echo "<option $selected_ value=\"$uid\">$name</option>";
                                                }

                                                ?>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label for="loan_limit" class="col-sm-3 control-label">Loan Limit</label>

                                        <div class="col-sm-9">
                                            <input type="text" class="form-control" value="<?php echo $cust['loan_limit']; ?>" id="loan_limit" placeholder="0">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="branch_" class="col-sm-3 control-label">Branch</label>

                                        <div class="col-sm-9">
                                            <select class="form-control" id="branch_">
                                                <option value="0">--Select One</option>
                                                <?php
                                                $o_branches_ = fetchtable('o_branches', "status=1", "uid", "desc", "0,1000", "uid ,name ");
                                                while ($u = mysqli_fetch_array($o_branches_)) {
                                                    $uid = $u['uid'];
                                                    $name = $u['name'];
                                                    if (($cust['branch']) == $uid) {
                                                        $selected_br = 'SELECTED';
                                                    } else {
                                                        $selected_br = '';
                                                    }
                                                    echo "<option $selected_br value='$uid'>$name</option>";
                                                }

                                                ?>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="form-group" style="display: <?php echo $group_display; ?>">
                                        <label for="group_" class="col-sm-3 control-label">Group</label>

                                        <div class="col-sm-9">
                                            <select class="form-control" id="group_">
                                                <option value="0">--Select One</option>
                                                <?php
                                                $o_groups = fetchtable('o_customer_groups', "status=1", "uid", "desc", "0,100", "uid , group_name");
                                                while ($g = mysqli_fetch_array($o_groups)) {
                                                    $guid = $g['uid'];
                                                    $g_name = $g['group_name'];

                                                    if ($guid == $customer_group) {
                                                        $selected_gr = 'SELECTED';
                                                    } else {
                                                        $selected_gr = "";
                                                    }

                                                    echo "<option $selected_gr value='$guid'>$g_name</option>";
                                                }

                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group" style="display: none;">
                                        <label for="agent_" class="col-sm-3 control-label">Current Agent</label>

                                        <div class="col-sm-9">
                                            <select class="form-control" id="agent_">
                                                <option value="0">--Select One</option>
                                                <?php

                                                $o_staff = fetchtable('o_users', "status=1 AND branch > 1", "name", "asc", "0,1000", "uid ,name");
                                                while ($ss = mysqli_fetch_array($o_staff)) {
                                                    $uid = $ss['uid'];
                                                    $name = $ss['name'];
                                                    if (($cust['current_agent']) == $uid) {
                                                        $selected_us = 'SELECTED';
                                                    } else {
                                                        $selected_us = '';
                                                    }
                                                    echo "<option $selected_us value='$uid'>$name</option>";
                                                }

                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="status_" class="col-sm-3 control-label">Status</label>

                                        <div class="col-sm-9">
                                            <select class="form-control" id="status_">
                                                <option value="0">--Select One</option>
                                                <?php

                                                $o_customer_statuses_ = fetchtable('o_customer_statuses', "uid>0", "uid", "desc", "0,10", "uid ,code ,name ");
                                                while ($e = mysqli_fetch_array($o_customer_statuses_)) {
                                                    $uid = $e['uid'];
                                                    $code = $e['code'];
                                                    $name = $e['name'];

                                                    if (($cust['status']) == $code) {
                                                        $selected_st = 'SELECTED';
                                                    } else {
                                                        $selected_st = '';
                                                    }
                                                    echo "<option $selected_st value=\"$code\">$name</option>";
                                                }

                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-sm-3">

                                    </div>
                                    <div class="col-sm-9">
                                        <div class="box-footer">
                                            <br />
                                            <button id="<?php $btn_action; ?>" type="submit" class="btn btn-lg btn-default">Cancel</button>
                                            <button id="customer-add-edit" type="submit" class="btn btn-success btn-lg pull-right" onclick="customer_save();">Save
                                            </button>
                                        </div>
                                    </div>

                                </div>
                                <!-- /.box-body -->

                                <!-- /.box-footer -->
                            </form>
                        <?php
                        }
                        ?>
                        <input type="hidden" id="cid" value="<?php echo $cid; ?>">
                    </div>
                    <div class="col-sm-4 box-body">
                        <?php
                        if (isset($_GET['contact'])) {
                            $contact_list = $_GET['customer-add-edit'];
                        ?>
                            <div class="small_list" id="contacts_">
                                Loading ...
                            </div>

                        <?php
                        }
                        if (isset($_GET['referees'])) {
                            $referee_list = $_GET['customer-add-edit'];
                        ?>
                            <div class="small_list" id="referees_">
                                Loading ...
                            </div>
                        <?php
                        }
                        if (isset($_GET['collateral'])) {
                            $collateral_list = $_GET['customer-add-edit'];
                        ?>
                            <div class="small_list" id="collateral_">
                                Loading ...
                            </div>
                        <?php
                        }
                        if (isset($_GET['uploads'])) {
                            $upload_list = $_GET['customer-add-edit'];
                        ?>
                            <div class="small_list" id="uploads_">
                                Loading ...
                            </div>
                        <?php
                        }


                        ?>

                    </div>
                </div>
                <!-- /.box-body -->
            </div>
            <!-- /.box -->
        </div>
        <!-- /.col -->
    </div>
</section>
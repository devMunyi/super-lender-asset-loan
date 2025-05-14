/////-------Graph
function graph_load() {
  let date_start = $("#date_start").val();
  let date_end = $("#date_end").val();
  let select_type = $("#select_type").val();
  let params =
    "search_type=" +
    select_type +
    "&start_date=" +
    date_start +
    "&end_date=" +
    date_end;
  load_std(
    "/jresources/dashboards/disbursement-collection-graph.php",
    "#graph1",
    params
  );
}
function graph2_load() {
  let date_start = $("#date_start").val();
  let date_end = $("#date_end").val();
  let select_type = $("#select_type").val();
  let params =
    "search_type=" +
    select_type +
    "&start_date=" +
    date_start +
    "&end_date=" +
    date_end;
  load_std(
    "/jresources/dashboards/nd_disbursement-collection-graph.php",
    "#graph1",
    params
  );
}
function monthly_performance() {
  $("#monthly_").html("Loading ...");
  let year = $("#year_").val();
  let month = $("#month_").val();
  let branch = $("#branch_").val();
  let product = $("#products_").val();
  let params =
    "year=" +
    year +
    "&month=" +
    month +
    "&branch=" +
    branch +
    "&product=" +
    product;
  load_std(
    "/jresources/dashboards/monthly-performance.php",
    "#monthly_",
    params
  );
}
function bdo_performance() {
  $("#bdo_").html("Loading ...");
  let year = $("#year_").val();
  let month = $("#month_").val();
  let params = "year=" + year + "&month=" + month;
  load_std("/jresources/dashboards/bdo-performance.php", "#bdo_", params);
}
function addData(chart, label, color, data) {
  chart.data.datasets.push({
    label: label,
    backgroundColor: color,
    data: data,
  });
  chart.update();
}
function generateRandomColor() {
  var randomColor = "#" + Math.floor(Math.random() * 16777215).toString(16);
  return randomColor;
  //random color will be freshly served
}
function performance_breakdown_load() {
  let date_start = $("#bdate_start").val();
  let date_end = $("#bdate_end").val();
  let params = "start_date=" + date_start + "&end_date=" + date_end;
  load_std(
    "/jresources/dashboards/performance_breakdown.php",
    "#perform_",
    params
  );
}

function income_load() {
  let date_start = $("#idate_start").val();
  let date_end = $("#idate_end").val();
  let params = "start_date=" + date_start + "&end_date=" + date_end;
  load_std("/jresources/dashboards/income_summary.php", "#income_", params);
}
function collection_rate() {
  let params = "";
  load_std(
    "/jresources/dashboards/collection-rate.php",
    "#collection_rate",
    params
  );
}
function defaulters_breakdown() {
  let date_start = $("#ddate_start").val();
  let date_end = $("#ddate_end").val();
  let params = "start_date=" + date_start + "&end_date=" + date_end;
  load_std("/jresources/dashboards/defaulters.php", "#defaulters_", params);
}

////--------------------New Dashboards
function nd_loan_progress() {
  let date_start = $("#start_date_progress").val();
  let date_end = $("#end_date_progress").val();
  let obj = $("#dobj").val();
  let params =
    "start_date=" + date_start + "&end_date=" + date_end + "&obj=" + obj;
  load_std(
    "/jresources/dashboards/nd_disb_progress.php",
    "#nd_disb_progress",
    params
  );
}

function nd_payments_progress() {
  let date_start = $("#start_date_payprogress").val();
  let date_end = $("#end_date_payprogress").val();
  let obj = $("#dobj").val();
  let params =
    "start_date=" + date_start + "&end_date=" + date_end + "&obj=" + obj;
  load_std(
    "/jresources/dashboards/nd_collections_progress.php",
    "#nd_collections_progress",
    params
  );
}

function nd_loan_list() {
  let date_start = $("#start_date_loans").val();
  let date_end = $("#end_date_loans").val();
  let obj = $("#dobj").val();
  let params =
    "start_date=" + date_start + "&end_date=" + date_end + "&obj=" + obj;
  load_std(
    "/jresources/dashboards/nd_loan_list_minimized.php",
    "#nd_dashboard_loans",
    params
  );
}
function nd_default_list() {
  let date_start = $("#start_date_default").val();
  let date_end = $("#end_date_default").val();
  let obj = $("#defobj").val();
  let params =
    "start_date=" + date_start + "&end_date=" + date_end + "&obj=" + obj;
  load_std(
    "/jresources/dashboards/nd_default_list_minimized.php",
    "#nd_default_loans",
    params
  );
}
function nd_col_today() {
  let params = "";
  load_std(
    "/jresources/dashboards/nd_collections_today.php",
    "#col_today",
    params
  );
}
function nd_col_this_week() {
  let params = "";
  load_std(
    "/jresources/dashboards/nd_collections_thisweek.php",
    "#col_this_week",
    params
  );
}
function nd_col_this_month() {
  let params = "";
  load_std(
    "/jresources/dashboards/nd_collections_thismonth.php",
    "#col_this_month",
    params
  );
}
function nd_numbers() {
  let date_start = $("#start_date_numbers").val();
  let date_end = $("#end_date_numbers").val();

  let params = "start_date=" + date_start + "&end_date=" + date_end;
  load_std(
    "/jresources/dashboards/nd_numbers_new.php",
    "#numbers_crunch",
    params
  );
  // let params = "start_date=" + date_start + "&end_date=" + date_end;
  // load_std("/jresources/dashboards/nd_numbers.php", "#numbers_crunch", params);
}
function nd_pay_list() {
  let date_start = $("#start_date_pay").val();
  let date_end = $("#end_date_pay").val();
  let obj = $("#dobjp").val();
  let params =
    "start_date=" + date_start + "&end_date=" + date_end + "&obj=" + obj;
  load_std(
    "/jresources/dashboards/nd_pay_list.php",
    "#nd_dashboard_payments",
    params
  );
}
function nd_distribution_list() {
  let date_start = $("#start_date_dist").val();
  let date_end = $("#end_date_dist").val();

  let obj = $("#dobjp").val();
  let params =
    "start_date=" + date_start + "&end_date=" + date_end + "&obj=" + obj;
  load_std(
    "/jresources/dashboards/nd_distribution_list.php",
    "#nd_dashboard_distribution",
    params
  );
}
////--------------------Accounting
function accounting_load(page) {
  let date_start = $("#start_date_acc").val();
  let date_end = $("#end_date_acc").val();
  let obj = $("#dobj").val();
  let params =
    "start_date=" + date_start + "&end_date=" + date_end + "&obj=" + obj;
  load_std("/jresources/accounting/" + page, "#account_load", params);
}
/////---------------------Other
function modal_view(resource, params, title = "Details") {
  $("#mainModal").modal("toggle");
  let server_ = $("#server_").val();
  let fields = params;
  $.ajax({
    method: "POST",
    url: server_ + resource,
    data: fields,
    beforeSend: function () {
      $("#processing").show();
    },

    complete: function () {
      $("#processing").hide();
    },
    success: function (feedback) {
      $("#mainModal").html(
        '<div class="modal-dialog">\n' +
          "\n" +
          "    <!-- Modal content-->\n" +
          '    <div class="modal-content">\n' +
          '        <div class="modal-header">\n' +
          '            <button type="button" class="close" data-dismiss="modal">&times;</button>\n' +
          '            <h3 class="modal-title">' +
          title +
          "</h3>\n" +
          "        </div>\n" +
          '        <div class="modal-body">\n' +
          feedback +
          "</div>\n" +
          '        <div class="modal-footer">\n' +
          '            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>\n' +
          "        </div>\n" +
          "    </div>\n" +
          "\n" +
          "</div>"
      );
    },
    error: function (err) {
      $("#mainModal").html(err);
    },
  });
}

function modal_hide() {
  $("#mainModal").modal("toggle");
}
function showhide(showitem, hideitem) {
  $(hideitem).fadeOut("fast");
  $(showitem).fadeIn("fast");
}
function modal_show() {
  setTimeout(function () {
    $("#mainModal").modal("show");
  }, 1000);
}

function clear_form(formid) {
  document.getElementById(formid).reset();
}

//////////--------------Notifications
function notifications_count() {
  let params = "";
  dbaction("/action/notifications_count", params, function (feed) {
    if (parseInt(feed) > 0) {
      $("#notif_count").html(feed).fadeIn("fast");
    } else {
      $("#notif_count").html("").css("display", "none");
    }
  });
}
function notifications_display() {
  let params = "";
  dbaction("/action/notifications_display", params, function (feed) {
    $("#popup_notif").append(feed).fadeIn("slow");
    updateCounter();
  });
}
function remove_element(el) {
  $(el).remove();
}

function reference_check() {
  let reference_phone = $("#reference_phone").val();
  let params = "reference_phone=" + reference_phone;
  if (reference_phone.length > 6) {
    modal_view(
      "/jresources/referees/reference-check",
      params,
      "Reference Results for " + reference_phone
    );
  } else {
    feedback("NOTICE", "TOAST", ".feedback", "Enter a valid phone", 2);
  }
}

function notifications_count_reset() {
  let params = "";
  $("#notif_count").fadeOut("fast");
  dbaction("/action/notifications_count_reset", params, function (feed) {});
}

function message_list() {
  $("#message_drop").html(
    "<li class='header'>You have 4 messages</li>\n" +
      "                        <li>\n" +
      "                            <!-- inner menu: contains the actual data -->\n" +
      '                            <ul class="menu">\n' +
      "                                <li><!-- start message -->\n" +
      '                                    <a href="#">\n' +
      '                                        <div class="pull-left">\n' +
      '                                            <img src="dist/img/user2-160x160.jpg" class="img-circle" alt="User Image">\n' +
      "                                        </div>\n" +
      "                                        <h4>\n" +
      "                                            Support Team\n" +
      '                                            <small><i class="fa fa-clock-o"></i> 5 mins</small>\n' +
      "                                        </h4>\n" +
      "                                        <p>Why not buy a new awesome theme?</p>\n" +
      "                                    </a>\n" +
      "                                </li>\n" +
      "                                <!-- end message -->\n" +
      "                                <li>\n" +
      '                                    <a href="#">\n' +
      '                                        <div class="pull-left">\n' +
      '                                            <img src="dist/img/user3-128x128.jpg" class="img-circle" alt="User Image">\n' +
      "                                        </div>\n" +
      "                                        <h4>\n" +
      "                                            OnePay Design Team\n" +
      '                                            <small><i class="fa fa-clock-o"></i> 2 hours</small>\n' +
      "                                        </h4>\n" +
      "                                        <p>Why not buy a new awesome theme?</p>\n" +
      "                                    </a>\n" +
      "                                </li>\n" +
      "                                <li>\n" +
      '                                    <a href="#">\n' +
      '                                        <div class="pull-left">\n' +
      '                                            <img src="dist/img/user4-128x128.jpg" class="img-circle" alt="User Image">\n' +
      "                                        </div>\n" +
      "                                        <h4>\n" +
      "                                            Developers\n" +
      '                                            <small><i class="fa fa-clock-o"></i> Today</small>\n' +
      "                                        </h4>\n" +
      "                                        <p>Why not buy a new awesome theme?</p>\n" +
      "                                    </a>\n" +
      "                                </li>\n" +
      "                                <li>\n" +
      '                                    <a href="#">\n' +
      '                                        <div class="pull-left">\n' +
      '                                            <img src="dist/img/user3-128x128.jpg" class="img-circle" alt="User Image">\n' +
      "                                        </div>\n" +
      "                                        <h4>\n" +
      "                                            Sales Department\n" +
      '                                            <small><i class="fa fa-clock-o"></i> Yesterday</small>\n' +
      "                                        </h4>\n" +
      "                                        <p>Why not buy a new awesome theme?</p>\n" +
      "                                    </a>\n" +
      "                                </li>\n" +
      "                                <li>\n" +
      '                                    <a href="#">\n' +
      '                                        <div class="pull-left">\n' +
      '                                            <img src="dist/img/user4-128x128.jpg" class="img-circle" alt="User Image">\n' +
      "                                        </div>\n" +
      "                                        <h4>\n" +
      "                                            Reviewers\n" +
      '                                            <small><i class="fa fa-clock-o"></i> 2 days</small>\n' +
      "                                        </h4>\n" +
      "                                        <p>Why not buy a new awesome theme?</p>\n" +
      "                                    </a>\n" +
      "                                </li>\n" +
      "                            </ul>\n" +
      "                        </li>\n" +
      '                        <li class="footer"><a href="#">See All Messages</a></li>'
  );
}

function notif_list(target, offset, rpp) {
  let where = $("#_where_").val();
  if (!where) {
    where = "uid > 0";
  }
  let orderby = $("#_orderby_").val();
  if (!orderby) {
    orderby = "uid";
  }
  let dir = $("#_dir_").val();
  if (!dir) {
    dir = "desc";
  }
  let search = $("#search_").val();
  if (!search) {
    search = "";
  }
  let params =
    "where_=" +
    where +
    "&offset=" +
    offset +
    "&rpp=" +
    rpp +
    "&orderby=" +
    orderby +
    "&dir=" +
    dir +
    "&search_=" +
    search;
  dbaction("/jresources/notifications_list", params, function (feed) {
    console.log(params);
    notifications_count_reset();

    $(target).html(
      feed +
        "<li class='footer'><a href='profile?notifications'>View all</a></li>"
    );
    setTimeout(function () {
      pager_refactor();
    }, 200);
  });
}

/////////--------------End of notifications
/////---------------------

/////////---------------Staff Update
function staff_save() {
  let sid = parseInt($("#sid").val());
  let name = $("#full_name").val();
  let email = $("#email_").val();
  let phone = $("#phone_number").val();
  let national_id = $("#national_id").val();
  let password = $("#passwo").val();
  let user_group = $("#group_").val();
  let branch = $("#branch_").val();
  let status = $("#status_").val();
  let tag = $("#tag_").val();
  let pair_ = $("#pair_").val();

  let endpoint = "staff_save";
  if (sid > 0) {
    endpoint = "update";
  }

  let params =
    "name=" +
    name +
    "&email=" +
    email +
    "&phone=" +
    phone +
    "&user_group=" +
    user_group +
    "&branch=" +
    branch +
    "&status=" +
    status +
    "&sid=" +
    sid +
    "&national_id=" +
    national_id +
    "&password=" +
    password +
    "&tag=" +
    tag +
    "&pair_=" +
    pair_;

  console.log("params =>", params);

  dbaction("/action/staff/" + endpoint, params, function (feed) {
    console.log(feed);
    feedback("DEFAULT", "TOAST", ".feedback_", feed, "4");
  });
}

function addbranches(aid) {
  if (parseInt(aid) > 0) {
    let branch = $("#branches_").val();
    let params = "aid=" + aid + "&branch=" + branch;
    dbaction("/action/staff/add-branch", params, function (feed) {
      console.log(feed);
      feedback("DEFAULT", "TOAST", ".feedback_", feed, "4");
    });
  } else {
    feedback("DEFAULT", "TOAST", ".feedback_", "Please save agent first", "4");
  }
}
function staff_branches(staff) {
  let fds = "staff=" + staff;
  load_std("/jresources/staff/staff-branches", "#staff_branches", fds);
}
function remove_agent_branch(rec_id) {
  Swal.fire({
    title: "Proceed?",
    text: "Are you sure you want to remove this branch from agent account?",
    showCancelButton: true,
    confirmButtonText: "Yes",
    cancelButtonText: "No",
  }).then(function (result) {
    if (result.isConfirmed) {
      let params = "rec_id=" + rec_id;
      dbaction("/action/staff/remove-branch", params, function (feed) {
        console.log(feed);
        feedback("DEFAULT", "TOAST", ".feedback_", feed, "4");
      });
    }
  });
}

function staff_list() {
  let where = $("#_where_").val();
  if (!where) {
    where = "uid > 0";
  }
  let offset = $("#_offset_").val();
  if (!offset) {
    offset = 0;
  }
  let rpp = $("#_rpp_").val();
  if (!rpp) {
    rpp = 10;
  }

  let page_no = $("#_page_no_").val();
  if (!page_no) {
    page_no = 2;
  }

  let orderby = $("#_orderby_").val();
  if (!orderby) {
    orderby = "uid";
  }
  let dir = $("#_dir_").val();
  if (!dir) {
    dir = "desc";
  }
  let search = $("#search_").val();
  if (!search) {
    search = "";
  }

  let params =
    "where_=" +
    where +
    "&offset=" +
    offset +
    "&rpp=" +
    rpp +
    "&page_no=" +
    page_no +
    "&orderby=" +
    orderby +
    "&dir=" +
    dir +
    "&search_=" +
    search;
  dbaction("/jresources/staff_list", params, function (feed) {
    console.log(params);
    $("#staff_list").html(feed);
    setTimeout(function () {
      pager_refactor();
    }, 200);
  });
}

//staff list filters
function staff_filters() {
  let staff_order = $("#staff_order").val();
  let sel_branch = parseInt($("#sel_branch").val());
  let user_group = parseInt($("#group_").val());
  const sel_status = parseInt($("#sel_status").val());

  let wher = "uid > 0";
  $("#_dir_").val(staff_order);

  if (sel_branch > 0) {
    wher += " AND branch=" + sel_branch;
  }

  if (user_group > 0) {
    wher += " AND user_group=" + user_group;
  }

  if (sel_status > 0) {
    wher += " AND status=" + sel_status;
  }

  if (wher) {
    $("#_where_").val(wher);
    $("#_offset_").val(0);

    pager_home();
  } else {
    $("#_where_").val(" status > -1");
    $("#_offset_").val(0);
  }
}

function update_password() {
  let old_pass = $("#old_password").val();
  let new_password = $("#new_password").val();
  let new_password2 =  $('#new_password2').val();

  let params = "old_pass=" + old_pass + "&new_pass=" + new_password+"&new_pass2="+new_password2;
  dbaction("/action/password_change", params, function (feed) {
    console.log(JSON.stringify(feed));
    feedback("DEFAULT", "TOAST", ".feedback_", feed, "3");
  });
}

function block_member(member_id, title) {
  Swal.fire({
    title: "Proceed?",
    text: "Are you sure you want to " + title + "?",
    showCancelButton: true,
    confirmButtonText: "Yes",
    cancelButtonText: "No",
  }).then(function (result) {
    if (result.isConfirmed) {
      var params = "member_id=" + member_id;
      dbaction("/action/staff/delete", params, function (feed) {
        feedback("DEFAULT", "TOAST", ".feedback_", feed, "4");
      });
    }
  });
}

/////////==============End of staff update

/////////--------------Customers
function input_add(element, value) {
  $(element).val(value);
}

function customer_save() {
  // toggleBtnState('customer-add-edit');
  let uid = parseInt($("#cid").val());
  let url = "customer_save";
  if (uid > 0) {
    url = "customer_update";
  } else {
    url = "customer_save";
  }
  let full_name = $("#full_name").val();
  let primary_mobile = $("#phone_number").val();
  const phone_number_provider = parseInt($("#phone_number_provider").val(), 10);
  // console.log("PROVIDER ", phone_number_provider);
  let email_address = $("#email_").val();
  let physical_address = encodeURIComponent($("#main_address").val());
  let town = $("#town_").val();
  let national_id = $("#national_id").val();
  let gender = $('input[name="gender"]:checked').val();
  let dob = $("#dob").val();
  let branch = $("#branch_").val();
  let group_id = $("#group_").val();
  let primary_product = $("#primary_product").val();
  let loan_limit = $("#loan_limit").val();
  let staff = $("#agent_").val();
  let status = $("#status_").val();
  const geolocation = encodeURIComponent($("#geolocation").val());
  let params =
    "cid=" +
    uid +
    "&full_name=" +
    full_name +
    "&primary_mobile=" +
    primary_mobile +
    "&email_address=" +
    email_address +
    "&physical_address=" +
    physical_address +
    "&town=" +
    town +
    "&national_id=" +
    national_id +
    "&gender=" +
    gender +
    "&dob=" +
    dob +
    "&branch=" +
    branch +
    "&primary_product=" +
    primary_product +
    "&loan_limit=" +
    loan_limit +
    "&status=" +
    status +
    "&agent=" +
    staff +
    "&group_id=" +
    group_id +
    "&geolocation=" +
    geolocation +
    "&phone_number_provider=" +
    phone_number_provider;
  dbaction("/action/" + url, params, function (feed) {
    console.log(JSON.stringify(feed));
    feedback("DEFAULT", "TOAST", ".feedback_", feed, "4");
    // toggleBtnState('customer-add-edit');
  });
}

function customerFileUpload() {
  // let sid = parseInt($('#sid').val());
  $("#bar").text("");
  const tbl = $("#tbl").val();
  const rec = $("#rec").val();
  const uploadTitle = $("#title").val();
  const description = $("#description").val();
  const uploadedFilename = $("#uploaded-file-name").val();
  const type_ = $("#type_").val();
  const endpoint = "/action/files/customer_upload_file_external";

  const params =
    "upload_title=" +
    uploadTitle +
    "&uploaded_filename=" +
    uploadedFilename +
    "&tbl=" +
    tbl +
    "&rec=" +
    rec +
    "&type_=" +
    type_ +
    "&description=" +
    description;
  dbaction(endpoint, params, function (feed) {
    console.log(feed);
    feedback("DEFAULT", "TOAST", ".feedback_", feed, "4");
  });
}

function customer_group_save() {
  let uid = parseInt($("#gid").val());
  let url = "group_save";
  if (uid > 0) {
    url = "group_update";
  } else {
    url = "group_save";
  }
  const group_name = $("#group_name").val();
  const group_branch = $("#group_branch").val();
  const group_description = $("#group_description").val();
  const leader_name = $("#leader_name").val();
  const group_phone = $("#group_phone").val();
  const group_till = $("#group_till").val();
  const group_acc = $("#group_acc").val();
  const meeting_day = $("#meeting_day").val()?.trim();
  const meeting_time = $("#meeting_time").val()?.trim();
  const meeting_venue = $("#meeting_venue").val()?.trim();

  let params =
    "gid=" +
    uid +
    "&group_name=" +
    group_name +
    "&group_description=" +
    group_description +
    "&group_phone=" +
    group_phone +
    "&group_till=" +
    group_till +
    "&group_acc=" +
    group_acc +
    "&leader_name=" +
    leader_name +
    "&group_branch=" +
    group_branch +
    "&meeting_day=" +
    meeting_day +
    "&meeting_time=" +
    meeting_time +
    "&meeting_venue=" +
    meeting_venue;
  dbaction("/action/customer_groups/" + url, params, function (feed) {
    console.log(JSON.stringify(feed));
    feedback("DEFAULT", "TOAST", ".feedback_", feed, "4");
  });
}
function addtogroup(gid) {
  let cid = $("#customer_id_").val();

  let params = "gid=" + gid + "&cid=" + cid;
  dbaction("/action/customer_groups/add_to_group", params, function (feed) {
    console.log(JSON.stringify(feed));
    feedback("DEFAULT", "TOAST", ".feedback_", feed, "4");
  });
}
function delete_member(cid, gid) {
  Swal.fire({
    title: "Proceed?",
    text: "Are you sure you want to remove this member?",
    showCancelButton: true,
    confirmButtonText: "Yes",
    cancelButtonText: "No",
  }).then(function (result) {
    if (result.isConfirmed) {
      let params = "gid=" + gid + "&cid=" + cid;
      dbaction(
        "/action/customer_groups/remove_from_group",
        params,
        function (feed) {
          console.log(JSON.stringify(feed));
          feedback("DEFAULT", "TOAST", ".feedback_", feed, "4");
        }
      );
    }
  });
}

function payment_delete(pid) {
  Swal.fire({
    title: "Proceed?",
    text: "Are you sure you want to delete this payment?",
    showCancelButton: true,
    confirmButtonText: "Yes",
    cancelButtonText: "No",
  }).then(function (result) {
    if (result.isConfirmed) {
      let params = "pid=" + pid;
      dbaction("/action/payments/delete", params, function (feed) {
        console.log(JSON.stringify(feed));
        feedback("DEFAULT", "TOAST", ".feedback_", feed, "4");
      });
    }
  });
}

function payment_approve(pid) {
  Swal.fire({
    title: "Proceed?",
    text: "Are you sure you want to approve this payment?",
    showCancelButton: true,
    confirmButtonText: "Yes",
    cancelButtonText: "No",
  }).then(function (result) {
    if (result.isConfirmed) {
      let params = "pid=" + pid;
      dbaction("/action/payments/approve", params, function (feed) {
        console.log(JSON.stringify(feed));
        feedback("DEFAULT", "TOAST", ".feedback_", feed, "4");
      });
    }
  });
}

function split_group_payment(gid, pid) {
  modal_view(
    "/forms/split-group-payment.php",
    "gid=" + gid + "&pid=" + pid,
    "Allocate Group Payment"
  );
}
function split_pay() {
  let amount = $("#amount_").val();
  let loan_id = $("#loan_id").val();
  let customer_id = $("#customer_id").val();
  let payment_for = $("#payment_for").val();
  let parent_payment = $("#payment_id").val();
  let params =
    "loan_id=" +
    loan_id +
    "&amount=" +
    amount +
    "&parent_payment=" +
    parent_payment +
    "&customer_id=" +
    customer_id +
    "&payment_for=" +
    payment_for;
  dbaction(
    "/action/customer_groups/split_payment.php",
    params,
    function (feed) {
      feedback("DEFAULT", "TOAST", ".feedback_", feed, "2");
    }
  );
}

function update_limit_popup(cid, mode) {
  modal_view(
    "/jresources/customers/limit",
    "cid=" + cid + "&mode=" + mode,
    mode + " Limit"
  );
}
function give_limit(cid) {
  let new_limit = $("#new_limit").val();
  let limit_reason = $("#limit_reason").val();
  let params =
    "cid=" + cid + "&new_limit=" + new_limit + "&limit_reason=" + limit_reason;
  dbaction("/action/customer/update_limit", params, function (feed) {
    feedback("DEFAULT", "TOAST", ".feedback_", feed, "2");
  });
}
function give_limit_approval(lid, action) {
  Swal.fire({
    title: "Proceed?",
    text: "Are you sure you want to " + action + " this limit?",
    showCancelButton: true,
    confirmButtonText: "Yes",
    cancelButtonText: "No",
  }).then(function (result) {
    if (result.isConfirmed) {
      let params = "lid=" + lid + "&action=" + action;
      dbaction("/action/customer/limit_action", params, function (feed) {
        feedback("DEFAULT", "TOAST", ".feedback_", feed, "2");
      });
    }
  });
}

function customer_save_additional_contact(cid, contact_id) {
  let url = "customer_save_contact";
  if (parseInt(contact_id) > 0) {
    url = "customer_update_contact";
  } else {
    url = "customer_save_contact";
  }

  let customer_id = cid;
  let contact_type = parseInt($("#contact_type").val());
  let value = $("#contact_value").val();
  let params =
    "customer_id=" +
    customer_id +
    "&contact_type=" +
    contact_type +
    "&value=" +
    value +
    "&contact_id=" +
    contact_id;
  dbaction("/action/" + url, params, function (feed) {
    console.log(JSON.stringify(feed));
    feedback("DEFAULT", "TOAST", ".eedback_", feed, "4");
  });
}

function customer_add_referee(cid) {
  let customer_id = cid;
  let refId = $("#ref_id").val();
  let referee_name = $("#full_name").val();
  let id_no = $("#national_id").val();
  let mobile_no = $("#phone_number").val();
  let physical_address = $("#main_address").val();
  let email_address = $("#email_").val();
  let relationship = $("#relationship").val();

  let action = "save_new";
  if (parseInt(refId) > 0) {
    action = "update";
  }

  let params =
    "customer_id=" +
    customer_id +
    "&referee_name=" +
    referee_name +
    "&id_no=" +
    id_no +
    "&mobile_no=" +
    mobile_no +
    "&physical_address=" +
    physical_address +
    "&email_address=" +
    email_address +
    "&relationship=" +
    relationship +
    "&ref_id=" +
    refId;
  dbaction("/action/referees/" + action, params, function (feed) {
    console.log(JSON.stringify(feed));
    feedback("DEFAULT", "TOAST", ".feedback_", feed, "4");
  });
}

function customer_add_guarantor(cid) {
  const customerId = cid;
  const guarantorId = $("#guarantor_id").val();
  const guarantorName = $("#guarantor_name").val();
  const nationalId = $("#g_national_id").val();
  const mobileNo = $("#g_mobile_no").val();
  const physicalAddress = $("#g_physical_address").val();
  const relationship = $("#g_relationship").val();

  let action = "save_new";
  if (parseInt(guarantorId) > 0) {
    action = "update";
  }

  const params =
    "customer_id=" +
    customerId +
    "&guarantor_name=" +
    guarantorName +
    "&national_id=" +
    nationalId +
    "&mobile_no=" +
    mobileNo +
    "&physical_address=" +
    physicalAddress +
    "&relationship=" +
    relationship +
    "&guarantor_id=" +
    guarantorId;
  dbaction("/action/guarantors/" + action, params, function (feed) {
    console.log(JSON.stringify(feed));
    feedback("DEFAULT", "TOAST", ".feedback_", feed, "4");
  });
}

function customer_add_collateral(cid) {
  let endpoint = "save_new";
  let col_id = $("#col_id").val();
  if (parseInt(col_id) > 0) {
    endpoint = "update";
  } else {
  }

  let customer_id = cid;
  let category = $("#category").val();
  let title = $("#title").val();
  let description = $("#description").val();
  let money_value = $("#money_value").val();
  let doc_reference_no = $("#reference_number").val();
  let filling_reference_no = $("#physical_file_number").val();
  let digital_file_number = $("#digital_file_number").val();
  let params =
    "&customer_id=" +
    customer_id +
    "&category=" +
    category +
    "&title=" +
    title +
    "&description=" +
    description +
    "&money_value=" +
    money_value +
    "&doc_reference_no=" +
    doc_reference_no +
    "&filling_reference_no=" +
    filling_reference_no +
    "&money_value=" +
    money_value +
    "&col_id=" +
    col_id +
    "&digital_file_number=" +
    digital_file_number;
  dbaction("/action/collateral/" + endpoint, params, function (feed) {
    console.log(JSON.stringify(feed));
    feedback("DEFAULT", "TOAST", ".feedback_", feed, "4");
  });
}

function change_customer_status(cust, status, act) {
  let params = "customer_id=" + cust + "&status=" + status;
  Swal.fire({
    title: "Proceed?",
    text: "Are you sure you want to " + act + "?",
    showCancelButton: true,
    confirmButtonText: "Yes",
    cancelButtonText: "No",
  }).then(function (result) {
    if (result.isConfirmed) {
      dbaction("/action/customer/change-status", params, function (feed) {
        feedback("DEFAULT", "TOAST", ".feedback_", feed, "3");
      });
    }
  });
}

function change_customer_agent(cust) {
  modal_view(
    "/widgets/customers/change_agent",
    "customer_id=" + cust,
    "Change Customer LO/CO"
  );
}

function customer_list(typ = "ACTIVE") {
  // let a_status = "";
  // alert(typ);
  // if(typ === 'ACTIVE'){
  //     a_status = " AND status in (1,2)";
  // }
  // else{
  //      a_status = " AND status in (0, 3)";
  // }
  let where = $("#_where_").val();
  if (!where) {
    where = "uid > 0";
  }
  let offset = $("#_offset_").val();
  if (!offset) {
    offset = 0;
  }
  let rpp = $("#_rpp_").val();
  if (!rpp) {
    rpp = 10;
  }
  let page_no = $("#_page_no_").val();
  if (!page_no) {
    page_no = 1;
  }
  let orderby = $("#_orderby_").val();
  if (!orderby) {
    orderby = "uid";
  }
  let dir = $("#_dir_").val();
  if (!dir) {
    dir = "desc";
  }
  let search = $("#search_").val();
  if (!search) {
    search = "";
  }

  console.log("type " + typ);

  let params =
    "where_=" +
    where +
    "&offset=" +
    offset +
    "&rpp=" +
    rpp +
    "&page_no=" +
    page_no +
    "&orderby=" +
    orderby +
    "&dir=" +
    dir +
    "&search_=" +
    search +
    "&type=" +
    typ;
  dbaction("/jresources/customer_list", params, function (feed) {
    console.log(params);
    $("#customer_list").html(feed);
    setTimeout(function () {
      pager_refactor();
    }, 500);
  });
}
function group_list() {
  let where = $("#_where_").val();
  if (!where) {
    where = "uid > 0";
  }
  let offset = $("#_offset_").val();
  if (!offset) {
    offset = 0;
  }
  let rpp = $("#_rpp_").val();
  if (!rpp) {
    rpp = 10;
  }
  let page_no = $("#_page_no_").val();
  if (!page_no) {
    page_no = 1;
  }
  let orderby = $("#_orderby_").val();
  if (!orderby) {
    orderby = "uid";
  }
  let dir = $("#_dir_").val();
  if (!dir) {
    dir = "desc";
  }
  let search = $("#search_").val();
  if (!search) {
    search = "";
  }

  let params =
    "where_=" +
    where +
    "&offset=" +
    offset +
    "&rpp=" +
    rpp +
    "&page_no=" +
    page_no +
    "&orderby=" +
    orderby +
    "&dir=" +
    dir +
    "&search_=" +
    search;
  dbaction(
    "/jresources/customer-groups/all_groups.php",
    params,
    function (feed) {
      console.log(params);
      $("#group_list").html(feed);
      setTimeout(function () {
        pager_refactor();
      }, 500);
    }
  );
}
function group_members_list(gid) {
  let params = "gid=" + gid;
  dbaction(
    "/jresources/customer-groups/group_members.php",
    params,
    function (feed) {
      $("#gmembers").html(feed);
    }
  );
}
function group_loan_list(gid) {
  let params = "gid=" + gid;
  dbaction(
    "/jresources/customer-groups/group_loans.php",
    params,
    function (feed) {
      $("#loan_info").html(feed);
    }
  );
  dbaction(
    "/jresources/customer-groups/group_payments.php",
    params,
    function (feed) {
      $("#payments_info").html(feed);
    }
  );
}
function group_savings_list(gid) {
  let params = "gid=" + gid;
  dbaction(
    "/jresources/customer-groups/group_savings.php",
    params,
    function (feed) {
      $("#savings_info").html(feed);
    }
  );
}

function group_payment_list(gid) {
  let params = "gid=" + gid;
  dbaction(
    "/jresources/customer-groups/all-payments.php",
    params,
    function (feed) {
      $("#payments_list").html(feed);
    }
  );
}

function split_payment_list(pid) {
  let params = "pid=" + pid;
  dbaction(
    "/jresources/customer-groups/split-payments.php",
    params,
    function (feed) {
      $("#payments_breakdown_list").html(feed);
    }
  );
}

//customer list filters
function customer_filters(page = "active") {
  let loan_order = $("#customer_order").val();
  let sel_branch = parseInt($("#sel_branch").val());
  let sel_status = parseInt($("#sel_status").val());
  let sel_agent = parseInt($("#sel_agent").val());

  let wher = "uid > 0";
  $("#_dir_").val(loan_order);

  if (sel_status > 0) {
    wher += " AND status=" + sel_status + " ";
  }

  if (sel_agent > 0) {
    wher += " AND current_agent = " + sel_agent + " ";
  }

  if (sel_branch > 0) {
    wher += " AND branch=" + sel_branch + " ";
  }

  console.log("filt " + wher);

  if (wher) {
    $("#_where_").val(wher);
    $("#_offset_").val(0);

    pager_home();
  } else {
    $("#_where_").val(" status > -1");
    $("#_offset_").val(0);
  }
}

function contact_list(customer, action) {
  let params = "customer=" + customer + "&action=" + action;
  dbaction("/jresources/contact_list", params, function (feed) {
    console.log(JSON.stringify(feed));
    $("#contacts_").html(feed);
  });
}

function delete_contact(contact_id) {
  Swal.fire({
    title: "Proceed?",
    text: "Are you sure you want to delete this contact?",
    showCancelButton: true,
    confirmButtonText: "Yes",
    cancelButtonText: "No",
  }).then(function (result) {
    if (result.isConfirmed) {
      var params = "contact_id=" + contact_id;
      dbaction("/action/contact_delete", params, function (feed) {
        feedback("DEFAULT", "TOAST", ".feedback_", feed, "4");
      });
    }
  });
}

function referee_list(customer, action) {
  let params = "customer=" + customer + "&action=" + action;
  dbaction("/jresources/referees/referee_list", params, function (feed) {
    console.log(JSON.stringify(feed));
    $("#referees_").html(feed);
  });
}

function guarantor_list(customer, action) {
  const params = "customer=" + customer + "&action=" + action;
  dbaction("/jresources/guarantors/guarantor_list", params, function (feed) {
    console.log(JSON.stringify(feed));
    $("#guarantors_").html(feed);
  });
}

function collateral_list(customer, action) {
  let params = "customer=" + customer + "&action=" + action;
  dbaction("/jresources/collateral/list", params, function (feed) {
    console.log(JSON.stringify(feed));
    $("#collateral_").html(feed);
  });
}

function loan_collateral_list(loan_id) {
  let params = "loan_id=" + loan_id;
  load_std("/jresources/loans/loan_collateral", "#collateral_", params);
}
function loan_action_modal(loan_id) {
  modal_view("/widgets/loans/actions", "loan_id=" + loan_id, "Loan Action");
}

function resend_loan(loan_id, sender, v = "") {
  const b2cPortals = {
    KE_SAF: "Safaricom M-PESA",
    KE_AIRTEL: "Kenya Airtel Mobile Money",
    UG_MTN: "Uganda MTN Mobile Money",
    UG_AIRTEL: "Uganda Airtel Mobile Money",
  };
  Swal.fire({
    title: "Proceed?",
    text: `Are you sure you want to resend this loan? Please check on ${b2cPortals[sender]} portal that the money is not already sent to prevent double disbursement`,
    showCancelButton: true,
    confirmButtonText: "Yes",
    cancelButtonText: "No",
    icon: "question",
  }).then(function (result) {
    if (result.isConfirmed) {
      const params = "loan_id=" + loan_id;
      let url = null;

      switch (sender) {
        case "KE_SAF":
          url = "/action/loan/resend-loan" + v;
          break;
        case "UG_MTN":
          url = "/action/loan/mtn/resend-loan";
          break;
        case "UG_AIRTEL":
          url = "/action/loan/airtel/resend-loan";
          break;
        default:
          url = "/action/loan/resend-loan" + v;
          break;
      }

      if (url) {
        dbaction(url, params, function (feed) {
          feedback("DEFAULT", "TOAST", ".feedback_", feed, "3");
        });
      } else {
        feedback(
          "DEFAULT",
          "TOAST",
          ".feedback_",
          "Invalid Resource URL Passed",
          "3"
        );
      }
    }
  });
}

function product_reminder_modal(reminder_id, product_id) {
  let params = "reminder_id=" + reminder_id + "&product_id=" + product_id;
  modal_view("/widgets/loans/product_reminder_edit", params, "Reminder Action");
}
function save_reminder() {
  let pid = $("#product_id").val();
  let rid = $("#rid").val();
  let loan_day = $("#loan_day").val();
  let custom_event = $("#custom_event").val();
  let loan_status = $("#loan_status").val();
  let message = $("#message").val();
  let status = $("#status_").val();
  let params =
    "pid=" +
    pid +
    "&rid=" +
    rid +
    "&loan_day=" +
    loan_day +
    "&custom_event=" +
    custom_event +
    "&loan_status=" +
    loan_status +
    "&message=" +
    message +
    "&status=" +
    status;
  dbaction("/action/loan_products/reminder-save", params, function (feed) {
    feedback("DEFAULT", "TOAST", ".feedback_", feed, "3");
  });
}

function update_loan(lid) {
  let current_lo = $("#current_lo").val();
  let current_co = $("#current_co").val();
  let current_branch = $("#current_branch").val();
  let disbursed_date = $("#disbursed_date").val();
  let next_due_date = $("#next_due_date").val();
  let final_due_date = $("#final_due_date").val();
  let loan_amount = $("#loan_amount").val();
  let disbursed_amount = $("#disbursed_amount").val();
  let loan_prod = $("#loan_prod").val();
  let income_earned = $("#income_earned").val();
  let group_id = $("#group_").val();
  let params =
    "lid=" +
    lid +
    "&current_lo=" +
    current_lo +
    "&current_co=" +
    current_co +
    "&current_branch=" +
    current_branch +
    "&disbursed_date=" +
    disbursed_date +
    "&next_due_date=" +
    next_due_date +
    "&final_due_date=" +
    final_due_date +
    "&income_earned=" +
    income_earned +
    "&loan_amount=" +
    loan_amount +
    "&disbursed_amount=" +
    disbursed_amount +
    "&loan_prod=" +
    loan_prod +
    "&group_id=" +
    group_id;

  dbaction("/action/loan/update", params, function (feed) {
    feedback("DEFAULT", "TOAST", ".feedback_", feed, "3");
  });
}
function update_agent(cid) {
  let agent_id = $("#agent_id").val();
  let params = "agent_id=" + agent_id + "&cid=" + cid;

  dbaction("/action/customer/change-agent", params, function (feed) {
    feedback("DEFAULT", "TOAST", ".feedback_", feed, "3");
  });
}

function modify_loan(loan_id) {
  modal_view("/widgets/loans/modify", "loan_id=" + loan_id, "Modify Loan");
}
function move_loan_to_current(loan_id) {
  Swal.fire({
    title: "Proceed?",
    text: "Are you sure you want to move this loan to current for editing? All the associated payments will be moved too.",
    showCancelButton: true,
    confirmButtonText: "Yes",
    cancelButtonText: "No",
  }).then(function (result) {
    if (result.isConfirmed) {
      var params = "table=o_loans&where=uid=" + loan_id;
      dbaction("/action/system/move-from-archive.php", params, function (feed) {
        feedback("DEFAULT", "TOAST", ".feedback_", feed, "30");
      });
    }
  });
}

function change_loan_status(loan_id, status, status_name) {
  loan_action(loan_id, status, "Mark this loan as " + status_name);
}

function loan_repayment_list(loan_id) {
  let params = "loan_id=" + loan_id;
  load_std("/jresources/loans/loan_repayments", "#repayments_", params);
}

function loan_calc() {
  let prod = $("#prod_calc").val();
  let amount = $("#calc_amount").val();
  if (parseInt(prod) > 0 && parseFloat(amount) > 0) {
    let params = "prod=" + prod + "&amount=" + amount;
    load_std("/jresources/loans/loan_calc", "#calc_", params);
  } else {
    $("#calc_").html("<i>Select Product and Enter Amount ...</i>");
  }
}

function loan_collateral_action(loan_id, collateral_id, action) {
  let params =
    "loan_id=" +
    loan_id +
    "&collateral_id=" +
    collateral_id +
    "&action=" +
    action;
  dbaction("/action/loan/collateral", params, function (feed) {
    feedback("DEFAULT", "TOAST", ".feedback_", feed, "3");
  });
}

function upload_list(customer, action) {
  let params = "customer=" + customer + "&action=" + action;
  dbaction("/jresources/files/list", params, function (feed) {
    console.log(JSON.stringify(feed));
    $("#uploads_").html(feed);
  });
}

function delete_file(fileId) {
  Swal.fire({
    title: "Proceed?",
    text: "Are you sure you want to delete this file?",
    showCancelButton: true,
    confirmButtonText: "Yes",
    cancelButtonText: "No",
  }).then(function (result) {
    if (result.isConfirmed) {
      var params = "file_id=" + fileId;
      dbaction("/action/files/delete", params, function (feed) {
        feedback("DEFAULT", "TOAST", ".feedback_", feed, "4");
      });
    }
  });
}

function view_file(file_id, mode) {
  modal_view(
    "/jresources/files/view_one",
    "file_id=" + file_id + "&mode=" + mode,
    "File Details"
  );
}

function view_collateral(col_id) {
  modal_view(
    "/jresources/collateral/view_one",
    "col_id=" + col_id,
    "Collateral Details"
  );
}

function view_referee(ref_id) {
  modal_view(
    "/jresources/referees/view_one",
    "ref_id=" + ref_id,
    "Referee Details"
  );
}

function view_guarantor(guarantorId) {
  modal_view(
    "/jresources/guarantors/view_one",
    "guarantor_id=" + guarantorId,
    "Guarantor Details"
  );
}

function delete_referee(refid) {
  Swal.fire({
    title: "Proceed?",
    text: "Are you sure you want to delete this referee?",
    showCancelButton: true,
    confirmButtonText: "Yes",
    cancelButtonText: "No",
  }).then(function (result) {
    if (result.isConfirmed) {
      var params = "ref_id=" + refid;
      dbaction("/action/referees/delete", params, function (feed) {
        feedback("DEFAULT", "TOAST", ".feedback_", feed, "4");
      });
    }
  });
}

function delete_guarantor(guarantorId) {
  Swal.fire({
    title: "Proceed?",
    text: "Are you sure you want to delete this guarantor?",
    showCancelButton: true,
    confirmButtonText: "Yes",
    cancelButtonText: "No",
  }).then(function (result) {
    if (result.isConfirmed) {
      var params = "guarantor_id=" + guarantorId;
      dbaction("/action/guarantors/delete", params, function (feed) {
        feedback("DEFAULT", "TOAST", ".feedback_", feed, "4");
      });
    }
  });
}

function delete_collateral(refid) {
  Swal.fire({
    title: "Proceed?",
    text: "Are you sure you want to delete this collateral?",
    showCancelButton: true,
    confirmButtonText: "Yes",
    cancelButtonText: "No",
  }).then(function (result) {
    if (result.isConfirmed) {
      var params = "ref_id=" + refid;
      dbaction("/action/collateral/delete", params, function (feed) {
        feedback("DEFAULT", "TOAST", ".feedback_", feed, "4");
      });
    }
  });
}

function other_list(tbl, record, action) {
  let params = "action=" + action + "&tbl=" + tbl + "&record=" + record;
  dbaction("/jresources/customer_sec/list", params, function (feed) {
    console.log(JSON.stringify(feed));
    $("#other_").html(feed);
  });
}

function save_other(tbl, record) {
  let fds = $("#other_fields").val();
  let fds_arr = fds.split(",");
  let params = "tbl=" + tbl + "&fds=" + fds + "&record=" + record;
  for (let i = 0; i < fds_arr.length; ++i) {
    let inpu = encodeURIComponent($("#in_" + fds_arr[i])
      .val()
      ?.trim());
    params += "&in_" + fds_arr[i] + "=" + inpu;
  }
  let endpoint = "create";

  dbaction("/action/customer_sec/" + endpoint, params, function (feed) {
    console.log(JSON.stringify(feed));
    feedback("DEFAULT", "TOAST", ".feedback_", feed, "4");
  });
}

function delete_other(other_id) {
  Swal.fire({
    title: "Proceed?",
    text: "Are you sure you want to delete this entry?",
    showCancelButton: true,
    confirmButtonText: "Yes",
    cancelButtonText: "No",
  }).then(function (result) {
    if (result.isConfirmed) {
      var params = "other_id=" + other_id;
      dbaction("/action/customer_sec/delete", params, function (feed) {
        feedback("DEFAULT", "TOAST", ".feedback_", feed, "4");
      });
    }
  });
}

/////////////-----------------------------Loans
function search_cust() {
  let customer_search = $("#customer_search").val();
  $("#customer_id_").val("");
  if (customer_search) {
    let params = "key=" + customer_search;
    dbaction("/jresources/loan_customer_find", params, function (result) {
      $("#customer_results").slideDown("fast");
      $("#customer_results").html(result);
    });
  } else {
    $("#customer_results").fadeOut("fast");
  }
}

function select_client(name, uid) {
  $("#customer_search").val(name);
  $("#customer_id_").val(uid);
  $("#customer_results").fadeOut("fast");
}

function clear_search() {
  $("#customer_search").val("");
  $("#customer_id_").val("");

  $("#customer_results").fadeOut("fast");
}

function create_loan() {
  let customer_id = $("#customer_id_").val();
  let product_id = $("#product").val();
  let loan_amount = $("#amount").val();
  let loan_type = $("#loan_type").val();
  let application_mode = "MANUAL";
  let params =
    "customer_id=" +
    customer_id +
    "&product_id=" +
    product_id +
    "&loan_amount=" +
    loan_amount +
    "&application_mode=" +
    application_mode +
    "&loan_type=" +
    loan_type;
  dbaction("/action/loan/loan_create", params, function (feed) {
    feedback("DEFAULT", "TOAST", ".feedback_", feed, "4");
  });
}

function loan_list() {
  let where = $("#_where_").val();
  if (!where) {
    where = "uid > 0";
  }
  let offset = $("#_offset_").val();
  if (!offset) {
    offset = 0;
  }
  let rpp = $("#_rpp_").val();
  if (!rpp) {
    rpp = 10;
  }
  let page_no = $("#_page_no_").val();
  if (!page_no) {
    page_no = 1;
  }
  let orderby = $("#_orderby_").val();
  if (!orderby) {
    orderby = "uid";
  }
  let dir = $("#_dir_").val();
  if (!dir) {
    dir = "desc";
  }
  let search = $("#search_").val();
  if (!search) {
    search = "";
  }

  let approvals = $("#_approvals_").val();
  if (!approvals) {
    approvals = "";
  }

  let params =
    "where_=" +
    where +
    "&offset=" +
    offset +
    "&rpp=" +
    rpp +
    "&page_no=" +
    page_no +
    "&orderby=" +
    orderby +
    "&dir=" +
    dir +
    "&search_=" +
    search +
    "&need_approval=" +
    approvals;
  dbaction("/jresources/loans/loan_list", params, function (feed) {
    console.log(params);
    $("#loan_list").html(feed);
    setTimeout(function () {
      pager_refactor();
    }, 500);
  });
}
function account_info(cid, lid = 0) {
  /////---------Loans
  let params = "cid=" + cid;
  dbaction("/jresources/customers/account_info", params, function (feed) {
    $("#account_info").html(feed);
  });

  /////-----------Payments
  let params2 = "loan_id=" + lid + "&customer_id=" + cid;

  dbaction("/jresources/loans/customer_repayments", params2, function (feed) {
    $("#payments_info").html(feed);
  });
}

function customerAccountStatement(cid) {
  let params = "cid=" + cid;
  dbaction(
    "/jresources/customers/customer_account_statement",
    params,
    function (feed) {
      $("#customer_account_statement").html(feed);
    }
  );
}

function load_archive_loans(cid, lid = 0) {
  /////---------Loans
  if (lid === 0) {
    let params = "cid=" + cid;
    dbaction(
      "/jresources/customers/account_info-archives",
      params,
      function (feed) {
        $("#account_info_archives").html(feed);
      }
    );
  }

  /////-----------Payments
  let params2 = "loan_id=" + lid + "&customer_id=" + cid;

  dbaction(
    "/jresources/loans/customer_repayments-archives",
    params2,
    function (feed) {
      $("#payments_info_archives").html(feed);
      scrollToDiv("payments_info_archives", 1000);
    }
  );
}

function falling_due_list() {
  let where = $("#_where_").val();
  if (!where) {
    where = "uid > 0";
  }
  let offset = $("#_offset_").val();
  if (!offset) {
    offset = 0;
  }
  let rpp = $("#_rpp_").val();
  if (!rpp) {
    rpp = 10;
  }
  let page_no = $("#_page_no_").val();
  if (!page_no) {
    page_no = 1;
  }
  let orderby = $("#_orderby_").val();
  if (!orderby) {
    orderby = "uid";
  }
  let dir = $("#_dir_").val();
  if (!dir) {
    dir = "desc";
  }
  let search = $("#search_").val();
  if (!search) {
    search = "";
  }

  let sort_opt = $("#_sort_").val();
  if (!sort_opt) {
    sort_opt = "all";
  }

  let params =
    "where_=" +
    where +
    "&offset=" +
    offset +
    "&rpp=" +
    rpp +
    "&page_no=" +
    page_no +
    "&orderby=" +
    orderby +
    "&dir=" +
    dir +
    "&search_=" +
    search +
    "&sort_option=" +
    sort_opt;
  dbaction("/jresources/loans/falling_due_list", params, function (feed) {
    console.log(params);
    $("#falling_due_list").html(feed);
    setTimeout(function () {
      pager_refactor();
    }, 0);
  });
}

function scrollToDiv(div_id, duration) {
  $("html, body").animate(
    {
      scrollTop: $("#" + div_id).offset().top,
    },
    duration,
    "swing"
  );
}

function defaulters_list() {
  let where = $("#_where_").val();

  if (!where) {
    where = "uid > 0";
  }
  let offset = $("#_offset_").val();
  if (!offset) {
    offset = 0;
  }
  let rpp = $("#_rpp_").val();
  if (!rpp) {
    rpp = 10;
  }
  let page_no = $("#_page_no_").val();
  if (!page_no) {
    page_no = 1;
  }
  let orderby = $("#_orderby_").val();
  if (!orderby) {
    orderby = "uid";
  }
  let dir = $("#_dir_").val();
  if (!dir) {
    dir = "desc";
  }
  let search = $("#search_").val();
  if (!search) {
    search = "";
  }

  let sort_opt = $("#_sort_").val();
  if (!sort_opt) {
    sort_opt = "default_sort";
  }

  let params =
    "where_=" +
    where +
    "&offset=" +
    offset +
    "&rpp=" +
    rpp +
    "&page_no=" +
    page_no +
    "&orderby=" +
    orderby +
    "&dir=" +
    dir +
    "&search_=" +
    search +
    "&sort_option=" +
    sort_opt;
  dbaction("/jresources/loans/defaulters_list", params, function (feed) {
    console.log(params);
    $("#defaulters_list").html(feed);
    setTimeout(function () {
      pager_refactor();
    }, 0);
  });
}

function installments_list() {
  let where = $("_where_").val();
  if (!where) {
    where = "uid > 0";
  }
  let offset = $("#_offset_").val();
  if (!offset) {
    offset = 0;
  }
  let rpp = $("#_rpp_").val();
  if (!rpp) {
    rpp = 10;
  }
  let page_no = $("#_page_no_").val();
  if (!page_no) {
    page_no = 1;
  }
  let orderby = $("#_orderby_").val();
  if (!orderby) {
    orderby = "uid";
  }
  let dir = $("#_dir_").val();
  if (!dir) {
    dir = "desc";
  }
  let search = $("#search_").val();
  if (!search) {
    search = "";
  }

  let sort_opt = $("#_sort_").val();
  if (!sort_opt) {
    sort_opt = "all";
  }

  let params =
    "where_=" +
    where +
    "&offset=" +
    offset +
    "&rpp=" +
    rpp +
    "&page_no=" +
    page_no +
    "&orderby=" +
    orderby +
    "&dir=" +
    dir +
    "&search_=" +
    search +
    "&sort_option=" +
    sort_opt;

  console.log(params);
  dbaction("/jresources/loans/installments_list", params, function (feed) {
    $("#installments_list").html(feed);
    setTimeout(function () {
      pager_refactor();
    }, 0);
  });
}
function falling_filters() {
  $("#_sort_").val("all");
  let sel_branch = parseInt($("#sel_branch").val());
  let start_d = $("#start_d").val();
  let end_d = $("#end_d").val();

  let wher = "uid > 0";
  if (start_d && end_d) {
    $("#period_").val(start_d + " to " + end_d);
    wher +=
      " AND final_due_date BETWEEN '" + start_d + "' AND '" + end_d + "' ";
  }
  $("#_dir_").val("desc");

  if (sel_branch > 0) {
    wher += " AND current_branch=" + sel_branch;
  }

  if (wher) {
    $("#_where_").val(wher);
    $("#_offset_").val(0);

    falling_due_list();
  } else {
    $("#_where_").val(" status > -1");
    $("#_offset_").val(0);
  }
}

function loan_filters() {
  let loan_order = $("#loan_order").val();
  let sel_product = parseInt($("#sel_product").val());
  let sel_branch = parseInt($("#sel_branch").val());
  let sel_stage = parseInt($("#sel_stage").val());
  let sel_status = parseInt($("#sel_status").val());

  let start_d = $("#start_d").val();
  let end_d = $("#end_d").val();

  let wher = " uid >-1";
  if (start_d && end_d) {
    $("#period_").val(start_d + " to " + end_d);
    wher += " AND given_date BETWEEN '" + start_d + "' AND '" + end_d + "' ";
  }
  $("#_dir_").val(loan_order);
  if (sel_product > 0) {
    wher += " AND product_id =" + sel_product;
  }
  if (sel_branch > 0) {
    wher += " AND current_branch=" + sel_branch;
  }
  if (sel_stage > 0) {
    wher += " AND loan_stage=" + sel_stage;
  }
  if (sel_status > 0) {
    wher += " AND status=" + sel_status;
  }

  console.log("filt " + wher);

  if (wher) {
    $("#_where_").val(wher);
    $("#_offset_").val(0);

    pager_home();
  } else {
    $("#_where_").val(" status > -1");
    $("#_offset_").val(0);
  }
}

function loan_addons(loan_id) {
  let params = "loan_id=" + loan_id;
  load_std("/jresources/loans/loan_addons", "#loan_addons", params);
}

function loan_stages(loan_id) {
  let params = "loan_id=" + loan_id;
  load_std("/jresources/loans/loan_stages", "#loan_stages", params);
}

function loan_addon_action(action, loan_id, addon_id) {
  let params =
    "action=" + action + "&loan_id=" + loan_id + "&addon_id=" + addon_id;
  dbaction("/action/loan/loan_addon", params, function (feed) {
    feedback("DEFAULT", "TOAST", ".feedback_", feed, "3");
  });
}

function save_edited_addon(uid) {
  let new_amount = $("#add_amount").val();
  let params = "uid=" + uid + "&amount=" + new_amount;
  dbaction("/action/loan/update_added_addon", params, function (feed) {
    feedback("DEFAULT", "TOAST", ".feedback_", feed, "3");
  });
}

function edit_applied_addon(uid) {
  let params = "uid=" + uid;
  modal_view("/forms/applied_addon_edit", params, "Edit Addon Amount");
}

function loan_deductions(loan_id) {
  let params = "loan_id=" + loan_id;
  load_std("/jresources/loans/loan_deductions", "#loan_deductions", params);
}

function save_edited_deduction(uid) {
  toggleBtnState("edit-deduction-btn", "Saving...");
  let new_amount = $("#deduct_amount").val();
  let params = "uid=" + uid + "&amount=" + new_amount;
  dbaction("/action/loan/update_added_deduction", params, function (feed) {
    feedback("DEFAULT", "TOAST", ".feedback_", feed, "3");
    toggleBtnState("edit-deduction-btn", "Save");
  });
}

function edit_applied_deduction(uid) {
  let params = "uid=" + uid;
  modal_view("/forms/applied_deduction_edit", params, "Edit Deduction Amount");
}

function loan_deduction_action(action, loan_id, deduction_id) {
  let params =
    "action=" +
    action +
    "&loan_id=" +
    loan_id +
    "&deduction_id=" +
    deduction_id;
  dbaction("/action/loan/loan_deduction", params, function (feed) {
    feedback("DEFAULT", "TOAST", ".feedback_", feed, "3");
  });
}

function loan_action(loan_id, act, title) {
  Swal.fire({
    title: "Proceed?",
    text: "Are you sure you want to " + title + "?",
    showCancelButton: true,
    confirmButtonText: "Yes",
    cancelButtonText: "No",
    icon: "question",
  }).then(function (result) {
    if (result.isConfirmed) {
      var params = "loan_id=" + loan_id + "&action=" + act;
      dbaction("/action/loan/action", params, function (feed) {
        feedback("DEFAULT", "TOAST", ".feedback_", feed, "4");
      });
    }
  });
}

function stk_push(phone, amount) {
  Swal.fire({
    title: "Proceed?",
    text: "Are you sure you want to send a prompt to " + phone + "?",
    showCancelButton: true,
    confirmButtonText: "Yes",
    cancelButtonText: "No",
  }).then(function (result) {
    if (result.isConfirmed) {
      let amount = $("#stk_amount").val();
      var params = "phone=" + phone + "&amount=" + amount;
      dbaction("/action/loan/stk_push", params, function (feed) {
        feedback("DEFAULT", "TOAST", ".feedback_", feed, "3");
      });
    }
  });
}

function session_variable(action, key_, value_, reload) {
  let params =
    "action=" +
    action +
    "&key_=" +
    key_ +
    "&value_=" +
    value_ +
    "&reload=" +
    reload;
  dbaction("/action/system/session_variable_set", params, function (feed) {
    feedback("DEFAULT", "TOAST", ".feedback_", feed, "2");
  });
}

//dues filter
function dues_filter(from_d, to_d) {
  $("#start_d").val(from_d);
  $("#end_d").val(to_d);

  $("#_sort_").val("all");
  $("#_dir_").val("desc");
  falling_filters();
}

//defaulters filter
function defaulters_filter(where) {
  if (where == "all") {
    reload();
    return;
  }
  $("#_sort_").val(where);
  $("#_dir_").val("desc");
  pager_home();
}
function defaulter_age() {
  let age = parseInt($("#loan_age").val());

  if (age > 0) {
    var today = new Date();
    var numberOfDaysToAdd = age;
    var result = today.setDate(today.getDate() - numberOfDaysToAdd);
    let final_date = new Date(result);
    let formatted_date = formatDate(final_date);

    $("#_where_").val("final_due_date <= '" + formatted_date + "'");
    pager_home();
  }
}

function defaulters_filter2() {
  let wher = "";
  const age = parseInt($("#loan_age").val());
  const sel_agent = parseInt($("#sel_agent").val());
  const sel_branch = parseInt($("#sel_branch").val());

  if (sel_agent > 0) {
    wher += `(current_agent = ${sel_agent} OR current_lo = ${sel_agent} OR current_co = ${sel_agent}) `;
  }

  if (sel_branch > 0) {
    if (wher) {
      wher += " AND ";
    }
    wher += `current_branch = ${sel_branch} `;
  }

  if (age > 0) {
    var today = new Date();
    var numberOfDaysToAdd = age;
    var result = today.setDate(today.getDate() - numberOfDaysToAdd);
    let final_date = new Date(result);
    let formatted_date = formatDate(final_date);

    if (wher) {
      wher += " AND ";
    }
    wher += `final_due_date <= '${formatted_date}'`;
  }

  console.log(wher);
  if (wher) {
    $("#_where_").val(wher);
  } else {
    $("#_where_").val("uid > 0");
  }

  console.log("filt " + wher);
  pager_home();
}

function installments_filter(where) {
  $("#_sort_").val(where);
  $("#_dir_").val("desc");
  pager_home();
}

///////////-----------------------------End of Loans

//////////-----------------------------Repayments
function payment_list() {
  let where = $("#_where_").val();
  if (!where) {
    where = "uid > 0";
  }
  let offset = $("#_offset_").val();
  if (!offset) {
    offset = 0;
  }
  let rpp = $("#_rpp_").val();
  if (!rpp) {
    rpp = 10;
  }

  let page_no = $("#_page_no_").val();
  if (!page_no) {
    page_no = 1;
  }

  let orderby = $("#_orderby_").val();
  if (!orderby) {
    orderby = "uid";
  }
  let dir = $("#_dir_").val();
  if (!dir) {
    dir = "desc";
  }
  const payment_splitted = sessionStorage.getItem("payment_splitted") || "";
  let search = $("#search_").val() || payment_splitted;

  if (payment_splitted) {
    $("#search_").val("payment_splitted");
  }

  if (!search) {
    search = "";
  }

  let sort_opt = $("#_sort_").val();
  if (!sort_opt) {
    sort_opt = "default_sort";
  }

  let params =
    "where_=" +
    where +
    "&offset=" +
    offset +
    "&rpp=" +
    rpp +
    "&page_no=" +
    page_no +
    "&orderby=" +
    orderby +
    "&dir=" +
    dir +
    "&search_=" +
    search +
    "&sort_option=" +
    sort_opt;
  dbaction("/jresources/repayments/payment_list", params, function (feed) {
    console.log(params);
    $("#payment_list").html(feed);
    setTimeout(function () {
      pager_refactor();
      if (payment_splitted) {
        sessionStorage.removeItem("payment_splitted");
      }
    }, 500);
  });
}

function payment_save() {
  let pid = parseInt($("#pid").val());
  let endpoint = "create";
  if (pid > 0) {
    endpoint = "update";
  }
  let payment_method = $("#payment_method").val();
  let mobile_number = $("#mobile_number").val();
  let amount = $("#amount").val();
  let transaction_code = $("#payment_code").val();
  let payment_for = $("#payment_for").val();
  let loan_id = $("#loan_code").val();
  const init_loan_code = $("#init_loan_code").val();
  let payment_date = $("#date_made").val();
  let comments = $("#comments").val();
  let record_method = "MANUAL";
  let group_id = $("#group_id").val();
  let status = $("#status_").val();
  let params =
    "payment_method=" +
    payment_method +
    "&mobile_number=" +
    mobile_number +
    "&amount=" +
    amount +
    "&transaction_code=" +
    transaction_code +
    "&loan_id=" +
    loan_id +
    "&comments=" +
    comments +
    "&payment_date=" +
    payment_date +
    "&record_method=" +
    record_method +
    "&pid=" +
    pid +
    "&payment_for=" +
    payment_for +
    "&group_id=" +
    group_id +
    "&status=" +
    status +
    "&init_loan_code=" +
    init_loan_code;
  dbaction("/action/payments/" + endpoint, params, function (feed) {
    console.log(JSON.stringify(feed));
    feedback("DEFAULT", "TOAST", ".feedback_", feed, "4");
  });
}
function select_loan(pid) {
  modal_view("/forms/loan-select.php", "pid=" + pid, "Find Payment");
}
function payment_allocate() {
  let endpoint = "allocate";
  let amount = $("#amount").val();
  let transaction_code = $("#payment_code").val();
  let loan_id = $("#loan_code").val();
  let mobile_number = $("#mobile_number").val();
  let payment_date = $("#date_made").val();
  let params =
    "amount=" +
    amount +
    "&transaction_code=" +
    transaction_code +
    "&loan_id=" +
    loan_id +
    "&payment_date=" +
    payment_date +
    "&mobile_number=" +
    mobile_number;
  dbaction("/action/payments/" + endpoint, params, function (feed) {
    console.log(JSON.stringify(feed));
    feedback("DEFAULT", "TOAST", ".feedback_", feed, "4");
  });
}

//payment filters
function repayment_filters() {
  let loan_order = $("#repayment_order").val();
  let sel_branch = parseInt($("#sel_branch").val());
  let repayment_method = parseInt($("#repayment_method").val());
  let start_d = $("#start_d").val();
  let end_d = $("#end_d").val();

  let wher = "uid > 0";
  if (start_d && end_d) {
    $("#period_").val(start_d + " to " + end_d);
    wher += " AND payment_date BETWEEN '" + start_d + "' AND '" + end_d + "' ";
  }
  $("#_dir_").val(loan_order);

  if (sel_branch > 0) {
    wher += " AND branch_id=" + sel_branch;
  }

  if (repayment_method > 0) {
    wher += " AND payment_method=" + repayment_method;
  }

  console.log("filt " + wher);

  if (wher) {
    $("#_where_").val(wher);
    $("#_offset_").val(0);

    pager_home();
  } else {
    $("#_where_").val(" status > -1");
    $("#_offset_").val(0);
  }
}

/////////------------------------------End of repayments

function formready(formid) {
  formhandler("#" + formid);
}

function formhandler(formid) {
  var options = {
    beforeSend: function () {
      toggleBtnState("submitBtn", "Submitting...");

      $("#progress").show();
      //clear everything
      $("#bar").width("0%");
      $("#message").html("");
      $("#percent").html("0%");
    },
    uploadProgress: function (event, position, total, percentComplete = 50) {
      $("#bar").width(percentComplete + "%");
      $("#percent").html(percentComplete + "%");
    },
    success: function () {
      $("#bar").width("100%");
      $("#percent").html("100%");
    },
    complete: function (response) {
      ///if success, refresh form
      var res = response.responseText;
      if (res.includes("ucces")) {
        toggleBtnState("submitBtn", "Completed");
        $("#message").html("<font color='green'>" + res + "</font>");
        $(formid)[0].reset();
      } else {
        toggleBtnState("submitBtn", "Submit");
        $("#message").html("<font color='red'>" + res + "</font>");
      }
    },
    error: function () {
      toggleBtnState("submitBtn", "Submit");
      $("#message").html(
        "<font color='red'> ERROR: unable to upload file</font>"
      );
    },
  };

  $(formid).ajaxForm(options);
}

function save_loan_product() {
  const product_id = parseInt($("#product_id").val());
  const name = $("#product_name").val();
  const description = $("#description").val();
  const period = $("#period").val();
  const period_units = $("#period_units").val();
  const min_amount = $("#min_amount").val();
  const max_amount = $("#max_amount").val();
  const pay_frequency = $("#pay_frequency").val();
  const percent_breakdown = $("#payment_breakdown").val();
  const params =
    "product_id=" +
    product_id +
    "&name=" +
    name +
    "&description=" +
    description +
    "&period=" +
    period +
    "&period_units=" +
    period_units +
    "&min_amount=" +
    min_amount +
    "&max_amount=" +
    max_amount +
    "&pay_frequency=" +
    pay_frequency +
    "&percent_breakdown=" +
    percent_breakdown;
  let endpoint = "loan_product_save";
  if (product_id > 0) {
    endpoint = "loan_product_update";
  }

  dbaction("/action/loan/" + endpoint, params, function (feed) {
    console.log(JSON.stringify(feed));
    feedback("DEFAULT", "TOAST", ".feedback_", feed, "4");
  });
}
function product_reminders(product_id) {
  let params = "product_id=" + product_id;
  load_std("/jresources/loans/product-reminders", "#preminders_", params);
}

function loan_product_delete(uid) {
  Swal.fire({
    title: "Proceed?",
    text: "Are you sure you want to delete?",
    showCancelButton: true,
    confirmButtonText: "Yes",
    cancelButtonText: "No",
  }).then(function (result) {
    if (result.isConfirmed) {
      const params = "product_id=" + uid;
      dbaction("/action/loan/loan_product_delete", params, function (feed) {
        console.log(JSON.stringify(feed));
        feedback("DEFAULT", "TOAST", ".feedback_", feed, "4");
      });
    }
  });
}

function addon_save(aid) {
  let name = $("#addon_name").val();
  let description = $("#addon_description").val();
  let amount = $("#addon_amount").val();
  let amount_type = $("#amount_type").val();
  let loan_stage = $("#loan_stage").val();
  let automatic = $("#automatic").val();
  let addon_on = $("#addon_on").val();
  let starting_day = $("#starting_day").val();
  let ending_day = $("#ending_day").val();
  let apply_frequency = $("#apply_frequency").val();
  let notify_user = $("#notify_user").val();
  let applicable_loan = $("#applicable_loan").val();
  let paid_upfront = $("#paid_upfront").val();
  let deducted_upfront = $("#deducted_upfront").val();

  let params =
    "name=" +
    name +
    "&description=" +
    description +
    "&amount=" +
    amount +
    "&amount_type=" +
    amount_type +
    "&loan_stage=" +
    loan_stage +
    "&automatic=" +
    automatic +
    "&addon_on=" +
    addon_on +
    "&starting_day=" +
    starting_day +
    "&ending_day=" +
    ending_day +
    "&apply_frequency=" +
    apply_frequency +
    "&notify_user=" +
    notify_user +
    "&applicable_loan=" +
    applicable_loan +
    "&aid=" +
    aid +
    "&paid_upfront=" +
    paid_upfront +
    "&deducted_upfront=" +
    deducted_upfront;

  if (parseInt(aid) > 0) {
    dbaction("/action/addons/addon_update", params, function (feed) {
      console.log(JSON.stringify(feed));
      feedback("DEFAULT", "TOAST", ".feedback_", feed, "4");
    });
  } else {
    dbaction("/action/addons/addon_save", params, function (feed) {
      console.log(JSON.stringify(feed));
      feedback("DEFAULT", "TOAST", ".feedback_", feed, "4");
    });
  }
}

function deduction_save() {
  let name = $("#deduction_name").val();
  let description = $("#deduction_description").val();
  let amount = $("#deduction_amount").val();
  let amount_type = $("#amount_type").val();
  let loan_stage = $("#loan_stage").val();
  let automatic = $("#automatic").val();
  let params =
    "name=" +
    name +
    "&description=" +
    description +
    "&amount=" +
    amount +
    "&amount_type=" +
    amount_type +
    "&loan_stage=" +
    loan_stage +
    "&automatic=" +
    automatic;
  dbaction("/action/deduction_save", params, function (feed) {
    console.log(JSON.stringify(feed));
    feedback("DEFAULT", "TOAST", ".feedback_", feed, "4");
  });
}

function product_addon_save(pid, aid, action) {
  let params = "addon_id=" + aid + "&product_id=" + pid + "&action=" + action;
  dbaction("/action/product_addon_save", params, function (feed) {
    let jso = JSON.parse(feed);
    let result = jso.result_;
    let final_ = jso.final_;
    console.log(result);
    if (result === 1) {
      let button = "";
      feedback("SUCCESS", "TOAST", ".feedback_", "Success", "2");
      if (final_ === 1) {
        button =
          '<a onclick="product_addon_save(' +
          pid +
          ", " +
          aid +
          ', \'REMOVE\')" title="Click to Remove" class="text-success pointer"><i class="fa fa-check"></i> Added </a>';
      } else {
        button =
          '<a onclick="product_addon_save(' +
          pid +
          ", " +
          aid +
          ', \'ADD\')" title="Click to Add" class="text-primary pointer"><i class="fa fa-times-circle"></i> Not Added </a>';
      }
      $("#a" + aid + pid).html(button);
      console.log(aid + pid);
    } else {
      feedback(
        "ERROR",
        "TOAST",
        ".feedback_",
        '<div class="alert danger"> Error Occurred </span>',
        "2"
      );
    }
    // feedback("DEFAULT", "TOAST", ".feedback_", feed, "2");
  });
}

function product_deduction_save(pid, did, action) {
  let params =
    "deduction_id=" + did + "&product_id=" + pid + "&action=" + action;
  dbaction("/action/product_deduction_save", params, function (feed) {
    console.log(feed);
    let jso = JSON.parse(feed);
    let result = jso.result_;
    let final_ = jso.final_;
    if (result === 1) {
      let button = "";
      feedback("SUCCESS", "TOAST", ".feedback_", "Success", "2");
      if (final_ === 1) {
        button =
          '<a onclick="product_deduction_save(' +
          pid +
          ", " +
          did +
          ', \'REMOVE\')" title="Click to Remove" class="text-success pointer"><i class="fa fa-check"></i> Added </a>';
      } else {
        button =
          '<a onclick="product_deduction_save(' +
          pid +
          ", " +
          did +
          ', \'ADD\')" title="Click to Add" class="text-primary pointer"><i class="fa fa-times-circle"></i> Not Added </a>';
      }
      $("#d" + did + pid).html(button);
    } else {
      feedback(
        "ERROR",
        "TOAST",
        ".feedback_",
        '<div class="alert danger"> Error Occurred </span>',
        "2"
      );
    }
    // feedback("DEFAULT", "TOAST", ".feedback_", feed, "2");
  });
}

function product_stage_action(pid, did, action) {
  modal_view(
    "/jresources/loans/product_stage_add",
    "pid=" + pid,
    "Add Approval Stage"
  );
}

function product_stage_save(pid, did, action) {
  let params = "stage_id=" + did + "&product_id=" + pid + "&action=" + action;
  dbaction("/action/loan/product_stage_save", params, function (feed) {
    console.log(feed);
    let jso = JSON.parse(feed);
    let result = jso.result_;
    let final_ = jso.final_;
    if (result === 1) {
      let button = "";
      feedback("SUCCESS", "TOAST", ".feedback_", "Success", "2");
      if (final_ === 1) {
        button =
          '<a onclick="product_stage_save(' +
          pid +
          ", " +
          did +
          ', \'REMOVE\')" title="Click to Remove" class="text-success pointer"><i class="fa fa-check"></i> Added </a>';
      } else {
        button =
          '<a onclick="product_stage_save(' +
          pid +
          ", " +
          did +
          ', \'ADD\')" title="Click to Add" class="text-primary pointer"><i class="fa fa-times-circle"></i> Not Added </a>';
      }
      $("#s" + did + pid).html(button);
    } else {
      feedback(
        "ERROR",
        "TOAST",
        ".feedback_",
        '<div class="alert danger"> Error Occurred </span>',
        "2"
      );
    }
    // feedback("DEFAULT", "TOAST", ".feedback_", feed, "2");
  });
}

///////--------------------------Loan Stage
function stage_save() {
  let name = $("#deduction_name").val();
  let description = $("#deduction_description").val();
  let stage_order = $("#stage_order").val();
  let can_addon = $("#can_addon").val();
  let can_deduct = $("#can_deduct").val();
  let params =
    "name=" +
    name +
    "&description=" +
    description +
    "&stage_order=" +
    stage_order +
    "&can_addon=" +
    can_addon +
    "&can_deduct=" +
    can_deduct;
  dbaction("/action/loan/loan_stage_save", params, function (feed) {
    console.log(JSON.stringify(feed));
    feedback("DEFAULT", "TOAST", ".feedback_", feed, "4");
  });
}

function move_stage(loan_id, stage_id) {
  ///----Prevent a double click
  if (localStorage.getItem("sl_clicked") === "1") {
    feedback("ERROR", "TOAST", ".feedback_", "Please refresh the page", "3");
  } else {
    localStorage.setItem("sl_clicked", "1");
    let comments = $("#comments_").val();
    let params = "loan_id=" + loan_id + "&comment=" + comments + "&stage_id=" + stage_id;
    dbaction("/action/loan/move_stage", params, function (feed) {
      console.log(JSON.stringify(feed));
      feedback("DEFAULT", "TOAST", ".feedback_", feed, "4");
    });
  }
}

function approve_disburse(loan_id, v2 = "") {
  if (localStorage.getItem("sl_clicked") === "1") {
    feedback("ERROR", "TOAST", ".feedback_", "Please refresh the page", "3");
  } else {
    localStorage.setItem("sl_clicked", "1");
    let comments = $("#comments_").val();
    let params = "loan_id=" + loan_id + "&comment=" + comments;
    dbaction("/action/loan/approve_disburse" + v2, params, function (feed) {
      console.log(JSON.stringify(feed));
      feedback("DEFAULT", "TOAST", ".feedback_", feed, "4");
    });
  }
}
function final_stage_save(pid) {
  let stage_id = $("#final_stage").val();
  let params = "pid=" + pid + "&stage_id=" + stage_id;
  dbaction("/action/loan/final_stage", params, function (feed) {
    console.log(JSON.stringify(feed));
    feedback("DEFAULT", "TOAST", ".feedback_", feed, "4");
  });
}

///////--------------------------End Loan Stages
///////////----------------------Interactions

function interactions_popup(cid) {
  modal_view(
    "/jresources/interactions/minimal-interactions",
    "cid=" + cid,
    "Customer Interactions"
  );
}

function interactions_load(cid, box, sortOption = "default_sort") {
  $("select")?.select2(); // reinitialize select2
  let where = $("#_where_").val();
  if (!where) {
    where = "uid > 0";
  }
  let offset = $("#_offset_").val();
  if (!offset) {
    offset = 0;
  }
  let rpp = $("#_rpp_").val();
  if (!rpp) {
    rpp = 10;
  }

  let page_no = $("#_page_no_").val();
  if (!page_no) {
    page_no = 1;
  }

  let orderby = $("#_orderby_").val();
  if (!orderby) {
    orderby = "uid";
  }
  let dir = $("#_dir_").val();
  if (!dir) {
    dir = "desc";
  }
  let search = $("#search_").val();
  if (!search) {
    search = "";
  }

  let params =
    "where_=" +
    where +
    "&offset=" +
    offset +
    "&rpp=" +
    rpp +
    "&page_no=" +
    page_no +
    "&orderby=" +
    orderby +
    "&dir=" +
    dir +
    "&search_=" +
    search +
    "&sort_option=" +
    sortOption +
    "&cid=" +
    cid;
  dbaction(
    "/jresources/interactions/minimal-interactions-paginated",
    params,
    function (feed) {
      $(box).html(feed);
      pager2("#pagination");
      setTimeout(function () {
        pager_refactor();
      }, 200);
    }
  );
}

function tag_client(client_id, badge) {
  let params = "client_id=" + client_id + "&badge=" + badge;
  dbaction("/action/interactions/badge", params, function (feed) {
    feedback("DEFAULT", "TOAST", ".feedback_", feed, "2");
  });
}

function getCurrentDate() {
  var currentDate = new Date();
  var year = currentDate.getFullYear();
  var month = ("0" + (currentDate.getMonth() + 1)).slice(-2);
  var day = ("0" + currentDate.getDate()).slice(-2);

  var formattedDate = year + "-" + month + "-" + day;
  return formattedDate;
}

function load_interactions(sortOption = "default_sort") {
  // if(sortOption == 'default_sort' || sortOption == 'duetoday' || sortOption == 'overdue'){
  //     $('#_where_').val('');
  // }

  let where = $("#_where_").val();
  if (!where) {
    where = "uid > 0";
  }
  let offset = $("#_offset_").val();
  if (!offset) {
    offset = 0;
  }
  let rpp = $("#_rpp_").val();
  if (!rpp) {
    rpp = 10;
  }

  let page_no = $("#_page_no_").val();
  if (!page_no) {
    page_no = 1;
  }

  let orderby = $("#_orderby_").val();
  if (!orderby) {
    orderby = "uid";
  }
  let dir = $("#_dir_").val();
  if (!dir) {
    dir = "desc";
  }
  let search = $("#search_").val();
  if (!search) {
    search = "";
  }

  let params =
    "where_=" +
    where +
    "&offset=" +
    offset +
    "&rpp=" +
    rpp +
    "&page_no=" +
    page_no +
    "&orderby=" +
    orderby +
    "&dir=" +
    dir +
    "&search_=" +
    search +
    "&sort_option=" +
    sortOption;
  dbaction(
    "/jresources/interactions/interactions-list",
    params,
    function (feed) {
      console.log(params);
      $("#interactions_").html(feed);
      setTimeout(function () {
        pager_refactor();
      }, 200);
    }
  );
}

function interactions_filter(duetodayFilter = "", overdueFilter = "") {
  const interactionMethod = parseInt($("#interaction_method").val(), 10);
  const interactionOutcome = parseInt($("#interaction_outcome").val(), 10);
  const branch = parseInt($("#sel_branch").val(), 10);

  const dueTodayFilter = duetodayFilter.trim();
  const overDueFilter = overdueFilter.trim();

  const c_start_d = $("#c_start_d").val();
  const c_end_d = $("#c_end_d").val();
  const ni_start_d = $("#ni_start_d").val();
  const ni_end_d = $("#ni_end_d").val();
  const tag = $('#tags').val().trim();
  const agent = parseInt($("#agent_").val());

  const today = getCurrentDate();

  let wher = "uid > 0";
  if (dueTodayFilter) {
    wher += " AND next_interaction = '" + today + "' ";
  }

  if (overDueFilter) {
    wher += " AND next_interaction < '" + today + "' ";
  }
  if (agent > 0) {
    wher += " AND agent_id = '" + agent + "' ";
  }

  if (c_start_d && c_end_d) {
    $("#period_").val(c_start_d + " to " + c_end_d);
    wher +=
      " AND conversation_date BETWEEN '" +
      c_start_d +
      "' AND '" +
      c_end_d +
      "' ";
  }

  if (ni_start_d && ni_end_d) {
    $("#ni_period_").val(ni_start_d + " to " + ni_end_d);
    wher +=
      " AND next_interaction BETWEEN '" +
      ni_start_d +
      "' AND '" +
      ni_end_d +
      "' ";
  }

  if (interactionMethod > 0) {
    wher += " AND conversation_method=" + interactionMethod + " ";
  }

  if (interactionOutcome > 0) {
    wher += " AND flag=" + interactionOutcome + " ";
  }

  if (branch > 0) {
    wher += " AND branch=" + branch + " ";
  }
  if(tag > 0){
    wher += " AND tag='" + tag + "'";
  }

  console.log("filt " + wher);

  if (wher) {
    $("#_where_").val(wher);
    $("#_offset_").val(0);

    pager_home();
  } else {
    $("#_where_").val(" status > -1");
    $("#_offset_").val(0);
  }
}
function select_item(el, id) {
  $(el).val(id);
}

function specific_customer_interactions() {
  let customer = $("#cust_id_").val();
  if (!customer) {
    customer = "";
  }

  let params = "customer= " + customer;
  dbaction(
    "/jresources/interactions/specific_customer_interactions",
    params,
    function (feed) {
      console.log(params);
      $("#customer_interactions").html(feed);
      setTimeout(function () {
        pager_refactor();
      }, 200);
    }
  );
}

function save_interaction() {
  toggleBtnState("save_interaction_btn", "Saving...");
  let customer_id = $("#customer_id_").val();
  let transcript = encodeURIComponent($("#details").val());
  let conversation_method = $("#conv_method").val();
  let flag = $("#conversation_outcome").val();
  let conversation_purpose = $("#conversation_purpose").val();
  let default_reason = $("#default_reason").val();
  let next_interaction = $("#next_int").val();
  let promised_amount = $("#promised_amount").val();
  let next_steps = $("#next_stage").val();

  let params =
    "customer_id=" +
    customer_id +
    "&transcript=" +
    transcript +
    "&conversation_method=" +
    conversation_method +
    "&next_interaction=" +
    next_interaction +
    "&next_steps=" +
    next_steps +
    "&flag=" +
    flag +
    "&conversation_purpose=" +
    conversation_purpose +
    "&promised_amount=" +
    promised_amount +
    "&default_reason=" +
    default_reason;
  dbaction("/action/interaction_save", params, function (feed) {
    console.log(JSON.stringify(feed));
    feedback("DEFAULT", "TOAST", ".feedback_", feed, "4");
    toggleBtnState("save_interaction_btn", "Save");
  });
}

function hide_div(elem) {
  $(elem).fadeOut("fast");
}

function view_interaction(iid) {
  let params = "iid=" + iid;
  modal_view(
    "/jresources/interactions/view-one",
    params,
    "Interaction Details"
  );
}

function all_interactions(where) {
  $("#_sort_").val(where);
  $("#_dir_").val("desc");
  pager_home();
}

function face_to_face_interactions(where) {
  $("#_sort_").val(where);
  $("#_dir_").val("desc");
  pager_home();
}

function chat_interactions(where) {
  $("#_sort_").val(where);
  $("#_dir_").val("desc");
  pager_home();
}

function call_interactions(where) {
  $("#_sort_").val(where);
  $("#_dir_").val("asc");
  pager_home();
}

//////////=======================End of interactions
/////////----------------Settings
function permissions(group_id, user_id, tbl, rec, act, value, opt) {
  let params =
    "group_id=" +
    group_id +
    "&user_id=" +
    user_id +
    "&tbl=" +
    tbl +
    "&rec=" +
    rec +
    "&act=" +
    act +
    "&val=" +
    value +
    "&opt=" +
    opt;

  dbaction("/jresources/permissions/update", params, function (feed) {
    $("#perm").html(feed);
  });
}

function save_settings() {
  let name = $("#name").val();
  let logo = $("#logo").val();
  let icon = $("#icon").val();
  let link = $("#link").val();

  let params =
    "name=" + name + "&logo=" + logo + "&icon=" + icon + "&link=" + link;
  dbaction("/action/system/system_settings_update", params, function (feed) {
    console.log(JSON.stringify(feed));
    feedback("DEFAULT", "TOAST", ".feedback_", feed, "4");
  });
}

function pair_users(action = 1) {
  let lo = $("#lo_").val();
  let co = $("#co_").val();
  let params = "lo=" + lo + "&co=" + co + "&act=" + action;
  dbaction("/action/system/pair-save", params, function (feed) {
    feedback("DEFAULT", "TOAST", ".feedback_", feed, "4");
  });
}
function add_leader(action = 1) {
  let lo = $("#lo_").val();
  let co = $("#co_").val();
  let params = "lo=" + lo + "&co=" + co + "&act=" + action;
  dbaction("/action/system/add-leader", params, function (feed) {
    feedback("DEFAULT", "TOAST", ".feedback_", feed, "4");
  });
}
function remove_leader_agent(record_id) {
  let params = "record_id=" + record_id;
  Swal.fire({
    title: "Proceed?",
    text: "Are you sure you want remove agent from leader?",
    showCancelButton: true,
    confirmButtonText: "Yes",
    cancelButtonText: "No",
  }).then(function (result) {
    if (result.isConfirmed) {
      dbaction("/action/system/remove-leader-agent", params, function (feed) {
        feedback("DEFAULT", "TOAST", ".feedback_", feed, "3");
      });
    }
  });
}
function pair_users2(lo, co) {
  let params = "lo=" + lo + "&co=" + co;
  dbaction("/action/system/pair-save", params, function (feed) {
    feedback("DEFAULT", "TOAST", ".feedback_", feed, "4");
  });
}

function auto_pair(b) {
  let params = "b=" + b;
  Swal.fire({
    title: "Proceed?",
    text: "Are you sure you want the system to try and fix the pairs automatically?",
    showCancelButton: true,
    confirmButtonText: "Yes",
    cancelButtonText: "No",
  }).then(function (result) {
    if (result.isConfirmed) {
      feedback(
        "DEFAULT",
        "TOAST",
        ".feedback_",
        "<div class='alert alert-info'>Processing! Please don't close the window</div>",
        "50000"
      );
      dbaction("/action/system/auto-fix-pairing", params, function (feed) {
        feedback("DEFAULT", "TOAST", ".feedback_", feed, "3");
      });
    }
  });
}

function fix_pairing_d(b, user_id, user_type) {
  let params = "b=" + b + "&user_id=" + user_id + "&user_type=" + user_type;
  modal_view(
    "/jresources/customers/wrong-pairs",
    params,
    "Interaction Details"
  );
}

function assign_bulk_accounts(lo, co, where_type, user_id, branch) {
  let params =
    "lo=" +
    lo +
    "&co=" +
    co +
    "&where_type=" +
    where_type +
    "&user_id=" +
    user_id +
    "&branch=" +
    branch;
  Swal.fire({
    title: "Proceed?",
    text: "Assign all loans and customers to the specified pair?",
    showCancelButton: true,
    confirmButtonText: "Yes",
    cancelButtonText: "No",
  }).then(function (result) {
    if (result.isConfirmed) {
      feedback(
        "DEFAULT",
        "TOAST",
        ".feedback_",
        "<div class='alert alert-info'>Processing! Please don't close the window</div>",
        "50000"
      );
      dbaction("/action/system/assign_bulk", params, function (feed) {
        feedback("DEFAULT", "TOAST", ".feedback_", feed, "4");
      });
    }
  });
}

function assign_bulk_dist(where_type, user_id, branch) {
  let params =
    "where_type=" + where_type + "&user_id=" + user_id + "&branch=" + branch;
  Swal.fire({
    title: "Proceed?",
    text: "Distribute all loans and customers equally to the available pairs?",
    showCancelButton: true,
    confirmButtonText: "Yes",
    cancelButtonText: "No",
  }).then(function (result) {
    if (result.isConfirmed) {
      feedback(
        "DEFAULT",
        "TOAST",
        ".feedback_",
        "<div class='alert alert-info'>Processing! Please don't close the window</div>",
        "50000"
      );
      dbaction("/action/system/distribute_bulk", params, function (feed) {
        feedback("DEFAULT", "TOAST", ".feedback_", feed, "3");
      });
    }
  });
}

function move_accounts(action) {
  let user1 = $("#user1_").val();
  let user2 = $("#user2_").val();
  let gro = $("#group_cat_").val();
  let params = "user1=" + user1 + "&user2=" + user2 + "&group_cat=" + gro;
  Swal.fire({
    title: "Proceed?",
    text:
      "All the accounts with " +
      gro +
      " as first account will be moved to the second account. Proceed?",
    showCancelButton: true,
    confirmButtonText: "Yes",
    cancelButtonText: "No",
  }).then(function (result) {
    if (result.isConfirmed) {
      feedback(
        "DEFAULT",
        "TOAST",
        ".feedback_",
        "<div class='alert alert-info'>Processing! Please don't close the window</div>",
        "50000"
      );
      dbaction("/action/system/move-accounts", params, function (feed) {
        feedback("DEFAULT", "TOAST", ".feedback_", feed, "3");
      });
    }
  });
}

function allocate_missing_loans(bid) {
  Swal.fire({
    title: "Proceed?",
    text: "Are you sure you want to allocate loans with no BDOs? This process may take a few minutes.",
    showCancelButton: true,
    confirmButtonText: "Yes",
    cancelButtonText: "No",
  }).then(function (result) {
    if (result.isConfirmed) {
      let params = "branch=" + bid;
      feedback(
        "DEFAULT",
        "TOAST",
        ".feedback_",
        "<div class='alert alert-info'>Processing! Please wait</div>",
        "50000"
      );
      dbaction(
        "/action/targets/distribute_loans_to_pairs",
        params,
        function (feed) {
          feedback("DEFAULT", "TOAST", ".feedback_", feed, "5");
        }
      );
    }
  });
}

function allocate_all_loans(bid) {
  Swal.fire({
    title: "Warning!",
    text: "Are you sure you want to allocate all loans in the branch? This process will remove all the current BDOs first. It may take a few minutes.",
    showCancelButton: true,
    confirmButtonText: "Yes",
    cancelButtonText: "No",
  }).then(function (result) {
    if (result.isConfirmed) {
      let params = "branch=" + bid;
      feedback(
        "DEFAULT",
        "TOAST",
        ".feedback_",
        "<div class='alert alert-info'>Processing! Please wait</div>",
        "50000"
      );
      dbaction(
        "/action/targets/distribute_all_loans_to_pairs",
        params,
        function (feed) {
          feedback("DEFAULT", "TOAST", ".feedback_", feed, "5");
        }
      );
    }
  });
}

function allocate_original_loans(bid) {
  Swal.fire({
    title: "Warning!",
    text: "Are you sure you want to allocate all loans to the persons who added the customers? This process may take a few minutes.",
    showCancelButton: true,
    confirmButtonText: "Yes",
    cancelButtonText: "No",
  }).then(function (result) {
    if (result.isConfirmed) {
      let params = "branch=" + bid;
      feedback(
        "DEFAULT",
        "TOAST",
        ".feedback_",
        "<div class='alert alert-info'>Processing! Please wait</div>",
        "50000"
      );
      dbaction(
        "/action/targets/distribute_all_loans_to_original",
        params,
        function (feed) {
          feedback("DEFAULT", "TOAST", ".feedback_", feed, "5");
          console.log(feed);
        }
      );
    }
  });
}

function allocation_soft_reshuffle(group) {
  Swal.fire({
    title: "Proceed?",
    text: "Re-shuffle the accounts among the agents. Accounts will be moved from one agent to the other as they are. For example, all accounts from Agent A will be given to Agent B and vice versa.",
    showCancelButton: true,
    confirmButtonText: "Yes",
    cancelButtonText: "No",
    icon: "question",
  }).then(function (result) {
    if (result.isConfirmed) {
      let params = "group=" + group;
      feedback(
        "DEFAULT",
        "TOAST",
        ".feedback_",
        "<div class='alert alert-info'>Processing! Please wait</div>",
        "50000"
      );
      dbaction("/action/targets/soft_reshuffle", params, function (feed) {
        feedback("DEFAULT", "TOAST", ".feedback_", feed, "5");
      });
    }
  });
}

function allocation_hard_reshuffle(group) {
  Swal.fire({
    title: "Proceed?",
    text: "Re-shuffle the accounts among the agents randomly. Accounts will first be un-allocated from all agents and then re-allocated afresh.",
    showCancelButton: true,
    confirmButtonText: "Yes",
    cancelButtonText: "No",
  }).then(function (result) {
    if (result.isConfirmed) {
      let params = "group=" + group;
      feedback(
        "DEFAULT",
        "TOAST",
        ".feedback_",
        "<div class='alert alert-info'>Processing! Please wait</div>",
        "50000"
      );
      dbaction("/action/targets/hard_reshuffle", params, function (feed) {
        feedback("DEFAULT", "TOAST", ".feedback_", feed, "5");
      });
    }
  });
}

function remove_allocations(agent_id) {
  Swal.fire({
    title: "Continue?",
    text:
      "All active accounts allocated to agent " +
      agent_id +
      " will be un-allocated. Continue...",
    showCancelButton: true,
    confirmButtonText: "Yes",
    cancelButtonText: "No",
    icon: "warning",
  }).then(function (result) {
    if (result.isConfirmed) {
      let params = "agent=" + agent_id;
      dbaction("/action/targets/remove_allocations", params, function (feed) {
        feedback("DEFAULT", "TOAST", ".feedback_", feed, "3");
      });
    }
  });
}

function update_target(typ = "ONE") {
  let br = $("#br_").val();
  let am = $("#target_").val();
  let target_date = $("#target_date").val();
  let message = "";

  if (typ === "ALL") {
    message =
      "Update Target for all branches to " + am + " for Month: " + target_date;
  } else {
    message =
      "Update Target for one branche to " + am + " for Month: " + target_date;
  }

  Swal.fire({
    title: "Continue?",
    text: message,
    showCancelButton: true,
    confirmButtonText: "Yes",
    cancelButtonText: "No",
    icon: "warning",
  }).then(function (result) {
    if (result.isConfirmed) {
      let params =
        "branch=" +
        br +
        "&amount=" +
        am +
        "&typ=" +
        typ +
        "&target_date=" +
        target_date;
      dbaction("/action/targets/save_branch_target", params, function (feed) {
        feedback("DEFAULT", "TOAST", ".feedback_", feed, "4");
      });
    }
  });
}

function copy_targets() {
  let from_date = $('#from_date').val();
  let to_date = $('#to_date').val();
  let message = "Copy all branch targets from Month "+from_date + " to "+to_date;


  Swal.fire({
    title: "Continue?",
    text: message,
    showCancelButton: true,
    confirmButtonText: "Yes",
    cancelButtonText: "No",
    icon: "warning"
  }).then(function (result) {
    if (result.isConfirmed) {
      let params = "copy_from=" + from_date + "&copy_to=" + to_date;
      dbaction("/action/targets/copy_targets", params, function (feed) {
        feedback("DEFAULT", "TOAST", ".feedback_", feed, "4");
      });
    }
  });
}

function delink(pid) {
  Swal.fire({
    title: "Proceed?",
    text: "Are you sure you want to break this pair?",
    showCancelButton: true,
    confirmButtonText: "Yes",
    cancelButtonText: "No",
    icon: "warning",
  }).then(function (result) {
    if (result.isConfirmed) {
      let params = "pid=" + pid;
      dbaction("/action/system/pair-break", params, function (feed) {
        feedback("DEFAULT", "TOAST", ".feedback_", feed, "4");
      });
    }
  });
}

////////----------------End of settings

////////////----------------------Reports
function save_report() {
  let uid = parseInt($("#report_id").val());
  let title = $("#title").val();
  let description = $("#description").val();
  let row_query = $("#row_query").val();
  let branch_query = $("#branch_query").val();
  let viewable_by = $("#viewable_by").val();

  let endpoint = "create-new";
  if (uid > 0) {
    endpoint = "update";
  }

  let params =
    "uid=" +
    uid +
    "&title=" +
    title +
    "&description=" +
    description +
    "&row_query=" +
    row_query +
    "&viewable_by=" +
    viewable_by +
    "&branch_query=" +
    branch_query;

  dbaction("/action/report/" + endpoint, params, function (feed) {
    console.log(JSON.stringify(feed));
    feedback("DEFAULT", "TOAST", ".feedback_", feed, "4");
  });
}

function run_reports(rid, report_type, other_get = "") {
  // Get all form values at once
  const inputValues = {
    start_date: $("#from_date").val(),
    end_date: $("#to_date").val(),
    branch: $("#branch_").val(),
    region: $("#region_").val(),
    product: $("#product_").val() || 0
  };

  // Validate required dates
  if (!inputValues.start_date || !inputValues.end_date) {
    feedback(
      "ERROR",
      "TOAST",
      ".feedback_",
      '<div class="alert danger">Please enter both dates</div>',
      "2"
    );
    return;
  }

  // Base parameters for all report types
  const baseParams = new URLSearchParams({
    from: inputValues.start_date,
    to: inputValues.end_date,
    branch: inputValues.branch,
    product: inputValues.product
  });

  // Determine the report type and build URL accordingly
  let urlParam = '';
  switch (report_type) {
    case "CUSTOM":
      urlParam = `report=${rid}`;
      break;
    case "SYSTEM":
      urlParam = `system=${rid}`;
      break;
    case "REGREPORT":
      urlParam = `regreport=${rid}`;
      baseParams.set('region', parseInt(inputValues.region, 10));
      break;
    case "HREPORT":
      urlParam = `hreport=${rid}`;
      baseParams.set('region', inputValues.region);
      break;
    default:
      console.error("Unknown report type:", report_type);
      return;
  }

  // Construct the final URL
  const url = `reports?${urlParam}&${baseParams.toString()}${other_get}`;
  gotourl(url);
}


function alloc_dates(gr, ag) {
  let start_date = $("#from_date").val();
  let to_date = $("#to_date").val();

  if (!start_date || !to_date) {
    feedback(
      "ERROR",
      "TOAST",
      ".feedback_",
      '<div class="alert danger"> Please enter both dates </span>',
      "2"
    );
  } else {
    let agent = "";
    if (parseInt(ag) > 0) {
      agent = "&agent=" + ag;
    }
    gotourl(
      "allocations-v2?" + gr + "&from=" + start_date + "&to=" + to_date + agent
    );
  }
}

function run_pairing(branch) {
  let start_date = $("#from_date").val();
  let to_date = $("#to_date").val();

  if (!start_date || !to_date) {
    feedback(
      "ERROR",
      "TOAST",
      ".feedback_",
      '<div class="alert danger"> Please enter both dates </span>',
      "2"
    );
  } else {
    gotourl("pairing?b=" + branch + "&from=" + start_date + "&to=" + to_date);
  }
}

//////////=====================End of reports

/////////----------------Campaigns

function campaign_list() {
  let where = $("_where_").val();
  if (!where) {
    where = "uid > 0";
  }
  let offset = $("#_offset_").val();
  if (!offset) {
    offset = 0;
  }
  let rpp = $("#_rpp_").val();
  if (!rpp) {
    rpp = 10;
  }
  let page_no = $("#_page_no_").val();
  if (!page_no) {
    page_no = 1;
  }
  let orderby = $("#_orderby_").val();
  if (!orderby) {
    orderby = "uid";
  }
  let dir = $("#_dir_").val();
  if (!dir) {
    dir = "desc";
  }
  let search = $("#search_").val();
  if (!search) {
    search = "";
  }

  let sort_opt = $("#_sort_").val();
  if (!sort_opt) {
    sort_opt = "default_sort";
  }

  let params =
    "where_=" +
    where +
    "&offset=" +
    offset +
    "&rpp=" +
    rpp +
    "&page_no=" +
    page_no +
    "&orderby=" +
    orderby +
    "&dir=" +
    dir +
    "&search_=" +
    search +
    "&sort_option=" +
    sort_opt;
  dbaction("/jresources/campaign_list", params, function (feed) {
    console.log(params);
    $("#campaign_list").html(feed);
    setTimeout(function () {
      pager_refactor();
    }, 0);
  });
}

function all_campaigns(where) {
  $("#_sort_").val(where);
  $("#_dir_").val("desc");
  pager_home();
}

function past_campaigns(where) {
  $("#_sort_").val(where);
  $("#_dir_").val("desc");
  pager_home();
}

function running_campaigns(where) {
  $("#_sort_").val(where);
  $("#_dir_").val("desc");
  pager_home();
}

function future_campaigns(where) {
  $("#_sort_").val(where);
  $("#_dir_").val("asc");
  pager_home();
}

function repetitive_campaigns(where) {
  $("#_sort_").val(where);
  $("#_dir_").val("desc");
  pager_home();
}

function delete_campaign(campaign_id, title) {
  Swal.fire({
    title: "Proceed?",
    text: "Are you sure you want to " + title + "?",
    showCancelButton: true,
    confirmButtonText: "Yes",
    cancelButtonText: "No",
  }).then(function (result) {
    if (result.isConfirmed) {
      var params = "campaign_id=" + campaign_id;
      dbaction("/action/campaign/delete", params, function (feed) {
        feedback("DEFAULT", "TOAST", ".feedback_", feed, "4");
      });
    }
  });
}

function disable_campaign(campaign_id, title) {
  Swal.fire({
    title: "Proceed?",
    text: "Are you sure you want to " + title + "?",
    showCancelButton: true,
    confirmButtonText: "Yes",
    cancelButtonText: "No",
  }).then(function (result) {
    if (result.isConfirmed) {
      var params = "campaign_id=" + campaign_id;
      dbaction("/action/campaign/disable", params, function (feed) {
        feedback("DEFAULT", "TOAST", ".feedback_", feed, "4");
      });
    }
  });
}

function run_campaign(campaign_id, title, version = "") {
  Swal.fire({
    title: "Proceed?",
    text: "Are you sure you want to " + title + "?",
    showCancelButton: true,
    confirmButtonText: "Yes",
    cancelButtonText: "No",
  }).then(function (result) {
    if (result.isConfirmed) {
      var params = "campaign_id=" + campaign_id;
      feedback(
        "DEFAULT",
        "TOAST",
        ".feedback_",
        "<div class='alert alert-info'>Processing... This may take a while. Please don't close or refresh this window </div>",
        "6000"
      );
      dbaction("/action/campaign/run" + version, params, function (feed) {
        feedback("DEFAULT", "TOAST", ".feedback_", feed, "4");
      });
    }
  });
}

function enable_campaign(campaign_id, title) {
  Swal.fire({
    title: "Proceed?",
    text: "Are you sure you want to " + title + "?",
    showCancelButton: true,
    confirmButtonText: "Yes",
    cancelButtonText: "No",
  }).then(function (result) {
    if (result.isConfirmed) {
      var params = "campaign_id=" + campaign_id;
      dbaction("/action/campaign/enable", params, function (feed) {
        feedback("DEFAULT", "TOAST", ".feedback_", feed, "4");
      });
    }
  });
}

function audience_list() {
  let where = $("#_where_").val();
  if (!where) {
    where = "uid > 0";
  }
  let offset = $("#_offset_").val();
  if (!offset) {
    offset = 0;
  }
  let rpp = $("#_rpp_").val();
  if (!rpp) {
    rpp = 10;
  }
  let page_no = $("#_page_no_").val();
  if (!page_no) {
    page_no = 1;
  }
  let orderby = $("#_orderby_").val();
  if (!orderby) {
    orderby = "uid";
  }
  let dir = $("#_dir_").val();
  if (!dir) {
    dir = "desc";
  }
  let search = $("#search_").val();
  if (!search) {
    search = "";
  }

  let camp_id = $("#_camp_id_").val();
  if (!camp_id) {
    camp_id = "";
  }

  let params =
    "where_=" +
    where +
    "&offset=" +
    offset +
    "&rpp=" +
    rpp +
    "&page_no=" +
    page_no +
    "&orderby=" +
    orderby +
    "&dir=" +
    dir +
    "&search_=" +
    search +
    "&camp_id=" +
    camp_id;
  dbaction("/jresources/campaign_sec/audience_list", params, function (feed) {
    console.log(params);
    $("#audience_list").html(feed);
    setTimeout(function () {
      pager_refactor();
    }, 500);
  });
}

function campaign_save_message(cid, message_id) {
  let url = "campaign_message_save";
  if (parseInt(message_id) > 0) {
    url = "campaign_message_update";
  } else {
    url = "campaign_message_save";
  }

  let campaign_id = cid;
  let message = $("#description").val();
  const message_type = $("#message_type").val();
  let params =
    "campaign_id=" +
    campaign_id +
    "&message_id=" +
    message_id +
    "&message=" +
    message +
    "&type=" +
    message_type;
  dbaction("/action/campaign/" + url, params, function (feed) {
    console.log(JSON.stringify(feed));
    feedback("DEFAULT", "TOAST", ".feedback_", feed, "4");
  });
}

function campaign_message_list(campaign, action) {
  let params = "campaign= " + campaign + "&action= " + action;
  dbaction(
    "/jresources/campaign_sec/campaign_message_list",
    params,
    function (feed) {
      console.log(JSON.stringify(feed));
      $("#message_").html(feed);
    }
  );
}

function delete_message(message_id) {
  Swal.fire({
    title: "Confirm deletion",
    text: "Are you sure you want to delete this message?",
    showCancelButton: true,
    confirmButtonText: "Yes",
    cancelButtonText: "No",
  }).then(function (result) {
    if (result.isConfirmed) {
      var params = "message_id=" + message_id;
      dbaction("/action/campaign/message_delete", params, function (feed) {
        feedback("DEFAULT", "TOAST", ".feedback_", feed, "4");
      });
    }
  });
}

////////================End of Campaigns
///-----Archives
function archives_toggle(action) {
  let params = "action=" + action;
  dbaction("/action/login-archives.php", params, function (feed) {
    console.log(JSON.stringify(feed));
    feedback("DEFAULT", "TOAST", ".feedback_", feed, "4");
  });
}

///// =========================== Customer broadcasts and interactive messages
function loadMessages() {
  const cid = parseInt($("#cust_reuse_id_msgs_tab").val().trim());
  const phone_no = $("#cust_phone_reuse_msgs_tab").val().trim();

  let msgType = persistence_read("msgType");

  if (!msgType) {
    msgType = "broadcasts";
  }

  document.getElementById(msgType).classList.add("disabled");

  let where = $("#_where_").val();
  if (!where) {
    where = "uid > 0";
  }
  let offset = $("#_offset_").val();
  if (!offset) {
    offset = 0;
  }
  let rpp = $("#_rpp_").val();
  if (!rpp) {
    rpp = 10;
  }

  let page_no = $("#_page_no_").val();
  if (!page_no) {
    page_no = 1;
  }

  let orderby = $("#_orderby_").val();
  if (!orderby) {
    orderby = "uid";
  }
  let dir = $("#_dir_").val();
  if (!dir) {
    dir = "desc";
  }

  let params =
    "where_=" +
    where +
    "&offset=" +
    offset +
    "&rpp=" +
    rpp +
    "&page_no=" +
    page_no +
    "&orderby=" +
    orderby +
    "&dir=" +
    dir +
    "&phone_no=" +
    phone_no +
    "&cid=" +
    cid;

  let url = "/jresources/messages/broadcasts";

  if (msgType === "interactive") {
    url = "/jresources/messages/interactive";
  }

  dbaction(url, params, function (feed) {
    $("#messages_list").html(feed);
    pager2("#pagination");
    setTimeout(function () {
      pager_refactor("Message");
    }, 200);
  });
}

function saveBroadcastMessage(cid, messageId = 0) {
  let url = "save_broadcast_message";
  if (parseInt(messageId) > 0) {
    url = "update_broadcast_message";
  }

  const customerId = cid;
  const message = $("#message-details").val();
  const params =
    "messageId=" +
    messageId +
    "&customerId=" +
    customerId +
    "&message=" +
    message;

  dbaction("/action/messages/" + url, params, function (feed) {
    console.log(JSON.stringify(feed));
    feedback("DEFAULT", "TOAST", ".feedback_", feed, "2.5");

    // reset offset to 0
    $("#_offset_").val(0);

    // reset page number to 1
    $("#_page_no_").val(1);

    // refresh the list after toast vanishes
    setTimeout(function () {
      loadMessages();
    }, 2500);
  });
}

if ($("#tab_9").length) {
  document.querySelectorAll(".list-group-item").forEach(function (li) {
    li.addEventListener("click", function (e) {
      // Remove "disabled" class from all li elements
      document.querySelectorAll(".list-group-item").forEach(function (li) {
        li.classList.remove("disabled");
      });

      // Add "disabled" class to the clicked li element
      this.classList.add("disabled");

      // reset offset to 0
      $("#_offset_").val(0);

      // reset page number to 1
      $("#_page_no_").val(1);

      // Call loadMessages function
      if (this.textContent.includes("Broadcasts")) {
        persistence("msgType", "broadcasts");
      } else if (this.textContent.includes("Interactive")) {
        persistence("msgType", "interactive");
      }
      loadMessages(e);
    });
  });
}

///==========================Begin Assets
function load_assets() {
  let where = $("#_where_").val();
  if (!where) {
    where = "uid > 0";
  }
  let offset = $("#_offset_").val();
  if (!offset) {
    offset = 0;
  }
  let rpp = $("#_rpp_").val();
  if (!rpp) {
    rpp = 10;
  }

  let page_no = $("#_page_no_").val();
  if (!page_no) {
    page_no = 1;
  }

  let orderby = $("#_orderby_").val();
  if (!orderby) {
    orderby = "uid";
  }
  let dir = $("#_dir_").val();
  if (!dir) {
    dir = "desc";
  }
  let search = $("#search_").val();
  if (!search) {
    search = "";
  }

  // let sort_opt =$("#_sort_").val();
  // if(!sort_opt){
  //     sort_opt = "default_sort";
  // }

  let params =
    "where_=" +
    where +
    "&offset=" +
    offset +
    "&rpp=" +
    rpp +
    "&page_no=" +
    page_no +
    "&orderby=" +
    orderby +
    "&dir=" +
    dir +
    "&search_=" +
    search;
  dbaction("/jresources/assets/asset-list", params, function (feed) {
    console.log(params);
    $("#asset-list").html(feed);
    setTimeout(function () {
      pager_refactor();
    }, 200);
  });
}

// asset list filter
function assets_filter() {
  let sel_category = parseInt($("#assets_category").val(), 10);

  let wher = "uid > 0";

  if (sel_category > 0) {
    wher += " AND category_=" + sel_category + " ";
  }

  console.log("filt " + wher);

  if (wher) {
    $("#_where_").val(wher);
    $("#_offset_").val(0);

    pager_home();
  } else {
    $("#_where_").val(" status > -1");
    $("#_offset_").val(0);
  }
}

function create_asset_loan() {
  const customer_id = $("#customer_id_").val()?.trim();
  const loan_amount = $("#loan_amount").val()?.trim();
  const product_id = $("#asset_product").val()?.trim() ?? 1;
  const asset_id = $("#asset_id_").val()?.trim();
  const period = $("#period").val()?.trim();
  const loan_type = 4;
  const application_mode = "MANUAL";

  const params =
    "customer_id=" +
    customer_id +
    "&product_id=" +
    product_id +
    "&loan_amount=" +
    loan_amount +
    "&application_mode=" +
    application_mode +
    "&loan_type=" +
    loan_type +
    "&asset_id=" +
    asset_id +
    "&period=" +
    period;
  dbaction("/action/asset_loans/create-asset-loan", params, function (feed) {
    feedback("DEFAULT", "TOAST", ".feedback_", feed, "4");
  });
}

function update_asset_stock() {
  const stock = $("#stock_amount").val()?.trim();
  const asset_id = $("#asset_id_val").val()?.trim();

  const params = "stock=" + stock + "&asset_id=" + asset_id;
  dbaction("/action/asset_loans/update-asset-stock", params, function (feed) {
    feedback("DEFAULT", "TOAST", ".feedback_", feed, "4");
  });
}

function cart_add(aid) {
  const params = "aid=" + aid;
  dbaction("/action/asset_loans/add-to-cart", params, function (feed) {
    feedback("DEFAULT", "TOAST", ".feedback_", feed, "2");
  });
}

function cart(action, uid) {
  const params = "action=" + action + "&uid=" + uid;
  dbaction("/action/asset_loans/cart-action", params, function (feed) {
    feedback("DEFAULT", "TOAST", ".feedback_", feed, "2");
  });
}

function get_events(staff_uid, tab = "") {
  const start_date = $("#from_date").val();
  const to_date = $("#to_date").val();

  if (!start_date || !to_date) {
    return feedback(
      "ERROR",
      "TOAST",
      ".feedback_",
      '<div class="alert danger"> Please enter both dates </span>',
      "2"
    );
  }
  gotourl(
    "staff?staff=" +
      staff_uid +
      "&events=&from=" +
      start_date +
      "&to=" +
      to_date +
      "&tab=" +
      tab
  );
}

function getGeolocation() {
  return new Promise((resolve, reject) => {
    // Check if Geolocation is supported by the browser
    if ("geolocation" in navigator) {
      navigator.geolocation.getCurrentPosition(
        function (position) {
          // Get latitude and longitude
          const lat = position.coords.latitude;
          const lon = position.coords.longitude;

          // Generate a Google Maps URL with the latitude and longitude
          const locationURL = `https://www.google.com/maps?q=${lat},${lon}`;
          // console.log("MAP URL => ", locationURL);

          // Resolve the Promise with the location URL
          resolve(locationURL);
        },
        function (error) {
          // console.log("ERROR => ", error);
          // Handle errors (e.g., if the user denies permission)
          reject(error);
        }
      );
    } else {
      // Geolocation not supported
      reject("Geolocation is not supported by your browser.");
    }
  });
}

function delete_payment(uid) {
  Swal.fire({
    title: "Confirm deletion",
    text: "Are you sure you want to remove this payment?",
    showCancelButton: true,
    confirmButtonText: "Yes",
    cancelButtonText: "No",
  }).then(function (result) {
    if (result.isConfirmed) {
      let params = "pid=" + uid;
      dbaction("/action/payments/deletev2", params, function (feed) {
        console.log(feed);
        feedback("DEFAULT", "TOAST", ".feedback_", feed, "4");
      });
    }
  });
}

function formatMoney(num) {
  return num
    .toFixed(2)
    .replace(/\d(?=(\d{3})+\.)/g, "$&,")
    .trim();
}

function split_individual_pay(splitFor) {
  const amount = $("#amount_").val();
  const init_loan_id = $("#current_loan_id").val();
  const new_loan_id = parseInt($("#new_loan_id").val(), 10);

  let resp = "";
  Swal.fire({
    title: "Confirm allocation",
    text: `Are you sure you want to allocate ${amount} to loan id ${new_loan_id}?`,
    showCancelButton: true,
    confirmButtonText: "Yes",
    cancelButtonText: "No",
  }).then(function (result) {
    if (!result.isConfirmed) {
      return; // stop further execution
    }

    const customer_id = $("#customer_id").val();
    const payment_for = $("#payment_for").val();
    const parent_payment = $("#payment_id").val();
    const params =
      "amount=" +
      amount +
      "&parent_payment=" +
      parent_payment +
      "&customer_id=" +
      customer_id +
      "&payment_for=" +
      payment_for +
      "&init_loan_id=" +
      init_loan_id +
      "&new_loan_id=" +
      new_loan_id +
      "&split_for=" +
      splitFor;

    if (splitFor === "overpayment") {
      dbaction("/action/payments/split-overpayment", params, function (feed) {
        feedback("DEFAULT", "TOAST", ".feedback_", feed, "2");
      });
    }

    if (splitFor === "unallocated") {
      dbaction("/action/payments/split-unallocated", params, function (feed) {
        feedback("DEFAULT", "TOAST", ".feedback_", feed, "2");
      });
    }
  });
}

function split_individual_stray_pay(customerName = "") {
  const amount = $("#amount_").val();
  const loan_id = $("#loan_id").val();

  let resp = "";
  Swal.fire({
    title: "Confirm allocation",
    text: `Are you sure you want to allocate ${amount} to ${customerName} loan id ${loan_id}?`,
    showCancelButton: true,
    confirmButtonText: "Yes",
    cancelButtonText: "No",
  }).then(function (result) {
    if (!result.isConfirmed) {
      return; // stop further execution
    }

    const customer_id = $("#customer_id").val() || 0;
    const payment_for = $("#payment_for").val();
    const parent_payment = $("#payment_id").val();
    const params =
      "amount=" +
      amount +
      "&parent_payment=" +
      parent_payment +
      "&customer_id=" +
      customer_id +
      "&payment_for=" +
      payment_for +
      "&loan_id=" +
      loan_id;

    dbaction("/action/payments/split-straypayment", params, function (feed) {
      feedback("DEFAULT", "TOAST", ".feedback_", feed, "2");
    });
  });
}

///==============Begin branch
function branch_list() {
  let where = $("#_where_").val();
  if (!where) {
    where = "uid > 0";
  }
  let offset = $("#_offset_").val();
  if (!offset) {
    offset = 0;
  }
  let rpp = $("#_rpp_").val();
  if (!rpp) {
    rpp = 10;
  }

  let page_no = $("#_page_no_").val();
  if (!page_no) {
    page_no = 1;
  }

  let orderby = $("#_orderby_").val();
  if (!orderby) {
    orderby = "uid";
  }
  let dir = $("#_dir_").val();
  if (!dir) {
    dir = "desc";
  }
  let search = $("#search_").val();
  if (!search) {
    search = "";
  }

  let params =
    "where_=" +
    where +
    "&offset=" +
    offset +
    "&rpp=" +
    rpp +
    "&page_no=" +
    page_no +
    "&orderby=" +
    orderby +
    "&dir=" +
    dir +
    "&search_=" +
    search;
  dbaction("/jresources/branch-list", params, function (feed) {
    console.log(params);
    $("#branch_list").html(feed);
    setTimeout(function () {
      pager_refactor();
    }, 200);
  });
}

function branch_filters() {
  let staff_order = $("#branch_order").val();
  let status = parseInt($("#sel_branch_status").val());
  let region_id = parseInt($("#sel_branch_region").val());

  let wher = "uid > 0";
  $("#_dir_").val(staff_order);

  if (status > 0) {
    wher += " AND status=" + status;
  }

  if (region_id > 0) {
    wher += " AND region_id=" + region_id;
  }

  console.log("filt " + wher);

  if (wher) {
    $("#_where_").val(wher);
    $("#_offset_").val(0);

    pager_home();
  } else {
    $("#_where_").val(" status > -1");
    $("#_offset_").val(0);
  }
}

function branch_action(branch_id, title, action) {
  Swal.fire({
    title: "Confirm action",
    text: "Are you sure you want to " + title + "?",
    showCancelButton: true,
    confirmButtonText: "Yes",
    cancelButtonText: "No",
  }).then(function (result) {
    if (result.isConfirmed) {
      const params = "branch_id=" + branch_id;

      // Determine URL to call based on action
      let url = "";
      if (action === "block") {
        url = "/action/branch/block";
      } else if (action === "unblock") {
        url = "/action/branch/unblock";
      } else {
        feedback(
          "ERROR",
          "TOAST",
          "Unexpected Action",
          '<div class="alert danger"> Invalid action </span>',
          "2"
        );
        return;
      }

      // console.log("PARAMS => ", params);
      dbaction(url, params, function (feed) {
        feedback("DEFAULT", "TOAST", ".feedback_", feed, "4");
      });
    }
  });
}

function branch_save() {
  const bid = parseInt($("#bid").val());
  const name = $("#branch_name").val();
  const address = $("#address").val();
  const region_id = parseInt($("#region_id").val()) || 0;

  let endpoint = "create";
  if (bid > 0) {
    endpoint = "update";
  }

  const params =
    "name=" +
    name +
    "&bid=" +
    bid +
    "&address=" +
    address +
    "&region_id=" +
    region_id;
  dbaction("/action/branch/" + endpoint, params, function (feed) {
    console.log(feed);
    feedback("DEFAULT", "TOAST", ".feedback_", feed, "4");
  });
}

///=============End Branch

function ugAirtelUGB2CStatusEnquiry(loanId) {
  toggleBtnState("b2c-resend", "Processing...");
  if (parseInt(loanId, 10) < 1) {
    feedback("ERROR", "TOAST", ".feedback_", "Loan id not parsed!", "4");
    return;
  }

  const params = "loan_id=" + loanId;
  dbaction(
    "/action/loan/airtel/b2c-transaction-enquiry",
    params,
    function (feed) {
      toggleBtnState("b2c-resend", "Enquire B2C Transaction Status");
      // Extracting JSON data from the feed
      const jsonStartIndex = feed.indexOf("{");
      const jsonEndIndex = feed.lastIndexOf("}");
      const jsonStr = feed.substring(jsonStartIndex, jsonEndIndex + 1);

      // console.log(`json string: ${jsonStr}`);

      // Parsing the JSON string to an object
      const feedObj = JSON.parse(jsonStr);

      // Extract status and loan_id
      const status = feedObj.status;
      const loanId = feedObj.loan_id;
      const respMessage = feedObj.message;

      let message = "";

      if (status == "TS") {
        message = "B2C Transaction Successful";
        Swal.fire({
          title: "Proceed?",
          text: `${message}. Would you like to update the loan status to disbursed?`,
          showCancelButton: true,
          confirmButtonText: "Yes",
          cancelButtonText: "No",
          icon: "question",
        }).then(function (result) {
          if (result.isConfirmed) {
            change_loan_status(loanId, 3, "Disbursed");
          }
        });
      } else if (status == "TF") {
        message =
          "B2C Transaction Failed!<br/> Please give it upto 1 hour since the disbursement was initiated and try resending if the status is still pending";
        message = `<div class='alert alert-danger'>${message}</div>`;
        feedback("DEFAULT", "TOAST", ".feedback_", message, "8");
      } else if (respMessage == "exttRID does not exists") {
        message = "B2C Transaction Not Found";
        Swal.fire({
          title: "Proceed?",
          text: `${message}. Would you like to resend the loan?`,
          showCancelButton: true,
          confirmButtonText: "Yes",
          cancelButtonText: "No",
          icon: "question",
        }).then(function (result) {
          if (result.isConfirmed) {
            resend_loan(loanId, "UG_AIRTEL");
          }
        });
      } else {
        message =
          "B2C Transaction has Unknown Status!<br/> Please give it upto 12 hours since the disbursement was initiated and try resending if the status is still pending";
        message = `<div class='alert alert-danger'>${message}</div>`;
        feedback("DEFAULT", "TOAST", ".feedback_", message, "8");
      }
    }
  );
}

function mtnUGB2CStatusEnquiry(loanId) {
  toggleBtnState("b2c-resend", "Processing...");
  if (parseInt(loanId, 10) < 1) {
    feedback("ERROR", "TOAST", ".feedback_", "Please enter the loan id", "4");
    return;
  }

  const params = "loan_id=" + loanId;
  dbaction("/action/loan/mtn/b2c-transaction-enquiry", params, function (feed) {
    toggleBtnState("b2c-resend", "Enquire B2C Transaction Status");
    // Extracting JSON data from the feed
    const jsonStartIndex = feed.indexOf("{");
    const jsonEndIndex = feed.lastIndexOf("}");
    const jsonStr = feed.substring(jsonStartIndex, jsonEndIndex + 1);

    // Parsing the JSON string to an object
    const feedObj = JSON.parse(jsonStr);

    // Extract status and loan_id
    const status = feedObj.status;
    const loanId = feedObj.loan_id;
    const respMessage = feedObj.message;

    let message = "";

    if (status == "TS") {
      message = "B2C Transaction Successful";
      Swal.fire({
        title: "Proceed?",
        text: `${message}. Would you like to update the loan status to disbursed?`,
        showCancelButton: true,
        confirmButtonText: "Yes",
        cancelButtonText: "No",
        icon: "question",
      }).then(function (result) {
        if (result.isConfirmed) {
          change_loan_status(loanId, 3, "Disbursed");
        }
      });
    } else if (status == "TF") {
      message =
        "B2C Transaction Failed!<br/> Please give it upto 1 hour since the disbursement was initiated and try resending if the status is still pending";
      message = `<div class='alert alert-danger'>${respMessage}</div>`;
      feedback("DEFAULT", "TOAST", ".feedback_", message, "8");
    } else if (respMessage == "exttRID does not exists") {
      message = "B2C Transaction Not Found";
      Swal.fire({
        title: "Proceed?",
        text: `${message}. Would you like to resend the loan?`,
        showCancelButton: true,
        confirmButtonText: "Yes",
        cancelButtonText: "No",
        icon: "question",
      }).then(function (result) {
        if (result.isConfirmed) {
          resend_loan(loanId, "UG_MTN");
        }
      });
    } else {
      message =
        "B2C Transaction has Unknown Status!<br/> Please give it upto 12 hours since the disbursement was initiated and try resending if the status is still pending";
      message = `<div class='alert alert-danger'>${message}</div>`;
      feedback("DEFAULT", "TOAST", ".feedback_", message, "8");
    }
  });
}

function top_highlights_load() {
  // $('#top-highlights').html('<i>Loading...</i>');
  load_std("/jresources/dashboards/top-highlights", "#top-highlights", "");
}

function disburse_progress_mtd_load() {
  load_std(
    "/jresources/dashboards/disburse-progress-mtd",
    "#disburse-progress-mtd",
    ""
  );
}

function daily_performance_load() {
  load_std(
    "/jresources/dashboards/daily-performance",
    "#daily-performance",
    ""
  );
}

function old_monthly_perfomance_load() {
  load_std(
    "/jresources/dashboards/old-monthly-perfomance",
    "#old-monthly-perfomance",
    ""
  );
}

// make an API call to load top highlights
function top_highlights_summary() {
  const server_ = $("#server_").val();
  $.ajax({
    url: server_ + "/apis/top-highlights-summary",
    type: "GET",
    success: function (response) {
      // console.log(response);
      // var data = JSON.parse(response);
      const data = response;
      // console.log(data);
      $("#loans_today_lbl").html(data.loans_today);
      $("#payments_today_lbl").html(data.payments_today);
      $("#due_today_lbl").html(data.due_today);
      $("#utility_balance_lbl").html(data.utility_balance);
      $("#paybill_balance_lbl").html(data.paybill_balance);
      $("#sms_balance_lbl").html(data.sms_balance);
      if (data.airtel_ug_utility_balance) {
        $("#airtel_ug_utility_balance_lbl").html(
          data.airtel_ug_utility_balance
        );
      }
      if (data.airtel_ug_paybill_balance) {
        $("#airtel_ug_paybill_balance_lbl").html(
          data.airtel_ug_paybill_balance
        );
      }
    },
  });
}

// Function to load top highlights initially and then every minute
function load_top_highlights_interval() {
  top_highlights_load(); // Load initially

  // Set interval to load every minute
  setInterval(function () {
    top_highlights_summary();
  }, 60000); // 60000 milliseconds = 1 minute
}

function get_loan_statement(loan_id) {
  let params = "loan_id=" + loan_id;
  load_std("/extensions/loan_statement", "#statement_", params);
}

function toggle_b2c_validation(loanId, withNidValidation, action) {
  if (parseInt(loanId, 10) < 1) {
    feedback("ERROR", "TOAST", ".feedback_", "Loan id not parsed!", "4");
    return;
  }

  const params =
    "loan_id=" +
    loanId +
    "&with_ni_validation=" +
    withNidValidation +
    "&action=" +
    action;
  const message = `Are you sure you want to ${action} for this loan?`;
  let btnText = action;
  Swal.fire({
    title: "Proceed?",
    text: `${message}`,
    showCancelButton: true,
    confirmButtonText: "Yes",
    cancelButtonText: "No",
    icon: "question",
  }).then(function (result) {
    if (result.isConfirmed) {
      toggleBtnState("toggle-b2c-validation", "Processing...");
      dbaction("/action/loan/toggle-b2c-validation", params, function (feed) {
        // Extracting JSON data from the feed
        const jsonStartIndex = feed.indexOf("{");
        const jsonEndIndex = feed.lastIndexOf("}");
        const jsonStr = feed.substring(jsonStartIndex, jsonEndIndex + 1);

        // Parsing the JSON string to an object
        let feedObj;

        try {
          feedObj = JSON.parse(jsonStr);
        } catch (error) {
          // If parsing fails, handle the error here
          console.error("Error parsing JSON:", error);
        }

        // Extract status and loan_id
        const status = feedObj?.status;
        const loanId = feedObj?.loan_id;
        const respMessage = feedObj?.message;
        btnText = feedObj?.btnText || action;

        if (status == "OK" && loanId && respMessage && btnText) {
          const message = `<div class='alert alert-success'>${
            respMessage + ". You can resend the loan by clicking Resend Button"
          }</div>`;
          feedback("DEFAULT", "TOAST", ".feedback_", message, "4");
          setTimeout(() => reload(), 4000); // reload the page after 5 seconds
        } else {
          feedback("DEFAULT", "TOAST", ".feedback_", feed, "4");
        }

        toggleBtnState("toggle-b2c-validation", btnText);
      });
    }

    
  });
}

function tag_loan(loan_id, npl_category) {
  let params = "loan_id=" + loan_id + "&npl_category=" + npl_category;
  dbaction("/action/loan/npl-category", params, function (feed) {
    feedback("DEFAULT", "TOAST", ".feedback_", feed, "2");
  });
}

function loadUploads(customerId) {
  let params = "customer_id=" + customerId;
  let url = "/jresources/customers/uploads";

  dbaction(url, params, function (feed) {
    $("#upload_list").html(feed);
  });
}

function loadContacts(customerId, primary_mobile) {
  let params =
    "customer_id=" + customerId + "&primary_mobile=" + primary_mobile;
  let url = "/jresources/customers/contacts";

  dbaction(url, params, function (feed) {
    $("#contacts_placeholder").html(feed);
  });
}

function loadGuarantors(customerId) {
  let params = "customer_id=" + customerId;
  let url = "/jresources/customers/guarantors";

  dbaction(url, params, function (feed) {
    $("#guarantors_placeholder").html(feed);
  });
}

function loadReferees(customerId) {
  let params = "customer_id=" + customerId;
  let url = "/jresources/customers/referees";

  dbaction(url, params, function (feed) {
    $("#referees_placeholder").html(feed);
  });
}

function loadCollateral(customerId) {
  let params = "customer_id=" + customerId;
  let url = "/jresources/customers/collateral";

  dbaction(url, params, function (feed) {
    $("#collateral_placeholder").html(feed);
  });
}

function loadCustomerEvents(customerId) {
  let params = "customer_id=" + customerId;
  let url = "/jresources/customers/events";

  dbaction(url, params, function (feed) {
    $("#customer_events_placeholder").html(feed);
  });
}

function loanPaySchedule(loan_id) {
  let params = "loan_id=" + loan_id;
  let url = "/jresources/loans/repay-schedule";

  dbaction(url, params, function (feed) {
    $("#repay_schedule_placeholder").html(feed);
  });
}

function loanEvents(loan_id) {
  let params = "loan_id=" + loan_id;
  let url = "/jresources/loans/events";

  dbaction(url, params, function (feed) {
    $("#loan_events_placeholder").html(feed);
  });
}

function otherPayments(customer_id) {
  let params = "customer_id=" + customer_id;
  let url = "/jresources/repayments/other-payments";

  dbaction(url, params, function (feed) {
    $("#other_payments_placeholder").html(feed);
  });
}

function paymentEvents(payment_id) {
  let params = "payment_id=" + payment_id;
  let url = "/jresources/repayments/events";

  dbaction(url, params, function (feed) {
    $("#payment_events_placeholder").html(feed);
  });
}

function group_filters() {
  // let staff_order = $('#staff_order').val();
  let sel_branch = parseInt($("#sel_branch").val());
  // let user_group = parseInt($('#group_').val());

  let wher = "uid > 0";
  // $('#_dir_').val(staff_order);

  if (sel_branch > 0) {
    wher += " AND branch=" + sel_branch;
  }

  // if (user_group > 0) {
  //     wher += " AND user_group=" + user_group;
  // }

  console.log("filt " + wher);

  if (wher) {
    $("#_where_").val(wher);
    $("#_offset_").val(0);

    pager_home();
  } else {
    $("#_where_").val(" status > -1");
    $("#_offset_").val(0);
  }
}
function showCallout(targetSelector, calloutText, localStorageKey) {
  var calloutDismissed = localStorage.getItem(localStorageKey);

  if (!calloutDismissed) {
    function displayCallout($target) {
      if (!$(".callout").length) {
        var offset = $target.offset();

        var $callout = $('<div class="callout">' + calloutText + "</div>");
        $("body").append($callout);

        $callout
          .css({
            top: offset.top - $callout.outerHeight() - 20, // 10px margin + 10px arrow
            left: offset.left,
          })
          .fadeIn("slow");

        $callout.on("click", function () {
          $callout.fadeOut(function () {
            $(this).remove();
            localStorage.setItem(localStorageKey, "true");
          });
        });
      }
    }

    var observer = new MutationObserver(function (mutations) {
      mutations.forEach(function (mutation) {
        mutation.addedNodes.forEach(function (node) {
          if ($(node).is(targetSelector)) {
            displayCallout($(node));
          } else if ($(node).find(targetSelector).length) {
            displayCallout($(node).find(targetSelector).first());
          }
        });
      });
    });

    observer.observe(document.body, { childList: true, subtree: true });

    // Initial call for elements already present in the DOM
    var $initialTarget = $(targetSelector).first();
    if ($initialTarget.length) {
      displayCallout($initialTarget);
    }
  }
}

function customer_preview(phone) {
  let params = "phone=" + phone;
  modal_view(
    "/jresources/customers/account-summary",
    params,
    "Customer Summary"
  );
}

function copyTextToClipboard(text) {
  // Create a temporary textarea element
  var textArea = document.createElement("textarea");

  // Set the value of the textarea to the text to be copied
  textArea.value = text;

  // Make the textarea element readonly and move it off-screen
  textArea.style.position = "fixed";
  textArea.style.top = "-9999px";
  textArea.setAttribute("readonly", "");

  // Append the textarea to the document body
  document.body.appendChild(textArea);

  // Select the text inside the textarea
  textArea.select();

  // Copy the selected text to the clipboard
  document.execCommand("copy");

  // Remove the textarea element from the document
  document.body.removeChild(textArea);

  // Optionally, you can alert the user that the text has been copied
  feedback("SUCCESS", "TOAST", ".feedback_", "Copied to clipboard", "2");
}

function convert_to_hyperlinks() {
  setTimeout(function () {
    var phoneRegex = /254\d{9}/g;

    // Function to process text nodes and replace phone numbers
    function processTextNodes(node) {
      if (node.nodeType === Node.TEXT_NODE) {
        // Check if the text node is inside an <a> element
        if (!isInsideLink(node)) {
          var text = node.nodeValue;
          var html = text.replace(phoneRegex, function (match) {
            return (
              '<a title="Go to customer account" class="superlnk_" onclick="customer_preview(' +
              match +
              ')">' +
              match +
              "</a>"
            );
          });

          if (html !== text) {
            var tempDiv = document.createElement("div");
            tempDiv.innerHTML = html;

            while (tempDiv.firstChild) {
              node.parentNode.insertBefore(tempDiv.firstChild, node);
            }
            node.parentNode.removeChild(node);
          }
        }
      } else if (node.nodeType === Node.ELEMENT_NODE) {
        // Skip processing if the element is an <a> tag
        if (node.nodeName.toLowerCase() !== "a") {
          for (var i = 0; i < node.childNodes.length; i++) {
            processTextNodes(node.childNodes[i]);
          }
        }
      }
    }

    // Helper function to check if a node is inside an <a> element
    function isInsideLink(node) {
      while (node) {
        if (node.nodeName && node.nodeName.toLowerCase() === "a") {
          return true;
        }
        node = node.parentNode;
      }
      return false;
    }

    processTextNodes(document.body);
  }, 1500); // Delay for 1.5 seconds
}
function make_call(phone, recipient_uid) {
  $("#call_logs").html("");
  Swal.fire({
    title: "Proceed?",
    text: "Call customer's Phone?",
    showCancelButton: true,
    confirmButtonText: "Yes",
    cancelButtonText: "No",
  }).then(function (result) {
    if (result.isConfirmed) {
      let params = "phone=" + phone + "&recipient_uid=" + recipient_uid;
      let url = "/action/system/make-call";

      dbaction(url, params, function (feed) {
        $("#call_logs").html(feed);
      });
    }
  });
}

/////------Call functions
function initiateListeners(token) {
  var audio = new Audio(
    "https://superlender.co.ke/zidicash/apis/dial-tone.mp3"
  );


  try {
    const params = {
      sounds: {
        dialing: '/apis/dial-tone.mp3',
        ringing: '/apis/ring-tone.mp3'
      }
    };
    client = new Africastalking.Client(token, params);

    ///-----Additional methods
    client.on('ready', function () {
     /////----Ready to make a call
      $('#start_call_btn').removeAttr('disabled').attr("title","Call ready");
      $("#call_logs").html("Ready...");
    }, false);


    client.on('notready', function () {
     /////----Not ready to make a call
      $('#start_call_btn').attr('disabled','disabled').attr("title","Not ready, refresh page");
      $("#call_logs").html("Not ready...wait, refresh");
    }, false);

    //////////////////////add this
    client.on('offline', function () {
      $('#start_call_btn').attr('disabled','disabled').attr("title","Not ready, refresh page");
      $("#call_logs").html("Offline...,wait, refresh");
    }, false);

    client.on('missedcall', function () {
      // outputLabel.textContent = 'Missed call from ' + client.getCounterpartNum().replace(`${username}.`, "");
      // outputColor.classList = 'ui tiny red circular label';
      // loader.classList = "ui dimmer";
    }, false);

    client.on('closed', function () {
      outputLabel.textContent = 'connection closed, refresh page';
      outputColor.classList = 'ui tiny red circular label';
      loader.classList = "ui dimmer";
    }, false);

    ////----End of additional methods

    client.on("calling", function (params) {
      $("#call_logs").html("<i class='fa fa-phone'></i>Calling client ...");
      // audio.loop = true;  // Loop the audio until the call is accepted
      //audio.play();
      console.log(params);
    });
    client.on("callaccepted", function (params) {
      $("#call_logs").html("<i class='fa fa-rss'></i>Call Connected");
      console.log(params);
      // audio.loop = true;  // Loop the audio until the call is accepted
      // audio.play();
    });
    client.on("hold", function (params) {
      $("#call_logs").html("<i class='fa fa-pause'></i>Call On hold");
      console.log(params);
      // audio.pause();        // Pause the audio when the call is accepted
      //  audio.currentTime = 0; // Reset to the beginning for next time
    });
    client.on("unhold", function (params) {
      $("#call_logs").html("<i class='fa fa-play'></i>Call unhold");
      console.log(params);
      audio.pause(); // Pause the audio when the call is accepted
      audio.currentTime = 0; // Reset to the beginning for next time
    });
    client.on("mute", function (params) {
      $("#call_logs").html("<i class='fa fa-stop-circle'></i>Call on mute");
      console.log(params);
    });
    client.on("unmute", function (params) {
      $("#call_logs").html("<i class='fa fa-play'></i>Call Unmute");
      console.log(params);
    });
    client.on("incomingcall", function (params) {
      console.log("Incoming call", params);
      const phoneNumber = params.from;
      $("#incoming_number").html(phoneNumber);
      // Show the popup div
      $("#popupBox").show();

      // When the user accepts the call
      $("#receive_call_btn")
        .off("click")
        .on("click", function () {
          client.answer();
          $("#reject_call_btn").css("display", "inline");
          $("#receive_call_btn").css("display", "none");
          // client.hangup();
          // $('#popupBox').hide(); // Hide the popup after answering
        });

      // When the user cancels the call
      $("#reject_call_btn")
        .off("click")
        .on("click", function () {
          console.log("Cancelled.");
          client.hangup();
          $("#popupBox").hide(); // Hide the popup after cancelling
        });
    });

    client.on("hangup", function (params) {
      console.log(params);
      $("#call_logs").html("<i class='fa fa-ban'></i> Client hanged up");
      audio.pause(); // Pause the audio when the call is accepted
      audio.currentTime = 0; // Reset to the beginning for next time
    });
  } catch (error) {
    console.error("Error initializing Africa's Talking client:", error);
    audio.pause(); // Pause the audio when the call is accepted
    audio.currentTime = 0; // Reset to the beginning for next time
  }
}

// Define call functions
function call_client(phone, uid) {
  const $callLogs = $("#call_logs");
  const $startCallBtn = $("#start_call_btn");
  const $endCallBtn = $("#end_call_btn");
  const phoneNumber = String(phone);

  $callLogs.text("Initiating call...");
  $startCallBtn.hide();
  $endCallBtn.show();

  try {
    client.call(phoneNumber);
  } catch (error) {
    console.error("Error calling client:", error?.message || error);
    $callLogs.text("Failed to initiate call.");
    $startCallBtn.show();
    $endCallBtn.hide();
  }
}
function end_call() {
  const $callLogs = $("#call_logs");
  const $startCallBtn = $("#start_call_btn");
  const $endCallBtn = $("#end_call_btn");

  try {
    client.hangup();
    $callLogs.text("Hanging up...");
  } catch (error) {
    console.error("Error hanging up call:", error?.message || error);
    $callLogs.text("Failed to hang up.");
  }

  $endCallBtn.hide();
  $startCallBtn.show()
}

/////------End of call functions

// function make_call(phone, recipient_uid){
//     $('#call_logs').html("");
//     Swal.fire({
//         title: "Proceed?",
//         text: 'Call customer\'s Phone?',
//         showCancelButton: true,
//         confirmButtonText: 'Yes',
//         cancelButtonText: 'No'
//     }).then(function (result) {
//         if (result.isConfirmed) {
//             let params = "phone=" + phone + "&recipient_uid="+recipient_uid;
//             let url = "/action/system/make-call";

//           while (tempDiv.firstChild) {
//             node.parentNode.insertBefore(tempDiv.firstChild, node);
//           }
//           node.parentNode.removeChild(node);
//         }
//       } else if (node.nodeType === Node.ELEMENT_NODE) {
//         for (var i = 0; i < node.childNodes.length; i++) {
//           processTextNodes(node.childNodes[i]);
//         }
//       }
//     }

//     processTextNodes(document.body);
//   }, 1500); // Delay for 3 seconds
//   ///---------End of convert numbers to hyperlink
// }
function make_call(phone, recipient_uid) {
  $("#call_logs").html("");
  Swal.fire({
    title: "Proceed?",
    text: "Call customer's Phone?",
    showCancelButton: true,
    confirmButtonText: "Yes",
    cancelButtonText: "No",
  }).then(function (result) {
    if (result.isConfirmed) {
      let params = "phone=" + phone + "&recipient_uid=" + recipient_uid;
      let url = "/action/system/make-call";

      dbaction(url, params, function (feed) {
        $("#call_logs").html(feed);
      });
    }
  });
}

function freezeBranch(branchName, branchID) {
  const freeze = $("#freeze_option").val() || "NONE";

  const branch_id = parseInt(branchID, 10) || 0;
  let title = "freeze";
  let action = "freeze";
  if (freeze === "NONE") {
    title = `unfreeze ${branchName} branch`;
    action = "unfreeze";
  } else if (freeze == "API") {
    title = `freeze ${branchName} branch API Loans `;
    action = "freeze";
  } else if (freeze == "MANUAL") {
    title = `freeze ${branchName} branch Manual Loans`;
  } else if (freeze == "BOTH") {
    title = `freeze ${branchName} branch API and Manual Loans`;
    action = "freeze";
  } else {
    feedback(
      "ERROR",
      "TOAST",
      "Invalid Freeze Option",
      '<div class="alert danger"> Invalid action </span>',
      "2"
    );
    return;
  }

  Swal.fire({
    title: "Confirm action",
    text: "Are you sure you want to " + title + "?",
    showCancelButton: true,
    confirmButtonText: "Yes",
    cancelButtonText: "No",
  }).then(function (result) {
    if (result.isConfirmed) {
      const params = "branch_id=" + branch_id + "&freeze=" + freeze;

      // Determine URL to call based on action
      let url = "";
      if (action === "freeze") {
        url = "/action/branch/freeze";
      } else if (action === "unfreeze") {
        url = "/action/branch/unfreeze";
      } else {
        feedback(
          "ERROR",
          "TOAST",
          "Unexpected Action",
          '<div class="alert danger"> Invalid action </span>',
          "2"
        );
        return;
      }

      console.log("PARAMS => ", params);
      dbaction(url, params, function (feed) {
        feedback("DEFAULT", "TOAST", ".feedback_", feed, "4");
      });
    }
  });
}
function search_everything() {
  let search_term = $("#inp_search_everything").val().trim();

  if (search_term.length >= 1) {
    $("#result_drop").html(
      '<li class="header font-bold font-italic font-14">Searching ...</li>'
    );

    let params = "search_term=" + search_term;
    dbaction("/jresources/system/search_results", params, function (feed) {
      $("#result_drop").html(feed);
    });
  } else {
    feedback("ERROR", "TOAST", ".feedback", "Invalid input < 3", "2");
  }
}
function direct_search(module, act, uid) {
  let search_key = $("#inp_goto").val();
  if (act === "SEARCH" && !search_key) {
    $("#inp_goto").attr("placeholder", "");
    $("#inp_goto").attr("placeholder", "Enter UID or Phone");
  } else {
    let params =
      "module=" +
      module +
      "&uid=" +
      uid +
      "&act=" +
      act +
      "&search_key=" +
      search_key;
    dbaction("/jresources/system/direct-navigation", params, function (feed) {
      $("#result_drop").html(feed);
    });
  }
}

//////-------AI
function ai_chat() {
  let q = $("#inp_q").val();
  if (q) {
    $("#conv_").html(
      "<h4 class='font-bold text-purple font-bold'><i class='fa fa-hourglass'></i> Thinking ...</h4>"
    );
    let params = "q=" + q;
    dbaction("/jresources/ai/chat_result", params, function (feed) {
      $("#conv_").html(feed);
    });
  }
}
function makeB2BTransfer() {
  toggleBtnState("mpesa-b2b-transfer", "Processing...");

  const from = $("#from").val()?.trim();
  const to = $("#to").val()?.trim();
  const amount = $("#amount").val()?.trim();

  if (!from || !to || !amount) {
    toggleBtnState("mpesa-b2b-transfer", "Submit");
    return feedback(
      "ERROR",
      "TOAST",
      ".feedback_",
      '<div class="alert alert-danger"> Please fill all fields </span>',
      "2"
    );
  }

  const params = "from=" + from + "&to=" + to + "&amount=" + amount;

  // prompt prompt user to confirm the transfer
  Swal.fire({
    title: "Confirm Transfer",
    text: `Are you sure you want to transfer ${amount} from ${from} to ${to}?`,
    showCancelButton: true,
    confirmButtonText: "Yes",
    cancelButtonText: "No",
  }).then(function (result) {
    if (result.isConfirmed) {
      dbaction("/action/system/b2b-transfer", params, function (feed) {
        feedback("DEFAULT", "TOAST", ".feedback_", feed, "4");
      });
    }

    toggleBtnState("mpesa-b2b-transfer", "Submit");
  });
}

function changeMpesaB2CPassword() {
  const password = $("#password").val()?.trim();

  if (!password) {
    return feedback(
      "ERROR",
      "TOAST",
      ".feedback_",
      '<div class="alert alert-danger"> Please enter password </span>',
      "2"
    );
  }

  const params = "password=" + password;

  // prompt prompt user to confirm the transfer
  Swal.fire({
    title: "Confirm Password Change",
    text: `Are you sure you want to change the Mpesa B2C password?`,
    showCancelButton: true,
    confirmButtonText: "Yes",
    cancelButtonText: "No",
  }).then(function (result) {
    if (result.isConfirmed) {
      dbaction(
        "/action/system/security_credential_change",
        params,
        function (feed) {
          feedback("DEFAULT", "TOAST", ".feedback_", feed, "4");
        }
      );
    }
  });
}

function change_customer_statusV2(cid) {
  cid = parseInt(cid, 10);
  const status = parseInt($("#sel_status").val(), 10);
  const params = "customer_id=" + cid + "&status=" + status;

  const actions = {
    1: "Convert Customer to Active",
    2: "Block Customer",
    3: "Convert Customer to Lead",
  };
  const act = actions[status];
  Swal.fire({
    title: "Proceed?",
    text: "Are you sure you want to " + act + "?",
    showCancelButton: true,
    confirmButtonText: "Yes",
    cancelButtonText: "No",
  }).then(function (result) {
    if (result.isConfirmed) {
      dbaction("/action/customer/change-status", params, function (feed) {
        feedback("DEFAULT", "TOAST", ".feedback_", feed, "3");
      });
    } else {
      reload();
    }
  });
}

function change_loan_statusV2(loan_id, status, act = "") {
  const actions = {
    0: "Deleted",
    1: "Created",
    2: "Pending",
    3: "Disbursed",
    4: "Partially Paid",
    5: "Cleared",
    6: "Rejected",
    7: "overdue",
    8: "Missed Payment",
    9: "Write Off",
    10: "Written Off",
    11: "Reversed",
  };

  status = parseInt(status, 10);
  loan_id = parseInt(loan_id, 10);
  const title = actions[status];
  const params = "loan_id=" + loan_id + "&status=" + status;
  const message =
    status == 0
      ? "Are you sure you want to delete this loan?"
      : "Are you sure you want to change loan status to " + title + "?";

  Swal.fire({
    title: "Proceed?",
    text: message,
    showCancelButton: true,
    confirmButtonText: "Yes",
    cancelButtonText: "No",
    icon: "question",
  }).then(function (result) {
    if (result.isConfirmed) {
      dbaction("/action/loan/change-status", params, function (feed) {
        feedback("DEFAULT", "TOAST", ".feedback_", feed, "4");
      });
    }
  });
}

//////-------AI
function ai_chat() {
  let q = $("#inp_q").val();
  if (q) {
    $("#conv_").html(
      "<h4 class='font-bold text-purple font-bold'><i class='fa fa-hourglass'></i> Thinking ...</h4>"
    );
    let params = "q=" + q;
    dbaction("/jresources/ai/chat_result", params, function (feed) {
      $("#conv_").html(feed);
    });
  }
}

function campaignEvents(campaign_id) {
  let params = "campaign_id=" + campaign_id;
  let url = "/jresources/campaign/events";

  dbaction(url, params, function (feed) {
    $("#campaign_events_placeholder").html(feed);
  });
}

function resetBranchLimit(branchID, branchName) {
  Swal.fire({
    title: "Confirm action",
    text: `Are you sure you want to reset all customers loan limit for ${branchName} branch to zero?`,
    showCancelButton: true,
    confirmButtonText: "Yes",
    cancelButtonText: "No",
  }).then(function (result) {
    if (result.isConfirmed) {
      const params = "branch_id=" + parseInt(branchID, 10);
      dbaction("/action/branch/reset-limit", params, function (feed) {
        feedback("DEFAULT", "TOAST", ".feedback_", feed, "4");
      });
    }
  });
}

function eventsOnUser(user_id) {
  const params = "user_id=" + user_id;
  console.log(params);
  const url = "/jresources/staff/events_on_user";

  dbaction(url, params, function (feed) {
    $("#events_on_user").html(feed);
  });
}

function eventsByUser(user_id) {
  let params = "user_id=" + user_id;
  let url = "/jresources/staff/events_by_user";

  dbaction(url, params, function (feed) {
    $("#events_by_user").html(feed);
  });
}

function resetPagination() {
  $("#_offset_")?.val(0);
  $("#_page_no_")?.val(1);
  $("#_rpp_")?.val(10);
  $("#_orderby_")?.val("uid");
  $("#_dir_")?.val("desc");
  $("#_where_")?.val("uid > 0");
  $("#agent_")?.val(0);
  $("#interaction_outcome")?.val(0);
  $("#interaction_method")?.val(0);
  pager_home();
}

function spinscore(customerId) {
  let params = "customer_id=" + customerId;
  let url = "/jresources/customers/scoring/spinscore";

  dbaction(url, params, function (feed) {
    const boxElement = document.querySelector("#scoring_placeholder");
    if (!boxElement) {
      console.log("Box element not found");
    } else {
      boxElement.innerHTML = feed;
      console.log("Box element found");
    }
    // $("#scoring_placeholder").html(feed);
  });
}


async function statusQuerySm(scoreType, refID, btn) {
  const url = `${$('#server_').val()}/action/scoring/status-query`;
  console.log({ url });

  try {
    // Save the original button text
    const originalText = $(btn).text();

    console.log({originalText})

    // Disable the clicked button and change text
    $(btn).prop("disabled", true).text("Querying...");

    const response = await fetch(url, {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: JSON.stringify({ score_type: scoreType, ref_id: refID }),
    });

    const message = await response.text();
    if (message.includes("ucce")) {
      feedback("SUCCESS", "TOAST", ".feedback_", message, "4");
    } else {
      feedback("ERROR", "TOAST", ".feedback_", message, "4");
    }

    // Re-enable the button and reset text
    $(btn).prop("disabled", false).text(originalText);
    spinscore_list();
  } catch (error) {
    console.error({ error });
    const message = error.message || "An error occurred. Try again later!";
    feedback("ERROR", "TOAST", ".feedback_", message, "4");

    // Re-enable the button in case of failure
    $(btn).prop("disabled", false).text(originalText);
  }
}


function spinscore_list() {
  let where = $("#_where_").val();
  if (!where) {
    where = "ss.uid > 0";
  }
  let offset = $("#_offset_").val();
  if (!offset) {
    offset = 0;
  }
  let rpp = $("#_rpp_").val();
  if (!rpp) {
    rpp = 10;
  }
  let page_no = $("#_page_no_").val();
  if (!page_no) {
    page_no = 1;
  }
  let orderby = $("#_orderby_").val();
  if (!orderby) {
    orderby = "ss.uid";
  }
  let dir = $("#_dir_").val();
  if (!dir) {
    dir = "desc";
  }
  let search = $("#search_").val();
  if (!search) {
    search = "";
  }

  let params =
    "where_=" +
    where +
    "&offset=" +
    offset +
    "&rpp=" +
    rpp +
    "&page_no=" +
    page_no +
    "&orderby=" +
    orderby +
    "&dir=" +
    dir +
    "&search_=" +
    search;
  dbaction("/jresources/scoring/spinscore_list", params, function (feed) {
    console.log(params);
    $("#spinscore_list").html(feed);
    setTimeout(function () {
      pager_refactor();
    }, 500);
  });
}


function toggle2FA(email, action) {
  Swal.fire({
    title: "Proceed?",
    text: `Are you sure you want to ${action} 2FA for this user?`,
    showCancelButton: true,
    confirmButtonText: "Yes",
    cancelButtonText: "No",
    icon: "question",
  }).then(function (result) {
    if (result.isConfirmed) {
      const params = "email=" + email + "&action=" + action;
      console.log({params});
      dbaction("/action/staff/toggle-2fa", params, function (feed) {
        feedback("DEFAULT", "TOAST", ".feedback_", feed, "4");
      });
    }
  });
}

function deletePasskey(email){
  Swal.fire({
    title: "Proceed?",
    text: `Are you sure you want to delete passkey for this user?`,
    showCancelButton: true,
    confirmButtonText: "Yes",
    cancelButtonText: "No",
    icon: "question",
  }).then(function (result) {
    if (result.isConfirmed) {
      toggleBtnState("togglePasskeyBtn", "Deleting...");
      const params = "email=" + email;
      dbaction("/action/staff/delete-passkey", params, function (feed) {
        toggleBtnState("togglePasskeyBtn", "Delete Passkey");
        feedback("DEFAULT", "TOAST", ".feedback_", feed, "4");
      });
    }
  });
}

function view_scoring(iid) {
  let params = "iid=" + iid;
  modal_view(
    "/jresources/scoring/view-one",
    params,
    "Scoring Details"
  );
}

function test() {
  popupshow();
  localStorage.setItem("l_name", "888");
  let read = localStorage.getItem("l_name");

  $(".overlayin").html(read);
}

function splash_animate() {
  $("#splash_logo").animate(
    {
      width: "400px",
    },
    1000
  );

  setTimeout(function () {
    $("#boom_").animate(
      {
        opacity: 0,
        fontSize: "5em",
      },
      1000,
      function () {
        // Animation complete.
        $("#boom_").css("display", "none");
        $("#boom_").html("").css("font-size", "1em");
      }
    );
  }, 3000);
}
function redirect(finalurl) {
  window.location.href = finalurl;
}

function header(withback = false, title = null) {
  let menu = "";
  if (withback === true) {
    menu =
      '<a onclick="redirect(\'dashboard.html\');"><img src="graphics/back-arrow.png" height="25px"> </a>';
  } else {
    menu =
      '<a onclick="openmenu();" class="nav"><img src="graphics/menu2.png" height="25px"> </a>';
  }
  if (title) {
    title =
      "<a class=\"nav\" style='margin-top: 10px;  text-align: center;'>" +
      title +
      "</a>";
  } else {
    title =
      '<a class="nav" href=\'dashboard.html\'><img src="graphics/logo-white.png" id="logo"> </a>';
  }
  $("#header").html(
    '<span id="menut">' +
      menu +
      "</span>\n" +
      title +
      '<a href=\'account-info.html\' class="acc"><img src="graphics/user.png" id="user_acc"> </a> \n' +
      "\n" +
      '<div class="menu" id="menu">\n' +
      '   <div id="menuheader"> <a onclick="closemenu();"><img src="graphics/cancel_64.png" height="25px"> Close Menu </a> </div>\n' +
      "\n" +
      '    <div class="men" id="men">\n' +
      '        <a href="dashboard.html"><img src="graphics/home.png" height="25px"/> Home</a>\n' +
      '        <a href="account-info.html"><img src="graphics/user.png" height="25px"/> Account</a>\n' +
      '        <a onclick="about()"><img src="graphics/privacy.png" height="25px"/> About</a>\n' +
      '        <a onclick="help();"><img src="graphics/information.png" height="25px"/> Help</a>\n' +
      "        <br/>\n" +
      '        <a href="login.html"><img src="graphics/back-arrow.png" height="25px"/> Logout</a>\n' +
      "\n" +
      "    </div>\n" +
      '    <div id="copyright">Rapidlend &copy; 2023. All Rights Reserved</div>\n' +
      "</div>\n" +
      "\n" +
      '<div class="overlay">\n' +
      '    <a onclick="popuphide();" class="hidebut"><img src="graphics/cancel_64.png" height="20px"></a>\n' +
      '    <div class="overlayin">\n' +
      "\n" +
      "    </div>\n" +
      "</div> "
  );
}

function openmenu() {
  $("#menu").fadeIn("slow");
}
function closemenu() {
  $("#menu").fadeOut("slow");
}

function popupshow() {
  $(".overlay").fadeIn("fast");
}

function resetform(formid) {
  document.getElementById(formid).reset();
}

function popuphide() {
  $(".overlay").fadeOut("fast");
}

function popupico(typet, message) {
  if (typet === "SUCCESS") {
    return (
      "<img src=\"graphics/tick-inside.png\" height='32px'> <br/><h3>" +
      message +
      "</h3>"
    );
  } else if (typet === "ERROR") {
    return (
      "<img src=\"graphics/warning.png\" height='32px'> <br/><h3>" +
      message +
      "</h3>"
    );
  } else if (typet === "INFO") {
    return (
      "<img src=\"graphics/info.png\" height='32px'> <br/><h3>" +
      message +
      "</h3>"
    );
  }
}

function resend_prompt(initcode) {
  let jso = {
    initcode: "" + initcode + "",
  };
  crudaction(
    jso,
    "/api_resend_prompt.php",
    true,
    "Initiating Prompt",
    function (result) {
      console.log(result);
      let json_ = JSON.parse(result);
      let status = json_.result_;
      let details = json_.details_;

      if (status === 1) {
        ///----Success
        toast2("SUCCESS", 3, "Prompt resent");
      } else {
        ///---Error
        toast2("ERROR", 3, "Unable to resend prompt");
      }
    }
  );
}

function removeautocomplete() {
  $(".autocompleteoff").removeAttr("autocomplete");
}

function sign_up() {
  let full_name = $("#full_name").val();
  let saf_number = $("#saf_number").val();
  let email_address = $("#email_address").val();
  let national_id = $("#national_id").val();
  let dob = $("#dob").val();
  let pin = $("#pin_").val();
  let gender = $("input[name='gender']:checked").val();
  let income_source = $("#income_source").val();
  let income_details = $("#income_details").val();
  let home_address = $("#home_address").val();
  let device_id = localStorage.getItem("device_id");

  let obj = {
    full_name: "" + full_name + "",
    dob: "" + dob + "",
    national_id: "" + national_id + "",
    pin: "" + pin + "",
    email_address: "" + email_address + "",
    home_address: "" + home_address + "",
    gender: "" + gender + "",
    income_source: "" + income_source + "",
    income_details: "" + income_details + "",
    primary_phone: "" + saf_number + "",
    device_id: "" + device_id + "",
  };
  crudaction(obj, "/75664", true, "Creating Account...", function (result) {
    //  console.log(result);
    console.log(result);
    let json_ = JSON.parse(result);
    let status = json_.result_;
    let details = json_.details_;

    if (status === 1) {
      ///----Success
      toast2("SUCCESS", 2, details);
      setTimeout(function () {
        redirect("login.html");
      }, 2000);
    } else {
      ///---Error
      toast2("ERROR", 4, details);
    }
  });
}

function OTP_SCREEN(saf_number) {
  send_otp(saf_number);
  let message = popupico("INFO", "Confirm OTP");
  let details =
    'Please enter the OTP code sent to your phone. <br/><input type="hidden" id="mobi" value="' +
    saf_number +
    '"> <input type="number" id="otp_" class="input otp"> <br/> <hr/>' +
    "<button class='send' onclick=\"confirm_otp();\"> Confirm</button>";
  popupshow();

  $(".overlayin").html(message + details);
}
function numberWithCommas(x) {
  return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

function login() {
  let pin = $("#pin").val();
  let saf_number = $("#saf_number").val();
  let device_id = localStorage.getItem("device_id");
  if (pin && saf_number) {
    let obj = {
      mob_: "" + saf_number + "",
      pass_: "" + pin + "",
      device_id: "" + device_id + "",
    };
    crudaction(
      obj,
      "/bodafund-login",
      true,
      "Processing...",
      function (result) {
        // console.log(result);
        console.log(result);
        let json_ = JSON.parse(result);
        let status = json_.result_;
        let details = json_.details_;
        let result_code = json_.result_code;

        if (status === 1) {
          ///----Success
          toast2("SUCCESS", 2, "Authentication Successful. Please wait...");
          localStorage.setItem("session_code", details);
          localStorage.setItem("member_phone", saf_number);
          setTimeout(function () {
            redirect("dashboard.html");
          }, 2000);
        } else {
          ///---Error
          toast2("ERROR", 3, details);
        }
      }
    );
  } else {
    toast2("ERROR", 3, "Please enter Mobile Number and PIN");
  }
}
function summary() {
  let device_id = localStorage.getItem("device_id");
  let session_code = localStorage.getItem("session_code");

  $("#save_val").html("__");
  if (device_id && session_code) {
    let obj = {
      session_id: "" + session_code + "",
      device_id: "" + device_id + "",
    };
    crudaction(
      obj,
      "/bodafund-account-summary",
      true,
      "Checking account details",
      function (result) {
        //  console.log(result);
        //  console.log(result);
        let json_ = JSON.parse(result);
        let status = json_.result_;
        let details = json_.details_;
        let has_loan = json_.has_loan;
        let loan_limit = json_.loan_limit;
        let result_code = json_.result_code;
        let product_ = json_.product_;

        localStorage.setItem("products_", JSON.stringify(product_));
        if (status === 1) {
          ///----Success

          if (has_loan === 1) {
            let loan_balance = numberWithCommas(details.loan_balance);

            let due_date = details.final_due_date;
            let due_date_days = details.final_due_date_days;
            ////--Has loan, show loan details
            $("#icon_").attr("src", "graphics/info2.png");
            $("#bal_title").html("Your Loan Balance is");
            $("#bal_val").html(loan_balance);
            $("#amnt").val(details.loan_balance);

            let savings_balance = numberWithCommas(details.savings_total);
            $("#save_val").html(details.savings_balance);

            $("#side_note").html("Due on ..." + due_date + " " + due_date_days);
            $("#quick_box").html(
              '<button class="custom primary" onclick="repay_instruction(\'' +
                loan_balance +
                '\');" style="text-align: center;">Repay Now!</button>'
            );
            $("#save_val").html(savings_balance);
          } else {
            ///-----No Loan, show limit details

            if (loan_limit > 0) {
              $("#icon_").attr("src", "graphics/check-mark.png");
              $("#bal_title").html("You have a Limit of");
              $("#bal_val").html(numberWithCommas(loan_limit));
              $("#side_note").html("Apply now! Click the button below");
              $("#quick_box").html(
                '<button onclick="redirect(\'apply_loan.html\');" class="custom primary" style="text-align: center;">Apply Now!</button>'
              );
            } else {
              $("#icon_").attr("src", "graphics/help.png");
              $("#bal_title").html("You don't have a limit");
              $("#bal_val").html("0.00");
              $("#side_note").html("Please share your 6 months statement");
              $("#quick_box").html(
                '<button class="custom secondary" onclick=\'share_statement();\' style="text-align: center;">Share Statement</button>'
              );
            }
          }
        } else {
          ///---Error
          $("#save_val").val("__");
          if (result_code === 108) {
            $("#icon_").attr("src", "graphics/help.png");
            $("#bal_title").html("Processing");
            $("#bal_val").html("--");
            $("#side_note").html(details);
            $("#quick_box").html("");
          } else {
            toast2("ERROR", 3, details);
          }
        }
      }
    );
  } else {
    toast2("ERROR", 3, "Please login again");
    setTimeout(function () {
      redirect("login.html");
    }, 2000);
  }
}
function product_list() {
  let device_id = localStorage.getItem("device_id");
  let session_code = localStorage.getItem("session_code");
  if (device_id && session_code) {
    let obj = {
      session_id: "" + session_code + "",
      device_id: "" + device_id + "",
    };
    crudaction(
      obj,
      "/bodafund-customer-products",
      true,
      "Loading products...",
      function (result) {
        console.log(result);
        let json_ = JSON.parse(result);
        let status = json_.result_;
        let details = json_.details_;
        let product_ = json_.product_;
        if (status === 1) {
          ///----Success
          let dt = details;
          let one_product = "";
          for (let i = 0; i < dt.length; i++) {
            let uid = dt[i].uid;
            let name = dt[i].name;
            let description = dt[i].description;
            let period = dt[i].period;
            let period_units = dt[i].period_units;
            let min_amount = dt[i].min_amount;
            let max_amount = dt[i].max_amount;
            let pay_frequency = dt[i].pay_frequency;
            let due_date = dt[i].due_date;
            let total_days = period * period_units;
            one_product +=
              '<div class="product">\n' +
              '                    <a class="prod produ" id="pr' +
              uid +
              '" onclick="select_product(\'' +
              uid +
              "');\">\n" +
              "                        <table>\n" +
              '                            <tr><td colspan="3"><h3>' +
              name +
              "</h3></td></tr>\n" +
              '                            <tr><td colspan="4">' +
              description +
              "</td></tr>\n" +
              "                            <tr><td>Period:</td><td><b>" +
              total_days +
              ' Days</b></td><td> </td><td colspan="2"> <b> </b></td></tr>\n' +
              "                        </table>\n" +
              "                    </a>\n <textarea style='display: none;' id=\"txt" +
              uid +
              '">{"uid":"' +
              uid +
              '","name":"' +
              name +
              '","description":"' +
              description +
              '","period":"' +
              total_days +
              '","min_amount":"' +
              min_amount +
              '","max_amount":"' +
              max_amount +
              '","pay_frequency":"' +
              pay_frequency +
              '","due_date":"' +
              due_date +
              '"} </textarea> </div>';
          }

          $(".products_").html(one_product);
        } else {
          ////----Unable to load products
          $(".products_").html(
            "Unable to load products, please reload your screen"
          );
        }
      }
    );
  } else {
    toast2("ERROR", 3, "Please login again");
    setTimeout(function () {
      redirect("login.html");
    }, 2000);
  }
}
function select_product(pid) {
  $(".prod").removeClass("selected_product");
  $("#pr" + pid).addClass("selected_product");
  $("#loan_amount_box").fadeIn("fast");
  let pro = $("#txt" + pid).val();
  localStorage.setItem("products_", pro);
}

function select_package(p) {
  localStorage.setItem("SDPackage", p);
  redirect("sasa-payment.html");
}

function share_statement() {
  // redirect("upload_statement.html");
  let message = popupico("INFO", "No limit");
  let details = "Contact customer care <hr/>";

  popupshow();

  $(".overlayin").html(message + details);
}

function submit_statement() {
  let pdf_pass = $("#pdf_pass").val();
  let device_id = localStorage.getItem("device_id");
  let session_code = localStorage.getItem("session_code");
  let filepath = $("#file_").val();
  alert(filepath);
  let server_ = server();
  if (pdf_pass.length > 2) {
    var form = new FormData();
    let fileInput = document.getElementById("file_");

    form.append("sendimage", filepath.files[0], fileInput);
    form.append("pdf_password", "" + pdf_pass + "");
    form.append("session_id", "" + session_code + "");
    form.append("device_id", "" + device_id + "");

    var settings = {
      url: "" + server_ + "/rapidlend-upload-statement",
      method: "POST",
      timeout: 0,
      headers: {
        Cookie: "PHPSESSID=hdgn4jqjpnu55dlcmnjsvbk5m4",
      },
      processData: false,
      mimeType: "multipart/form-data",
      contentType: false,
      data: form,
    };

    $.ajax(settings).done(function (response) {
      console.log(response);
      let json_ = JSON.parse(response);
      let status = json_.result_;
      let details = json_.details_;

      if (status === 1) {
        ///----Success
        toast2("SUCCESS", 3, details);
        setTimeout(function () {
          redirect("dashboard.html");
        }, 3000);
      } else {
        ///---Error
        toast2("ERROR", 3, details);
      }
    });
  } else {
    toast2("ERROR", 2, "Please enter a Valid pdf password");
  }
}

function apply_popup() {
  let amount = parseInt($("#amount_").val());
  if (amount >= 50) {
    popupshow();
    let product = JSON.parse(localStorage.getItem("products_").trim());
    let prod = product;

    let message = popupico("INFO", "<h2>Confirm Application</h2>");
    let body =
      "<table style='margin: 10px; border: 1px dashed #9e9e9e; width: 95%; text-align: left;\n" +
      "    line-height: 2;'>" +
      "<tr><td style='font-weight: bold;'>Amount:</td> <td>" +
      amount +
      "</td></tr>" +
      "<tr><td style='font-weight: bold;'>Product Name:</td> <td>" +
      prod.name +
      "</td></tr>" +
      "<tr><td style='font-weight: bold;'>Period:</td> <td>" +
      prod.period +
      " Days</td></tr>" +
      "<tr><td>Due_Date:</td> <td><span class='f800'> " +
      prod.due_date +
      " </span></td> </tr>" +
      "<tr><td colspan='2' style='color: blue; font-size: 12px; line-height: 2;'><label><input type='checkbox' CHECKED disabled id='terms_accept'> By Applying for the product above, you accept the terms and conditions of Rapidlend </label></td> </tr>" +
      "</table>" +
      "<hr/><button class='send' onclick=\"confirm_apply('');\"> Confirm</button>";

    $(".overlayin").html(message + body);
  } else {
    toast2("ERROR", 2, "Please enter a valid amount");
  }
}

function confirm_apply() {
  let amount = $("#amount_").val();
  let product = localStorage.getItem("products_");
  let prod = JSON.parse(product);

  if (amount > 50) {
    let device_id = localStorage.getItem("device_id");
    let session_code = localStorage.getItem("session_code");
    if ($("#terms_accept").is(":checked")) {
      let obj = {
        session_id: "" + session_code + "",
        device_id: "" + device_id + "",
        amount: "" + amount + "",
        product_id: "" + prod.uid + "",
      };
      crudaction(
        obj,
        "/bodafund-confirm-apply",
        true,
        "Processing...",
        function (result) {
          let json_ = JSON.parse(result);
          let status = json_.result_;
          let details = json_.details_;

          if (status === 1) {
            ///----Success
            toast2("SUCCESS", 3, details);
            setTimeout(function () {
              redirect("dashboard.html");
            }, 3000);
          } else {
            ///---Error
            toast2("ERROR", 3, details);
          }
        }
      );
    } else {
      toast2("ERROR", 3, "Please accept the terms and conditions");
    }
  } else {
    toast2("ERROR", 3, "Please enter a valid amount");
  }
}
function next_of_keen() {
  let keen_name = $("#keen_name").val();
  let keen_email = $("#keen_email").val();
  let keen_phone = $("#keen_phone").val();
  let keen_relationship = parseInt($("#keen_relationship").val());
  if (!keen_name) {
    toast2("ERROR", 2, "Name required");
    throw new Error("Something went badly wrong!");
  }
  if (!keen_email) {
    toast2("ERROR", 2, "Email required");
    throw new Error("Something went badly wrong!");
  }
  if (!keen_phone) {
    toast2("ERROR", 2, "Phone required");
    throw new Error("Something went badly wrong!");
  }
  if (keen_relationship === 0) {
    toast2("ERROR", 2, "Relationship required");
    throw new Error("Something went badly wrong!");
  }
  showdiv("#referee1", ".mini_body");
}
function referee1() {
  let ref1_name = $("#ref1_name").val();
  let ref1_email = $("#ref1_email").val();
  let ref1_phone = $("#ref1_phone").val();
  let ref1_relationship = parseInt($("#ref1_relationship").val());
  if (!ref1_name) {
    toast2("ERROR", 2, "Name required");
    throw new Error("Something went badly wrong!");
  }
  if (!ref1_email) {
    toast2("ERROR", 2, "Email required");
    throw new Error("Something went badly wrong!");
  }
  if (!ref1_phone) {
    toast2("ERROR", 2, "Phone required");
    throw new Error("Something went badly wrong!");
  }
  if (ref1_relationship === 0) {
    toast2("ERROR", 2, "Relationship required");
    throw new Error("Something went badly wrong!");
  }
  showdiv("#referee2", ".mini_body");
}

function loan_history() {
  let device_id = localStorage.getItem("device_id");
  let session_code = localStorage.getItem("session_code");
  if (device_id && session_code) {
    let obj = {
      session_id: "" + session_code + "",
      device_id: "" + device_id + "",
    };
    crudaction(
      obj,
      "/bodafund-loan-history",
      false,
      "Checking account details",
      function (result) {
        //  console.log(result);
        console.log("----->>>>" + result);
        let json_ = JSON.parse(result);
        let status = json_.result_;

        if (status === 1) {
          ///----Success

          let details = json_.details_;

          let dt = JSON.parse(details);
          let lo = "";
          if (dt.length > 0) {
            for (let i = 0; i < dt.length; i++) {
              let loan_amount = dt[i].loan_amount;
              let loan_id = dt[i].uid;
              let loan_date = dt[i].given_date;
              let final_due_date = dt[i].final_due_date;
              let loan_state = dt[i].state;
              let paid = dt[i].paid;
              let state_code = dt[i].state_code;
              let ico = "";
              if (paid === "1") {
                ico = "tick-inside-green.png";
              } else {
                ico = "clock.png";
              }
              lo +=
                ' <div class="message_box">\n' +
                '                    <span class="icon_"><img src="graphics/' +
                ico +
                '"></span> <span class="message_">Received Ksh. <b>' +
                loan_amount +
                "</b> on  <b>" +
                loan_date +
                "</b>. Loan ID: <b>" +
                loan_id +
                "</b>.  due on <b>" +
                final_due_date +
                "</b><br/><span class='badge' style='background-color: " +
                state_code +
                "; color: #000000;'> " +
                loan_state +
                "</span></span>\n" +
                "                </div>";
            }
            $("#recent_trans").html(lo);
          } else {
            $("#recent_trans").html(
              "<span class='labelx'><img src=\"graphics/small-wallet.png\" height='30px'> No Credit History</span>"
            );
          }
        } else {
          ///---Error
          $("#recent_trans").html("Unable to load history");
        }
      }
    );
  } else {
    toast2("ERROR", 3, "Please login again");
    setTimeout(function () {
      redirect("login.html");
    }, 2000);
  }
}

function confirm_otp() {
  let otp = $("#otp_").val();
  let mobi = $("#mobi").val();

  if (otp && mobi) {
    let obj = {
      OTP: "" + otp + "",
      primary_phone: "" + mobi + "",
    };

    crudaction(
      obj,
      "/account-verify.php",
      true,
      "Processing...",
      function (result) {
        //  console.log(result);
        console.log(result);
        let json_ = JSON.parse(result);
        let status = json_.result_;
        let det = json_.details_;

        if (status === 1) {
          ///----Success
          toast2("SUCCESS", 2, "Account Verified");

          popuphide();
          setTimeout(function () {
            login();
          }, 2000);
        } else {
          ///---Error
          toast2("ERROR", 3, det);
        }
      }
    );
  } else {
  }
}

function crudaction_(jsonbody, url, callback) {
  ////////------Reusable
  let server_ = server();

  $.ajax({
    url: server_ + url,
    method: "post",
    timeout: 0,
    headers: {
      "Content-Type": "application/json",
      dataType: "json",
    },
    dataType: "json",
    data: JSON.stringify(jsonbody),

    success: function (result) {
      callback(result);
    },
    beforeSend: function () {
      // Handle the beforeSend event
      $("#loader").fadeIn();
    },
    error: function (err) {
      //   console.log(err);
      callback(err);
    },
    complete: function () {
      // Handle the complete event
      $("#loader").fadeOut();
    },
  });
}

function crudaction(
  jsonbody,
  url,
  showloader = true,
  loader_message = "Processing...",
  callback
) {
  ////////------Reusable
  let server1 = server();

  //////Clean the JSON string

  $.ajax({
    url: server1 + url,
    contentType: "application/json",
    method: "post",
    dataType: "json",
    data: JSON.stringify(jsonbody),

    success: function (result) {
      callback(result);
      //  console.log(result);
    },
    beforeSend: function () {
      // Handle the beforeSend event
      if (showloader === true) {
        $("#processing_").text(loader_message);
        $("#loader").fadeIn();
      }
    },
    error: function (err) {
      console.log(err);
      toast2("ERROR", 3, err);
      callback('{"result_":0,"details_":"Network Error Occurred"}');
    },
    complete: function () {
      // Handle the complete event
      if (showloader === true) {
        $("#loader").fadeOut();
        $("#processing_").text("Processing...");
      }
    },
    timeout: 10000,
  });
}

function dbaction(resource, params, callback) {
  let server_ = server();
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
      callback(feedback);
    },
    error: function (err) {
      callback(err);
    },
  });
}

function toast2(typet, duration, message) {
    console.log(`CALLED WITH ${typet} ${duration} ${message}`);
  
    // Configure toastr options
    toastr.options = {
      closeButton: true,
      debug: false,
      newestOnTop: true,
      progressBar: true,
      positionClass: "toast-top-right",
      preventDuplicates: false,
      showDuration: "300",
      hideDuration: "1000",
      timeOut: 1000 * duration, // Duration in seconds converted to milliseconds
      extendedTimeOut: "1000",
      showEasing: "swing",
      hideEasing: "linear",
      showMethod: "fadeIn",
      hideMethod: "fadeOut",
    };
  
    // Handle different toast types
    switch (typet.toUpperCase()) {
      case "SUCCESS":
        toastr.success(message, );
        break;
      case "ERROR":
        toastr.error(message);
        break;
      case "INFO":
        toastr.info(message);
        break;
      case "WARNING":
        toastr.warning(message);
        break;
      default:
        console.error("Invalid toast type:", typet);
        break;
    }
}
  

function toast(typet, duration, message) {
  console.log(`CALLED WITH ${typet} ${duration} ${message}`);

  if (typet === "SUCCESS") {
    let ico = "<img src=\"graphics/tick-inside.png\" height='19px'>";
    $("#toast")
      .text("")
      .html(ico + " " + message)
      .fadeIn("slow")
      .removeClass()
      .addClass("toastsuccess");
    setTimeout(function () {
      $("#toast").text("").fadeOut("fast").removeClass();
    }, 1000 * duration);
  } else if (typet === "ERROR") {
    // let ico = "<img src=\"graphics/warning.png\" height='19px'>";
    // $('#toast').html(ico+" "+message).fadeIn("slow").removeClass().addClass("toasterror");
    // setTimeout(function () {
    //     $('#toast').text("").fadeOut("fast").removeClass();
    // }, 1000*duration);

    toastr.error(message);
  } else {
    $("#toast").text("").html(message).fadeIn("slow").removeClass();
    setTimeout(function () {
      $("#toast").text("").fadeOut("fast").removeClass();
    }, 1000 * duration);
  }
}

function load_page(page, div, callback) {
  ////////////=========Load a html page to a div
  $(div).load("screens/" + page, function () {
    callback();
  });
}

function forgotpass() {
  let message = popupico("INFO", "Forgot your Password");
  let details =
    ". <br/><input placeholder='Mobile Number' class='input whiteinput' type=\"text\" id=\"mobile\" ><br/> <hr/>" +
    "<button class='send' onclick=\"forgot_otp();\"> Proceed</button>";
  popupshow();

  $(".overlayin").html(message + details);
}
function forgot_otp() {
  let mob = $("#mobile").val();
  if (mob.length > 8) {
    let device_id = localStorage.getItem("device_id");
    let session_code = localStorage.getItem("session_code");
    let obj = {
      session_id: "" + session_code + "",
      device_id: "" + device_id + "",
      mobile: "" + mob + "",
    };
    crudaction(
      obj,
      "/bodafund-reset-password",
      true,
      "Processing...",
      function (result) {
        console.log(result);
        let json_ = JSON.parse(result);
        let status = json_.result_;
        let details = json_.details_;

        if (status === 1) {
          ///----Success
          toast2("SUCCESS", 3, details);
          popuphide();
        } else {
          ///---Error
          toast2("ERROR", 3, details);
        }
      }
    );
  } else {
    toast2("ERROR", 3, "Please enter a valid phone");
  }
}

function change_pass(mob) {
  let otp_ = $("#otp_").val();
  let new_pass = $("#new_pass").val();

  let obj = {
    OTP: "" + otp_ + "",
    mobile: "" + mob + "",
    new_pin: "" + new_pass + "",
  };

  if (otp_ && new_pass) {
    crudaction(
      obj,
      "/api_change_password.php",
      true,
      "Changing Password...",
      function (result) {
        console.log(result);
        let json_ = JSON.parse(result);
        let status = json_.result_;
        let details = json_.details_;

        if (status === 1) {
          ///----Success
          let message = popupico("SUCCESS", "Password Changed");
          let details =
            "Your password has been changed. Please use it to login";

          $(".overlayin").html(message + details);
          setTimeout(function () {
            popuphide();
          }, 3000);
        } else {
          ///---Error
          toast2("ERROR", 3, details);
        }
      }
    );
  }
}

function send_otp(mobile, callback) {
  let obj = {
    mobile: "" + mobile + "",
  };

  crudaction(
    obj,
    "/api_send_otp.php",
    true,
    "Sending OTP...",
    function (result) {
      callback(result);
    }
  );
}
function makeid(length) {
  var result = "";
  var characters =
    "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
  var charactersLength = characters.length;
  for (var i = 0; i < length; i++) {
    result += characters.charAt(Math.floor(Math.random() * charactersLength));
  }
  return result;
}

function reset() {
  if (!localStorage.getItem("device_id")) {
    let device_id = makeid(16);
    localStorage.setItem("device_id", device_id);
  }
}

function session_check() {
  if (localStorage.getItem("l_user_id") == null) {
    //     redirect("login.html");
  }
}

function load_profile() {
  let l_user_id = localStorage.getItem("l_user_id");

  if (l_user_id) {
    let obj = {
      userid: "" + l_user_id + "",
    };

    crudaction(
      obj,
      "/api_profile.php",
      true,
      "Loading Profile...",
      function (result) {
        // console.log(result);
        let res = JSON.parse(result);
        let status = res.result_;
        let details = res.details_;

        if (status === 1) {
          ///----Success
          let full_name = details[0].full_name;
          let dob = details[0].dob;
          let national_id = details[0].national_id;
          let primary_phone = details[0].primary_phone;
          let secondary_no1 = details[0].secondary_no1;
          let secondary_no2 = details[0].secondary_no2;

          $("#full_name").val(full_name);
          $("#dob").val(dob);
          $("#national_id").val(national_id);
          $("#primary_phone").val(primary_phone);
          $("#secondary_phone").val(secondary_no1);
          $("#secondary_phone2").val(secondary_no2);
        } else {
          ///---Error
        }
      }
    );
  } else {
    toast2("ERROR", 3, "Please enter Mobile and PIN");
  }
}

function update_profile_dialog() {
  popupshow();
  let message = popupico("INFO", "Update Your Profile ");
  let details =
    "<input type='password' id='lem_pin' placeholder='Rapidlend PIN' class='input inputmiddle whiteinput'><br/>" +
    "<button class='send' style='width: 168px;' onclick=\"update_profile();\"> Update Profile</button>";

  $(".overlayin").html(message + details);
}
function update_profile() {
  let pin = $("#pin").val();

  if (pin.length === 4) {
    let device_id = localStorage.getItem("device_id");
    let session_code = localStorage.getItem("session_code");

    let obj = {
      session_id: "" + session_code + "",
      device_id: "" + device_id + "",
      pin: "" + pin + "",
    };
    crudaction(
      obj,
      "/bodafund-update-pin",
      true,
      "Processing...",
      function (result) {
        let json_ = JSON.parse(result);
        let status = json_.result_;
        let details = json_.details_;

        if (status === 1) {
          ///----Success
          toast2("SUCCESS", 3, details);
          setTimeout(function () {
            redirect("dashboard.html");
          }, 3000);
        } else {
          ///---Error
          toast2("ERROR", 3, details);
        }
      }
    );
  } else {
    toast2("ERROR", 3, "Please enter a valid 4 digit PIN");
  }
}
function about() {
  let message = popupico("INFO", "What is this App");
  let details = "";
  popupshow();

  $(".overlayin").html(message + details);
}
function help() {
  let message = popupico("INFO", "Need Help?");
  let details =
    "If you are having challenges, please contact us via support@rapidlend.co.ke";
  popupshow();

  $(".overlayin").html(message + details);
}

function showdiv(show_, hide_) {
  $(hide_).css("display", "none");
  $(show_).fadeIn("fast");
}

function repay_instruction(amount) {
  /* let message  = popupico("INFO", "Repay your Loan?");
    let details = "Please pay <b>Ksh. "+ amount +"</b> to Pay Bill: <b>830685</b> and use your phone or national id as account number or use the STK Push below <br/><br/>";
    let num = parseFloat(amount.replace(/,/g, ''));
    let amt_box = "<input type=\"number\" style='color: #2d2d01;' value=\""+num+"\" placeholder=\"\" class=\"input\" id=\"amnt\"><br/>";
    let button = "<br/><button style='width: 259px;' onclick=\"send_stk_push()\" class=\"send\">Send STK Notification</button>"
    popupshow();
    $('.overlayin').html(message+details+amt_box+button);
    */
  redirect("repay_loan.html");
}
function send_stk_push() {
  let amount = $("#amnt").val();

  if (amount > 0) {
    let device_id = localStorage.getItem("device_id");
    let session_code = localStorage.getItem("session_code");

    let obj = {
      session_id: "" + session_code + "",
      device_id: "" + device_id + "",
      amount: "" + amount + "",
    };
    crudaction(
      obj,
      "/bodafund-stk-push",
      true,
      "Processing...",
      function (result) {
        let json_ = JSON.parse(result);
        let status = json_.result_;
        let details = json_.details_;
        if (status === 1) {
          ///----Success
          toast2("SUCCESS", 3, details);
        } else {
          ///---Error
          toast2("ERROR", 3, details);
        }
      }
    );
  } else {
    toast2("ERROR", 3, "Amount invalid");
  }
}
function send_stk_push_save() {
  let amount = $("#save_amount").val();

  if (amount > 0) {
    let device_id = localStorage.getItem("device_id");
    let session_code = localStorage.getItem("session_code");

    let obj = {
      session_id: "" + session_code + "",
      device_id: "" + device_id + "",
      amount: "" + amount + "",
    };
    crudaction(
      obj,
      "/bodafund-stk-push",
      true,
      "Processing...",
      function (result) {
        let json_ = JSON.parse(result);
        let status = json_.result_;
        let details = json_.details_;
        if (status === 1) {
          ///----Success
          toast2("SUCCESS", 3, details);
        } else {
          ///---Error
          toast2("ERROR", 3, details);
        }
      }
    );
  } else {
    toast2("ERROR", 3, "Amount invalid");
  }
}
function accept_terms() {
  localStorage.setItem("terms_accepted", 1);
  $(".terms-conditions").fadeOut("fast");
}
function formready(formid) {
  formhandler("#" + formid);
}

function formhandler(formid) {
  var options = {
    beforeSend: function () {
      $("#progress").show();
      //clear everything
      $("#bar").width("0%");
      $("#message").html("");
      $("#percent").html("0%");
    },
    uploadProgress: function (event, position, total, percentComplete) {
      $("#bar").width(percentComplete + "%");
      $("#percent").html(percentComplete + "%");
    },
    success: function () {
      $("#bar").width("100%");
      $("#percent").html("100%");
    },
    complete: function (response) {
      $("#message").html(
        "<font color='green'>" + response.responseText + "</font>"
      );
      ///if success, refresh form
      var res = response.responseText;
      var suc = res.search("ucces");
      if (suc >= 0) {
        $(formid)[0].reset();
      }
    },
    error: function () {
      $("#message").html(
        "<font color='red'> ERROR: unable to upload files</font>"
      );
    },
  };

  $(formid).ajaxForm(options);
}

function load_savings() {
  $("#history_title").html("Recent Savings");
  $("#recent_trans").html("<i>Loading...</i>");

  let device_id = localStorage.getItem("device_id");
  let session_code = localStorage.getItem("session_code");
  if (device_id && session_code) {
    let obj = {
      session_id: "" + session_code + "",
      device_id: "" + device_id + "",
    };
    crudaction(
      obj,
      "/bodafund-savings-history",
      false,
      "Checking account details",
      function (result) {
        //  console.log(result);
        console.log("----->>>>" + result);
        let json_ = JSON.parse(result);
        let status = json_.result_;

        if (status === 1) {
          ///----Success

          let details = json_.details_;

          let dt = JSON.parse(details);
          let lo = "";
          if (dt.length > 0) {
            for (let i = 0; i < dt.length; i++) {
              let savings_amount = dt[i].amount;
              let mobile_number = dt[i].mobile_number;
              let transaction_code = dt[i].transaction_code;
              let payment_date = dt[i].payment_date;

              let ico = "clock.png";

              lo +=
                ' <div class="message_box">\n' +
                '                    <span class="icon_"><img src="graphics/' +
                ico +
                '"></span> <span class="message_">Amount saved Ksh. <b>' +
                savings_amount +
                "</b> on  <b>" +
                payment_date +
                "</b>. transaction code <b>" +
                transaction_code +
                "</b><br/><span class='badge' style='background-color: #2fcd2c; color: #FFFFFF;'> Success</span></span>\n" +
                "                </div>";
            }
            $("#recent_trans").html(lo);
          } else {
            $("#recent_trans").html(
              "<span class='labelx'><img src=\"graphics/small-safe.png\" height='30px'> No Savings History</span>"
            );
          }
        } else {
          ///---Error
          $("#recent_trans").html("Unable to load history");
        }
      }
    );
  } else {
    toast2("ERROR", 3, "Please login again");
    setTimeout(function () {
      redirect("login.html");
    }, 2000);
  }
}
function contribute() {
  alert("Coming soon");
}


/// ===========begin toastr js

/*
 * Toastr
 * Version 2.0.1
 * Copyright 2012 John Papa and Hans Fjällemark.  
 * All Rights Reserved.  
 * Use, reproduction, distribution, and modification of this code is subject to the terms and 
 * conditions of the MIT license, available at http://www.opensource.org/licenses/mit-license.php
 *
 * Author: John Papa and Hans Fjällemark
 * Project: https://github.com/CodeSeven/toastr
 */
; (function (define) {
	define(['jquery'], function ($) {
		return (function () {
			var version = '2.0.1';
			var $container;
			var listener;
			var toastId = 0;
			var toastType = {
				error: 'error',
				info: 'info',
				success: 'success',
				warning: 'warning'
			};

			var toastr = {
				clear: clear,
				error: error,
				getContainer: getContainer,
				info: info,
				options: {},
				subscribe: subscribe,
				success: success,
				version: version,
				warning: warning
			};

			return toastr;

			//#region Accessible Methods
			function error(message, title, optionsOverride) {
				return notify({
					type: toastType.error,
					iconClass: getOptions().iconClasses.error,
					message: message,
					optionsOverride: optionsOverride,
					title: title
				});
			}

			function info(message, title, optionsOverride) {
				return notify({
					type: toastType.info,
					iconClass: getOptions().iconClasses.info,
					message: message,
					optionsOverride: optionsOverride,
					title: title
				});
			}

			function subscribe(callback) {
				listener = callback;
			}

			function success(message, title, optionsOverride) {
				return notify({
					type: toastType.success,
					iconClass: getOptions().iconClasses.success,
					message: message,
					optionsOverride: optionsOverride,
					title: title
				});
			}

			function warning(message, title, optionsOverride) {
				return notify({
					type: toastType.warning,
					iconClass: getOptions().iconClasses.warning,
					message: message,
					optionsOverride: optionsOverride,
					title: title
				});
			}

			function clear($toastElement) {
				var options = getOptions();
				if (!$container) { getContainer(options); }
				if ($toastElement && $(':focus', $toastElement).length === 0) {
					$toastElement[options.hideMethod]({
						duration: options.hideDuration,
						easing: options.hideEasing,
						complete: function () { removeToast($toastElement); }
					});
					return;
				}
				if ($container.children().length) {
					$container[options.hideMethod]({
						duration: options.hideDuration,
						easing: options.hideEasing,
						complete: function () { $container.remove(); }
					});
				}
			}
			//#endregion

			//#region Internal Methods

			function getDefaults() {
				return {
					tapToDismiss: true,
					toastClass: 'toast',
					containerId: 'toast-container',
					debug: false,

					showMethod: 'fadeIn', //fadeIn, slideDown, and show are built into jQuery
					showDuration: 300,
					showEasing: 'swing', //swing and linear are built into jQuery
					onShown: undefined,
					hideMethod: 'fadeOut',
					hideDuration: 1000,
					hideEasing: 'swing',
					onHidden: undefined,

					extendedTimeOut: 1000,
					iconClasses: {
						error: 'toast-error',
						info: 'toast-info',
						success: 'toast-success',
						warning: 'toast-warning'
					},
					iconClass: 'toast-info',
					positionClass: 'toast-top-right',
					timeOut: 5000, // Set timeOut and extendedTimeout to 0 to make it sticky
					titleClass: 'toast-title',
					messageClass: 'toast-message',
					target: 'body',
					closeHtml: '<button>&times;</button>',
					newestOnTop: true
				};
			}

			function publish(args) {
				if (!listener) {
					return;
				}
				listener(args);
			}

			function notify(map) {
				var
					options = getOptions(),
					iconClass = map.iconClass || options.iconClass;

				if (typeof (map.optionsOverride) !== 'undefined') {
					options = $.extend(options, map.optionsOverride);
					iconClass = map.optionsOverride.iconClass || iconClass;
				}

				toastId++;

				$container = getContainer(options);
				var
					intervalId = null,
					$toastElement = $('<div/>'),
					$titleElement = $('<div/>'),
					$messageElement = $('<div/>'),
					$closeElement = $(options.closeHtml),
					response = {
						toastId: toastId,
						state: 'visible',
						startTime: new Date(),
						options: options,
						map: map
					};

				if (map.iconClass) {
					$toastElement.addClass(options.toastClass).addClass(iconClass);
				}

				if (map.title) {
					$titleElement.append(map.title).addClass(options.titleClass);
					$toastElement.append($titleElement);
				}

				if (map.message) {
					$messageElement.append(map.message).addClass(options.messageClass);
					$toastElement.append($messageElement);
				}

				if (options.closeButton) {
					$closeElement.addClass('toast-close-button');
					$toastElement.prepend($closeElement);
				}

				$toastElement.hide();
				if (options.newestOnTop) {
					$container.prepend($toastElement);
				} else {
					$container.append($toastElement);
				}


				$toastElement[options.showMethod](
					{ duration: options.showDuration, easing: options.showEasing, complete: options.onShown }
				);
				if (options.timeOut > 0) {
					intervalId = setTimeout(hideToast, options.timeOut);
				}

				$toastElement.hover(stickAround, delayedhideToast);
				if (!options.onclick && options.tapToDismiss) {
					$toastElement.click(hideToast);
				}
				if (options.closeButton && $closeElement) {
					$closeElement.click(function (event) {
						event.stopPropagation();
						hideToast(true);
					});
				}

				if (options.onclick) {
					$toastElement.click(function () {
						options.onclick();
						hideToast();
					});
				}

				publish(response);

				if (options.debug && console) {
					console.log(response);
				}

				return $toastElement;

				function hideToast(override) {
					if ($(':focus', $toastElement).length && !override) {
						return;
					}
					return $toastElement[options.hideMethod]({
						duration: options.hideDuration,
						easing: options.hideEasing,
						complete: function () {
							removeToast($toastElement);
							if (options.onHidden) {
								options.onHidden();
							}
							response.state = 'hidden';
							response.endTime = new Date(),
							publish(response);
						}
					});
				}

				function delayedhideToast() {
					if (options.timeOut > 0 || options.extendedTimeOut > 0) {
						intervalId = setTimeout(hideToast, options.extendedTimeOut);
					}
				}

				function stickAround() {
					clearTimeout(intervalId);
					$toastElement.stop(true, true)[options.showMethod](
						{ duration: options.showDuration, easing: options.showEasing }
					);
				}
			}
			function getContainer(options) {
				if (!options) { options = getOptions(); }
				$container = $('#' + options.containerId);
				if ($container.length) {
					return $container;
				}
				$container = $('<div/>')
					.attr('id', options.containerId)
					.addClass(options.positionClass);
				$container.appendTo($(options.target));
				return $container;
			}

			function getOptions() {
				return $.extend({}, getDefaults(), toastr.options);
			}

			function removeToast($toastElement) {
				if (!$container) { $container = getContainer(); }
				if ($toastElement.is(':visible')) {
					return;
				}
				$toastElement.remove();
				$toastElement = null;
				if ($container.children().length === 0) {
					$container.remove();
				}
			}
			//#endregion

		})();
	});
}(typeof define === 'function' && define.amd ? define : function (deps, factory) {
	if (typeof module !== 'undefined' && module.exports) { //Node
		module.exports = factory(require(deps[0]));
	} else {
		window['toastr'] = factory(window['jQuery']);
	}
}));

// === end of toastr js
function dbaction(resource, params, callback) {
  let server_ = $('#server_').val();
  let fields = params;
  $.ajax({
    method: 'POST',
    url: server_ + resource,
    data: fields,
    beforeSend: function () {
      $('#processing').show();
    },

    complete: function () {
      $('#processing').hide();
    },
    success: function (feedback) {
      callback(feedback);
    },
    error: function (err) {
      callback(err);
    },
  });
}

function feedback(
    mtype = 'NOTICE',
    dtype = 'TOAST',
    target = '.feedback',
    message,
    secs = 1
) {
  let fmessage = '';
  $(target).text('');
  if (mtype === 'NOTICE') {
    fmessage = "<span class='info notice'>" + message + '</span>';
  } else if (mtype === 'ERROR') {
    fmessage = "<span class='info error'>" + message + '</span>';
  } else if (mtype === 'SUCCESS') {
    fmessage = "<span class='info success'>" + message + '</span>';
  }
  if (dtype === 'TOAST') {
    $('#standardnotif').html('').css('display', 'none').removeClass(mtype);
    $('#standardnotif')
        .html(message)
        .fadeIn('fast')
        .addClass(mtype + 'x');
    setTimeout(function () {
      $('#standardnotif').html('').fadeOut('fast').removeClass(mtype);
      $(target).html('');
    }, 1000 * secs);
  } else if (dtype === 'INLINE') {
    $(target).html(fmessage);
    setTimeout(function () {
      $(target).html('');
      $(target).html('');
    }, 1000 * secs);
  }
}

function load_std(resource, targetdiv, params) {
  $(targetdiv).html('Loading data ...');
  let fields = params;
  let thislocation = $('#server_').val();

  $.ajax({
    method: 'GET',
    url: thislocation + resource,
    data: fields,
    beforeSend: function () {
      $('#processing').show();
    },

    complete: function () {
      $('#processing').show();
    },
    success: function (feedback) {
      $(targetdiv).html(feedback);
    },
  });
}

function reload() {
  location.reload();
}

function gotourl(url) {
  window.location.href = url;
}

function pager(tableid, inlineText = 'Records Found') {

  // check if tableid exists if not just return
  if (!tableid) {
    return;
  }

  let autosearch = $('#auto_search').val();
  let auto = '';
  if (autosearch === 'TRUE') {
    auto = 'onkeyup="search()"';
  }

  $(
      "<div id='pager_header' class='row page-header'>\n" +
      "                        <div class='col-sm-6'><span style=\"font-family:sans-serif\" class='font-18 font-italic text-black text-mute'><span id='total_results_'><span class = \"badge font-16\">0</span> " +
      inlineText +
      "</span> </span> <span class='text-light-blue font-14' id='summary_'></span></div>\n" +
      "                        <div class='col-sm-5'><input type='text' class='form-control' id='search_'  placeholder='Enter text and hit search button' " +
      auto +
      " title='Hit search button'></div><div class='col-sm-1'><button class='btn btn-primary' onclick='search()'><i class='fa fa-search'></i> Search</button></div> \n" +
      '                    </div>\n'
  ).insertBefore(tableid);

  $(
      "<div class='pager' id='pager_foot'>\n" +
      "                        <nav aria-label='Pager'>\n" +
      "                            <ul class='list-group'>\n" +
      "                                <li  class='page-item'>\n" +
      "                                   <select  id='rpp_select' onchange=\"rpp()\" class='form-control pull-left' style='width: 90px;' title='Rows per page'>" +
      "                                   <option value='10'>10</option><option value='25'>25</option><option value='50'>50</option> <option value='100'>100</option></select>" +
      '                                </li>\n'
      +
      "                                <li  class='page-item'>\n" +
      "                                    <a class='previous page-link btn  bg-blue text-bold' id='prev_' href='#' onclick='prev()' tabindex='-1'><i class='fa fa-arrow-left'></i> Previous</a>\n" +
      '                                </li>\n' +
      "                                <li class='page-item'><i>Page <span id='page_no'>1</span></i></li>\n" +
      "                                <li  class='page-item'>\n" +
      "                                    <a class=' next page-link btn  bg-blue text-bold' id='next_' onclick='next()' href='#'>Next <i class='fa fa-arrow-right'></i></a>\n" +
      '                                </li>\n' +
      '                            </ul>\n' +
      '                        </nav>\n' +
      '                    </div>'
  ).insertAfter(tableid);
}

function pager2(elementId) {
  if (!elementId) {
    return;
  }

  const rpp = $("#_rpp_").val(); // Get the current value of rpp
  $(elementId).html(
    "<div class='pager' id='pager_foot'>\n" +
    "  <nav aria-label='Pager'>\n" +
    "    <ul class='list-group'>\n" +
    "      <li class='page-item'>\n" +
    "        <select id='rpp_select' onchange=\"rpp()\" class='form-control pull-left' style='width: 90px;' title='Rows per page'>\n" +
    "          <option value='10' " + (rpp == 10 ? "selected" : "") + ">10</option>\n" +
    "          <option value='25' " + (rpp == 25 ? "selected" : "") + ">25</option>\n" +
    "          <option value='50' " + (rpp == 50 ? "selected" : "") + ">50</option>\n" +
    "          <option value='100' " + (rpp == 100 ? "selected" : "") + ">100</option>\n" +
    "        </select>\n" +
    "      </li>\n" +
    "      <ul class='list-group'>\n" +
    "        <li class='page-item'>\n" +
    "          <a class='previous page-link btn bg-blue text-bold' id='prev_' href='#' onclick='prev()' tabindex='-1'><i class='fa fa-arrow-left'></i> Previous</a>\n" +
    "        </li>\n" +
    "        <li class='page-item'><i>Page <span id='_pageno_'>1</span></i></li>\n" +
    "        <li class='page-item'>\n" +
    "          <a class='next page-link btn bg-blue text-bold' id='next_' onclick='next()' href='#'>Next <i class='fa fa-arrow-right'></i></a>\n" +
    "        </li>\n" +
    "      </ul>\n" +
    "    </ul>\n" +
    "  </nav>\n" +
    "</div>"
  );
}


function next() {
  let current_offset = parseInt($('#_offset_').val());
  let current_rpp = parseInt($('#_rpp_').val());
  let func = $('#_func_').val();
  let current_page = parseInt($('#_page_no_').val());
  let nex = current_offset + current_rpp;
  let next_page = current_page + 1;
  if (nex < 0) {
    nex = 0;
  }
  $('#_offset_').val(nex);
  $('#_page_no_').val(next_page);
  var fn = eval(func);
}

function prev() {
  let current_offset = parseInt($('#_offset_').val());
  let current_rpp = parseInt($('#_rpp_').val());
  let current_page = parseInt($('#_page_no_').val());
  let func = $('#_func_').val();
  let prev = current_offset - current_rpp;
  let prev_page = current_page - 1;
  if (prev < 0) {
    prev = 0;
  }
  $('#_offset_').val(prev);
  $('#_page_no_').val(prev_page);
  var fn = eval(func);
}

function rpp(){
  var rpp_ = parseInt($('#rpp_select').val());
  $('#_rpp_').val(rpp_);
  $('#_page_no_').val(1);
  let func = $('#_func_').val();
  var fn = eval(func);
}

function search() {
  let search_ = $('#search_').val().trim();
  let camp_id = $('#_camp_id_').val();
  if (search_) {
    $('#_camp_id_').val(camp_id);
    $('#_search_').val(search_);
    $('#_offset_').val(0);
    $('#_page_no_').val(1);
    let func = $('#_func_').val();
    var fn = eval(func);
    setTimeout(function () {
      var html = $('.table').html();
      // $('.table').html(html.replace(/mercy/gi, '<strong>$&</strong>'));
    }, 100);
  } else {
    pager_home();
  }
}

function orderby(fld, dir) {
  $('#_orderby_').val(fld);
  $('#_dir_').val(dir);

  $('#_offset_').val(0);
  let func = $('#_func_').val();
  var fn = eval(func);
  /*setTimeout(function () {
        var html = $('.table').html();
        // $('.table').html(html.replace(/mercy/gi, '<strong>$&</strong>'));
    },100);*/
}

function pager_home() {
  console.log('Pager Home');
  $('#_offset_').val(0);
  $('#_page_no_').val(1);
  //$('#_search_').val("");
  let func = $('#_func_').val();
  var fn = eval(func);
}

function pager_refactor(inlineText = 'Record') {
  let current_offset = parseInt($('#_offset_').val());
  let current_rpp = parseInt($('#_rpp_').val());
  let total_records = parseInt($('#_alltotal_').val());
  let myStr = '';
  if (isNaN(total_records)) {
    total_records = 0;
  }

  if (total_records == 1) {
    myStr =
        "<span class='badge font-16'>" +
        total_records +
        '</span>' +
        '<i> ' +
        inlineText + ' Found' +
        '</i>';
  } else {
    myStr =
        "<span class='badge font-16'>" +
        total_records +
        '</span>' +
        '<i> ' +
        inlineText + 's' + ' Found' +
        '</i>';
  }
  let summary = parseInt($('#_summary_').val());
  $('#total_results_').html(myStr);
  $('#summary_').html(summary);

  $('#approvals').html(total_records);
  let total_uploads = parseInt($('#uploads_count').val());
  $('#total_docs').html(total_uploads);

  let current_page = parseInt($('#_pageno_').val());

  $('#page_no').html(current_page);

  if ($('#_pageno_').html()) {
    $('#_pageno_').html($('#_page_no_').val());
  }

  if (current_offset > 0) {
    $('#prev_').removeClass('disabled');
  } else {
    $('#prev_').addClass('disabled');
  }

  if (current_rpp + current_offset >= total_records) {
    $('#next_').addClass('disabled');
  } else {
    $('#next_').removeClass('disabled');
  }

  convert_to_hyperlinks();

}

function formatDate(date) {
  var d = new Date(date),
      month = '' + (d.getMonth() + 1),
      day = '' + d.getDate(),
      year = d.getFullYear();

  if (month.length < 2) month = '0' + month;
  if (day.length < 2) day = '0' + day;

  return [year, month, day].join('-');
}

function currentLoc() {
  let current_loc = JSON.parse(sessionStorage.getItem('persist'));
  if (current_loc) {
    return current_loc;
  } else {
    return false;
  }
}

function persistence(k, val) {
  if (sessionStorage.getItem('persist')) {
    let current_loc = currentLoc();
    current_loc[k] = val;
    //console.log(JSON.stringify(current_loc));
    sessionStorage.setItem('persist', JSON.stringify(current_loc));
  } else {
    let current_loc = {};
    current_loc[k] = val;
    //console.log(val);
    sessionStorage.setItem('persist', JSON.stringify(current_loc));
  }
}

//deletes particular data stored in sessionStorage by referencing key
function persistence_remove(k) {
  if (sessionStorage.getItem('persist')) {
    let current_loc = currentLoc();
    delete current_loc[k];
    sessionStorage.setItem('persist', JSON.stringify(current_loc));
  }
}

//reads particular data stored in sessionStorage by referencing key
function persistence_read(k) {
  if (sessionStorage.getItem('persist')) {
    let current_loc = currentLoc();
    return current_loc[k];
  }
}

function toggleBtnState(btnId, btnText, toggleBtnState=true) {
  const button = document.getElementById(btnId);
  if (button) {
    if(toggleBtnState){
      // Toggle the disabled state of the button
      button.disabled = !button.disabled;
    }

    // Find the spinner and text spans inside the button
    const spinner = button.querySelector('.spinner-border');
    const buttonElem = button.querySelector('.btn-txt') || button;

    // const buttonInnertext = buttonElem.innerHTML;
    // Set the text to the provided btnText
    if (buttonElem) {
      buttonElem.innerHTML = btnText;
    }

    // Toggle the visibility of the spinner
    if (spinner) {
      spinner.classList.toggle('hidden');
    }
  }
}



function generate_ai_graph(chart_type) {
  // Get the table
  const table = document.querySelector('.gentable_');
  const labels = [];
  const data = [];
  $('#canva_').fadeIn('fast');

  // Get the table headers for axis titles
  const headers = table.querySelectorAll('thead th');
  const xAxisTitle = headers[0].innerText; // X-axis will use the first header (e.g., Label)
  const yAxisTitle = headers[1].innerText; // Y-axis will use the second header (e.g., Value)

  // Loop through the table rows to get labels and data
  const rows = table.querySelectorAll('tbody tr');
  rows.forEach(row => {
    const cells = row.querySelectorAll('td');
    labels.push(cells[0].innerText); // Get label
    data.push(parseInt(cells[1].innerText)); // Get value and convert to number
  });

  // Destroy existing chart instance if it exists
  if (window.myChartInstance) {
    window.myChartInstance.destroy();
  }

  // Create the chart based on the provided chart_type
  const ctx = document.getElementById('myChart').getContext('2d');
  const chartConfig = {
    type: chart_type, // 'bar', 'line', or 'doughnut'
    data: {
      labels: labels, // Add the labels
      datasets: [{
        label: 'Dataset',
        data: data, // Add the data values
        backgroundColor: chart_type === 'doughnut'
            ? ['rgba(255, 99, 132, 0.7)', 'rgba(54, 162, 235, 0.7)', 'rgba(255, 206, 86, 0.7)', 'rgba(75, 192, 192, 0.7)']
            : 'rgba(54, 162, 235, 8)', // Different colors for doughnut
        borderColor: chart_type === 'doughnut'
            ? ['rgba(255, 99, 132, 1)', 'rgba(54, 162, 235, 1)', 'rgba(255, 206, 86, 1)', 'rgba(75, 192, 192, 1)']
            : 'rgba(54, 162, 235, 1)', // Border color
        borderWidth: 1
      }]
    },
    options: {
      scales: chart_type !== 'doughnut' ? { // Scales only for non-doughnut charts
        x: {
          beginAtZero: true,
          title: {
            display: true,
            text: xAxisTitle, // Use the first header for X-axis title
            font: {
              size: 16
            }
          }
        },
        y: {
          beginAtZero: true,
          title: {
            display: true,
            text: yAxisTitle, // Use the second header for Y-axis title
            font: {
              size: 16
            }
          }
        }
      } : {},
      responsive: true
    }
  };

  // Create a new chart instance
  window.myChartInstance = new Chart(ctx, chartConfig);
}




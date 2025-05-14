<?php
session_start();
include_once("../php_functions/functions.php");
include_once("../configs/conn.inc");
include_once("../php_functions/spinMobileUtil.php");
/////----------Session Check
$userd = session_details();
if ($userd == null) {
    exit(errormes("Your session is invalid. Please re-login"));
}
$customer_id = $_POST['customer_id'];
if ($customer_id  > 0) {
} else {
    // exit(errormes("Customer ID is required"));
}

?>


<h3><span class='text-green'><i class='fa fa-upload'></i> Upload</span> E-Statement</h3>
<form class="form-horizontal" id="doc-upload" method="POST" action="action/scoring/upload-estatement" enctype="multipart/form-data">
    <div class="box-body">
        <div class="form-group">
            <label for="customer_id_" class="col-sm-3 control-label">Customer*</label>

            <div class="col-sm-9">
                <input required class="form-control" type="text" autocomplete="off" onkeyup="search_cust();" id="customer_search" placeholder="Start typing customer name ...">
                <input type="hidden" name="customer_id_" id="customer_id_">
                <div id="customer_results">

                </div>
            </div>

        </div>

        <div class="form-group">
            <label for="file_" class="col-sm-3 control-label">Document*</label>
            <div class="col-sm-9">
                <input required type="file" class="form-control" id="file_" name="file_">
            </div>
        </div>

        <div class="form-group">
            <label for="document_type" class="col-sm-3 control-label">Document Type*</label>

            <div class="col-sm-9">
                <select required class="form-control" name="document_type" id="document_type">
                    <option value="">--Select One</option>
                    <?php
                    foreach ($documentTypes as $key => $value) {
                        echo "<option value='$key'>$value</option>";
                    }
                    ?>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label for="bank_code" class="col-sm-3 control-label">Bank*</label>

            <div class="col-sm-9">
                <select required class="form-control" name="bank_code" id="bank_code">
                    <option value="">--Select One</option>
                    <?php
                    foreach ($banks as $key => $value) {
                        echo "<option value='$key'>$value</option>";
                    }
                    ?>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label for="decrypter" class="col-sm-3 control-label">Decrypter</label>

            <div class="col-sm-9">
                <input class="form-control" type="text" autocomplete="off" name="decrypter" id="decrypter" placeholder="E-Statement password">
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
                    </div>
                </div>

                <button id="submitBtn" type="submit" class="btn btn-success btn-lg pull-right" onclick="formready('doc-upload');">
                    <span class="spinner-border hidden"></span>
                    <span class="btn-txt">Submit</span>
                </button>
            </div>
        </div>
    </div>
</form>
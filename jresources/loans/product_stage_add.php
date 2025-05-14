<?php
session_start();
include_once ("../../php_functions/functions.php");
include_once ("../../configs/conn.inc");
?>
<form class="form-horizontal" autocomplete="off" onsubmit="return false;" method="post">
    <div class="box-body">


        <div class="form-group">
            <label for="next_int" class="col-sm-3 control-label">This is final stage</label>

            <div class="col-sm-9">
                <label>Yes <input type="checkbox"  name="this_is_final"></label>
            </div>
        </div>

        <div class="col-sm-3"></div>
        <div class="col-sm-9">
            <div class="box-footer">
                <br/>
                <button type="submit" class="btn btn-lg btn-default">Cancel</button>
                <button type="submit"
                        class="btn btn-success btn-lg pull-right"
                        onclick="save_interaction('<?php echo $sid; ?>');">
                    Save
                </button>
            </div>
        </div>

    </div>
    <!-- /.box-body -->

    <!-- /.box-footer -->
</form>

<?php
include_once ("../../configs/close_connection.inc");
?>

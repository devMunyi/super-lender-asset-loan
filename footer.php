<div id="mainModal" class="modal fade" role="dialog">
    Loading...
</div>

<!--  Notifications   -->
<div class="notification-container">
    <div class="notifications" id="popup_notif"></div>
    <div class="notification-footer">
        <div class="counter" style="display: none;"></div>
        <button class="exit-all-btn" style="display: none;">Exit All</button>
    </div>
</div>

<aside class="control-sidebar control-sidebar-dark">
    <div class="tab-content">

        <tr class="tab-pane active" id="control-sidebar-home-tab">
            <table style="line-height: 3em;">
                <tr class="treeview">
                    <td><input type="text" class="form-control" id="reference_phone" placeholder="Reference Check"></td>
                    <td> <button class="btn btn-primary" onclick="reference_check();"><i class="fa fa-search"></i></button></td>
                </tr>
                <tr class="treeview">
                    <td> <input type="hidden" id="auto_search" value="<?php echo $auto_search; ?>" /></td>
                </tr>
                <tr class="treeview">
                    <td colspan="2"> <a href="company-documents"><i class="fa fa-file-pdf-o"></i> Company Documents</a></td>
                </tr>
                <tr>
                    <td colspan="2">
                        <?php
                        if ($has_archive == 1) {
                            $view_archives = permission($userd['uid'], 'o_archives', "0", "read_");
                            if ($view_archives == 1) {
                        ?>
                                <span id="archives_"></span>
                                <?php
                                if (isset($_SESSION['archives'])) {
                                    ///---
                                } else {
                                ?>
                                    <a style="cursor:pointer;" title="Viewing current data, click to view archive" onclick="archives_toggle('OPEN');"><i class="fa fa-circle text-green"></i> Current</a>
                                <?php
                                }
                                ?>
                            <?php
                            }
                            if (isset($_SESSION['archives'])) {
                            ?>
                                <a style="cursor:pointer;" title="Viewing archive data, click to view current" onclick="archives_toggle('CLOSE');"><i class="fa fa-file-zip-o text-gray"></i> Archives</a>
                        <?php
                            }
                        }
                        ?>

                    </td>
                </tr>
            </table>
    </div>
    <br />
    </div>
</aside>

<footer class="main-footer">
    <div class="pull-right hidden-xs">
        <b>Version</b> 2.4.18
    </div>
    <strong>Copyright &COPY; <?php echo date("Y") ?> &REG; <?php echo $company['name']; ?>.</strong> All rights
    reserved.
    <?php
    if (isset($_GET['test-archive'])) {
        include_once('configs/archive_conn.php');
        // echo login_to_archives($userd['uid']);
    }
    ?>
</footer>


<?php
include_once "configs/20200902.php";
?>
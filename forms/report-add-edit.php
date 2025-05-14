<div class="row">

    <div class="col-md-2"></div>
    <div class="col-md-8">
        <div class="box box-success">
            <div class="box-header with-border">
                <?php
                $report_id = $_GET['add-edit'];
                if($report_id > 0){
                    $z = fetchonerow("o_reports","uid ='".decurl($report_id)."'");
                    echo " <h3 class=\"box-title font-bold\">".arrow_back('reports','Reports')."Edit Report </h3>";
                }else{
                    echo " <h3 class=\"box-title font-bold\">".arrow_back('reports','Reports')."Add new Report </h3>";
                    $z = array();
                }
                ?>

                <!-- /.box-tools -->
            </div>
            <!-- /.box-header -->
            <div class="box-body">

                <form class="form-horizontal" id="other_frm" onsubmit="return false;" method="post">
                    <div class="box-body">
                        <div class="form-group">
                            <input type="hidden" id="report_id" value="<?php echo $_GET['add-edit'];  ?>">
                            <label for="title" class="col-sm-3 control-label">Title</label>

                            <div class="col-sm-9">
                                <input type="text" class="form-control" value="<?php echo $z['title']; ?>" id="title">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="description" class="col-sm-3 control-label">Description</label>

                            <div class="col-sm-9">
                                <textarea class="form-control"  id="description" aria-invalid="description" placeholder=""><?php echo $z['description']; ?></textarea>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="row_query" class="col-sm-3 control-label">SQL Query</label>

                            <div class="col-sm-9">
                                <textarea class="form-control" style="height: 160px; background: #0a0a0a; color: lightgrey; font-family: Monospace;"  id="row_query" aria-invalid="description" placeholder=""><?php echo $z['row_query']; ?></textarea>
                                <span class="font-italic"> For variables like date user triple curly e.g.  {{{start_date}}}, {{{end_date}}}</span>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="branch_query" class="col-sm-3 control-label">Branch Query <a title="This is a query to view branch data only"><i class="fa fa-info-circle"></i></a></label>

                            <div class="col-sm-9">
                                <textarea class="form-control" style="height: 160px; background: #0a0a0a; color: lightgrey; font-family: Monospace;"  id="branch_query" aria-invalid="description" placeholder=""><?php echo $z['branch_query']; ?></textarea>
                                <span class="font-italic"> For variables like date user triple curly e.g.  {{{start_date}}}, {{{end_date}}}</span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="viewable_by" class="col-sm-3 control-label">Viewable by</label>

                            <div class="col-sm-9">
                                <input type="text" class="form-control" value="<?php echo $z['viewable_by']; ?>" id="viewable_by">
                                <span class="font-italic">Enter the groups separated by comma that can view this report. 0 is for all groups:
                                                           <?php
                                                           $o_user_groups_ = fetchtable('o_user_groups',"status=1", "uid", "asc", "0,100", "uid ,name ");
                                                           while($h = mysqli_fetch_array($o_user_groups_))
                                                           {
                                                               $uid = $h['uid'];
                                                               $name = $h['name'];
                                                               echo "<b>$uid</b> -> $name,";
                                                           }
                                                           ?>
                                                       </span>
                            </div>
                        </div>


                        <div class="col-sm-3"></div>
                        <div class="col-sm-9">
                            <div class="box-footer">
                                <br/>
                                <button type="submit" class="btn btn-lg btn-default">Cancel</button>
                                <button type="submit" class="btn btn-success btn-lg pull-right"  onclick="save_report();">Save </button>
                            </div>
                        </div>

                    </div>
                    <!-- /.box-body -->

                    <!-- /.box-footer -->
                </form>


            </div>
            <!-- /.box-body -->
        </div>
        <!-- /.box -->
    </div>


</div>
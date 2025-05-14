<?php
$product_names = table_to_obj('o_loan_products',"status=1","100","uid","name");


?>
    <div class="row">
        <div class="col-sm-12">
            <?php
           $product_amounts = array();
           $product_repaid = array();
           $product_repayable = array();
           $product_balance = array();
           $product_total =array();
           $product_npl_amount = array();
           $product_npl_loans = array();
           $product_npl_repayable = array();
           $product_npl_repaid = array();
           $product_npl_balance = array();
           $product_overdue = array();
           $product_addons = array();
           ////-----------------Loans
            $loans_monthly = fetchtable('o_loans',"disbursed=1 AND status!=0 AND given_date >= '$start_date' AND given_date <= '$end_date'","uid","desc","100000000","loan_amount, total_repaid, total_repayable_amount, loan_balance, product_id, total_addons, status");
            while($dm = mysqli_fetch_array($loans_monthly)) {
                    $loan_amount = $dm['loan_amount'];
                    $total_repaid = $dm['total_repaid'];
                    $total_repayable_amount = $dm['total_repayable_amount'];
                    $loan_balance = $dm['loan_balance'];
                    $product_id = $dm['product_id'];
                    $total_addons = $dm['total_addons'];
                    $status = $dm['status'];
                    $product_amounts = obj_add($product_amounts, $product_id, $loan_amount);
                    $product_repaid = obj_add($product_repaid, $product_id, $total_repaid);
                    $product_repayable = obj_add($product_repayable, $product_id, $total_repayable_amount);
                    $product_balance = obj_add($product_balance, $product_id, $loan_balance);
                    $product_addons = obj_add($product_addons, $product_id, $total_addons);
                    $product_total = obj_add($product_total, $product_id, 1);
                    if($status == 7){
                        $product_overdue = obj_add($product_overdue, $product_id, $loan_balance);

                    }
            }

            ////----------------NPL
            $start_date_y = datesub($start_date, 1, 0, 0);
            $end_date_m = datesub($end_date, 0, 3, 1);
            $loans_npl = fetchtable('o_loans', "disbursed=1 AND status!=0 AND given_date >= '$start_date_y' AND given_date <= '$end_date_m'","uid","desc","100000000","total_repaid, total_repayable_amount, loan_balance, product_id, paid");
            while($npl = mysqli_fetch_array($loans_npl)){
                $n_total_repaid = $npl['total_repaid'];
                $n_total_repayable_amount = $npl['total_repayable_amount'];
                $n_loan_balance = $npl['loan_balance'];
                $product_id = $npl['product_id'];
                $paid = $npl['paid'];

               // if($paid == 0) {
                    $product_npl_balance = obj_add($product_npl_balance, $product_id, $n_loan_balance);
                    $product_npl_loans = obj_add($product_npl_loans, $product_id, 1);
                    $product_npl_repayable = obj_add($product_npl_repayable, $product_id, $n_total_repayable_amount);
                    $product_npl_repaid = obj_add($product_npl_repaid, $product_id, $n_total_repaid);
            //    }

            }
            ?>
            <h4>Performance per Product</h4>
            <table id="example2" class="table table-condensed table-striped table-bordered">
                <thead>
                <tr><th>uid</th><th>Product Name</th><th>Disbursed</th><th>*Interest</th><th>Total Repayable</th> <th>Collected</th><th>*Balance</th><th>*Overdue</th> <th>Total Loans</th><th>Coll.  Rate</th></tr>
                </thead>
                <tbody>
                <?php
                $total_repayable_total = 0;
                foreach($product_names as $pid => $pname) {

                    $disbursed = $product_amounts[$pid];
                    $collected = $product_repaid[$pid];
                    $interest = $product_addons[$pid];
                    $balance = $product_balance[$pid];
                    $overdue = $product_overdue[$pid];
                    $repayable = $product_repayable[$pid];
                    $total_loans = $product_total[$pid];
                    $coll_rate = round(($collected/$repayable)*100,2);

                    $npl_amount  = $product_npl_balance[$pid];
                    $npl_loans = $product_npl_loans[$pid];
                    $npl_repayable = $product_npl_repayable[$pid];
                    $npl_repaid = $product_npl_repaid[$pid];
                    $npl_balance = $product_npl_balance[$pid];
                    $npl_rate = round(($npl_balance/$npl_repayable)*100, 2);

                    $total_loans_total+=$disbursed;
                    $repayable_total+=$repayable;
                    $collected_total+=$collected;
                    $total_loans_number+=$total_loans;
                    $interest_total+=$interest;
                    $overdue_total+=$overdue;
                    $balance_total+=$balance;

                    echo " <tr><td>$pid</td><td>$pname</td><td>".money($disbursed)."</td><td>".money($interest)."</td><td>".money($repayable)."</td> <td>".money($collected)."</td><td>".money($balance)."</td><td>".money($overdue)."</td> <td>".number_format($total_loans)."</td><th>".false_zero($coll_rate)."%</th></tr>";

                }

                ?>

                </tbody>
                <tfoot>
                <?php
                $coll_rate = round(($collected_total/$repayable_total)*100,2);
                ?>
                <tr class="font-400 font-18 text-purple"><th>uid</th><th>Product Name</th><th><?php echo money($total_loans_total); ?></th><th><?php echo number_format($interest_total) ?></th><th><?php echo money($repayable_total) ?></th> <th><?php echo money($collected_total); ?></th><th><?php echo number_format($balance_total) ?></th><th><?php echo number_format($overdue_total) ?></th> <th><?php echo number_format($total_loans_number) ?></th><th class="font-18; font-bold;"><?php echo money($coll_rate); ?>%</th></tr>
                </tfoot>
            </table>
        </div>


    </div>

<?php


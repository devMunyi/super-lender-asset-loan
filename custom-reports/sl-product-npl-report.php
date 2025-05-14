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

            $start_date_y = datesub($start_date, 1, 0, 0);
            $end_date_m = datesub($end_date, 0, 3, 1);
           ////-----------------Loans
            $loans_monthly = fetchtable('o_loans',"disbursed=1 AND status!=0 AND given_date >= '$start_date_y' AND given_date <= '$end_date_m'","uid","desc","100000000","loan_amount, total_repaid, total_repayable_amount, loan_balance, product_id");
            while($dm = mysqli_fetch_array($loans_monthly)) {
                    $loan_amount = $dm['loan_amount'];
                    $total_repaid = $dm['total_repaid'];
                    $total_repayable_amount = $dm['total_repayable_amount'];
                    $loan_balance = $dm['loan_balance'];
                    $product_id = $dm['product_id'];
                    $product_amounts = obj_add($product_amounts, $product_id, $loan_amount);
                    $product_repaid = obj_add($product_repaid, $product_id, $total_repaid);
                    $product_repayable = obj_add($product_repayable, $product_id, $total_repayable_amount);
                    $product_balance = obj_add($product_balance, $product_id, $loan_balance);
                    $product_total = obj_add($product_total, $product_id, 1);
            }

            ////----------------NPL


            $loans_npl = fetchtable('o_loans', "disbursed=1 AND status!=0 AND given_date >= '$start_date_y' AND given_date <= '$end_date_m'","uid","desc","100000000","total_repaid, total_repayable_amount, loan_balance, product_id, paid, final_due_date");
            while($npl = mysqli_fetch_array($loans_npl)){
                $n_total_repaid = $npl['total_repaid'];
                $n_total_repayable_amount = $npl['total_repayable_amount'];
                $n_loan_balance = $npl['loan_balance'];
               // $final_due_date = $npl['final_due_date'];
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
            <h4>NPL per Product</h4>
            <table id="example2" class="table table-condensed table-striped table-bordered">
                <thead>
                <tr><th>uid</th><th>Product Name</th><th>Disbursed</th><th>Total Repayable</th> <th>Collected</th> <th>Total Loans</th><th>Coll.  Rate</th><th>NPL Amount</th><th>NPL Loans</th><th>NPL Rate</th></tr>
                </thead>
                <tbody>
                <?php
                $total_repayable_total = 0;
                foreach($product_names as $pid => $pname) {

                    $disbursed = $product_amounts[$pid];
                    $collected = $product_repaid[$pid];
                    $repayable = $product_repayable[$pid];
                    $total_loans = $product_total[$pid];
                    $coll_rate = round(($collected/$repayable)*100,2);

                    $npl_amount  = $product_npl_balance[$pid];
                    $npl_loans = $product_npl_loans[$pid];
                    $npl_repayable = $product_npl_repayable[$pid];
                    $npl_repaid = $product_npl_repaid[$pid];
                    $npl_balance = $product_npl_balance[$pid];
                    $npl_rate = round(($npl_balance/$npl_repayable)*100, 2);

                    $disbursed_total+=$disbursed;
                    $repayable_total+=$repayable;
                    $collected_total+=$collected;
                    $loans_total+=$total_loans;
                    $npl_balance_total+=$npl_balance;
                    $npl_amount_total+=$npl_amount;


                    echo " <tr><td>$pid</td><td>$pname</td><td>".money($disbursed)."</td><td>".money($repayable)."</td> <td>".money($collected)."</td> <td>".money($total_loans)."</td><th>".false_zero($coll_rate)."%</th><td>".money($npl_amount)."</td><td>".$npl_loans."</td><th>".false_zero($npl_rate)."%</th></tr>";

                }
                $coll_rate_total = round(($collected_total/$repayable_total)*100, 2);
                $npl_rate_total = round(($npl_balance_total/$repayable_total)*100, 2);
                ?>

                </tbody>
                <tfoot>
                <tr class="font-400 font-18 text-purple"><th>uid</th><th>Product Name</th><th><?php echo money($disbursed_total) ?></th><th><?php echo money($repayable_total); ?></th> <th><?php echo money($collected_total) ?></th> <th><?php echo money($loans_total) ?></th><th><?php echo money($coll_rate_total) ?></th><th><?php echo money($npl_amount_total) ?></th><th><?php echo number_format($loans_total) ?></th><th><?php echo money($npl_rate_total) ?>%</th></tr>
                </tfoot>
            </table>
        </div>


    </div>

<?php


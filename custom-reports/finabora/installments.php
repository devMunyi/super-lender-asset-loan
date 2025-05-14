<?php



?>

        <div class="col-sm-12">
            <table id="example2" class="table table-condensed table-striped table-bordered">
                <thead>
                <tr><th>ID</th><th>Customer Name</th><th>Phone</th><th>Principle</th> <th>Addons</th><th>Total</th> <th>Repaid</th><th>Instalment</th><th>All Total Loans</th><th>Total Repayable</th><th>Paid</th><th>Balance</th><th>Rate</th></tr>
                </thead>
                  <tbody>
                  <?php

                  ?>
                  </tbody>

                <tfoot>
                <tr><th>ID</th><th>BDO</th><th>Branch</th><th><?php echo number_format($total_leads); ?></th> <th><?php echo number_format($total_new_customers); ?></th> <th><?php echo number_format($new_sum, 2); ?></th> <th><?php echo number_format($total_repeat_loans); ?></th><th><?php echo number_format($repeat_sum, 2); ?></th><th><?php echo number_format($repeat_sum + $new_sum) ?></th><th>--</th><th>--</th><th>--</th><th>--</th></tr>
                </tfoot>
            </table>


        </div>

    </div>

<?php


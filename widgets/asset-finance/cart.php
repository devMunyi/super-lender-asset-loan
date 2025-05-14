<section class="content-header">
    <h1>
      Asset Finance - Cart

    </h1>
    <ol class="breadcrumb">
        <li><a href="index"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Cart</li>
    </ol>
</section>
<section class="content">
    <div class="box">
        <div class="box-body">

            <!-- /.box -->



                <!-- /.box-header -->
                <div class="row">


                    <div class="col-md-2">
                        <div class="box box-default box-solid">
                            <div class="box-body">
                                <div class="box-title font-bold font-24 text-green"></div>

                            </div>
                        </div>
                    </div>
                    <div class="col-md-7">
                        <div class="box box-default box-solid">
                            <div class="box-body">
                                <div class="box-title font-bold font-24 text-green">Cart</div>
                               <table class="table table-striped">
                                   <thead><tr><th>UID</th><th>Asset Name</th><th>Quantity</th><th>Unit Price</th><th>Line Total</th><th>Action</th></tr></thead>
                                   <tbody>
                                   <?php

                                   if(isset($_GET['loan'])){
                                       $lid = decurl($_GET['loan']);
                                       $andloan = "loan_id='$lid' AND status='2' ";
                                   }
                                   else{
                                       $andloan = " status=1 AND loan_id=0";
                                       $edit_buttons = 1;
                                   }

                                   $assets_array = table_to_obj('o_assets',"uid > 0","10000","uid","name");

                                   $cart_total = totaltable('o_asset_cart',"$andloan","total_price");

                                   $cart = fetchtable('o_asset_cart',"$andloan","uid","asc","1000","uid, asset_id, quantity, unit_price, total_price");
                                   $total = mysqli_num_rows($cart);
                                   if($total > 0) {
                                       while ($c = mysqli_fetch_array($cart)) {
                                           $uid = $c['uid'];
                                           $asset_id = $c['asset_id'];
                                           $quantity = $c['quantity'];
                                           $unit_price = $c['unit_price'];
                                           $total_price = $c['total_price'];
                                           $asset_name = $assets_array[$asset_id];

                                           if($edit_buttons == 1) {

                                               $act_remove = "<button onclick=\"cart('REMOVE', $uid);\" title='Remove' class='btn btn-sm btn-danger'><i class='fa fa-trash'></i></button>";

                                               $act_plus = "<button title='Add' class='btn btn-sm btn-primary' onclick=\"cart('ADD', $uid);\"><i class='fa fa-plus'></i></button>";

                                               $act_minus = "<button title='Reduce' class='btn btn-sm btn-primary' onclick=\"cart('MINUS', $uid);\"><i class='fa fa-minus'></i></button>";
                                           }

                                               echo "<tr id='rec$uid'><td>$uid</td><td>$asset_name</td><td id='q$uid'>$quantity</td><td id='u$uid'>" . money($unit_price) . "</td><td id='l$uid'>" . money($total_price) . "</td><td>$act_plus $act_minus $act_remove</td></tr>";

                                       }
                                   }
                                   else{
                                       echo "<tr><td colspan='4'><i>No items found <a href='assets?cat=assets'>Shop</a> </i></td></tr>";
                                   }
                                   ?>
                                   </tbody>
                                   <tfoot>
                                   <tr class="bg-gray-light font-18">
                                       <th>__</th> <th>__</th> <th>..</th> <th>Total: <span class="font-bold" id="cart_total"><?php echo money($cart_total); ?></span></th>
                                   </tr>
                                   </tfoot>
                               </table>
                                <hr/>

                                <button class="btn btn-primary" onclick="modal_view('/forms/asset_loan.php','asset_id=<?php echo $asset_id; ?>','New Asset Loan');"> Checkout</button>
                            </div>
                        </div>
                    </div>


                </div>
                <!-- /.box-body -->

            <!-- /.box -->
        </div>
        <!-- /.col -->
    </div>
</section>

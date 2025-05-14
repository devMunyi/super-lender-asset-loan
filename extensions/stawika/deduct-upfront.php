<?php

//----Deduct upfront fee from disbursed amount
$upfront_deducted = 0.9552  *$loan_amount;
//$upd = updatedb('o_loans',"disbursed_amount='$upfront_deducted'","uid='$loan_id'");
if($upd == 1){
    $event = "Upfront fee of 4.5% deducted by after script";
    store_event('o_loans', $loan_id,"$event");
}
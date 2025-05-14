<?php

// $primary_product and $loan_limit are expected to be available from calling script

$pls = fetchmaxid("o_loan_products", "uid=$primary_product", "min_amount, max_amount");
$min_amount = enforceInteger($pls['min_amount'] ?? 0);
$max_amount = enforceInteger($pls['max_amount'] ?? 0);


if ($loan_limit > 20000) {
    if (($min_amount > $loan_limit) || ($max_amount < $loan_limit)) {
        exit(errormes("The Product allows loan amounts between Ksh. $min_amount.00 and Ksh. $max_amount.00"));
    }
}


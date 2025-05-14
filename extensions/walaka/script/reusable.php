<?php 

function customerCode($string, $branch_abbrev = 'K') {
    $last_two_digits = substr($string, -3);
    $branch_abbrev =  strtoupper($string[0]);
    return $branch_abbrev . ltrim($last_two_digits, '0');
}


?>
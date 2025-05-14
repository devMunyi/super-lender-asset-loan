<?php
session_start();
include_once("../../php_functions/functions.php");
include_once("../../configs/conn.inc");
?>

<table class="table table-bordered table-striped">
    <tr>
        <td><b>60 Customers</b> are under <b>Peter John</b> who is not a current LO</td><td>Reassign</td>
    </tr>
    <tr>
        <td><b>60 Customers</b> are under <b>Owen Mwangi</b> who is not a current LO</td><td>Reassign</td>
    </tr>
    <tr>
        <td><b>20 Customers</b> are not assigned to any LO</td><td>Reassign</td>
    </tr>
    <tr>
        <td><b>60 Loans</b> are under <b>Peter John</b> who is not a valid LO</td><td>Reassign</td>
    </tr>
    <tr>
        <td><b>10 Loans</b> are under <b>Paul Mwangi</b> who is not a current CO</td><td>Reassign</td>
    </tr>
    <tr>
        <td><b>10 Loans don't have an LO</b> are not assigned to any LO</td><td>Reassign</td>
    </tr>
</table>

<?php
session_start();
include_once ("../../php_functions/functions.php");
include_once ("../../configs/conn.inc");

$staff = $_GET['staff'];

if($staff > 0){
    $staff_ = decurl($staff);
    ?>

    <table class="table well well-sm table-hover font-italic  table-striped">


        <?php
        $branch_obj = table_to_obj('o_branches',"uid > 0","1000","uid","name");
        $branches = fetchtable('o_staff_branches',"agent='$staff_' AND status=1","uid","asc","10000","uid, branch");

        while($b = mysqli_fetch_array($branches)){
            $buid = $b['uid'];
            $branch = $b['branch'];
            $branch_name = $branch_obj[$branch];

            $act = "<button onclick='remove_agent_branch($buid);' title='Remove' class='btn btn-danger btn-sm'><i class='fa fa-times'></i></button>";

            echo " <tr><td class='font-16'>$branch_name</td><td>$act</td></tr>";
        }
        ?>
    </table>



<?php


}

?>
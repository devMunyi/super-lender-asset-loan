<?php

session_start();
include_once("../php_functions/functions.php");
include_once("../configs/conn.inc");
/////----------Session Check
$userd = session_details();
if ($userd == null) {
    exit(errormes("Your session is invalid. Please re-login"));
}

$loan_id = decurl($_POST['loan_id']);
if ($loan_id  > 0) {
    $loan_fetch = fetchonerow('o_loans', "uid=$loan_id", "npl_uid");
    $npl_uid = $loan_fetch['npl_uid'] ?? 0;
}else {
    exit(errormes("Loan ID Missing!"));
}

?>

<h4 class="box box-body" style="background: #fff5e3;padding: 5px;">
    <table style="width: 100%;" class="text-bold;">
        <tr>
            <div style="width: inherit; display: flex; flex: wrap; justify-content: center;">
                <?php

                // 3, 8, 9
                $npl_tags = fetchtable('o_npl_categories', "status= 1", "uid", "asc", "100");
                echo '<div style="width: inherit; display: flex; flex-wrap: wrap; gap: 10px;">';
                while ($npl_tag = mysqli_fetch_array($npl_tags)) {
                    $tid = $npl_tag['uid'];
                    $title = $npl_tag['title'];
                    $description = $npl_tag['description'];
                    $icon = $npl_tag['icon'];

                    if($npl_uid == $tid){
                        $current_class = 'btn-primary';
                    }else{
                        $current_class = 'btn-default';
                    }

                    if($npl_uid > 0){
                        $action = 'EDIT';
                    }else{
                        $action = 'ADD';
                    }

                    echo "<span><a class='btn $current_class' title=\"$description\" onclick=\"tag_loan($loan_id, $tid)\"><img height='20px' src=\"badges/$icon\"><span class='text-black font-bold'>$title</span></a></span>";
                }


                if($npl_uid > 0){
                    echo "<div style='margin-top: 50px;' class='pull-right'><a  class='btn btn-warning  bg-warning' title=\"Remove NPL\" onclick=\"tag_loan($loan_id, 0)\"><img height='20px' src=\"badges/remove.png\"><span class='text-black font-bold'>Remove NPL Tag</span></a></div>";
                }
                echo "</div>";
                ?>
        </tr>
    </table>
</h4>
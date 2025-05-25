<?php

session_start();
include_once("../php_functions/functions.php");
include_once("../configs/conn.inc");
/////----------Session Check
$userd = session_details();
if ($userd == null) {
    exit(errormes("Your session is invalid. Please re-login"));
}

$customer_id = decurl($_POST['customer_id']);
if ($customer_id  > 0) {
    $cust = fetchonerow('o_customers', "uid='$customer_id'", "uid, national_id, full_name, badge_id");
    $client_details = $cust['full_name'] . ' (ID: ' . $cust['national_id'] . ')';
    $client_id = $cust['uid'];
    $badge_id = $cust['badge_id'] ?? 0;
}

?>

<h4 class="box box-body" style="background: #fff5e3;padding: 5px;">
    <table style="width: 100%;" class="text-bold;">
        <tr>
            <div style="width: inherit; display: flex; flex: wrap; justify-content: center;">
                <!-- <td>Tag Client <sup><span class="text-red font-bold typ">New</span></sup> </td> -->
                <?php

                // 3, 8, 9
                $badges = fetchtable('o_badges', "status= 1", "uid", "asc", "1000");
                echo '<div style="width: inherit; display: flex; flex-wrap: wrap; gap: 10px;">';
                while ($b = mysqli_fetch_array($badges)) {
                    $bid = $b['uid'];
                    $title = $b['title'];
                    $description = $b['description'];
                    $icon = $b['icon'];

                    if($badge_id == $bid){
                        $current_class = 'btn-primary';
                    }else{
                        $current_class = 'btn-default';
                    }

                    if($badge_id > 0){
                        $action = 'EDIT';
                    }else{
                        $action = 'ADD';
                    }

                    echo "<span><a class='btn $current_class' title=\"$description\" onclick=\"tag_client($client_id, $bid)\"><img height='20px' src=\"badges/$icon\">  <span class='text-black font-bold'>$title</span></a></span>";
                }


                if($badge_id > 0){
                    echo "<div style='margin-top: 50px;' class='pull-right'><a  class='btn btn-warning  bg-warning' title=\"Remove badge\" onclick=\"tag_client($client_id, 0)\"><img height='20px' src=\"badges/remove.png\"><span class='text-black font-bold'>Remove Badge</span></a></div>";
                }
                echo "</div>";
                ?>
        </tr>
    </table>
</h4>
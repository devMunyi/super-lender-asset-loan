<?php
session_start();
include_once("../../php_functions/functions.php");
include_once("../../configs/conn.inc");

$file_id = $_POST['file_id'];
$mode = $_POST['mode'];

echo "<table class='table table-bordered'>";
if ($file_id > 0) {
    $o = fetchonerow('o_documents', "uid=" . decurl($file_id), "*");
    $code_name = $o['code_name'];
    $title = $o['title'];
    $description = $o['description'];
    $category = $o['category'];           $category_name = fetchrow('o_customer_document_categories',"uid='$category'","name");
    $added_by = $o['added_by'];
    $added_date = $o['added_date'];
    $stored_address = $o['stored_address'];
    $status = $o['status'];
    // $added_by = $t['added_by'];

    $img = locateImageServer($stored_address);

    if(strtolower(substr($img, -4)) === '.pdf')  //////Is pdf
    {
            $toolbar = "#toolbar=0";
            $file = "<iframe style=\"width: 100%; height: 300px; border: none;\" src=\"$img$toolbar\"></iframe> <br/> <a href='$img' target='_blank'>Open External <i class='fa fa-external-link-square'></i></a>";
    }
    else{
        ////----Is image
        $file = "<a href='$img' target='_blank'> <img src='$img' width='100%'></a>";
    }


    echo "<tr><td>Title</td><td class='font-18 font-bold'>$title</td></tr>";
    echo "<tr><td colspan='2'>$file</td></tr>";
    echo "<tr><td>Description</td><td class='font-bold'>$description</td></tr>";
    echo "<tr><td>Category</td><td class='font-bold'>$category_name</td></tr>";
    echo "<tr><td>Added_date</td><td class='font-bold'>$added_date</td></tr>";
    //echo "<tr><td>Added Date</td><td class='font-bold'>$added_date</td></tr>";


} else {
    die(errormes("Referee invalid"));
    exit();
}
echo "</table>";
if($mode == 'EDIT') {
    ?>
    <button class="btn bg-red-gradient btn-lg" onclick="delete_file('<?php echo $file_id; ?>');">Delete</button>
    <?php
}
?>
<?php 

session_start();
include_once("../../php_functions/functions.php");
include_once("../../configs/conn.inc");
$userd = session_details();

$customer_id = $_POST['customer_id'] ?? 0;

if($customer_id == 0){
    echo "<div class='row'><span class='font-18 font-italic text-black text-mute'>No customer selected</span></div>";
    exit();
}else {
    $customer_id = decurl($customer_id);
}

$o_documents_ = fetchtable('o_documents', "tbl='o_customers' AND rec=$customer_id AND status=1", "uid", "desc", "0,100", "uid, title, category, stored_address, added_date");
$uploads_total = mysqli_num_rows($o_documents_);
$display_header = $uploads_total == 1 ? "File" : "Files";

$header_row = "<div class='row'>
    <span class='font-18 font-italic text-black text-mute'><span class='badge font-16' id='total_docs'>$uploads_total</span> $display_header Found</span>
</div>";

$files_listing_container = "<div class='row'>";
$catgories_obj = table_to_obj('o_customer_document_categories', 'status = 1', '50', 'uid', 'name');
$category_array = array();

if ($uploads_total > 0) {
    while ($q = mysqli_fetch_array($o_documents_)) {
        $uid = $q['uid'];
        $title = $q['title'];
        $added_date = $q['added_date'];
        $category = $q['category'];
        $stored_address = $q['stored_address'];
        $description = $q['description'];

        if (isset($category_array[$category])) {
            $category_array[$category][] = array('uid' => $uid, 'stored_address' => $stored_address, 'title' => $title, 'added_date' => $added_date, 'description' => $description);
        } else {
            $category_array[$category] = array(array('uid' => $uid, 'stored_address' => $stored_address, 'title' => $title, 'added_date' => $added_date, 'description' => $description));
        }
    }

    $category_row = "";
    foreach ($catgories_obj as $category_uid => $category_name) {
        if (isset($category_array[$category_uid])) {
            if (substr($category_name, -4) == 'hoto' && count($category_array[$category_uid]) != 1) {
                $category_name .= 's';
            }
            $category_row .= "<div class='row' style='margin-top:20px; text-align: center; margin-bottom: 5px;'><h3 class='font-16 text-black text-bold label label-default'>$category_name</h3></div>";

            $category_row_items_container = "<div class='row well well-sm'>";
            foreach ($category_array[$category_uid] as $key => $value) {
                $uid = $value['uid'];
                $title = $value['title'];
                $stored_address = $value['stored_address'];
                $added_date = $value['added_date'];
                $img = locateImageServer($stored_address);
                $date_only = date('Y-m-d', strtotime($added_date));
                $time_only = date('H:i:s', strtotime($added_date));
                $fancy_addedDate = fancydate($date_only);
                $display_date = $date_only . " " . $time_only . $fancy_addedDate;

                ////----PDF previews
                if(strtolower(substr($img, -4)) === '.pdf')  //////Is pdf
                {
                    $img = "custom_icons/pdf128.png";
                }
                /// ----
                $category_row_items_container .= "<div class='col-sm-6 col-md-4' style='margin-bottom: 10px;'>
                    <a title='click to view details' class='pointer' onclick='view_file(" . encurl($uid) . ", \"EDIT\")'>
                        <div class='thumbnail'>
                            <img style='height:200px; width:100%; object-fit:cover; !important;' src='$img' alt='$title'>

                            <table class='table table-bordered table-condensed table-reponsive text-muted'>
                                <tr>
                                    <td class='text-bold'>Title</td>
                                    <td style='max-width: 100px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;'>
                                        $title
                                    </td>
                                </tr>
                                <tr>
                                    <td class='text-bold'>Added Date</td>
                                    <td>$display_date</td>
                                </tr>
                            </table>
                        </div>
                    </a>
                </div>";
            }
            $category_row_items_container .= "</div>";
            $files_listing_container .= $category_row . $category_row_items_container;
            
            // Reset category_row
            $category_row = "";
        }
    }
}
$files_listing_container .= "</div>";

echo $header_row . $files_listing_container;


// include close connection
include_once("../../configs/close_connection.inc");
?>

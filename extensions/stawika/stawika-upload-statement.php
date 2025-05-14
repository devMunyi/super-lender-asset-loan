<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: access");
header("Access-Control-Allow-Methods: POST, GET, PUT");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

session_start();

$_SESSION['db_name'] = 'stawika_db';
include_once ("../configs/conn.inc");
include_once ("../php_functions/functions.php");

//include 'dbconfig.php'; // include database connection file
$session_code = $_POST['session_id'];
$device_id = $_POST['device_id'];
	
$fileName  =  $_FILES['sendimage']['name'];
$tempPath  =  $_FILES['sendimage']['tmp_name'];
$fileSize  =  $_FILES['sendimage']['size'];
$pdf_password = $_POST['pdf_password'];



if((input_length($device_id, 10)) == 0){
    $result_ = 0;
    $details_ = '"Invalid device Id"';
    $result_code = 102;
    echo json_encode("{\"result_\":$result_,\"details_\":$details_, \"result_code\":$result_code}");
    die();
    exit();
}
if((input_length($session_code, 10)) == 0){
    $result_ = 0;
    $details_ = '"Invalid Session Code"';
    $result_code = 102;
    echo json_encode("{\"result_\":$result_,\"details_\":$details_, \"result_code\":$result_code}");
    die();
    exit();
}


$session_d = fetchonerow('o_customer_sessions',"device_id='$device_id' AND session_code='$session_code' AND ending_date >= '$fulldate' AND status=1","uid, customer_id");
if($session_d['uid'] < 1){
    $result_ = 0;
    $details_ = '"Session Invalid"';
    $result_code = 107;
    echo json_encode("{\"result_\":$result_,\"details_\":$details_, \"result_code\":$result_code}");
    die();
    exit();
}
else{
    $cust_id = $session_d['customer_id'];
}

if(input_available($pdf_password) != 1){
	$result_ = 0;
    $details_ = '"Please enter a PDF password"';
    $result_code = 157;
    echo json_encode("{\"result_\":$result_,\"details_\":$details_, \"result_code\":$result_code}");
    die();
    exit();
}
		
if(empty($fileName))
{
	$result_ = 0;
    $details_ = '"Please attach your statement"';
    $result_code = 158;
    echo json_encode("{\"result_\":$result_,\"details_\":$details_, \"result_code\":$result_code}");
    die();
    exit();
}
else
{
	$upload_path = '../mpesa_statements/'; // set upload folder path 
	
	$fileExt = strtolower(pathinfo($fileName,PATHINFO_EXTENSION)); // get image extension
		
	// valid image extensions
	$valid_extensions = array('pdf', 'PDF'); 
					
	// allow valid image file formats
	if(in_array($fileExt, $valid_extensions))
	{				
		//check file not exist our upload folder path
	
			// check file size '20MB'

			if($fileSize < 20000000){
				 
				$phone_ = fetchrow('o_customers',"uid='$cust_id'","primary_mobile");

				$new_name = $phone_.'.'.$fileExt;

				$upload = upload_file_name($new_name, $tempPath, $upload_path); // move file from system temporary path to our upload folder path 
		        if($upload == '0'){
					$result_ = 0;
					$details_ = '"An error occured while uploading the file"';
					$result_code = 157;
					echo json_encode("{\"result_\":$result_,\"details_\":$details_, \"result_code\":$result_code}");
					die();
					 exit();
				}
				else{

					$fds = array('code_name','title','description','category','added_by','added_date','tbl','rec','stored_address','status');
					$vals = array("MPESA-STATEMENT","Mpesa Statement","$pdf_password","4","0","$fulldate","o_customers","$cust_id","$upload","1");
				 
					$save = addtodb('o_documents', $fds, $vals);
					if($save == 1){
					$result_ = 1;
					$details_ = '"Success submitting your statement. Please wait while we review it"';
					$result_code = 111;
					echo json_encode("{\"result_\":$result_,\"details_\":$details_, \"result_code\":$result_code}");
					die();
					 exit();
					}
					else{
						$result_ = 0;
						$details_ = '"Error, unable to upload statement"';
						$result_code = 151;
						echo json_encode("{\"result_\":$result_,\"details_\":$details_, \"result_code\":$result_code}");
						die();
						 exit();
					}
					
				}
		
			}
			else{		
				$result_ = 0;
                 $details_ = '"Sorry, your file is too large, please upload 20 MB size"';
                 $result_code = 157;
                 echo json_encode("{\"result_\":$result_,\"details_\":$details_, \"result_code\":$result_code}");
                 die();
 				 exit();
			}
		
		
	}
	else
	{		
		
		$result_ = 0;
                 $details_ = '"Sorry, only Pdf files are allowed"';
                 $result_code = 157;
                 echo json_encode("{\"result_\":$result_,\"details_\":$details_, \"result_code\":$result_code}");
                 die();
 				 exit();
	}
}
		


?>

<?php
session_start();

include_once ("../../configs/20200902.php");
$_SESSION['db_name'] = $db_;
include_once ("../../configs/conn.inc");
include_once ("../../php_functions/functions.php");


// The data is stored as a multiline string
$data = "
Kitengela	Region One
Wote	Region One
Naivasha	Region Three
Gatundu	Region Eight
Isiolo	Region Ten
Limuru	Region Nine
Marigat	Region Seven
Runyenjes	Region Ten
Nyahururu	Region Four
Mbale	Region Two
Kangari	Region Eight
Changamwe	Region Six
Nyeri	Region Four
Kibwezi	Region Six
Mwea	Region Ten
Narok	Region Nine
Kamulu	Region Eight
Kitui	Region One
Kapsowar	Region Seven
Bungoma	Region Eleven
Dagoretti	Region Nine
Kakamega	Region Eleven
Bahati	Region Three
Kabarnet	Region Seven
Molo	Region Three
Garissa	Region Eight
Kapcherop	Region Seven
Jogoo Road	Region One
Matunda	Region Seven
Mumias	Region Eleven
Njoro	Region Three
Machakos	Region One
Kitale	Region Seven
Bondo	Region Two
Luanda	Region Two
Busia	Region Two
Sondu	Region Two
Ngong	Region Nine
Kerugoya	Region Four
Githunguri	Region Eight
Rongai	Region Nine
Ruiru	Region Eight
Burnt Forest	Region Three
Eldama Ravine	Region Three
Letein	Region Five
Kiriani	Region Four
Bomet	Region Nine
Nakuru	Region Three
Iten	Region Seven
Nanyuki	Region Four
Muranga	Region Four
Ruai	Region One
Kimende	Region Nine
Kenol	Region Ten
Kasarani	Region Eight
Kilifi	Region Six
Sotik	Region Five
Chwele	Region Eleven
Serem	Region Two
Nyali	Region Six
Kawangware	Region Nine
Ahero	Region Two
Oyugis	Region Five
Kajiado	Region One
Migori	Region Five
Engineer	Region Three
Maua	Region Ten
Kiambu	Region Eight
Siaya	Region Two
Webuye	Region Eleven
Ukunda	Region Six
Kimilili	Region Eleven
Kisumu	Region Two
Umoja	Region One
Butere	Region Eleven
Chuka	Region Ten
Kinoo	Region Nine
Kapsabet	Region Two
Makutano-Kapenguria	Region Seven
Eldoret	Region Seven
Kericho	Region Three
Turbo	Region Seven
Kimana	Region One
Embu	Region Ten
Kilgoris	Region Five
Mariakani	Region Six
Emali	Region One
Keroka	Region Five
Homabay	Region Five
Gilgil	Region Three
Mtwapa	Region Six
Malindi	Region Six
Wangige	Region Nine
Rongo	Region Five
Pipeline	Region One
Thika	Region Eight
Nkubu	Region Ten
Kariobangi North	Region Eight
Marimanti	Region Ten
Malaba	Region Eleven
Mbita	Region Five
Olenguruone	Region Three
Voi	Region Six
Port Victoria	Region Two
Taita Taveta	Region Six
Mwingi	Region Eight
Kapskwony	Region Eleven
Ugunja	Region Eleven
Kehancha	Region Five
Bumala	Region Eleven
Wundanyi	Region Six
Kahatia	Region Four
Kikima	Region One
Siakago	Region Ten
Maralal	Region Four
Isebania	Region Five
Lessos	Region Seven
Mukurweini	Region Four
Mikinduri	Region Ten
Kinamba	Region Four
Adams	Region Nine
Kagio	Region Four
";

// Convert the string into an array of lines
$lines = explode("\n", trim($data));

// Loop through each line
$det = array();
foreach ($lines as $line) {
    // Split each line by tab or multiple spaces to separate name and region
    list($name_, $region) = preg_split('/\s{2,}/', trim($line));

    $det = explode(' ', trim($name_));
    $branch = $det[0];
    $region = wordToNumber($det[1]);
    $name = explode(' ', $branch);
    $branch_name = $name[0];
   $branch_ = trim(str_replace("Region", "", $branch_name));

    // Access and display the name and region
  //  echo "Name:$branch_, Region: $region<br/>";
  //  echo "$region";
  $det = array();
  if($region > 0) {
      echo "Update o_branches set region_id = '$region' WHERE uid > 0 AND name = '$branch_'; <br/>";
  }
}


function wordToNumber($word) {
    // Define an associative array for numbers in words and their corresponding numerals
    $numberWords = [
        'one' => 1,
        'two' => 2,
        'three' => 3,
        'four' => 4,
        'five' => 5,
        'six' => 6,
        'seven' => 7,
        'eight' => 8,
        'nine' => 9,
        'ten' => 10,
        'eleven' => 11,
        'twelve' => 12,
        'thirteen' => 13,
        'fourteen' => 14,
        'fifteen' => 15,
        'sixteen' => 16,
        'seventeen' => 17,
        'eighteen' => 18,
        'nineteen' => 19,
        'twenty' => 20
    ];

    // Convert word to lowercase to ensure case-insensitive matching
    $word = strtolower($word);

    // Return the corresponding number or null if the word is not found
    return isset($numberWords[$word]) ? $numberWords[$word] : null;
}



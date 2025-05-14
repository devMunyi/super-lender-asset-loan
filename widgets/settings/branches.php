<?php

/////----------Session Check
$userd = session_details();
if($userd == null){
    die(errormes("Your session is invalid. Please re-login"));
    exit();
}
$color_codes = array('#EEF2E6','#EBC7E8','#CFE8A9','#C1EFFF','#B1E1FF','#E6B325');
$regions_array = table_to_obj('o_regions',"status=1","1000","uid","name");
/////---------End of session check
?>

<div class="row">
    <div class="col-md-7">
    <h4>Branches</h4>
    <table class="table table-condensed table-striped">
            <thead>
            <tr><th>ID</th><th>Branch</th><th>Region</th></tr>
            </thead>
            <tbody>
            
    <?php
      $branches = fetchtable('o_branches',"status=1","region_id","asc","10000","uid, name, region_id");
      while($b = mysqli_fetch_array($branches)){
          $buid = $b['uid'];
          $name = $b['name'];
          $region_id = $b['region_id'];
          $region_name = $regions_array[$region_id];
          $color =  $color_codes[$region_id];

          echo "<tr style=\"background: $color;\"><td>$buid</td><td>$name</td><td>$region_name</td></tr>";
      }
    ?>
      </tbody>
        </table>

    </div>
    <div class="col-md-5 bg-info">
        <h4>Regions</h4>
        <table class="table table-condensed table-striped">
            <thead>
            <tr><th>UID</th><th>Name</th></tr>
            </thead>
            <tbody>
            <?php
            foreach ($regions_array as $reg_id => $reg_name) {
               
                    echo "<tr><td>$reg_id</td><td>$reg_name</td></tr>";
                
            }
            ?>
            </tbody>
        </table>
    </div>
</div>


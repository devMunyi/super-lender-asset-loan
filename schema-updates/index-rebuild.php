<?php
/* This script does Index Rebuilding:
- It should be run regularly for instance once per week (preferably on sunday) 
as a database maintenance routine to ensure table indexes remain effective for 
lookup and the overall optimal database performance.
- Index rebuilding overcome index fragmentation that occurs as a result of data inserts and updates 
to the indexed tables. 
- The operation can be scheduled using a service to run past midgnight when application 
has less usage like 3:00AM as the process might take sometime(a couple of minutes) to finish. 
- The impact of process does affect current table being rebuild and not all tables/entire database.
*/
session_start();
include_once ("php_functions/functions.php");
include_once ("configs/conn.inc");

// Get the list of tables in the database

$query = "SELECT table_name FROM information_schema.tables WHERE table_schema = '$dbname'";
$result = mysqli_query($con, $query);

// Rebuild indexes for each table
while ($row = mysqli_fetch_assoc($result)) {
    $table = $row['table_name'];
    echo "TABLE: $table<br>";
    $alterQuery = "ALTER TABLE `$table` FORCE";
    mysqli_query($con, $alterQuery);
}

echo "INDEX REBUILD COMPLETED";

// Close the MySQL connection
mysqli_close($con);

/*

IMPORTANT TAKEAWAY:
DATABASE TABLE(S) STATISTICS UPDATE FEATURE
- Query to ensure database takes care of automatic statistics update
    SET GLOBAL innodb_stats_on_metadata = ON;

- Alternatively you could go manual using:
    a) ANALYZE TABLE table_name; => for a single table
        OR
    b) ANALYZE TABLE database_name.*; => for all tables in the database

Why do we need statistics update feature set ON?
- Updating statistics helps the MySQL query optimizer make better decisions when generating query execution plans, 
which can improve the performance of your queries. 
- It ensures that the optimizer has accurate information about the distribution of data in the tables, 
leading to more efficient query execution

*/
?>
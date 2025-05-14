<?php


// ================== prepared statements


function addtodb_v2($tb, $fds, $bind_vals)
{
    global $con;

    try {
        // Create an array of placeholders equal to the number of values
        $placeholders = array_fill(0, count($bind_vals), '?');

        // Combine field names and placeholders
        $fields = implode(',', $fds);
        $placeholders = implode(',', $placeholders);

        $insertq = "INSERT INTO $tb ($fields) VALUES ($placeholders)";

        // Prepare the statement
        if ($stmt = mysqli_prepare($con, $insertq)) {
            // Bind parameters to the prepared statement
            if (empty($bind_vals)) {
                throw new Exception("No Binding Values Passed");
            } else {
                // bind types
                $bind_types = get_vals_types($bind_vals);

                // binding
                mysqli_stmt_bind_param($stmt, $bind_types, ...$bind_vals);
            }

            // Execute the prepared statement
            if (mysqli_stmt_execute($stmt)) {
                // Close the statement
                mysqli_stmt_close($stmt);

                return 1;
            } else {
                throw new Exception("Error executing prepared statement: " . mysqli_stmt_error($stmt));
            }
        } else {
            throw new Exception("Error preparing statement: " . mysqli_error($con));
        }
    } catch (Exception $e) {
        // Handle the exception (e.g., log the error or perform error-specific actions)
        return $e->getMessage();
    }
}

function updatedb_v2($tb, $update_fds, $update_vals, $where_fds, $where_vals)
{
    // db connection
    global $con;

    // Build the SQL query
    $updateQuery = "UPDATE $tb SET $update_fds WHERE $where_fds";

    try {
        // Prepare the statement
        if ($stmt = mysqli_prepare($con, $updateQuery)) {

            // merge update vals and where vals
            $vals = array_merge($update_vals, $where_vals);

            // types
            $bind_types = get_vals_types($vals);

            // Bind all values at once
            mysqli_stmt_bind_param($stmt, $bind_types, ...$vals);

            // Execute the prepared statement
            if (mysqli_stmt_execute($stmt)) {
                // logupdate($tb, $updateQuery);
                mysqli_stmt_close($stmt);
                return 1;
            } else {
                throw new Exception("Error executing prepared statement: " . mysqli_stmt_error($stmt));
            }
        } else {
            throw new Exception("Error preparing statement: " . mysqli_error($con));
        }
    } catch (Exception $e) {
        // Handle the exception (e.g., log it, show an error message)
        // You can customize this part based on your error handling needs.
        // error_log("Error in updatedb: " . $e->getMessage());
        return $e->getMessage();
    }
}


function fetchrow_v2($table, $where, $bind_vals, $fd)
{
    global $con;

    $query = "SELECT $fd FROM $table WHERE $where ORDER BY uid DESC";

    try {
        // Prepare the statement
        if ($stmt = mysqli_prepare($con, $query)) {

            // bind types
            $bind_types = get_vals_types($bind_vals);

            // Bind parameters
            mysqli_stmt_bind_param($stmt, $bind_types, ...$bind_vals);

            // Execute the statement
            if (mysqli_stmt_execute($stmt)) {
                // Bind the result
                mysqli_stmt_bind_result($stmt, $attrequired);

                // Fetch the result
                mysqli_stmt_fetch($stmt);

                // Close the statement
                mysqli_stmt_close($stmt);

                return $attrequired;
            } else {
                throw new Exception("Error executing statement: " . mysqli_stmt_error($stmt));
            }
        } else {
            // Handle the error if the prepare statement fails
            throw new Exception("Error preparing statement: " . mysqli_error($con));
        }
    } catch (Exception $e) {
        // Handle the exception
        // You can log or return an error message as needed
        return null;
    }
}

function checkrowexists_v2($table, $where, $bind_vals)
{
    global $con;
    // $payload = null;
    // $message = null;

    // Construct the SQL query with the provided conditions
    $query = "SELECT * FROM $table WHERE $where";

    try {
        // Prepare the statement
        if ($stmt = mysqli_prepare($con, $query)) {
            // Bind parameters to the prepared statement
            if (empty($bind_vals)) {
                throw new Exception("No Binding Values Passed");
            } else {
                // bind types
                $bind_types = get_vals_types($bind_vals);

                // binding
                mysqli_stmt_bind_param($stmt, $bind_types, ...$bind_vals);
            }

            // Execute the statement
            if (mysqli_stmt_execute($stmt)) {
                // Store the result
                mysqli_stmt_store_result($stmt);

                // Get the number of rows
                $totalrows = mysqli_stmt_num_rows($stmt);

                // Close the statement
                mysqli_stmt_close($stmt);

                if ($totalrows > 0) {
                    return 1;
                } else {
                    return 0;
                }
            } else {
                throw new Exception("Error executing statement: " . mysqli_stmt_error($stmt));
            }
        } else {
            // Throw an exception for statement preparation failure
            throw new Exception("Error preparing statement: " . mysqli_error($con));
        }
    } catch (Exception $e) {
        // Handle the exception (you can log or display an error message)
        echo "An error occurred: " . $e->getMessage();
        // $payload = -1;
        return 0;
    }
}

function validatetoken_v2($token): int
{
    global $fulldate;

    $where_tkn = "token = ? AND status = ? AND expiry_date >= ?";
    $tkn_vals = [$token, 1, $fulldate];

    $result = checkrowexists_v2("o_tokens", $where_tkn, $tkn_vals);

    return $result;
}

function fetchtable_v2($table, $where, $bind_vals, $orderby, $dir, $limit, $fds = '*')
{
    global $con;

    $query = "SELECT $fds FROM $table WHERE $where ORDER BY $orderby $dir LIMIT $limit";

    try {

        // Prepare the statement
        if ($stmt = mysqli_prepare($con, $query)) {
            // bind types
            $bind_types = get_vals_types($bind_vals);

            // Bind parameters
            mysqli_stmt_bind_param($stmt, $bind_types, ...$bind_vals);

            // Execute the statement
            if (mysqli_stmt_execute($stmt)) {
                // Get the result
                $result = mysqli_stmt_get_result($stmt);

                return $result;
            } else {
                throw new Exception("Error executing statement: " . mysqli_stmt_error($stmt));
            }
        } else {
            throw new Exception("Error in preparing statement: " . mysqli_error($con));
        }
    } catch (Exception $e) {
        // Handle the exception
        echo "An error occurred: " . $e->getMessage();
        // You can choose to log the error or perform other error handling tasks here.
        return null; // Return false or a suitable default value to indicate failure.
    }
}

function fetchtable2_v2($table, $where, $bind_vals, $orderby, $dir, $fds = '*')
////####################################Fetch whole table without a LIMIT
{
    global $con;
    $query = "SELECT $fds FROM $table  WHERE $where ORDER BY $orderby $dir";

    try {

        // Prepare the statement
        if ($stmt = mysqli_prepare($con, $query)) {
            // bind types
            $bind_types = get_vals_types($bind_vals);

            // Bind parameters
            mysqli_stmt_bind_param($stmt, $bind_types, ...$bind_vals);

            // Execute the statement
            if (mysqli_stmt_execute($stmt)) {
                // Get the result
                $result = mysqli_stmt_get_result($stmt);

                return $result;
            } else {
                throw new Exception("Error executing statement: " . mysqli_stmt_error($stmt));
            }
        } else {
            throw new Exception("Error in preparing statement: " . mysqli_error($con));
        }
    } catch (Exception $e) {
        // Handle the exception
        echo "An error occurred: " . $e->getMessage();
        // You can choose to log the error or perform other error handling tasks here.
        return null; // Return null or a suitable default value to indicate failure.
    }
}

function table_to_array_v2($tbl, $where, $bind_vals, $limit, $fld, $orderby = 'uid', $dir = 'asc')
{
    $res_array = array();
    $recs = fetchtable_v2($tbl, $where, $bind_vals, $orderby, $dir, $limit, "$fld");
    while ($r = mysqli_fetch_array($recs)) {
        $value = $r[$fld];
        array_push($res_array, $value);
    }
    return $res_array;
}


function fetchonerow_v2($table, $where, $bind_vals, $fds = '*')
{
    global $con;

    $query = "SELECT $fds FROM $table WHERE ($where) ORDER BY uid DESC LIMIT 0,1";

    try {

        // Prepare the statement
        if ($stmt = mysqli_prepare($con, $query)) {
            // bind types
            $bind_types = get_vals_types($bind_vals);

            // Bind parameters
            mysqli_stmt_bind_param($stmt, $bind_types, ...$bind_vals);

            // Execute the statement
            if (mysqli_stmt_execute($stmt)) {
                // Get the result
                $result = mysqli_stmt_get_result($stmt);

                // Fetch one row as an associative array
                $roww = mysqli_fetch_assoc($result);

                // Close the statement
                mysqli_stmt_close($stmt);

                return $roww;
            } else {
                throw new Exception("Error executing statement: " . mysqli_stmt_error($stmt));
            }
        } else {
            throw new Exception("Error in preparing statement: " . mysqli_error($con));
        }
    } catch (Exception $e) {
        // Handle the exception
        echo "An error occurred: " . $e->getMessage();
        // You can choose to log the error or perform other error handling tasks here.
        return null; // Return null or a suitable default value to indicate failure.
    }
}


function fetchmaxid_v2($table, $where, $bind_vals, $fds = '*')
{
    global $con;

    $query = "SELECT $fds FROM $table WHERE ($where) ORDER BY uid DESC LIMIT 0,1";

    try {

        // Prepare the statement
        if ($stmt = mysqli_prepare($con, $query)) {
            // bind types
            $bind_types = get_vals_types($bind_vals);

            // Bind parameters
            mysqli_stmt_bind_param($stmt, $bind_types, ...$bind_vals);

            // Execute the statement
            if (mysqli_stmt_execute($stmt)) {
                // Get the result
                $result = mysqli_stmt_get_result($stmt);

                // Fetch one row as an associative array
                $roww = mysqli_fetch_assoc($result);

                // Close the statement
                mysqli_stmt_close($stmt);

                return $roww;
            } else {
                throw new Exception("Error executing statement: " . mysqli_stmt_error($stmt));
            }
        } else {
            throw new Exception("Error in preparing statement: " . mysqli_error($con));
        }
    } catch (Exception $e) {
        // Handle the exception
        echo "An error occurred: " . $e->getMessage();
        // You can choose to log the error or perform other error handling tasks here.
        return null; // Return null or a suitable default value to indicate failure.
    }
}


function session_details_v2()
{
    $userd = null;
    if (isset($_SESSION['o-token'])) {
        $token = $_SESSION['o-token'];
        $valid = validatetoken_v2($token);

        if ($valid == 0) {
            header("location:login");
            return null;
        } else {
            $where_tkn = "token = ?";
            $tkn_vals = ["$token"];
            $token_user = fetchrow_v2('o_tokens', $where_tkn, $tkn_vals, "userid");

            $where_user_tkn = "uid = ?";
            $user_tkn_vals = ["$token_user"];
            $userd = fetchonerow_v2('o_users', $where_user_tkn, $user_tkn_vals, "*");
        }
    }
    return $userd;
}


function permission_v2($user_id, $tbl, $rec, $act)
{
    if (isNotArrayOrObject($rec)) {
        $rec = intval($rec);
    } else {
        $rec = 0;
    }

    $where_usr_grp = "uid = ?";
    $usr_grp_vals = [$user_id];
    $user_group = fetchrow_v2('o_users', $where_usr_grp, $usr_grp_vals, "user_group");
    if ($user_group == 1) {
        return  1;
    } else {
        $where_permi = "(group_id = ? OR user_id = ?) AND tbl = ? AND rec = ? AND $act = ?";
        $permi_vals = [$user_group, $user_id, "$tbl", $rec, 1];
        return checkrowexists_v2('o_permissions', $where_permi, $permi_vals);
    }
}

function store_event_v2($tbl, $fld, $event_details): void
{
    global $fulldate;
    $ses = session_details_v2();
    $event_by = $ses['uid'] ?? 0;

    $fds = array('tbl', 'fld', 'event_details', 'event_date', 'event_by', 'status');
    $vals = array("$tbl", "$fld", "$event_details", "$fulldate", $event_by, 1);

    addtodb_v2('o_events', $fds, $vals);
}


// non-db functions
function get_vals_types($vals): string
{
    $types = "";
    foreach ($vals as $val) {
        if (is_int($val)) {
            $types .= 'i'; // Integer
        } elseif (is_float($val)) {
            $types .= 'd'; // Double/Float
        } else {
            $types .= 's'; // String (default)
        }
    }
    return $types;
}

function isNotArrayOrObject($value): bool
{
    return !is_array($value) && !is_object($value);
}


//// =============End of Prepared statements functions

?>
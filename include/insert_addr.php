<?php


$json_data = file_get_contents('new_state_updated_local.json');
$data = json_decode($json_data, true);
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "doodhbazar";

$conn = mysqli_connect($servername, $username, $password, $dbname);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

             // Insert data into the villages table
            // foreach ($villages as $village) {
            //     $village_name = strtolower($village['village']);
            //     $local_name = $village['local_name'];
            //     $sql = "SELECT sd_id FROM sub_districts WHERE sub_district_name = ? AND district_id = ? AND state_id = ?";
            //     $stmt = mysqli_prepare($conn, $sql);
            //     mysqli_stmt_bind_param($stmt, "sii", $sub_district_name, $district_id, $state_id);
            //     mysqli_stmt_execute($stmt);
            //     $result = mysqli_stmt_get_result($stmt);
            //     $row = mysqli_fetch_assoc($result);
            //     $sub_district_id = $row['sd_id'];

            //     $sql = "INSERT INTO villages (village_name, local_name, sub_district_id, district_id, state_id) VALUES (?, ?, ?, ?, ?)";
            //     $stmt = mysqli_prepare($conn, $sql);
            //     mysqli_stmt_bind_param($stmt, "ssiii", $village_name, $local_name, $sub_district_id, $district_id, $state_id);
            //     if (mysqli_stmt_execute($stmt)) {
            //         echo "Village: $village_name, Local Name: $local_name<br>";
            //     } else {
            //         echo "<font color='red'>Error villages table: " . mysqli_error($conn) . "</font><br>";
            //     }
            // }


// Insert data into the states table
foreach ($data as $state) {
    $state_name = strtolower($state['state']);
    $sql = "INSERT INTO states (state_name) VALUES ('$state_name')";

    if (mysqli_query($conn, $sql)) {
        $state_id = mysqli_insert_id($conn);
        echo "<font color='green'>State => $state_name</font> <br>";

    } else {
        echo "<font color='red'>Error State table: Error state table: " . mysqli_error($conn)."</font><br>";
    }

    foreach ($state['districts'] as $district) {
        $district_name = strtolower($district['district']);
        $sql = "INSERT INTO districts (district_name, states_id) VALUES ('$district_name', $state_id)";

        if (mysqli_query($conn, $sql)) {
            echo "<b>$district_name </b><br>";

            // Insert data into the sub-districts table

            foreach ($district['subDistricts'] as $sub_district) {
                $sub_district_name = strtolower($sub_district['subDistrict']);
                $sql = "SELECT d_id FROM districts WHERE district_name = ? AND states_id = ?";
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "si", $district_name, $state_id);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                $row = mysqli_fetch_assoc($result);
                $district_id = $row['d_id'];
            
                $sql = "INSERT INTO sub_districts (sub_district_name, district_id, state_id) VALUES (?, ?, ?)";
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "sii", $sub_district_name, $district_id, $state_id);
                if (mysqli_stmt_execute($stmt)) {
                   // echo "<font color='blue'> => $sub_district_name</font><br>";

                    // Insert Village Data


                    foreach ($sub_district['villages'] as $village) {
                        // Get the village and local_name values
                        $village_name = strtolower($village["village"]);
                        $local_name = $village["local_name"];
                    
                        $sql = "SELECT sd_id FROM sub_districts WHERE sub_district_name = ? AND district_id = ? AND  state_id = ?";
        
                        $stmt = mysqli_prepare($conn, $sql);
                        mysqli_stmt_bind_param($stmt, "sii", $sub_district_name, $district_id, $state_id);

                        mysqli_stmt_execute($stmt);
                        $result = mysqli_stmt_get_result($stmt);
                        $row = mysqli_fetch_assoc($result);
                        $sub_district_id = $row['sd_id'];
                        // Prepare the SQL query
                        $sql = "INSERT INTO villages (village, local_name, sub_district_id, district_id, state_id) VALUES (?, ?, ?, ?, ?)";
                    
                        // Prepare the SQL statement
                        $stmt = mysqli_prepare($conn, $sql);
                    
                        // Bind the parameters
                        mysqli_stmt_bind_param($stmt, 'ssiii', $village_name, $local_name, $sub_district_id, $district_id, $state_id);
                    
                        // Execute the prepared statement
                        if(mysqli_stmt_execute($stmt)){
                            //echo "<b> Villages Added Succefully</b><br>";
                        }
                    
                        // Close the statement
                        mysqli_stmt_close($stmt);
                    }
                } else {
                    echo "<font color='red'>Error sub_districts table: " . mysqli_error($conn) . "</font><br>";
                }
            }
          
        } else {
            echo "<font color='red'>Error districts table: " . mysqli_error($conn)."</font><br>";
        }
    }

    // Insert data into the districts table


}


mysqli_close($conn);

?>

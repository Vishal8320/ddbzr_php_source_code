<?php

$json_data = file_get_contents('new_state_updated_local.json');
$data = json_decode($json_data, true);

// Connect to your database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "test";

$conn = mysqli_connect($servername, $username, $password, $dbname);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Insert data into the villages table
foreach ($data as $state) {
    $state_name = strtolower($state['state']);

    foreach ($state['districts'] as $district) {
        $district_name = strtolower($district['district']);

        foreach ($district['subDistricts'] as $sub_district) {
            $sub_district_name = strtolower($sub_district['subDistrict']);

            foreach ($sub_district['villages'] as $village) {
                $village_name = $village['village'];
                $local_name = $village['local_name'];

                $sql = "SELECT sd_id, d_id FROM sub_districts sd
                        INNER JOIN districts d ON sd.district_id = d.d_id
                        WHERE sd.sub_district_name = ? AND d.district_name = ? AND d.states_id = ?";
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "ssi", $sub_district_name, $district_name, $state_name);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                $row = mysqli_fetch_assoc($result);
                $sub_district_id = $row['sd_id'];
                $district_id = $row['d_id'];

                $sql = "INSERT INTO villages (village_name, local_name, sub_district_id, district_id, state_id)
                        VALUES (?, ?, ?, ?, ?)";
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "sssii", $village_name, $local_name, $sub_district_id, $district_id, $state_id);
                if (mysqli_stmt_execute($stmt)) {
                    echo "<font color='green'>Village => $village_name, $sub_district_name, $district_name, $state_name</font> <br>";
                } else {
                    echo "<font color='red'>Error villages table: " . mysqli_error($conn) . "</font><br>";
                }
            }
        }
    }
}

mysqli_close($conn);


?>
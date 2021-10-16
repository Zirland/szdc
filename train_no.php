<?php
$link = mysqli_connect('localhost', 'root', 'root', 'SZDC');
if (!$link) {
    echo "Error: Unable to connect to MySQL." . PHP_EOL;
    echo "Debugging errno: " . mysqli_connect_errno() . PHP_EOL;
    exit;
}

$query9 = "SELECT trip_id FROM trip;";
if ($result9 = mysqli_query($link, $query9)) {
    while ($row9 = mysqli_fetch_row($result9)) {
        $trip_id = $row9[0];

        $newtrip = preg_replace('/\D+/', '', substr($trip_id, 0, -8));

        $query16 = "UPDATE trip SET train_no = '$newtrip' WHERE trip_id = '$trip_id';";
        $prikaz16 = mysqli_query($link, $query16);
    }
}

echo "Hotovo...";

mysqli_close($link);

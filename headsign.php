<?php
$link = mysqli_connect('localhost', 'root', 'root', 'vlaky');
if (!$link) {
    echo "Error: Unable to connect to MySQL." . PHP_EOL;
    echo "Debugging errno: " . mysqli_connect_errno() . PHP_EOL;
    exit;
}

unset($seznam);

$query1 = "SELECT trip_id FROM trip;";
if ($result1 = mysqli_query($link, $query1)) {
    while ($row1 = mysqli_fetch_row($result1)) {
        $trip_id = $row1[0];

        $query_max = "SELECT stop_name FROM stop WHERE stop_id IN (
			SELECT stop_id FROM stoptime WHERE trip_id ='$trip_id' AND stop_sequence IN (
				SELECT max(stop_sequence) FROM stoptime WHERE trip_id = '$trip_id'
			)
		);";
        $data_max = mysqli_fetch_row(mysqli_query($link, $query_max));
        $max_name = $data_max[0];

        $query19  = "UPDATE trip SET trip_headsign = '$max_name' WHERE trip_id= '$trip_id';";
        $prikaz19 = mysqli_query($link, $query19);

        echo "$trip_id > $max_name<br/>";
    }
}

echo "<br/>";
echo "Hotovo...";

mysqli_close($link);

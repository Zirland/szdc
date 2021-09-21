<?php
$link = mysqli_connect ('localhost', 'gtfs', 'gtfs', 'SZDC');
if (!$link) {
	echo "Error: Unable to connect to MySQL.".PHP_EOL;
	echo "Debugging errno: ".mysqli_connect_errno ().PHP_EOL;
	exit;
}

$query9 = "SELECT id, trip_id FROM jizdy;";
if ($result9 = mysqli_query($link, $query9)) {
	while ($row9 = mysqli_fetch_row($result9)) {
		$id = $row9[0];
		$trip_id = $row9[1];

		$skupina = substr($trip_id, 0, -6);

		$query17 = "UPDATE jizdy SET shortname = '$skupina' WHERE id = '$id';";
		$prikaz17 = mysqli_query($link, $query17);
	}
}

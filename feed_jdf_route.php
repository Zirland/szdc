<?php
$oblast = $_GET["oblast"];

$link = mysqli_connect ('localhost', 'gtfs', 'gtfs', 'JDF');
if (!$link) {
	echo "Error: Unable to connect to MySQL.".PHP_EOL;
	echo "Debugging errno: ".mysqli_connect_errno ().PHP_EOL;
	exit;
}

$akt_trip = "SELECT route_id, matice, trip_id, trip_headsign, direction_id, shape_id, wheelchair_accessible, bikes_allowed FROM trip WHERE active='1' AND (trip_id LIKE '$oblast%' OR trip_id LIKE 'F$oblast%');";

$current = "";

if ($result85 = mysqli_query ($link, $akt_trip)) {
	while ($row85 = mysqli_fetch_row ($result85)) {
		$route_id = $row85[0];
		$matice = $row85[1];
		$trip_id = $row85[2];
		$trip_headsign = $row85[3];
		$direction_id = $row85[4];
		$shape_tvar = $row85[5];
		$wheelchair_accessible = $row85[6];
		$bikes_allowed = $row85[7];

		$matice_start = mktime (0,0,0,12,3,2017);
		$dnes_den = date("j", time ());
		$dnes_mesic = date("n", time ());
		$dnes_rok = date("Y", time ());

		$calendar_start = mktime (0,0,0,$dnes_mesic,$dnes_den,$dnes_rok);
		$calendar_start_format = date ("Ymd", $calendar_start);
		$calendar_stop_format = date ("Ymd", $calendar_start + 6 * 86400);
		$vtydnu = date ('w',$calendar_start);

		$sek = $calendar_start - $matice_start;
		$min = floor ($sek / 60);
		$sek = $sek % 60;
		$hod = floor ($min / 60);
		$min = $min % 60;
		$dni = floor ($hod / 24);
		$hod = $hod % 24;
		$aktual = substr ($matice,$dni + 1,7);

		$adjust = substr ($aktual,-$vtydnu + 1).substr ($aktual,0,-$vtydnu + 1);
		$dec = bindec ($adjust) + 1;

		$service_id = $dec;

		$mark_cal = mysqli_query ($link, "INSERT INTO SZDC.cal_use (trip_id, kalendar) VALUES ('$trip_id', '$service_id');");

		$query152 = "SELECT shape_id FROM shapetvary WHERE tvartrasy = '$shape_tvar';";
		if ($result152 = mysqli_query ($link, $query152)) {
			$radku = mysqli_num_rows ($result152);
			if ($radku == 0) {
				$vloztrasu = mysqli_query ($link, "INSERT INTO shapetvary (tvartrasy, complete) VALUES ('$shape_tvar', '0');");
				$shape_id = mysqli_insert_id ($link);
			} else {
				while ($row152 = mysqli_fetch_row ($result152)) {
					$shape_id = $row152[0];
				}
			}
			mysqli_free_result($result152);
		}

		$current .= "$route_id,$service_id,$trip_id,\"$trip_headsign\",$direction_id,J$shape_id,$wheelchair_accessible,$bikes_allowed\n";

		$query171 = "INSERT INTO shapecheck (trip_id, shape_id) VALUES ('$trip_id', '$shape_id');";
		$zapistrasy = mysqli_query ($link, $query171);
	}
	$file = 'trips.txt';
	file_put_contents ($file, $current, FILE_APPEND);
	mysqli_free_result($result85);
}

$tripstops = "SELECT trip_id, arrival_time, departure_time, stop_id, stop_sequence, pickup_type, drop_off_type FROM stoptime WHERE trip_id IN (SELECT trip_id FROM trip WHERE active='1' AND (trip_id LIKE '$oblast%' OR trip_id LIKE 'F$oblast%'));";

$current = "";

if ($result166 = mysqli_query ($link, $tripstops)) {
	while ($row166 = mysqli_fetch_row ($result166)) {
		$trip_id = $row166[0];
		$arrival_time = $row166[1];
		$departure_time = $row166[2];
		$stop_id = $row166[3];
		$stop_sequence = $row166[4];
		$pickup_type = $row166[5];
		$drop_off_type = $row166[6];

		$current .= "$trip_id,$arrival_time,$departure_time,$stop_id,$stop_sequence,$pickup_type,$drop_off_type\n";

		$mark_stop = mysqli_query ($link, "INSERT INTO stop_use (trip_id, stop_id) VALUES ('$trip_id', '$stop_id');");
	}
	$file = 'stop_times.txt';
	file_put_contents ($file, $current, FILE_APPEND);
	mysqli_free_result($result166);
}

echo "Oblast: $oblast\n";

mysqli_close ($link);
?>

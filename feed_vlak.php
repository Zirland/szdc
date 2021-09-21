<?php
$l1 = $_GET["l1"];
$l2 = $_GET["l2"];

$link = mysqli_connect ('localhost', 'gtfs', 'gtfs', 'SZDC');
if (!$link) {
	echo "Error: Unable to connect to MySQL.".PHP_EOL;
	echo "Debugging errno: ".mysqli_connect_errno ().PHP_EOL;
	exit;
}

$time_start0 = microtime(true);

$akt_trip = "SELECT route_id,trip_id,trip_headsign,direction_id,shape_id,wheelchair_accessible,bikes_allowed FROM trip WHERE active = '1' AND route_id NOT IN (SELECT route FROM `ignore`) AND trip_id > ".$l1."00000000 AND trip_id < ".$l2."00000000;";

if ($result85 = mysqli_query ($link, $akt_trip)) {
	while ($row85 = mysqli_fetch_row ($result85)) {
		$route_id = $row85[0];
		$trip_id = $row85[1];
		$trip_headsign = $row85[2];
		$direction_id = $row85[3];
		$shape_tvar = $row85[4];
		$wheelchair_accessible = $row85[5];
		$bikes_allowed = $row85[6];

		$matice = "0000000";

		$dnes = date("Y-m-d", time());
		$tyden = date("Y-m-d", strtotime("+ 1 week"));

		$dnesden = substr($dnes,8,2);
		$dnesmesic = substr($dnes,5,2);
		$dnesrok = substr($dnes,0,4);
		$dnestime = mktime(0,0,0,$dnesmesic,$dnesden,$dnesrok);

		$tydenden = substr($tyden,8,2);
		$tydenmesic = substr($tyden,5,2);
		$tydenrok = substr($tyden,0,4);
		$tydentime = mktime(0,0,0,$tydenmesic,$tydenden,$tydenrok);

		$query64 = "SELECT * FROM jizdy WHERE trip_id = '$trip_id' AND (datum>='$dnes' AND datum<'$tyden');";
		if ($result64 = mysqli_query($link, $query64)) {
			while ($row64 = mysqli_fetch_row($result64)) {
				$datum = $row64[3];

				$datumden = substr($datum,8,2);
				$datummesic = substr($datum,5,2);
				$datumrok = substr($datum,0,4);
				$datumtime = mktime(0,0,0,$datummesic,$datumden,$datumrok);

				$dnu = round (($datumtime - $dnestime) / 86400); 
				$matice[$dnu] = 1;
			}
		}

		$vtydnu = date ('w',$dnestime);

		$adjust = substr($matice,-$vtydnu + 1).substr ($matice,0,-$vtydnu + 1);
		$dec= bindec ($adjust) + 1;

		$service_id = $dec;
		$mark_cal = mysqli_query ($link, "INSERT INTO cal_use (trip_id, kalendar) VALUES ('$trip_id', '$service_id');");

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

		$current = "$route_id,$service_id,$trip_id,\"$trip_headsign\",$direction_id,$shape_id,$wheelchair_accessible,$bikes_allowed\n";
		$file = 'trips.txt';
		file_put_contents ($file, $current, FILE_APPEND);

		$query171 = "INSERT INTO shapecheck (trip_id, shape_id) VALUES ('$trip_id', '$shape_id');";
		$zapistrasy = mysqli_query ($link, $query171);

	}
	mysqli_free_result($result85);
}

$tripstops = "SELECT trip_id,arrival_time,departure_time,stop_id,stop_sequence,pickup_type,drop_off_type FROM stoptime WHERE trip_id IN (SELECT trip_id FROM trip WHERE active = '1' AND route_id NOT IN (SELECT route FROM `ignore`) AND trip_id > ".$l1."00000000 AND trip_id < ".$l2."00000000);";
if ($result166 = mysqli_query ($link, $tripstops)) {
	while ($row166 = mysqli_fetch_row ($result166))  {
		$trip_id = $row166[0];
		$arrival_time = $row166[1];
		$departure_time = $row166[2];
		$stop_id = $row166[3];
		$stop_sequence = $row166[4];
		$pickup_type = $row166[5];
		$drop_off_type = $row166[6];

		$current = "$trip_id,$arrival_time,$departure_time,$stop_id,$stop_sequence,$pickup_type,$drop_off_type\n";
		$file = 'stop_times.txt';
		file_put_contents ($file, $current, FILE_APPEND);

		$mark_stop = mysqli_query($link, "INSERT INTO stop_use (trip_id, stop_id) VALUES ('$trip_id', '$stop_id');");
	}
}

$now = microtime(true);
echo "Rozsah $l1 až $l2: ";
echo $now - $time_start0;
echo "<br>\n";
$time_start = $now;

mysqli_close ($link);
?>

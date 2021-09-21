<?php
$link = mysqli_connect ('localhost', 'gtfs', 'gtfs', 'JDF');
if (!$link) {
	echo "Error: Unable to connect to MySQL.".PHP_EOL;
	echo "Debugging errno: ".mysqli_connect_errno ().PHP_EOL;
	exit;
}

$time_start = microtime(true);

$query11 = "SELECT shape_id, tvartrasy FROM shapetvary WHERE complete = 0;";
if ($result11 = mysqli_query($link, $query11)) {
	while($row11 = mysqli_fetch_row($result11)) {
		$shape_id = $row11[0];
		$tvartrasy = $row11[1];

		$smaz16 = "DELETE FROM shape WHERE shape_id = '$shape_id';";
		$smazanitrasy = mysqli_query ($link,$smaz16);

		$i = 0;
		$prevstop = "";
		$komplet = 1;

		$output = explode('|', $tvartrasy);

		foreach ($output as $prujstop) {
			$query107 = "SELECT du.path FROM du WHERE (STOP1 = '$prevstop') AND (STOP2 = '$prujstop');";
			$result235 = mysqli_query ($link, $query107);

			$pom235 = mysqli_fetch_row ($result235);
			$linie = $pom235[0];

			$body = explode(';', $linie);

			foreach ($body as $point) {
				$sourad = explode(',', $point);
				$lon = $sourad[0];
				$lat = $sourad[1];

				if ($lat != '' && $lon != '') {
					$i = $i + 1;
					$query144 = "INSERT INTO shape VALUES ('$shape_id','$lat','$lon','$i',0);";
					$command = mysqli_query ($link, $query144);
				}
			}
			$prevstop = $prujstop;
		}
		$query217 = "UPDATE shapetvary SET complete = '$komplet' WHERE shape_id = '$shape_id';";
		$command217 = mysqli_query ($link, $query217);
	}
	mysqli_free_result($result11);
}

$now = microtime(true);
echo "Check shapes: ";
echo $now - $time_start;
echo "<br>\n";
$time_start = $now;

$current = "";

$query46 = "SELECT agency_id,agency_name,agency_url,agency_timezone,agency_phone FROM agency WHERE agency_id IN (SELECT DISTINCT agency_id FROM ag_use);";

if ($result46 = mysqli_query ($link, $query46)) {
	while ($row46 = mysqli_fetch_row ($result46)) {
		$agency_id = $row46[0];
		$agency_name = $row46[1];
		$agency_url = $row46[2];
		$agency_timezone = $row46[3];
		$agency_phone = $row46[4];
		$agencynums = mysqli_num_rows ($result46);

		$current .= "$agency_id,\"$agency_name\",$agency_url,$agency_timezone,\"$agency_phone\"\n";
	}
	mysqli_free_result($result46);
}

$file = 'agency.txt';
file_put_contents ($file, $current, FILE_APPEND);

$now = microtime(true);
echo "Agencies: ";
echo $now - $time_start;
echo "<br>\n";
$time_start = $now;

$current = "";

$file = 'stops.txt';
$query233 = "SELECT stop_id,stop_name,stop_lat,stop_lon,location_type,parent_station,wheelchair_boarding,stop_code FROM stop WHERE (stop_id IN (SELECT stop_id FROM stop_use));";
if ($result233 = mysqli_query ($link, $query233)) {
	while ($row233 = mysqli_fetch_row ($result233)) {
		$stop_id = $row233[0];
		$stop_name = $row233[1];
		$stop_lat = $row233[2];
		$stop_lon = $row233[3];
		$location_type = $row233[4];
		$parent_station = $row233[5];
		$wheelchair_boarding = $row233[6];
		$stop_code = $row233[7];
		$stopnums = mysqli_num_rows ($result233);

		$current = "$stop_id,$stop_code,\"$stop_name\",$stop_lat,$stop_lon,$location_type,$parent_station,$wheelchair_boarding\n";
		file_put_contents ($file, $current, FILE_APPEND);

		if ($parent_station != '') {
			$mark_parent = mysqli_query ($link, "INSERT INTO parent_use (stop_id) VALUES ('$parent_station');");
		}
	}
	mysqli_free_result($result233);
}

$now = microtime(true);
echo "Stops: ";
echo $now - $time_start;
echo "<br>\n";
$time_start = $now;

$query313 = "SELECT stop_id,stop_name,stop_lat,stop_lon,location_type,parent_station,wheelchair_boarding,stop_code FROM stop WHERE (stop_id IN (SELECT stop_id FROM parent_use));";
if ($result313 = mysqli_query ($link, $query313)) {
	while ($row313 = mysqli_fetch_row ($result313)) {
		$stop_id = $row313[0];
		$stop_name = $row313[1];
		$stop_lat = $row313[2];
		$stop_lon = $row313[3];
		$location_type = $row313[4];
		$parent_station = $row313[5];
		$wheelchair_boarding = $row313[6];
		$stop_code = $row313[7];
		$stopnums = $stopnums + mysqli_num_rows ($result313);

		$current = "$stop_id,$stop_code,\"$stop_name\",$stop_lat,$stop_lon,$location_type,$parent_station,$wheelchair_boarding\n";
		file_put_contents ($file, $current, FILE_APPEND);
	}
	mysqli_free_result($result313);
}

$now = microtime(true);
echo "Parents: ";
echo $now - $time_start;
echo "<br>\n";
$time_start = $now;

$current = "";
$file = 'shapes.txt';

$query260 = "SELECT shape_id,shape_pt_lat,shape_pt_lon,shape_pt_sequence FROM shape WHERE shape_id IN (SELECT DISTINCT shape_id FROM shapecheck);";
if ($result260 = mysqli_query ($link, $query260)) {
	while ($row260 = mysqli_fetch_row ($result260)) {
		$shape_id = $row260[0];
		$shape_pt_lat = $row260[1];
		$shape_pt_lon = $row260[2];
		$shape_pt_sequence = $row260[3];

		$current = "J$shape_id,$shape_pt_lat,$shape_pt_lon,$shape_pt_sequence\n";
		file_put_contents ($file, $current, FILE_APPEND);
	}
	mysqli_free_result($result260);
}

$now = microtime(true);
echo "Shapes: ";
echo $now - $time_start;
echo "<br>\n";
$time_start = $now;

mysqli_close ($link);

$link = mysqli_connect ('localhost', 'gtfs', 'gtfs', 'SZDC');
if (!$link) {
	echo "Error: Unable to connect to MySQL.".PHP_EOL;
	echo "Debugging errno: ".mysqli_connect_errno ().PHP_EOL;
	exit;
}

$time_start = microtime(true);

$query11 = "SELECT shape_id, tvartrasy FROM shapetvary WHERE complete = 0;";
if ($result11 = mysqli_query($link, $query11)) {
	while($row11 = mysqli_fetch_row($result11)) {
		$shape_id = $row11[0];
		$tvartrasy = $row11[1];

		$smaz16 = "DELETE FROM shape WHERE shape_id = '$shape_id';";
		$smazanitrasy = mysqli_query ($link,$smaz16);

		$i = 0;
		$prevstop = "";
		$komplet = 1;

		$output = explode('|', $tvartrasy);

		foreach ($output as $prujbod) {
			$pom139 = mysqli_fetch_row (mysqli_query ($link, "SELECT stop_name,stop_lat,stop_lon FROM stop WHERE (stop_id='$prujbod');"));
			$name = $pom139[0];
			$lat = $pom139[1];
			$lon = $pom139[2];
			$i = $i + 1;

			if ($lat != '' && $lon != '') {
				$query144 = "INSERT INTO shape VALUES ('$shape_id','$lat','$lon','$i',0);";
				$command = mysqli_query($link, $query144);
			} 
		}
	}
	$query217 = "UPDATE shapetvary SET complete = '$komplet' WHERE shape_id = '$shape_id';";
	$command217 = mysqli_query ($link, $query217);
	mysqli_free_result($result11);
}

$now = microtime(true);
echo "Check shapes: ";
echo $now - $time_start;
echo "<br>\n";
$time_start = $now;

$current = "";

$query46 = "SELECT agency_id,agency_name,agency_url,agency_timezone,agency_phone FROM agency WHERE agency_id IN (SELECT DISTINCT agency_id FROM ag_use);";

if ($result46 = mysqli_query ($link, $query46)) {
	while ($row46 = mysqli_fetch_row ($result46)) {
		$agency_id = $row46[0];
		$agency_name = $row46[1];
		$agency_url = $row46[2];
		$agency_timezone = $row46[3];
		$agency_phone = $row46[4];
		$agencynums = mysqli_num_rows ($result46);

		$current .= "$agency_id,\"$agency_name\",$agency_url,$agency_timezone,\"$agency_phone\"\n";
	}
	mysqli_free_result($result46);
}

$file = 'agency.txt';
file_put_contents ($file, $current, FILE_APPEND);

$now = microtime(true);
echo "Agencies: ";
echo $now - $time_start;
echo "<br>\n";
$time_start = $now;

$current = "";

$dnes_den = date ("j", time ());
$dnes_mesic = date ("n", time ());
$dnes_rok = date ("Y", time ());

$calendar_start = mktime (0,0,0,$dnes_mesic,$dnes_den,$dnes_rok);
$calendar_start_format = date("Ymd", $calendar_start);
$calendar_stop_format = date("Ymd", $calendar_start+6*86400);

$query193 = "SELECT service_id,monday,tuesday,wednesday,thursday,friday,saturday,sunday FROM calendar WHERE (service_id IN (SELECT DISTINCT kalendar FROM cal_use)) ORDER BY service_id;";
if ($result193 = mysqli_query ($link, $query193)) {
	while ($row193 = mysqli_fetch_row ($result193)) {
		$service_id = $row193[0];
		$monday = $row193[1];
		$tuesday = $row193[2];
		$wednesday = $row193[3];
		$thursday = $row193[4];
		$friday = $row193[5];
		$saturday = $row193[6];
		$sunday = $row193[7];

		$current .= "$service_id,$monday,$tuesday,$wednesday,$thursday,$friday,$saturday,$sunday,$calendar_start_format,$calendar_stop_format\n";
	}
}

$file = 'calendar.txt';
file_put_contents ($file, $current, FILE_APPEND);

$now = microtime(true);
echo "Calendars: ";
echo $now - $time_start;
echo "<br>\n";
$time_start = $now;

$current = "";

$file = 'stops.txt';
$query233 = "SELECT stop_id,stop_name,stop_lat,stop_lon,location_type,parent_station,wheelchair_boarding,stop_code FROM stop WHERE (stop_id IN (SELECT stop_id FROM stop_use));";
if ($result233 = mysqli_query ($link, $query233)) {
	while ($row233 = mysqli_fetch_row ($result233)) {
		$stop_id = $row233[0];
		$stop_name = $row233[1];
		$stop_lat = $row233[2];
		$stop_lon = $row233[3];
		$location_type = $row233[4];
		$parent_station = $row233[5];
		$wheelchair_boarding = $row233[6];
		$stop_code = $row233[7];
		$stopnums = mysqli_num_rows ($result233);

		$current = "$stop_id,$stop_code,\"$stop_name\",$stop_lat,$stop_lon,$location_type,$parent_station,$wheelchair_boarding\n";
		file_put_contents ($file, $current, FILE_APPEND);

		if ($parent_station != '') {
			$mark_parent = mysqli_query ($link, "INSERT INTO parent_use (stop_id) VALUES ('$parent_station');");
		}
	}
}

$now = microtime(true);
echo "Stops: ";
echo $now - $time_start;
echo "<br>\n";
$time_start = $now;

$query313 = "SELECT stop_id,stop_name,stop_lat,stop_lon,location_type,parent_station,wheelchair_boarding,stop_code FROM stop WHERE (stop_id IN (SELECT stop_id FROM parent_use));";
if ($result313 = mysqli_query ($link, $query313)) {
	while ($row313 = mysqli_fetch_row ($result313)) {
		$stop_id = $row313[0];
		$stop_name = $row313[1];
		$stop_lat = $row313[2];
		$stop_lon = $row313[3];
		$location_type = $row313[4];
		$parent_station = $row313[5];
		$wheelchair_boarding = $row313[6];
		$stop_code = $row313[7];
		$stopnums = $stopnums + mysqli_num_rows ($result313);

		$current = "$stop_id,$stop_code,\"$stop_name\",$stop_lat,$stop_lon,$location_type,$parent_station,$wheelchair_boarding\n";
		file_put_contents ($file, $current, FILE_APPEND);
	}
}

$now = microtime(true);
echo "Parents: ";
echo $now - $time_start;
echo "<br>\n";
$time_start = $now;

$current = "";
$file = 'shapes.txt';

$query260 = "SELECT shape_id,shape_pt_lat,shape_pt_lon,shape_pt_sequence FROM shape WHERE shape_id IN (SELECT DISTINCT shape_id FROM shapecheck);";
if ($result260 = mysqli_query ($link, $query260)) {
	while ($row260 = mysqli_fetch_row ($result260)) {
		$shape_id = $row260[0];
		$shape_pt_lat = $row260[1];
		$shape_pt_lon = $row260[2];
		$shape_pt_sequence = $row260[3];

		$current = "$shape_id,$shape_pt_lat,$shape_pt_lon,$shape_pt_sequence\n";
		file_put_contents ($file, $current, FILE_APPEND);
	}
}

$now = microtime(true);
echo "Shapes: ";
echo $now - $time_start;
echo "<br>\n";
$time_start = $now;

mysqli_close ($link);
?>

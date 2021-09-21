<?php
date_default_timezone_set('Europe/Prague');

function getContrastYIQ ($hexcolor){
	$r = hexdec(substr($hexcolor,0,2));
	$g = hexdec(substr($hexcolor,2,2));
	$b = hexdec(substr($hexcolor,4,2));
	$yiq = (($r * 299) + ($g * 587) + ($b * 114)) / 1000;
	return ($yiq >= 128) ? '000000' : 'FFFFFF';
}

function SelfCheck ($Code) {
	$arr = str_split($Code,1);
	$j = 1;
	$souc = 0;
	foreach ($arr as $num) {
		if (($j % 2) == 1) {$num = $num * 2;}
		if ($num > 9) {
			$rozp = str_split($num,1);
			$num = $rozp[0] + $rozp[1];
		}
		$souc = $souc + $num;
		$j = $j + 1;
	}
	$kontr = 100 - $souc;
	while ($kontr > 9) {
		$kontr = $kontr - 10;
	}
	return $kontr;
}

$staty = [
	"AT" => "81",
	"BY" => "21",
	"CZ" => "54",
	"DE" => "80",
	"FR" => "87",
	"HU" => "55",
	"IT" => "83",
	"PL" => "51",
	"RU" => "20",
	"SK" => "56",
];

$barvy = [
	"50" => "008000",
	"63" => "008000",
	"69" => "008000",
	"70" => "B51741",
	"84" => "0094DE",
	"94" => "008000",
	"122" => "0094DE",
	"157" => "B51741",
	"209" => "008000",
	"9000" => "008000",
	"9001" => "0094DE",
	"9002" => "0094DE",
	"9003" => "000000",
	"9004" => "ECAE01",
	"9005" => "008983",
	"9006" => "008000",

	"11" => "0094DE",
	"C1" => "008000",
	"C2" => "B51741",
	"C3" => "0094DE",
	"" => "0094DE",
];

$link = mysqli_connect('localhost', 'gtfs', 'gtfs', 'SZDC');
if (!$link) {
	echo "Error: Unable to connect to database.".PHP_EOL;
	echo "Reason: ".mysqli_connect_error().PHP_EOL;
	exit;
}

$sync = exec('./sync.sh');

$files = glob("ftp.cisjr.cz/draha/celostatni/szdc/2018/*.xml");
usort($files, create_function('$a,$b', 'return filemtime($a) > filemtime($b);'));

if ($files) {
	foreach ($files as $file) {
		$nazev = substr($file, 40);

		$handle = fopen($file, "r");
		$obsah = fread($handle, filesize($file));
		fclose($handle);

		$xml = simplexml_load_file($file);
		$agency_id = $xml->Identifiers->PlannedTransportIdentifiers[1]->Company;

		$Variant = $xml->Identifiers->PlannedTransportIdentifiers[1]->Variant;
		$Locations = $xml->CZPTTInformation->CZPTTLocation;

		unset($shortnames);
		unset($routes);
		unset($triplist);
		unset($logid);

		$prev_route_id = $trip_id = $route_id = $headsign = $odd = $trasa = $route_color = "";

		foreach ($Locations as $locat) {
			$CommercialTrafficType = (string)$locat->CommercialTrafficType;
			$TrafficType = (string)$locat->TrafficType;
			$OperationalTrainNumber = $locat->OperationalTrainNumber;
			$shortname = $OperationalTrainNumber;
			$shortnames[] = $shortname;

			$route_color = $barvy[$CommercialTrafficType];

			if ($route_color == "") {
				$route_color = $barvy[$TrafficType];
			}

			switch ($agency_id) {
				case "3189":
					$route_color = "008983";
				break;

				case "3246":
					$route_color = "ECAE01";
				break;

				case "3244":
					$route_color = "000000";
				break;
			}

			$textcolor = getContrastYIQ ($route_color);

			$routes[] = "$shortname|$route_color|$textcolor";
		}

		$routes = array_unique($routes);

		$shortnames = array_unique($shortnames);

		$StartPeriod = $xml->CZPTTInformation->PlannedCalendar->ValidityPeriod->StartDateTime;
		$EndPeriod = $xml->CZPTTInformation->PlannedCalendar->ValidityPeriod->EndDateTime;
		$datumod = substr($StartPeriod,0,4).substr($StartPeriod,5,2).substr($StartPeriod,8,2);
		$datumdo = substr($EndPeriod,0,4).substr($EndPeriod,5,2).substr($EndPeriod,8,2);
		if ($datumdo == "") {$datumdo = $datumod;}

		foreach ($shortnames as $shortname) {
			switch ($shortname % 2) {
				case "0" :
					$odd = "1";
				break;
				case "1" :
					$odd = "0";
				break;
			}

			$query167 = "INSERT INTO log(file, shortname, trip_id, datumod, datumdo, obsah) VALUES ('$nazev','$shortname','','$datumod','$datumdo', '$obsah');";
//			echo "$query167<br/>";
			$prikaz167 = mysqli_query($link, $query167);
			$logid[] = mysqli_insert_id($link);
		}

		$vznik1 = $logid[0];
		$vznik = $vznik1;
		if ($vznik1 > 999999) {$vznik = substr($vznik, -6);}
		if ($vznik1 < 100000) {$vznik = "0".$vznik;}
		if ($vznik1 < 10000) {$vznik = "0".$vznik;}
		if ($vznik1 < 1000) {$vznik = "0".$vznik;}
		if ($vznik1 < 100) {$vznik = "0".$vznik;}
		if ($vznik1 < 10) {$vznik = "0".$vznik;}

		foreach ($logid as $log_id) {
			$shortname_data = mysqli_fetch_row(mysqli_query($link, "SELECT shortname from log WHERE id = '$log_id';"));
			$shortname = $shortname_data[0];
			$trip_id = $shortname.$Variant.$vznik;

			$query172 = "UPDATE log SET trip_id = '$trip_id' WHERE id = '$log_id';";
//			echo "$query172<br/>";
			$prikaz172 = mysqli_query($link, $query172);
		}

		$seq = 0;
		$prev_short = $Locations[0]->OperationalTrainNumber;
		$prev_trip_id = $prev_short.$Variant.$vznik;
		$prev_skupina = $prev_short.$Variant;
		$query199 = "SELECT route_id FROM linky WHERE skupina = '$prev_skupina';";
		if ($result199 = mysqli_query($link, $query199)) {
			while ($row199 = mysqli_fetch_row($result199)) {
				$prev_route_id = $row199[0];
			}
		}
		if ($prev_route_id == "") {
			$prev_route_id = "K".$prev_short;
		}

		unset($breakpoint);
		$breakpoint[] = "";
		$query212 = "SELECT stop_id FROM break WHERE vlak = '$prev_short';";
		if ($result212 = mysqli_query($link, $query212)) {
			while ($row212 = mysqli_fetch_row($result212)) {
				$breakpoint[] = $row212[0];
			}
		}

		unset($ignore);
		$ignore[] = "";
		$query220 = "SELECT stop_id FROM skip WHERE shortname = '$prev_short';";
		if ($result220 = mysqli_query($link, $query220)) {
			while ($row220 = mysqli_fetch_row($result220)) {
				$ignore[] = $row220[0];
			}
		}

		foreach ($Locations as $lokace) {
			$seq = $seq + 1;
			$Country = (string)$lokace->CountryCodeISO;
			$LocCode = $lokace->LocationPrimaryCode;
			$LocName = $lokace->PrimaryLocationName;
			$shortname = $lokace->OperationalTrainNumber;
			$trip_id = $shortname.$Variant.$vznik;
			$Dwell = $lokace->TimingAtLocation->DwellTime;
			$LocType = $lokace->attributes()->JourneyLocationTypeCode;
			$KontrCis = SelfCheck($LocCode);
			
			$countrcode = $staty[$Country];
			
			if ($countrcode == "") {
				$countrcode = $Country;
			}

			$stop_id = $countrcode.$LocCode.$KontrCis."0";
			$trasa .= "$stop_id|";
			$prijezd = 0;
			$odjezd = 0;
			$Timing = $lokace->TimingAtLocation->Timing;
			foreach ($Timing as $cas) {
				$TypCasu = $cas->attributes()->TimingQualifierCode;
				$Hodnota = $cas->Time;
				$Offset = $cas->Offset;
				
				$Hodnota_hod = substr($Hodnota,0,2);
				$Hodnota_rest = substr($Hodnota,2,6);
				$Hodnota_hod = ($Offset*24)+$Hodnota_hod;
				if ($Hodnota_hod < 10) {$Hodnota_hod = "0".$Hodnota_hod;}
				$Hodnota = $Hodnota_hod . $Hodnota_rest;
				if ($TypCasu == "ALA") {$prijezd = $Hodnota;}
				if ($TypCasu == "ALD") {$odjezd = $Hodnota;}
			}
			if ($prijezd == "0" || $Offset < 0) {$prijezd = $odjezd;}
			if ($odjezd == "0") {$odjezd = $prijezd;}
			$TrainActivity = $lokace->TrainActivity->TrainActivityType;
			$nastup = $vystup = 0;
			switch ($TrainActivity) {
				case '0028':
					$vystup = 1;
				break;
				case '0029':
					$nastup = 1;
				break;
				case '0030':
					$nastup = 3;
					$vystup = 3;
				break;
			}
			
			$query214 = "INSERT INTO stoptime (trip_id, arrival_time, departure_time, stop_id, stop_sequence, stop_headsign, pickup_type, drop_off_type, shape_dist_traveled, timepoint) VALUES ('$trip_id','$prijezd','$odjezd','$stop_id','$seq', '','$nastup','$vystup','','');";
			if ((($LocType == "01" || $LocType == "03" || !$LocType || $TrainActivity == "CZ02" || $TrainActivity == "0030") || (($LocType == "02" || $LocType == "04") && $Dwell > 0)) && !in_array($stop_id, $ignore)) {
//				echo "$query214<br/>";
				$prikaz214 = mysqli_query($link, $query214);
			}
			$headsign = $LocName;

			if ($trip_id != $prev_trip_id) {
				$triplist[] = $prev_trip_id;
				$query223 = "INSERT INTO trip (route_id, trip_id, trip_headsign, direction_id, shape_id, wheelchair_accessible, bikes_allowed, active) VALUES ('$prev_route_id', '$prev_trip_id', '', '$odd', '$trasa','0', '0', '1');";
//				echo "$query223<br/>";
				$prikaz223 = mysqli_query($link, $query223);

				$query295 = "INSERT INTO stoptime (trip_id, arrival_time, departure_time, stop_id, stop_sequence, stop_headsign, pickup_type, drop_off_type, shape_dist_traveled, timepoint) VALUES ('$prev_trip_id','$prijezd','$odjezd','$stop_id','$seq', '','$nastup','$vystup','','');";
//				echo "$query295<br/>";
				$prikaz295 = mysqli_query($link, $query295);
				$query298 = "UPDATE trip SET headsign = '$headsign' WHERE trip_id = '$prev_trip_id';";
//				echo "$query298<br/>";
				$prikaz295 = mysqli_query($link, $query298);

				$trasa = "$stop_id|";

				$prev_route_id = $route_id;
				$prev_trip_id = $trip_id;

				unset($breakpoint);
				$breakpoint[] = "";
				$query223 = "SELECT stop_id FROM break WHERE vlak = '$shortname';";
				if ($result223 = mysqli_query($link, $query223)) {
					while ($row223 = mysqli_fetch_row($result223)) {
						$breakpoint[] = $row223[0];
					}
				}
			}

			if (in_array($stop_id, $breakpoint)) {
				$triplist[] = $prev_trip_id;
				$query223 = "INSERT INTO trip (route_id, trip_id, trip_headsign, direction_id, shape_id, wheelchair_accessible, bikes_allowed, active) VALUES ('$prev_route_id', '$prev_trip_id', '', '$odd', '$trasa','0', '0', '1');";
//				echo "$query223<br/>";
				$prikaz223 = mysqli_query($link, $query223);
				$query298 = "UPDATE trip SET headsign = '$headsign' WHERE trip_id = '$prev_trip_id';";
//				echo "$query298<br/>";
				$prikaz295 = mysqli_query($link, $query298);

				$Variant = (int)substr($Variant, 0, 1) + 1 . substr($Variant, -1);
				$prev_trip_id = $shortname.$Variant.$vznik;

				$query295 = "INSERT INTO stoptime (trip_id, arrival_time, departure_time, stop_id, stop_sequence, stop_headsign, pickup_type, drop_off_type, shape_dist_traveled, timepoint) VALUES ('$prev_trip_id','$prijezd','$odjezd','$stop_id','$seq', '','$nastup','$vystup','','');";
//				echo "$query295<br/>";
				$prikaz295 = mysqli_query($link, $query295);

				$trasa = "$stop_id|";
				$prev_skupina = $shortname.$Variant;
				$query199 = "SELECT route_id FROM linky WHERE skupina = '$prev_skupina';";
				if ($result199 = mysqli_query($link, $query199)) {
					while ($row199 = mysqli_fetch_row($result199)) {
						$prev_route_id = $row199[0];
					}
				}
				if ($prev_route_id == "") {
					$prev_route_id = "K".$shortname;
				}
			}
		}

		$skupina = substr($trip_id, 0, -6);
		$query357 = "SELECT route_id FROM linky WHERE skupina = '$skupina';";
		if ($result357 = mysqli_query($link, $query357)) {
			while ($row357 = mysqli_fetch_row($result357)) {
				$route_id = $row357[0];
			}
		}
		if ($route_id == "") {
			$route_id = "K".substr($trip_id, 0, -8);
		}

		$triplist[] = $trip_id;
		$query223 = "INSERT INTO trip (route_id, trip_id, trip_headsign, direction_id, shape_id, wheelchair_accessible, bikes_allowed, active) VALUES ('$route_id', '$trip_id', '$headsign', '$odd', '$trasa','0', '0', '1');";
//		echo "$query223<br/>";
		$prikaz223 = mysqli_query($link, $query223);

		foreach ($routes as $route_string) {
			$wholename = "";
			$route_data = explode("|", $route_string);
			$route_id = "K".$route_data[0];
			$shortname = $route_data[0];
			$route_color = $route_data[1];
			$textcolor = $route_data[2];

			$query_min = "SELECT stop_name FROM stop WHERE stop_id IN (
				SELECT stop_id FROM stoptime WHERE trip_id IN (
					SELECT trip_id FROM trip WHERE route_id='$route_id'
				) AND stop_sequence IN (
					SELECT min(stop_sequence) FROM stoptime WHERE trip_id IN (
						SELECT trip_id FROM trip WHERE route_id='$route_id'
					)
				)
			);";
			$data_min = mysqli_fetch_row (mysqli_query ($link, $query_min));
			$min_name = $data_min[0];

			$query_max = "SELECT stop_name FROM stop WHERE stop_id IN (
				SELECT stop_id FROM stoptime WHERE trip_id IN (
					SELECT trip_id FROM trip WHERE route_id='$route_id'
				) AND stop_sequence IN (
					SELECT max(stop_sequence) FROM stoptime WHERE trip_id IN (
						SELECT trip_id FROM trip WHERE route_id='$route_id'
					)
				)
			);";
			$data_max = mysqli_fetch_row (mysqli_query ($link, $query_max));
			$max_name = $data_max[0];

			$wholename = "$min_name – $max_name";

			$query125 = "DELETE FROM route WHERE route_id = '$route_id';";
			$prikaz125 = mysqli_query($link, $query125);

			$query108 = "INSERT INTO route (route_id, agency_id, route_short_name, route_long_name, route_type, route_color, route_text_color, active) VALUES ('$route_id', '$agency_id', '$shortname', '$wholename', '2', '$route_color', '$textcolor', '1');";
//			echo "$query108<br/>";
			$prikaz108 = mysqli_query($link, $query108);
		}

		$maticestart = mktime(0,0,0,6,1,2018);
		$matice = "";

		$bitmap = $xml->CZPTTInformation->PlannedCalendar->BitmapDays;

		$Dod = substr($datumod,6,2); $Mod = substr($datumod,4,2); $Yod = substr($datumod,0,4); $timeod = mktime(0,0,0,$Mod, $Dod, $Yod);
		$zacdnu = round(($timeod - $maticestart) / 86400); 
		$Ddo = substr($datumdo,6,2); $Mdo = substr($datumdo,4,2); $Ydo = substr($datumdo,0,4); $timedo = mktime(0,0,0,$Mdo, $Ddo, $Ydo); 
		$kondnu = round(($timedo - $maticestart) / 86400); 

		for ($g=0; $g<406; $g++) {
			if ($g<$zacdnu) {$matice[$g] = 0;}
			if ($g==$zacdnu) {$matice .= $bitmap;}
			if ($g>$kondnu) {$matice[$g] = 0;}
		}

		foreach ($triplist as $trip_id) {
			for ($h=0; $h<406; $h++) {
				$tentoden = $maticestart + ($h * 86400);
				$totodatum = date("Y-m-d", $tentoden);
				$skupina = substr($trip_id, 0, -6);

				if ($matice[$h] == "1") {
					$query188 = "INSERT INTO jizdy (shortname, trip_id, datum) VALUES ('$skupina','$trip_id','$totodatum');";
//					echo "$query188<br/>";
					$prikaz188 = mysqli_query($link, $query188);
				} 
			}
		}

		unlink($file);
		echo "unlink $file<br/>";
	}
}

mysqli_close($link);
?>

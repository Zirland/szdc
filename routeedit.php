<?php
include 'header.php';

function getContrastYIQ ($hexcolor){
	$r = hexdec(substr($hexcolor,0,2));
	$g = hexdec(substr($hexcolor,2,2));
	$b = hexdec(substr($hexcolor,4,2));
	$yiq = (($r * 299) + ($g * 587) + ($b * 114)) / 1000;
	return ($yiq >= 128) ? '000000' : 'FFFFFF';
}

$route = @$_GET['id'];
$action = @$_POST['action'];

switch ($action) {
	case "oprav" :

	$route = $_POST['route_id'];
	$dopravce = $_POST['dopravce'];
	$shortname = $_POST['shortname'];
	$longname = $_POST['longname'];
	$pozadi = $_POST['route_pozadi'];
	$foreground = getContrastYIQ ($pozadi);
	$kraj = $_POST['kraj'];
	$aktif = $_POST['aktif'];

	$ready0 = "UPDATE route SET agency_id='$dopravce', route_short_name='$shortname', route_long_name='$longname', route_color='$pozadi', route_text_color='$foreground', kraj = '$kraj', active='$aktif' WHERE (route_id = '$route');";
	$aktualz0 = mysqli_query ($link, $ready0);
}

echo "<table><tr><td>";
echo "<table>";
echo "<tr>";

$hlavicka = mysqli_fetch_row (mysqli_query ($link, "SELECT * FROM route WHERE (route_id='$route');"));
$route_id = $hlavicka[0];
$agency_id = $hlavicka[1];
$route_short_name = $hlavicka[2];
$route_long_name = $hlavicka[3];
$route_color = $hlavicka[5];
$route_text_color = $hlavicka[6];
$route_kraj = $hlavicka[8];
$route_active = $hlavicka[7];

echo "<form method=\"post\" action=\"routeedit.php\" name=\"oprav\"><input name=\"action\" value=\"oprav\" type=\"hidden\"><input name=\"route_id\" value=\"$route_id\" type=\"hidden\">";
echo "<td>Dopravce: <select name=\"dopravce\">";

$query24 = "SELECT agency_id, agency_name FROM agency ORDER BY agency_id;";
if ($result24 = mysqli_query ($link, $query24)) {
	while ($row24 = mysqli_fetch_row ($result24)) {
		$agid = $row24[0];
		$agname = $row24[1];

		echo "<option value=\"$agid\"";
		if ($agid == $agency_id) {
			echo " SELECTED";
		}
		echo ">$agname</option>";
	}
}
echo "</select></td><td style=\"background-color : #$route_color;\">Linka: <input type=\"text\" name=\"shortname\" size=\"10\" value=\"$route_short_name\">";

echo "<input type=\"text\" name=\"kraj\" size=\"1\" value=\"$route_kraj\"><br />";
echo "<input type=\"text\" name=\"longname\" value=\"$route_long_name\"></td>";

echo "<td>Aktivní <input type=\"checkbox\" name=\"aktif\" value=\"1\"";
if ($route_active == '1') {
	echo " CHECKED";
}
echo "></td><td><input type=\"submit\"></td></tr></form></table>";

echo "<table>";
echo "<tr><th>Linky odchozí</th><th>Linky příchozí</th></tr>";
echo "<tr><td>";

$query80 = "SELECT trip_id,trip_headsign,active FROM trip WHERE ((route_id = '$route_id') AND (direction_id = '0')) ORDER BY trip_id;";
if ($result80 = mysqli_query ($link, $query80)) {
	$count = mysqli_num_rows($result80);
	echo "$count<br/>";
	while ($row80 = mysqli_fetch_row ($result80)) {
		$trip_id = $row80[0];
		$trip_headsign = $row80[1];
		$trip_split = explode("~", $trip_id);
		$vlak = $trip_split[0];
		$trip_aktif = $row80[2];

		$query15 = "SELECT stop_name FROM stop WHERE stop_id IN (SELECT stop_id FROM stoptime WHERE trip_id = '$trip_id' AND stop_sequence IN (SELECT min(stop_sequence) FROM stoptime WHERE (trip_id = '$trip_id')));";
		$result15 = mysqli_query ($link, $query15);
		$pomhead = mysqli_fetch_row ($result15);
		$from = $pomhead[0];

		if ($trip_aktif == '1') {
			echo "<span style=\"background-color:#54FF00;\">";
		}
		echo "$from - ";
		echo "$vlak - $trip_headsign - <a href=\"tripedit.php?id=$trip_id\">Upravit</a>";
		if ($trip_aktif == '1') {
			echo "</span>";
		}

		$cislo7 = $vlak."/".substr ($vlak,-1);

		$query114 = "SELECT POZNAM FROM kango.OBP WHERE CISLO7='$cislo7';";
		if ($result114 = mysqli_query ($link, $query114)) {
			while ($row114 = mysqli_fetch_row ($result114)) {
				$poznamka = $row114[0];
				if (strpos ($poznamka, "linka") !== false) {
					echo "$poznamka";
				}
			}
		}
		echo "<br/>";
	}
}
echo "</td><td>";

$query96 = "SELECT trip_id, trip_headsign, active FROM trip WHERE ((route_id = '$route_id') AND (direction_id = '1')) ORDER BY trip_id;";
if ($result96 = mysqli_query ($link, $query96)) {
	$count = mysqli_num_rows($result96);
	echo "$count<br/>";
	while ($row96 = mysqli_fetch_row ($result96)) {
		$trip_id = $row96[0];
		$trip_headsign = $row96[1];
		$trip_split = explode("~", $trip_id);
		$vlak = $trip_split[0];
		$trip_aktif = $row96[2];

		$query15 = "SELECT stop_name FROM stop WHERE stop_id IN (SELECT stop_id FROM stoptime WHERE trip_id = '$trip_id' AND stop_sequence IN (SELECT min(stop_sequence) FROM stoptime WHERE (trip_id = '$trip_id')));";
		$result15 = mysqli_query ($link, $query15);
		$pomhead = mysqli_fetch_row ($result15);
		$from = $pomhead[0];

		if ($trip_aktif == '1') {
			echo "<span style=\"background-color:#54FF00;\">";
		}
		echo "$from - ";
		echo "$vlak - $trip_headsign - <a href=\"tripedit.php?id=$trip_id\">Upravit</a>";
		if ($trip_aktif == '1') {
			echo "</span>";
		}

		$cislo7 = $vlak."/".substr ($vlak,-1);

		$query114 = "SELECT POZNAM FROM kango.OBP WHERE CISLO7='$cislo7';";
		if ($result114 = mysqli_query ($link, $query114)) {
			while ($row114 = mysqli_fetch_row ($result114)) {
				$poznamka = $row114[0];
				if (strpos ($poznamka, "linka") !== false) {
					echo "$poznamka";
				}
			}
		}
		echo "<br/>";
	}
}
echo "</td></tr></table>";

include 'footer.php';
?>

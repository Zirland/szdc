<?php
include 'header.php';

$action = @$_POST['action'];
$filtr = @$_POST['filtr'];
$smer = @$_POST['smer'];

echo "<form method=\"post\" action=\"station.php\" name=\"filtr\">
	<input name=\"action\" value=\"filtr\" type=\"hidden\">";
	echo "<select name=\"filtr\">";
	$query0 = "SELECT stop_id, stop_name FROM stop WHERE active=1 ORDER BY stop_name;";
	if ($result0 = mysqli_query ($link, $query0)) {
		while ($row0 = mysqli_fetch_row ($result0)) {
			$kod = $row0[0];
			$nazev = $row0[1];

			echo "<option value=\"$kod\"";
			if ($kod == $filtr) {
				echo " SELECTED";
			}
			echo ">$nazev</option>";
		}
		mysqli_free_result($result0);
	}
	echo "</select>";
	echo "<input type=\"text\" name=\"smer\" value=\"1\">";
	echo "<input type=\"submit\"></form>";

switch ($action) {
	case "update" :
		$pocet = $_POST['pocet'];

		for ($y = 0; $y < $pocet; $y++) {
			$$ind = $y;
			$numtripindex = "spoj".${$ind};
			$numtrip = $_POST[$numtripindex];
			$newlineindex = "line".${$ind};
			$newline = $_POST[$newlineindex];

			$query30 = "UPDATE trip SET route_id = '$newline' WHERE trip_id = '$numtrip';";
			$prikaz30 = mysqli_query ($link, $query30);

			if (strpos ($newline, 'K') === false) {
				$skupina = substr($numtrip, 0, -6);
				$query41 = "INSERT INTO linky (skupina, route_id) VALUES ('$skupina','$newline');";
				$prikaz41 = mysqli_query ($link, $query41);
			}
		}
		$action="filtr";
	break;

	case "filtr" : 
		echo "<table border=\"1\">";
		echo "<tr>";
		echo "<th>Vlak</th>
		<th>Linka</th>
		<th>Čas</th>
		<th>Cílová stanice</th>
		<th>Poznámka</th>";
		echo "</tr>";

		$x = 0;
		$now = date ("H:i:s", time ());
		$end = date ("H:i:s", time ()+3600);

		$now = "00:00:00";
		$end = "48:00:00";

		$query = "SELECT trip_id FROM stoptime WHERE (stop_id='$filtr' AND departure_time>='$now' AND departure_time<='$end') ORDER BY departure_time;";
		echo "$query<br/>";
		if ($result = mysqli_query ($link, $query)) {
			while ($row = mysqli_fetch_row ($result)) {
				$trip_id = $row[0];

				$query73 = "SELECT * FROM trip WHERE trip_id = '$trip_id';";
				if ($result73 = mysqli_query($link, $query73)) {
					while($row73 = mysqli_fetch_row($result73)) {
						$routedata = $row73[0];
						$trip_headsign = $row73[3];
						$direction = $row73[4];
						$wheel = $row73[7];
						$bike = $row73[8];
					}
					mysqli_free_result($result73);
				}

				$cislo = substr ($trip_id,0,-8);

				$cislo7 = $cislo."/".substr($cislo,-1);
				if (strpos ($routedata, 'K') !== false) {
					echo "<form method=\"post\" action=\"station.php\" name=\"update\"><input name=\"action\" value=\"update\" type=\"hidden\"><input name=\"filtr\" value=\"$filtr\" type=\"hidden\"><input name=\"spoj$x\" value=\"$trip_id\" type=\"hidden\">";
					echo "<tr>";
					echo "<td>$cislo</td>";

					$query93 = "SELECT route_short_name,route_color,route_text_color FROM route WHERE (route_id = '$routedata') ORDER BY route_short_name;";
					if ($result93 = mysqli_query($link, $query93)) {
						while($row93 = mysqli_fetch_row($result93)) {
							$route_short_name = $row93[0];
							$route_color = $row93[1];
							$route_text_color = $row93[2];
						}
						mysqli_free_result($result93);
					}

					echo "<td style=\"background-color: #$route_color; text-align: center;\"><span style=\"color: #$route_text_color;\">";
					echo "<select name=\"line$x\">";
					$query84 = "SELECT route_id, route_short_name, kraj, agency_id, route_long_name FROM route ORDER BY route_short_name;"; // WHERE route_id NOT LIKE 'K%' 
					if ($result84 = mysqli_query ($link, $query84)) {
						while ($row84 = mysqli_fetch_row ($result84)) {
							$route_id = $row84[0];
							$route_short_name = $row84[1];
							$kraj = $row84[2];
							$agency_id = $row84[3];
							$route_long_name = $row84[4];

							$query113 = "SELECT agency_name FROM agency WHERE agency_id = '$agency_id';";
							if ($result113 = mysqli_query($link, $query113)) {
								while($row113 = mysqli_fetch_row($result113)) {
									$dopravce = $row113[0];
									
								}
								mysqli_free_result($result113);
							}

							echo "<option value=\"$route_id\"";
							if ($route_id == $routedata) {
								echo " SELECTED";
							}
							echo ">$route_short_name$kraj | $route_long_name | $dopravce</option>";
						}
						mysqli_free_result($result84);
					}
					echo "</select></td><td>";

					$query132 = "SELECT * FROM stoptime WHERE ((trip_id = '$trip_id') AND (stop_id = '$filtr'));";
					if ($result132 = mysqli_query($link, $query132)) {
						while($row132 = mysqli_fetch_row($result132)) {
							$zde = $row132[2];
						}
						mysqli_free_result($query132);
					}

					$zde = substr ($zde,0,5);
					echo $zde;
					echo "</td>";

/*					$query144 = "SELECT stop_name FROM stop WHERE stop_id = (SELECT stop_id FROM stoptime WHERE (trip_id = '$trip_id') ORDER BY stop_sequence LIMIT 1);";
					if ($result144 = mysqli_query($link, $query144)) {
						while($row144 = mysqli_fetch_row($result144)) {
							$odkud = $row144[0];
						}
						mysqli_free_result($result144);
					}

					$query153 = "SELECT stop_name FROM stop WHERE stop_id = (SELECT stop_id FROM stoptime WHERE (trip_id = '$trip_id') ORDER BY stop_sequence DESC LIMIT 1);";
					if ($result153 = mysqli_query($link, $query153)) {
						while($row153 = mysqli_fetch_row($result153)) {
							$kam = $row153[0];
						}
						mysqli_free_result($result153);
					}

					echo "<td>$odkud – $kam</td><td>";*/
					$query161 = "SELECT trip_headsign FROM trip WHERE trip_id = '$trip_id';";
					if ($result161 = mysqli_query($link, $query161)) {
						while($row161 = mysqli_fetch_row($result161)) {
							$cil = $row161[0];
							
						}
						mysqli_free_result($result161);
					}
					echo "<td>$cil</td><td>";

					$query114 = "SELECT POZNAM FROM kango.OBP WHERE CISLO7='$cislo7';";
					if ($result114 = mysqli_query ($link, $query114)) {
						while ($row114 = mysqli_fetch_row ($result114)) {
							$poznamka = $row114[0];
							if (strpos ($poznamka, "linka") !== false) {
								echo "$poznamka";
							}
						}
						mysqli_free_result($result114);
					}

					$x = $x + 1;
					echo "</td></tr>";
				}
			}
			mysqli_free_result($result);
		}
		echo "</table>";

		echo "<input type=\"hidden\" name=\"pocet\" value=\"$x-1\">";
		echo "<input type=\"submit\">";
		echo "</form>";
	break;
}

include 'footer.php';
?>
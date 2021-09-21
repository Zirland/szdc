<?php
include 'header.php';

$action = @$_POST['action'];
$route = @$_POST['route'];
$stop = @$_POST['stop'];

echo "<form method=\"post\" action=\"breakline.php\" name=\"input\">";
echo "<input type=\"hidden\" name=\"action\" value=\"break\">";
echo "<select name=\"route\">";
$query84 = "SELECT route_id, route_short_name, kraj, agency_id, route_long_name FROM route WHERE route_id NOT LIKE 'K%' ORDER BY route_short_name;";
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
		if ($route_id == $route) {
			echo " SELECTED";
		}
		echo ">$route_short_name$kraj | $route_long_name | $dopravce</option>";
	}
	mysqli_free_result($result84);
}
echo "</select>";

echo "<select name=\"stop\">";
$query0 = "SELECT stop_id, stop_name FROM stop WHERE active=1 ORDER BY stop_name;";
if ($result0 = mysqli_query ($link, $query0)) {
	while ($row0 = mysqli_fetch_row ($result0)) {
		$kod = $row0[0];
		$nazev = $row0[1];

		echo "<option value=\"$kod\"";
		if ($kod == $stop) {
			echo " SELECTED";
		}
		echo ">$nazev</option>";
	}
	mysqli_free_result($result0);
}
echo "</select>";

echo "<input type=\"submit\"></form>";

if ($action == "break") {
	$query59 = "SELECT trip_id FROM trip WHERE route_id = '$route';";
	if ($result59 = mysqli_query($link, $query59)) {
		while ($row59 = mysqli_fetch_row($result59)) {
			$trip_id = $row59[0];

			$short_name = substr($trip_id, 0, -8);
			$log_id = substr($trip_id, -6);
			$log_id = intval($log_id,10);

			$query67 = "INSERT INTO break (vlak, stop_id) VALUES ('$short_name', '$stop');";
			echo "$query67<br/>";
			$prikaz67 = mysqli_query($link, $query67);

			echo "<a href=\"regen.php?logid=$log_id\" target=\"_blank\">$trip_id</a><br/>";
		}
	}
	
}

include 'footer.php';
?>


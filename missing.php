<?php
include 'header.php';

$query = "SELECT DISTINCT stop_id,trip_id FROM stoptime WHERE (stop_id NOT IN (SELECT stop_id FROM stop)) ORDER BY stop_id;";
if ($result = mysqli_query ($link, $query)) {
	while ($row = mysqli_fetch_row ($result)) {
		$stop_id = $row[0];
		$trip_id = $row[1];

		echo "$stop_id - <a href=\"tripedit.php?id=$trip_id\">$trip_id</a> <a href=\"newstop.php?newid=$stop_id&newname=&newact=0\" target=\"_blank\">Vytvořit bod</a><br/>";

	}
}

echo "== MISSING ROUTES ==<br/>";

$query17 = "SELECT DISTINCT route_id FROM trip WHERE route_id NOT IN (SELECT route_id FROM route) ORDER BY route_id;";
if ($result17 = mysqli_query($link, $query17)) {
	while ($row17 = mysqli_fetch_row($result17)) {
		$miss_route = $row17[0];

		echo "$miss_route<br/>";
	}
}

include 'footer.php';
?>

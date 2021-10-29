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

include 'footer.php';
?>

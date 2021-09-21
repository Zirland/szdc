<?php
$link = mysqli_connect ('localhost', 'gtfs', 'gtfs', 'SZDC');
if (!$link) {
	echo "Error: Unable to connect to MySQL.".PHP_EOL;
	echo "Debugging errno: ".mysqli_connect_errno ().PHP_EOL;
	exit;
}

$dnes = date("Y-m-d", time());
$dnessrt = date("Ymd", time());

$query11 = "DELETE FROM jizdy WHERE datum<'$dnes';";
//echo "13: $query11<br/>";
$prikaz11 = mysqli_query($link, $query11);

$query16 = "SELECT id FROM break LEFT OUTER JOIN (SELECT MAX(id) as RowId, vlak, stop_id FROM break GROUP BY vlak, stop_id) as KeepRows ON break.id = KeepRows.RowId WHERE KeepRows.RowId IS NULL;";
if ($result16 = mysqli_query($link, $query16)) {
	while ($row16 = mysqli_fetch_row($result16)) {
		$id = $row16[0];

		$query45 = "DELETE FROM break WHERE id = '$id';";
//		echo "22: $query45<br/>";
		$prikaz45 = mysqli_query($link, $query45);
	}
}

$query40 = "SELECT id FROM jizdy LEFT OUTER JOIN (SELECT MAX(id) as RowId, shortname, datum FROM jizdy GROUP BY shortname, datum) as KeepRows ON jizdy.id = KeepRows.RowId WHERE KeepRows.RowId IS NULL;";
if ($result40 = mysqli_query($link, $query40)) {
	while ($row40 = mysqli_fetch_row($result40)) {
		$id = $row40[0];

		$query45 = "DELETE FROM jizdy WHERE id = '$id';";
//		echo "22: $query45<br/>";
		$prikaz45 = mysqli_query($link, $query45);
	}
}

$query27 = "SELECT id FROM linky LEFT OUTER JOIN (SELECT MAX(id) as RowId, skupina FROM linky GROUP BY skupina) as KeepRows ON linky.id = KeepRows.RowId WHERE KeepRows.RowId IS NULL;";
if ($result27 = mysqli_query($link, $query27)) {
	while ($row27 = mysqli_fetch_row($result27)) {
		$id = $row27[0];

		$query32 = "DELETE FROM linky WHERE id = '$id';";
//		echo "22: $query32<br/>";
		$prikaz32 = mysqli_query($link, $query32);
	}
}

$query14 = "SELECT trip_id FROM trip WHERE trip_id NOT IN (SELECT DISTINCT trip_id FROM jizdy);";
if ($result14 = mysqli_query($link, $query14)) {
	while ($row14 = mysqli_fetch_row($result14)) {
		$trip_id = $row14[0];

		$query19 = "DELETE FROM trip WHERE trip_id = '$trip_id';";
//		echo "33: $query19<br/>";
		$prikaz19 = mysqli_query($link, $query19);

		$query22 = "DELETE FROM stoptime WHERE trip_id = '$trip_id';";
//		echo "37: $query22<br/>";
		$prikaz22 = mysqli_query($link, $query22);
	}
}

$query27 = "SELECT route_id FROM route WHERE route_id NOT IN (SELECT DISTINCT route_id FROM trip);";
if ($result27 = mysqli_query($link, $query27)) {
	while ($row27 = mysqli_fetch_row($result27)) {
		$route_id = $row27[0];

		$query32 = "UPDATE route SET active=0 WHERE route_id = '$route_id';";
//		echo "48: $query32<br/>";
		$prikaz32 = mysqli_query($link, $query32);
	}
}

$query37 = "DELETE FROM stoptime WHERE stop_id IN (SELECT stop_id FROM stop WHERE active=0);";
//echo "54: $query37<br/>";
$prikaz37 = mysqli_query($link, $query37);

$query50 = "SELECT trip_id FROM trip WHERE route_id = 'K';";
if ($result50 = mysqli_query($link, $query50)) {
	while ($row50 = mysqli_fetch_row($result50)) {
		$trip_id = $row50[0];

		$query55 = "DELETE FROM stoptime WHERE trip_id = '$trip_id';";
		echo "$query55<br/>";
		$prikaz55 = mysqli_query($link, $query55);

		$query58 = "DELETE FROM jizdy WHERE trip_id = '$trip_id';";
		echo "$query58<br/>";
		$prikaz58 = mysqli_query($link, $query58);

		$query61 = "DELETE FROM trip WHERE trip_id = '$trip_id';";
		echo "$query61<br/>";
		$prikaz61 = mysqli_query($link, $query61);
	}
}

$query66 = "DELETE FROM route WHERE route_id = 'K';";
$prikaz66 = mysqli_query($link, $query66);

$query69 = "DELETE FROM log WHERE datumdo<'$dnessrt';";
$prikaz69 = mysqli_query($link, $query69);

$query79 = "DELETE FROM route WHERE route_id LIKE 'K%' AND active=0;";
$prikaz79 = mysqli_query($link, $query79);
mysqli_close ($link);
?>

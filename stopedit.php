<?php
include 'header.php';

$stop_id = @$_GET['id'];
$action = @$_POST['action'];

switch ($action) {
	case 'edit' :
		$stop_id = $_POST['stopid'];
		$stoplat = $_POST['stoplat'];
		$stoplon = $_POST['stoplon'];
		
		$query14 = "UPDATE stop SET stop_lat = '$stoplat', stop_lon = '$stoplon' WHERE stop_id = '$stop_id';";
		$prikaz4 = mysqli_query ($link, $query14);

		$deaktivace = "UPDATE shapetvary SET complete = '0' WHERE (tvartrasy LIKE '%$stop_id%'));";
		$prikaz19 = mysqli_query ($link, $deaktivace);
	break;
}

echo "<table>";
echo "<tr><td colspan=\"4\">Edit stop</td></tr>";

echo "<form method=\"post\" action=\"stopedit.php\" name=\"edit\">
		<input name=\"action\" value=\"edit\" type=\"hidden\">";
		
$query29 = "SELECT stop_name, stop_lat, stop_lon FROM stop WHERE stop_id = '$stop_id';";
if ($result29 = mysqli_query ($link, $query29)) {
	while ($row29 = mysqli_fetch_row ($result29)) {
		$stop_name = $row29[0];
		$stop_lat = $row29[1];
		$stop_lon = $row29[2];

		echo "<tr><td>Stop ID</td><td>Stop name</td><td>Latitude ~50.123456</td><td>Longitude ~16.987654</td></tr>";
		echo "<tr><td><input type=\"text\" name=\"stopid\" value=\"$stop_id\"></td><td><input name=\"stopname\" value=\"$stop_name\" type=\"text\"></td><td><input name=\"stoplat\" value=\"$stop_lat\" type=\"text\"></td><td><input name=\"stoplon\" value=\"$stop_lon\" type=\"text\"></td></tr>";
		echo "<tr><td colspan=\"4\"><input type=\"submit\" value=\"Insert\"></td></tr>";
		echo "</table>";
	}
}

include 'footer.php';
?>
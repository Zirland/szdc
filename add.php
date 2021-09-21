<?php
include 'header.php';

function getContrastYIQ ($hexcolor){
	$r = hexdec(substr($hexcolor,0,2));
	$g = hexdec(substr($hexcolor,2,2));
	$b = hexdec(substr($hexcolor,4,2));
	$yiq = (($r * 299) + ($g * 587) + ($b * 114)) / 1000;
	return ($yiq >= 128) ? '000000' : 'FFFFFF';
}

$action = @$_POST['action'];

switch ($action) {
	case 'generuj' :
		$route_id = $_POST['route_id'];
		$agency_id = $_POST['agency_id'];
		$route_short = $_POST['route_short'];
		$route_long = $_POST['route_long'];
		$route_desc = $_POST['route_desc'];
		$route_type = $_POST['route_type'];
		$route_url = $_POST['route_url'];
		$route_color = $_POST['route_color'];
		$route_text_color = getContrastYIQ ($route_color);
		$route_kraj = $_POST['route_kraj'];

		$query = "INSERT INTO route VALUES ('$route_id','$agency_id','$route_short','$route_long','$route_type','$route_color','$route_text_color','0','$route_kraj');";
//		echo "$query<br/>";
		$command = mysqli_query ($link, $query);
	break;
}

echo "<form method=\"post\" action=\"add.php\" name=\"generuj\">
<input name=\"action\" value=\"generuj\" type=\"hidden\">";

$ro_max_pom = mysqli_fetch_row (mysqli_query ($link, "SELECT MAX(CAST(route_id AS signed)) FROM route WHERE route_id NOT LIKE 'K%';"));
$ro_max = $ro_max_pom['0'] + 1;

echo "<table>";
echo "<tr>";
echo "<th>ID</th><th>Přepravce</th><th>Linka</th><th>Trasa</th></tr><tr><th>Popis</th><th>Typ</th><th>URL trasy</th><th>Kraj</th></tr><tr><th>Pozadí linky</th><th>Barva textu</th><th></th>";
echo "</tr>";
echo "<tr>";
echo "<td><input name=\"route_id\" type=\"text\" value=\"$ro_max\"></td>";
echo "<td><select name=\"agency_id\">";

$query0 = "SELECT agency_id, agency_name FROM agency ORDER BY agency_id;";
if ($result0 = mysqli_query ($link, $query0)) {
	while ($row0 = mysqli_fetch_row ($result0)) {
		$kod = $row0[0];
		$nazev = $row0[1];

		echo "<option value=\"$kod\"";
		if ($kod == 1) {
			echo " SELECTED";
		}
		echo ">$nazev</option>";
	}
	mysqli_free_result ($result0);
} else echo("Error description: ".mysqli_error ($link));
echo "</select>";

echo "<td><input name=\"route_short\" value=\"\" type=\"text\"></td>";
echo "<td><input name=\"route_long\" value=\"\" type=\"text\"></td></tr>";
echo "<tr><td><input name=\"route_desc\" value=\"\" type=\"text\"></td>";
echo "<td><select name=\"route_type\">
<option value=\"0\">tramvaj</option>
<option value=\"1\">metro</option>
<option value=\"2\" SELECTED>vlak</option>
<option value=\"3\">autobus</option>
<option value=\"4\">přívoz</option>
<option value=\"5\">trolejbus</option>
<option value=\"6\">visutá lanovka</option>
<option value=\"7\">kolejová lanovka</option>
</select>
</td>";

echo "<td><input name=\"route_url\" value=\"\" type=\"text\"></td>";
echo "<td><input name=\"route_kraj\" value=\"\" type=\"text\"></td></tr>";

echo "<tr><td><select name=\"route_color\">";
$query157 = "SELECT color, popis FROM kango.colors;";
if ($result157 = mysqli_query ($link, $query157)) {
	while ($row157 = mysqli_fetch_row ($result157)) {
		$rtclr = $row157[0];
		$clrnm = $row157[1];

		echo "<option value=\"$rtclr\"";
		if ($rtclr == $route_color) {
			echo " SELECTED";
		}
		echo ">$clrnm</option>";
	}
}
echo "</select></td>";

echo "<td><input name=\"route_text_color\" value=\"FFFFFF\" type=\"text\"></td>";
echo "<td><input type=\"submit\"></td>";
echo "</tr></table></form>";

include 'footer.php';
?>
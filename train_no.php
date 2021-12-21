<?php
$link = mysqli_connect('localhost', 'root', 'root', 'vlaky');
if (!$link) {
    echo "Error: Unable to connect to MySQL." . PHP_EOL;
    echo "Debugging errno: " . mysqli_connect_errno() . PHP_EOL;
    exit;
}

unset($seznam);

$query9 = "SELECT route_id, agency_id FROM route WHERE active=0 AND route_id LIKE 'K%' AND route_id LIKE '%~%';";
if ($result9 = mysqli_query($link, $query9)) {
    while ($row9 = mysqli_fetch_row($result9)) {
        $route_id  = $row9[0];
        $agency_id = $row9[1];

        $split_route = explode("~", $route_id);
        $linka       = substr($split_route[1], 0, -8);
        $seznam[]    = "$linka | $agency_id | $route_id"; //
    }
}

$seznam = array_unique($seznam);
sort($seznam);

foreach ($seznam as $radek) {
    echo "$radek<br/>";
}

echo "<br/>";
echo "Hotovo...";

mysqli_close($link);

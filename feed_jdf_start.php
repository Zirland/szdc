<?php
$link = mysqli_connect('localhost', 'root', 'root', 'vlaky');
if (!$link) {
    echo "Error: Unable to connect to MySQL." . PHP_EOL;
    echo "Debugging errno: " . mysqli_connect_errno() . PHP_EOL;
    exit;
}

$current = "";

$akt_route = "SELECT route_id, agency_id, route_short_name, route_long_name, route_type, route_color, route_text_color FROM route WHERE active='1';";
if ($result69 = mysqli_query($link, $akt_route)) {
    while ($row69 = mysqli_fetch_row($result69)) {
        $route_id         = $row69[0];
        $agency_id        = $row69[1];
        $route_short_name = $row69[2];
        $route_long_name  = $row69[3];
        $route_type       = $row69[4];
        $route_color      = $row69[5];
        $route_text_color = $row69[6];
        $routenums        = mysqli_num_rows($result69);

        $current .= "$route_id,$agency_id,\"$route_short_name\",\"$route_long_name\",$route_type,$route_color,$route_text_color\n";

        $useag_query = "INSERT INTO ag_use VALUES ('$route_id','$agency_id');";
        $zapisag     = mysqli_query($link, $useag_query);
    }
    mysqli_free_result($result69);
}

$file = 'routes.txt';
file_put_contents($file, $current, FILE_APPEND);

mysqli_close($link);

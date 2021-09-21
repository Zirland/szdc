<?php
$time_start = microtime(true);

$file = 'agency.txt';
$current = "agency_id,agency_name,agency_url,agency_timezone,agency_phone\n";
file_put_contents ($file, $current);

$file = 'routes.txt';
$current = "route_id,agency_id,route_short_name,route_long_name,route_type,route_color,route_text_color\n";
file_put_contents ($file, $current);

$file = 'trips.txt';
$current = "route_id,service_id,trip_id,trip_headsign,direction_id,shape_id,wheelchair_accessible,bikes_allowed\n";
file_put_contents ($file, $current);

$file = 'stop_times.txt';
$current = "trip_id,arrival_time,departure_time,stop_id,stop_sequence,pickup_type,drop_off_type\n";
file_put_contents ($file, $current);

$file = 'calendar.txt';
$current = "service_id,monday,tuesday,wednesday,thursday,friday,saturday,sunday,start_date,end_date\n";
file_put_contents ($file, $current);

$file = 'stops.txt';
$current = "stop_id,stop_code,stop_name,stop_lat,stop_lon,location_type,parent_station,wheelchair_boarding\n";
file_put_contents ($file, $current);

$file = 'shapes.txt';
$current = "shape_id,shape_pt_lat,shape_pt_lon,shape_pt_sequence\n";
file_put_contents ($file, $current);

$link = mysqli_connect ('localhost', 'gtfs', 'gtfs', 'JDF');
if (!$link) {
	echo "Error: Unable to connect to MySQL.".PHP_EOL;
	echo "Debugging errno: ".mysqli_connect_errno ().PHP_EOL;
	exit;
}

$agency_trunc = mysqli_query ($link, "TRUNCATE TABLE ag_use;");
$stop_trunc = mysqli_query ($link, "TRUNCATE TABLE stop_use;");
$shapecheck_trunc = mysqli_query ($link, "TRUNCATE TABLE shapecheck;");
$parent_trunc = mysqli_query ($link, "TRUNCATE TABLE parent_use;");
$export_trunc = mysqli_query ($link, "TRUNCATE TABLE exportlist;");

mysqli_close ($link);

$link = mysqli_connect ('localhost', 'gtfs', 'gtfs', 'SZDC');
if (!$link) {
	echo "Error: Unable to connect to MySQL.".PHP_EOL;
	echo "Debugging errno: ".mysqli_connect_errno ().PHP_EOL;
	exit;
}

$agency_trunc = mysqli_query ($link, "TRUNCATE TABLE ag_use;");
$calendar_trunc = mysqli_query ($link, "TRUNCATE TABLE cal_use;");
$stop_trunc = mysqli_query ($link, "TRUNCATE TABLE stop_use;");
$shapecheck_trunc = mysqli_query ($link, "TRUNCATE TABLE shapecheck;");
$parent_trunc = mysqli_query ($link, "TRUNCATE TABLE parent_use;");
$export_trunc = mysqli_query ($link, "TRUNCATE TABLE exportlist;");

mysqli_close ($link);
?>

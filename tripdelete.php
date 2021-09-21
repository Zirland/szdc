<?php
include 'header.php';

$trip = @$_GET['trip'];

$query1 = "DELETE FROM stoptime WHERE trip_id='$trip';";

$command1 = mysqli_query ($link, $query1);

$query2 = "DELETE FROM trip WHERE trip_id='$trip';";
$command2 = mysqli_query ($link, $query2);

echo "Smazáno";

include 'footer.php';
?>
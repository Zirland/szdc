<?php
include 'header.php';

echo "DUPLICITE STOP<br/>";
$query4 = "SELECT DISTINCT trip_id FROM (SELECT trip_id, stop_sequence, count(*) as pocet FROM stoptime GROUP BY trip_id, stop_sequence HAVING pocet > 1) AS pom;";
if ($result4 = mysqli_query($link, $query4)) {
    while ($row4 = mysqli_fetch_row($result4)) {
        $trip_id = $row4[0];

        echo "<a href=\"tripedit.php?id=$trip_id\">$trip_id</a><br/>";
    }
}

echo "HEADSIGN<br/>";
$query1 = "SELECT trip_id FROM trip WHERE trip_headsign='';";
if ($result1 = mysqli_query($link, $query1)) {
    while ($row1 = mysqli_fetch_row($result1)) {
        $trip_id = $row1[0];

        $query_max = "SELECT stop_name FROM stop WHERE stop_id IN (
			SELECT stop_id FROM stoptime WHERE trip_id ='$trip_id' AND stop_sequence IN (
				SELECT max(stop_sequence) FROM stoptime WHERE trip_id = '$trip_id'
			)
		);";
//        echo "$query_max<br/>";
        $data_max = mysqli_fetch_row(mysqli_query($link, $query_max));
        $max_name = $data_max[0];

        $query19  = "UPDATE trip SET trip_headsign = '$max_name' WHERE trip_id= '$trip_id';";
        $prikaz19 = mysqli_query($link, $query19);

        echo "$trip_id > $max_name<br/>";
    }
}

echo "LONGNAME<br/>";
$query1 = "SELECT route_id FROM route WHERE (route_long_name LIKE '%– ' OR route_long_name LIKE ' –%') AND active=1;";
if ($result1 = mysqli_query($link, $query1)) {
    while ($row1 = mysqli_fetch_row($result1)) {
        $route_id = $row1[0];

        $query_min = "SELECT stop_name FROM stop WHERE stop_id IN (
			SELECT stop_id FROM stoptime WHERE trip_id IN (
				SELECT trip_id FROM trip WHERE route_id='$route_id'
			) AND stop_sequence IN (
				SELECT min(stop_sequence) FROM stoptime WHERE trip_id IN (
					SELECT trip_id FROM trip WHERE route_id='$route_id'
				)
			)
		);";
        $data_min = mysqli_fetch_row(mysqli_query($link, $query_min));
        $min_name = $data_min[0];

        $query_max = "SELECT stop_name FROM stop WHERE stop_id IN (
			SELECT stop_id FROM stoptime WHERE trip_id IN (
				SELECT trip_id FROM trip WHERE route_id='$route_id'
			) AND stop_sequence IN (
				SELECT max(stop_sequence) FROM stoptime WHERE trip_id IN (
					SELECT trip_id FROM trip WHERE route_id='$route_id'
				)
			)
		);";
        $data_max = mysqli_fetch_row(mysqli_query($link, $query_max));
        $max_name = $data_max[0];

        $wholename = "$min_name – $max_name";

        $query58  = "UPDATE route SET route_long_name = '$wholename' WHERE route_id= '$route_id';";
        $prikaz58 = mysqli_query($link, $query58);

        echo "$route_id > $wholename<br/>";
    }
}

echo "NO STOP<br/>";
$query2 = "SELECT trip_id FROM trip WHERE trip_id NOT IN (SELECT DISTINCT trip_id FROM stoptime);";
if ($result2 = mysqli_query($link, $query2)) {
    while ($row2 = mysqli_fetch_row($result2)) {
        $trip_id = $row2[0];

        echo "<a href=\"tripedit.php?id=$trip_id\">$trip_id</a><br/>";

//        $deaktivace = mysqli_query ($link, "UPDATE trip SET active=0 WHERE trip_id = '$trip_id';");
        $smazat = mysqli_query($link, "DELETE FROM trip WHERE trip_id='$trip_id';");
    }
}

echo "ONE STOP<br/>";
$query3 = "SELECT trip_id FROM (SELECT trip_id, count(*) AS pocet FROM stoptime GROUP BY trip_id) AS pomoc WHERE pocet=1 ORDER BY trip_id;";
if ($result3 = mysqli_query($link, $query3)) {
    while ($row3 = mysqli_fetch_row($result3)) {
        $trip_id = $row3[0];

        echo "<a href=\"tripedit.php?id=$trip_id\">$trip_id</a> > <a href=\"mark.php?id=$trip_id\">Regenerace</a><br/>";

//        $deaktivace = mysqli_query ($link, "UPDATE trip SET active=0 WHERE trip_id = '$trip_id';");
        $smazat1 = mysqli_query($link, "DELETE FROM trip WHERE trip_id='$trip_id';");
        $smazat2 = mysqli_query($link, "DELETE FROM stoptime WHERE trip_id = '$trip_id';");
    }
}

echo "NO TRIP<br/>";
$query4 = "SELECT route_id FROM route WHERE active=1 AND route_id NOT IN (SELECT DISTINCT route_id FROM trip WHERE active=1);";
if ($result4 = mysqli_query($link, $query4)) {
    while ($row4 = mysqli_fetch_row($result4)) {
        $route_id = $row4[0];

        echo "<a href=\"routeedit.php?id=$route_id\">$route_id</a><br/>";

        $deaktivace = mysqli_query($link, "UPDATE route SET active=0 WHERE route_id='$route_id';");
    }
}

echo "NO SHAPE<br/>";
$query = "SELECT trip_id FROM trip WHERE shape_id='';";
if ($result = mysqli_query($link, $query)) {
    while ($row = mysqli_fetch_row($result)) {
        $trip_id = $row[0];

        echo "$trip_id : <a href=\"tripedit.php?id=$trip_id\">Editace</a><br/>";
    }
}

echo "NO TIME<br/>";
$query124 = "SELECT trip_id FROM stoptime WHERE arrival_time='0' OR departure_time = '0';";
if ($result124 = mysqli_query($link, $query124)) {
    while ($row124 = mysqli_fetch_row($result124)) {
        $trip_id124 = $row124[0];

        echo "$trip_id124 : <a href=\"tripedit.php?id=$trip_id124\">Editace</a><br/>";
    }
}

echo "INACTIVE<br/>";
$query5 = "SELECT route_id FROM route WHERE active=0 AND route_id IN (SELECT DISTINCT route_id FROM trip WHERE active=1);";
if ($result5 = mysqli_query($link, $query5)) {
    while ($row5 = mysqli_fetch_row($result5)) {
        $route_id = $row5[0];

        echo "<a href=\"routeedit.php?id=$route_id\">$route_id</a><br/>";

        $aktivace = mysqli_query($link, "UPDATE route SET active=1 WHERE route_id='$route_id';");
    }
}

$query135 = "SELECT trip_id, stop_id, arrival_time, count(*) as pocet FROM stoptime GROUP BY trip_id, stop_id, arrival_time HAVING pocet > 1;";
if ($result135 = mysqli_query($link, $query135)) {
    while ($row135 = mysqli_fetch_row($result135)) {
        $trip_id = $row135[0];
        $stop_id = $row135[1];

        $query141 = "SELECT MAX(stop_sequence) FROM stoptime WHERE trip_id='$trip_id' AND stop_id='$stop_id';";
        if ($result141 = mysqli_query($link, $query141)) {
            while ($row141 = mysqli_fetch_row($result141)) {
                $maxseq = $row141[0];

                $query146 = "DELETE FROM stoptime WHERE trip_id = '$trip_id' AND stop_id = '$stop_id' AND stop_sequence = '$maxseq';";
//                echo "$query146<br/>";
                $prikaz146 = mysqli_query($link, $query146);
            }
        }

    }
}

include 'footer.php';

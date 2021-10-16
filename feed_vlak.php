<?php
$l1 = $_GET["l1"];
$l2 = $_GET["l2"];

$link = mysqli_connect('localhost', 'root', 'root', 'vlaky');
if (!$link) {
    echo "Error: Unable to connect to MySQL." . PHP_EOL;
    echo "Debugging errno: " . mysqli_connect_errno() . PHP_EOL;
    exit;
}

$time_start0 = microtime(true);

$dnes  = date("Y-m-d", time());
$konec = date("Y-m-d", strtotime("+ 63 days"));

$akt_trip = "SELECT route_id,trip_id,trip_headsign,direction_id,shape_id,wheelchair_accessible,bikes_allowed FROM trip WHERE active = '1' AND train_no > " . $l1 . " AND train_no < " . $l2 . " AND trip_id IN (SELECT trip_id FROM jizdy WHERE datum>='$dnes' AND datum<'$konec');";
if ($result85 = mysqli_query($link, $akt_trip)) {
    while ($row85 = mysqli_fetch_row($result85)) {
        $route_id              = $row85[0];
        $trip_id               = $row85[1];
        $trip_headsign         = $row85[2];
        $direction_id          = $row85[3];
        $shape_tvar            = $row85[4];
        $wheelchair_accessible = $row85[5];
        $bikes_allowed         = $row85[6];

        $matice = "";

        for ($i = 0; $i < 63; $i++) {
            $matice[$i] = 0;
        }

        $dnesden   = substr($dnes, 8, 2);
        $dnesmesic = substr($dnes, 5, 2);
        $dnesrok   = substr($dnes, 0, 4);
        $dnestime  = mktime(0, 0, 0, $dnesmesic, $dnesden, $dnesrok);

        $konecden   = substr($konec, 8, 2);
        $konecmesic = substr($konec, 5, 2);
        $konecrok   = substr($konec, 0, 4);
        $konectime  = mktime(0, 0, 0, $konecmesic, $konecden, $konecrok);

        $query64 = "SELECT datum FROM jizdy WHERE trip_id = '$trip_id' AND (datum>='$dnes' AND datum<='$konec');";
        if ($result64 = mysqli_query($link, $query64)) {
            while ($row64 = mysqli_fetch_row($result64)) {
                $datum = $row64[0];

                $datumden   = substr($datum, 8, 2);
                $datummesic = substr($datum, 5, 2);
                $datumrok   = substr($datum, 0, 4);
                $datumtime  = mktime(0, 0, 0, $datummesic, $datumden, $datumrok);

                $dnu          = (int) round(($datumtime - $dnestime) / 86400);
                $matice[$dnu] = 1;
            }
        }

        $vtydnu = date('w', $dnestime);

        $weekmatrix = "";
        $vyjimky_0  = [];
        $vyjimky_1  = [];

        for ($k = 0; $k < 7; $k++) {
            $linecount = 0;
            $except_0  = [];
            $except_1  = [];
            for ($j = $k; $j < strlen($matice); $j += 7) {
                $hodnota = (int) $matice[$j];
                $linecount += $hodnota;
                if ($hodnota == 0) {
                    array_push($except_0, $j);
                } else {
                    array_push($except_1, $j);
                }
            }
            if ($linecount >= 3) {
                $matrix_value = 1;
            } else {
                $matrix_value = 0;
            }
            $weekmatrix .= $matrix_value;
            if ($matrix_value == 0) {
                foreach ($except_1 as $vyjimka) {
                    array_push($vyjimky_1, $vyjimka);
                }
            } else {
                foreach ($except_0 as $vyjimka) {
                    array_push($vyjimky_0, $vyjimka);
                }
            }
        }

        sort($vyjimky_0);
        sort($vyjimky_1);

        $adjust = substr($weekmatrix, -$vtydnu + 1) . substr($weekmatrix, 0, -$vtydnu + 1);
        $dec    = bindec($adjust) + 1;

        $service_id = $dec;

        $except = "";
        foreach ($vyjimky_1 as $den) {
            $posun  = "+ " . $den . " days";
            $zaznam = date("Ymd", strtotime($posun));
            $day_id = date("z", strtotime($zaznam));
            $except .= "_" . $day_id . "(1)";
        }

        foreach ($vyjimky_0 as $den) {
            $posun  = "+ " . $den . " days";
            $zaznam = date("Ymd", strtotime($posun));
            $day_id = date("z", strtotime($zaznam));
            $except .= "_" . $day_id . "(2)";
        }

        $query119 = "SELECT id FROM calendar_except WHERE vyjimky = '$except';";
        if ($result119 = mysqli_query($link, $query119)) {
            $radku = mysqli_num_rows($result119);
            if ($radku == 0) {
                $vlozvyjimku = mysqli_query($link, "INSERT INTO calendar_except (vyjimky) VALUES ('$except');");
                $except_id   = mysqli_insert_id($link);
            } else {
                while ($row119 = mysqli_fetch_row($result119)) {
                    $except_id = $row119[0];
                }
            }
            mysqli_free_result($result119);
        }

        if ($except_id != "") {
            $service_id .= "_" . $except_id;
        }

        $mark_cal = mysqli_query($link, "INSERT INTO cal_use (trip_id, kalendar) VALUES ('$trip_id', '$service_id');");

        $query152 = "SELECT shape_id FROM shapetvary WHERE tvartrasy = '$shape_tvar';";
        if ($result152 = mysqli_query($link, $query152)) {
            $radku = mysqli_num_rows($result152);
            if ($radku == 0) {
                $vloztrasu = mysqli_query($link, "INSERT INTO shapetvary (tvartrasy, complete) VALUES ('$shape_tvar', '0');");
                $shape_id  = mysqli_insert_id($link);
            } else {
                while ($row152 = mysqli_fetch_row($result152)) {
                    $shape_id = $row152[0];
                }
            }
            mysqli_free_result($result152);
        }

        $current = "$route_id,$service_id,$trip_id,\"$trip_headsign\",$direction_id,$shape_id,$wheelchair_accessible,$bikes_allowed\n";
        $file    = 'trips.txt';
        file_put_contents($file, $current, FILE_APPEND);

        $query171   = "INSERT INTO shapecheck (trip_id, shape_id) VALUES ('$trip_id', '$shape_id');";
        $zapistrasy = mysqli_query($link, $query171);

    }
    mysqli_free_result($result85);
}

$tripstops = "SELECT trip_id,arrival_time,departure_time,stop_id,stop_sequence,stop_headsign,pickup_type,drop_off_type FROM stoptime WHERE trip_id IN (SELECT trip_id FROM trip WHERE active = '1' AND train_no > " . $l1 . " AND train_no < " . $l2 . " AND trip_id IN (SELECT trip_id FROM jizdy WHERE datum>='$dnes' AND datum<'$konec'));";
if ($result166 = mysqli_query($link, $tripstops)) {
    while ($row166 = mysqli_fetch_row($result166)) {
        $trip_id        = $row166[0];
        $arrival_time   = $row166[1];
        $departure_time = $row166[2];
        $stop_id        = $row166[3];
        $stop_sequence  = $row166[4];
        $stop_headsign  = $row166[5];
        $pickup_type    = $row166[6];
        $drop_off_type  = $row166[7];

        $current = "$trip_id,$arrival_time,$departure_time,$stop_id,$stop_sequence,\"$stop_headsign\",$pickup_type,$drop_off_type\n";
        $file    = 'stop_times.txt';
        file_put_contents($file, $current, FILE_APPEND);

        $mark_stop = mysqli_query($link, "INSERT INTO stop_use (trip_id, stop_id) VALUES ('$trip_id', '$stop_id');");
    }
}

$now = microtime(true);
echo "Rozsah $l1 až $l2: ";
echo $now - $time_start0;
echo "<br>\n";
$time_start = $now;

mysqli_close($link);

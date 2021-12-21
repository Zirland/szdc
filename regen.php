<?php
date_default_timezone_set('Europe/Prague');

function getContrastYIQ($hexcolor)
{
    $r   = hexdec(substr($hexcolor, 0, 2));
    $g   = hexdec(substr($hexcolor, 2, 2));
    $b   = hexdec(substr($hexcolor, 4, 2));
    $yiq = (($r * 299) + ($g * 587) + ($b * 114)) / 1000;
    return ($yiq >= 128) ? '000000' : 'FFFFFF';
}

function SelfCheck($Code)
{
    $arr  = str_split($Code, 1);
    $j    = 1;
    $souc = 0;
    foreach ($arr as $num) {
        if (($j % 2) == 1) {$num = (int) $num * 2;}
        if ($num > 9) {
            $rozp = str_split($num, 1);
            $num  = $rozp[0] + $rozp[1];
        }
        $souc = $souc + $num;
        $j    = $j + 1;
    }
    $kontr = 100 - $souc;
    while ($kontr > 9) {
        $kontr = $kontr - 10;
    }
    return $kontr;
}

$staty = [
    "AT" => "81",
    "BY" => "21",
    "CZ" => "54",
    "DE" => "80",
    "FR" => "87",
    "HU" => "55",
    "IT" => "83",
    "PL" => "51",
    "RU" => "20",
    "SK" => "56",
];

$barvy = [
    "50"   => "008000",
    "63"   => "008000",
    "69"   => "008000",
    "70"   => "B51741",
    "84"   => "0094DE",
    "94"   => "008000",
    "122"  => "0094DE",
    "157"  => "B51741",
    "209"  => "008000",
    "9000" => "008000",
    "9001" => "0094DE",
    "9002" => "0094DE",
    "9003" => "000000",
    "9004" => "ECAE01",
    "9005" => "008983",
    "9006" => "008000",
    "9007" => "000000",

    "11"   => "0094DE",
    "C1"   => "008000",
    "C2"   => "B51741",
    "C3"   => "0094DE",
    "C4"   => "0094DE",
    ""     => "0094DE",
];

$commerce = [
    "50"   => "EC",
    "63"   => "IC",
    "69"   => "Ex",
    "70"   => "EN",
    "84"   => "Os",
    "94"   => "SC",
    "122"  => "Sp",
    "157"  => "R",
    "209"  => "rj",
    "9000" => "Rx",
    "9001" => "TLX",
    "9002" => "TL",
    "9003" => "LE",
    "9004" => "RJ",
    "9005" => "AEx",
    "9006" => "NJ",
    "9007" => "LET",

    "11"   => "Os",
    "C1"   => "Ex",
    "C2"   => "R",
    "C3"   => "Sp",
    "C4"   => "",
    ""     => "",
];

$link = mysqli_connect('localhost', 'root', 'root', 'vlaky');
if (!$link) {
    echo "Error: Unable to connect to database." . PHP_EOL;
    echo "Reason: " . mysqli_connect_error() . PHP_EOL;
    exit;
}

$logid = $_GET["logid"];

$query81 = "SELECT obsah FROM log WHERE id = '$logid';";
if ($result81 = mysqli_query($link, $query81)) {
    while ($row81 = mysqli_fetch_row($result81)) {
        $obsah = $row81[0];
    }
}

$instrukce = <<<XML
$obsah
XML;

$xml  = simplexml_load_string($instrukce);
$type = $xml->getname();
switch ($type) {
    case 'CZPTTCISMessage':
        $agency_id = $xml->Identifiers->PlannedTransportIdentifiers[1]->Company;
        $Variant   = $xml->Identifiers->PlannedTransportIdentifiers[1]->Variant;
        $Locations = $xml->CZPTTInformation->CZPTTLocation;
        $planPA    = $xml->Identifiers->PlannedTransportIdentifiers[0];
        $block     = $planPA->ObjectType . "_" . $planPA->Company . "_" . $planPA->Core . "_" . $planPA->Variant . "_" . $planPA->TimetableYear;

        unset($shortnames);
        unset($routes);
        unset($triplist);

        $prev_route_id = $trip_id = $route_id = $headsign = $odd = $trasa = $route_color = "";

        foreach ($Locations as $locat) {
            $TrafficType           = (string) $locat->TrafficType;
            $CommercialTrafficType = (string) $locat->CommercialTrafficType;
            if ($CommercialTrafficType != "") {
                $route_color = $barvy[$CommercialTrafficType];
                $druh        = $commerce[$CommercialTrafficType];
            } else {
                $route_color = $barvy[$TrafficType];
                $druh        = $commerce[$TrafficType];
            }

            $shortname = (string) $locat->OperationalTrainNumber;
            $textcolor = getContrastYIQ($route_color);

            $Specific = '';
            foreach ($locat->NetworkSpecificParameter as $parametr) {
                if ($parametr->Name == "CZPassengerServiceNumber") {
                    $Specific = (string) $parametr->Value;
                }
            }

            if ($druh != "" || $shortname != "") {
                $shortnames[] = $druh . $shortname . "~" . $Specific;
                $routes[]     = "$druh$shortname~$Specific|$route_color|$textcolor";
            }
        }

        $routes     = array_unique($routes);
        $shortnames = array_unique($shortnames);

        $StartPeriod = $xml->CZPTTInformation->PlannedCalendar->ValidityPeriod->StartDateTime;
        $EndPeriod   = $xml->CZPTTInformation->PlannedCalendar->ValidityPeriod->EndDateTime;
        $datumod     = substr($StartPeriod, 0, 4) . substr($StartPeriod, 5, 2) . substr($StartPeriod, 8, 2);
        $datumdo     = substr($EndPeriod, 0, 4) . substr($EndPeriod, 5, 2) . substr($EndPeriod, 8, 2);
        if ($datumdo == "") {$datumdo = $datumod;}

        foreach ($shortnames as $shortname) {
            $number = explode("~", $shortname);
            switch ((int) preg_replace("/\D+/", "", $number[0]) % 2) {
                case "0":
                    $odd = "1";
                    break;
                case "1":
                    $odd = "0";
                    break;
            }
        }

        $vznik1 = $logid;
        $vznik  = $vznik1;
        if ($vznik1 > 999999) {$vznik = substr($vznik, -6);}
        if ($vznik1 < 100000) {$vznik = "0" . $vznik;}
        if ($vznik1 < 10000) {$vznik = "0" . $vznik;}
        if ($vznik1 < 1000) {$vznik = "0" . $vznik;}
        if ($vznik1 < 100) {$vznik = "0" . $vznik;}
        if ($vznik1 < 10) {$vznik = "0" . $vznik;}

        $shortname_data = mysqli_fetch_row(mysqli_query($link, "SELECT shortname from log WHERE id = '$logid';"));
        $shortname      = $shortname_data[0];
        $trip_id        = $shortname . $Variant . $vznik;

        $seq                        = 0;
        $prev_TrafficType           = (string) $Locations[0]->TrafficType;
        $prev_CommercialTrafficType = (string) $Locations[0]->CommercialTrafficType;
        if ($prev_CommercialTrafficType != "") {
            $prev_typ = $commerce[$prev_CommercialTrafficType];
        } else {
            $prev_typ = $commerce[$prev_TrafficType];
        }
        $prev_Specific = '';
        foreach ($Locations[0]->NetworkSpecificParameter as $parametr) {
            if ($parametr->Name == "CZPassengerServiceNumber") {
                $prev_Specific = (string) $parametr->Value;
            }
        }
        $prev_short   = $prev_typ . $Locations[0]->OperationalTrainNumber . "~" . $prev_Specific;
        $prev_trip_id = $prev_short . $Variant . $vznik;

        foreach ($Locations as $lokace) {
            unset($Activities);
            $seq            = $seq + 1;
            $Country        = (string) $lokace->Location->CountryCodeISO;
            $LocCode        = $lokace->Location->LocationPrimaryCode;
            $LocName        = $lokace->Location->PrimaryLocationName;
            $train_type     = (string) $lokace->TrafficType;
            $com_train_type = (string) $lokace->CommercialTrafficType;
            $shortname      = $lokace->OperationalTrainNumber;
            $Specific       = '';
            foreach ($lokace->NetworkSpecificParameter as $parametr) {
                if ($parametr->Name == "CZPassengerServiceNumber") {
                    $Specific = (string) $parametr->Value;
                }
            }

            if ($com_train_type != "" && $shortname != "") {
                $trip_id = $commerce[$com_train_type] . $shortname . "~" . $Specific . $Variant . $vznik;
            } else if ($shortname != "") {
                $trip_id = $commerce[$train_type] . $shortname . "~" . $Specific . $Variant . $vznik;
            }

            $Dwell    = $lokace->TimingAtLocation->DwellTime;
            $KontrCis = SelfCheck($LocCode);

            $countrcode = $staty[$Country];

            if ($countrcode == "") {
                $countrcode = $Country;
            }

            $stop_id = $countrcode . $LocCode . $KontrCis . "0";
            $trasa .= "$stop_id|";
            $prijezd = 0;
            $odjezd  = 0;
            $Timing  = $lokace->TimingAtLocation->Timing;
            if ($Timing) {
                foreach ($Timing as $cas) {
                    $TypCasu = $cas->attributes()->TimingQualifierCode;
                    $Hodnota = $cas->Time;
                    $Offset  = $cas->Offset;

                    $Hodnota_hod  = substr($Hodnota, 0, 2);
                    $Hodnota_rest = substr($Hodnota, 2, 6);
                    $Hodnota_hod  = ($Offset * 24) + $Hodnota_hod;
                    if ($Hodnota_hod < 10) {$Hodnota_hod = "0" . $Hodnota_hod;}
                    $Hodnota = $Hodnota_hod . $Hodnota_rest;
                    if ($TypCasu == "ALA") {$prijezd = $Hodnota;}
                    if ($TypCasu == "ALD") {$odjezd = $Hodnota;}
                }
            }
            if ($prijezd == "0" || $Offset < 0) {$prijezd = $odjezd;}
            if ($odjezd == "0") {$odjezd = $prijezd;}

            $Activities[] = "";
            foreach ($lokace->TrainActivity as $TrainActivity) {
                $Activities[] = $TrainActivity->TrainActivityType;
            }
            $nastup = $vystup = 0;
            if (in_array('0028', $Activities)) {
                $vystup = 1;
            }
            if (in_array('0029', $Activities)) {
                $nastup = 1;
            }
            if (in_array('0030', $Activities)) {
                $nastup = 3;
                $vystup = 3;
            }

            if ($odjezd < $prijezd) {
                $new_prijezd = $odjezd;
            } else {
                $new_prijezd = $prijezd;
            }
            $query214 = "INSERT INTO stoptime (trip_id, arrival_time, departure_time, stop_id, stop_sequence, stop_headsign, pickup_type, drop_off_type, shape_dist_traveled, timepoint) VALUES ('$trip_id','$new_prijezd','$odjezd','$stop_id','$seq', '','$nastup','$vystup',0,0);";
            if (((in_array('0001', $Activities)) || (in_array('0030', $Activities))) && ($train_type == "11" || $train_type == "C1" || $train_type == "C2" || $train_type == "C3")) {
                echo "287: $query214 = $LocName<br/>";
                $prikaz214 = mysqli_query($link, $query214);
            }
            $headsign = $LocName;

            if ($trip_id != $prev_trip_id) {
                $triplist[]       = $prev_trip_id;
                $prev_route_id    = "K" . $prev_trip_id;
                $prev_route_split = explode("~", $prev_route_id);
                if (substr($prev_route_split[1], 0, -8) != "") {
                    $prev_route_id = substr($prev_route_split[1], 0, -8);
                }
                $newtrip   = preg_replace('/\D+/', '', $prev_route_split[0]);
                $query284  = "DELETE FROM trip WHERE trip_id='$prev_trip_id';";
                $prikaz284 = mysqli_query($link, $query284);
                $query223  = "INSERT INTO trip (route_id, trip_id, trip_headsign, direction_id, shape_id, wheelchair_accessible, bikes_allowed, active, train_no, block_id) VALUES ('$prev_route_id', '$prev_trip_id', '$headsign', '$odd', '$trasa','0', '0', '1', '$newtrip', '$block');";
                $prikaz223 = mysqli_query($link, $query223);

                if ($odjezd < $prijezd) {
                    $new_odjezd = $prijezd;
                } else {
                    $new_odjezd = $odjezd;
                }
                if (((in_array('0001', $Activities)) || (in_array('0030', $Activities))) && ($train_type == "11" || $train_type == "C1" || $train_type == "C2" || $train_type == "C3")) {
                    $query295 = "INSERT INTO stoptime (trip_id, arrival_time, departure_time, stop_id, stop_sequence, stop_headsign, pickup_type, drop_off_type, shape_dist_traveled, timepoint) VALUES ('$prev_trip_id','$prijezd','$new_odjezd','$stop_id','$seq', '','$nastup','$vystup',0,0);";
                    echo "306: $query295 = $LocName<br/>";
                    $prikaz295 = mysqli_query($link, $query295);
                }

                $query298  = "UPDATE trip SET trip_headsign = '$headsign' WHERE trip_id = '$prev_trip_id';";
                $prikaz295 = mysqli_query($link, $query298);

                $trasa = "$stop_id|";

                $prev_route_id = $route_id;
                $prev_trip_id  = $trip_id;
            }
        }

        $route_id    = "K" . $trip_id;
        $route_split = explode("~", $route_id);
        if (substr($route_split[1], 0, -8) != "") {
            $route_id = substr($route_split[1], 0, -8);
        }

        $newtrip = preg_replace('/\D+/', '', $route_split[0]);

        $triplist[] = $trip_id;
        $query284   = "DELETE FROM trip WHERE trip_id='$trip_id';";
        $prikaz284  = mysqli_query($link, $query284);
        $query223   = "INSERT INTO trip (route_id, trip_id, trip_headsign, direction_id, shape_id, wheelchair_accessible, bikes_allowed, active, train_no, block_id) VALUES ('$route_id', '$trip_id', '$headsign', '$odd', '$trasa','0', '0', '1', '$newtrip', '$block');";
        $prikaz223  = mysqli_query($link, $query223);

        $i = 0;
        foreach ($routes as $route_string) {
            $wholename      = "";
            $route_data     = explode("|", $route_string);
            $route_id       = "K" . $triplist[$i];
            $shortname_expl = explode("~", $route_data[0]);
            $shortname      = $shortname_expl[0];
            $route_color    = $route_data[1];
            $textcolor      = $route_data[2];

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

            $query125 = "DELETE FROM route WHERE route_id = '$route_id';";
            echo "$query125<br/>";
            $prikaz125 = mysqli_query($link, $query125);

            $query108 = "INSERT INTO route (route_id, agency_id, route_short_name, route_long_name, route_type, route_color, route_text_color, active) VALUES ('$route_id', '$agency_id', '$shortname', '$wholename', '2', '$route_color', '$textcolor', '1');";
            echo "$query108<br/>";
            $prikaz108 = mysqli_query($link, $query108);
            $i         = $i + 1;
        }

        $matice = "";

        $bitmap = $xml->CZPTTInformation->PlannedCalendar->BitmapDays;

        $Dod         = substr($datumod, 6, 2);
        $Mod         = substr($datumod, 4, 2);
        $Yod         = substr($datumod, 0, 4);
        $timeod      = mktime(0, 0, 0, $Mod, $Dod, $Yod);
        $maticestart = mktime(0, 0, 0, $Mod, $Dod, $Yod);
        $format_od   = date("Y-m-d", $timeod);
        $zacdnu      = round(($timeod - $maticestart) / 86400);
        $Ddo         = substr($datumdo, 6, 2);
        $Mdo         = substr($datumdo, 4, 2);
        $Ydo         = substr($datumdo, 0, 4);
        $timedo      = mktime(0, 0, 0, $Mdo, $Ddo, $Ydo);
        $format_do   = date("Y-m-d", $timedo);
        $kondnu      = round(($timedo - $maticestart) / 86400);

        for ($g = 0; $g < 406; $g++) {
            if ($g < $zacdnu) {$matice[$g] = 0;}
            if ($g == $zacdnu) {$matice .= $bitmap;}
            if ($g > $kondnu) {$matice[$g] = 0;}
        }

        foreach ($triplist as $trip_id) {
            $skupina   = substr($trip_id, 0, -8);
            $cisti388  = "DELETE FROM jizdy WHERE shortname = '$skupina' AND datum >= '$format_od' AND datum <= '$format_do';";
            $prikaz388 = mysqli_query($link, $cisti388);
            for ($h = 0; $h < 406; $h++) {
                $tentoden  = $maticestart + ($h * 86400);
                $totodatum = date("Y-m-d", $tentoden);

                if ($matice[$h] == "1") {
                    $query188  = "INSERT INTO jizdy (shortname, trip_id, datum) VALUES ('$skupina','$trip_id','$totodatum');";
                    $prikaz188 = mysqli_query($link, $query188);
                }
            }
        }
        break;

    case 'CZCanceledPTTMessage':
        $planPA = $xml->PlannedTransportIdentifiers[0];
        $PA_id  = $planPA->ObjectType . "_" . $planPA->Company . "_" . $planPA->Core . "_" . $planPA->Variant . "_" . $planPA->TimetableYear . ".xml";

        $shortnames[] = '';

        $query414 = "SELECT shortname FROM log WHERE file = '$PA_id';";
        echo "$query414<br/>";
        if ($result414 = mysqli_query($link, $query414)) {
            while ($row414 = mysqli_fetch_row($result414)) {
                $shortnames[] = $row414[0];
            }
        }
        $StartPeriod = $xml->PlannedCalendar->ValidityPeriod->StartDateTime;
        $EndPeriod   = $xml->PlannedCalendar->ValidityPeriod->EndDateTime;
        $datumod     = substr($StartPeriod, 0, 4) . substr($StartPeriod, 5, 2) . substr($StartPeriod, 8, 2);
        $datumdo     = substr($EndPeriod, 0, 4) . substr($EndPeriod, 5, 2) . substr($EndPeriod, 8, 2);
        if ($datumdo == "") {$datumdo = $datumod;}

        foreach ($shortnames as $shortname) {
            $query167  = "INSERT INTO log(file, shortname, trip_id, datumod, datumdo, obsah) VALUES ('$nazev','$shortname','','$datumod','$datumdo', '$obsah');";
            $prikaz167 = mysqli_query($link, $query167);

            $matice = "";
            $bitmap = $xml->PlannedCalendar->BitmapDays;

            $Dod         = substr($datumod, 6, 2);
            $Mod         = substr($datumod, 4, 2);
            $Yod         = substr($datumod, 0, 4);
            $timeod      = mktime(0, 0, 0, $Mod, $Dod, $Yod);
            $maticestart = mktime(0, 0, 0, $Mod, $Dod, $Yod);
            $format_od   = date("Y-m-d", $timeod);
            $zacdnu      = round(($timeod - $maticestart) / 86400);
            $Ddo         = substr($datumdo, 6, 2);
            $Mdo         = substr($datumdo, 4, 2);
            $Ydo         = substr($datumdo, 0, 4);
            $timedo      = mktime(0, 0, 0, $Mdo, $Ddo, $Ydo);
            $format_do   = date("Y-m-d", $timedo);
            $kondnu      = round(($timedo - $maticestart) / 86400);

            for ($g = 0; $g < 406; $g++) {
                if ($g < $zacdnu) {$matice[$g] = 0;}
                if ($g == $zacdnu) {$matice .= $bitmap;}
                if ($g > $kondnu) {$matice[$g] = 0;}
            }

            foreach ($shortnames as $shortname) {
                for ($h = 0; $h < 406; $h++) {
                    $tentoden  = $maticestart + ($h * 86400);
                    $totodatum = date("Y-m-d", $tentoden);

                    if ($matice[$h] == "1") {
                        $query188 = "DELETE FROM jizdy WHERE shortname = '$shortname' AND datum = '$totodatum';";
                        echo "$query188<br/>";
                        $prikaz188 = mysqli_query($link, $query188);
                    }
                }
            }

        }

        break;
}

mysqli_close($link);

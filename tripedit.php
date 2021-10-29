<style>
 /* Popup container */
.popup {
    position: relative;
    display: inline-block;
    cursor: pointer;
}

/* The actual popup (appears on top) */
.popup .popuptext {
    visibility: hidden;
    width: 400px;
    background-color: #555;
    color: #fff;
    text-align: center;
    border-radius: 6px;
    padding: 8px 0;
    position: absolute;
    z-index: 1;
    bottom: 125%;
    left: 50%;
    margin-left: -100px;
}

/* Popup arrow */
.popup .popuptext::after {
    content: "";
    position: absolute;
    top: 100%;
    left: 50%;
    margin-left: -5px;
    border-width: 5px;
    border-style: solid;
    border-color: #555 transparent transparent transparent;
}

/* Toggle this class when clicking on the popup container (hide and show the popup) */
.popup .show {
    visibility: visible;
    -webkit-animation: fadeIn 1s;
    animation: fadeIn 1s
}

/* Add animation (fade in the popup) */
@-webkit-keyframes fadeIn {
    from {opacity: 0;}
    to {opacity: 1;}
}

@keyframes fadeIn {
    from {opacity: 0;}
    to {opacity:1 ;}
}
</style>

<script>
// When the user clicks on <div>, open the popup
function myFunction() {
    var popup = document.getElementById("myPopup");
    popup.classList.toggle("show");
}
</script>

<?php
include 'header.php';

$trip   = @$_GET['id'];
$action = @$_POST['action'];

switch ($action) {
    case "hlava":
        $trip     = @$_POST['trip_id'];
        $linka    = @$_POST['route_id'];
        $smer     = @$_POST['smer'];
        $blok     = @$_POST['block_id'];
        $invalida = @$_POST['invalida'];
        $cyklo    = @$_POST['cyklo'];

        $ready0   = "UPDATE trip SET route_id='$linka', direction_id='$smer', block_id='$blok', wheelchair_accessible='$invalida', bikes_allowed='$cyklo' WHERE (trip_id = '$trip');";
        $aktualz0 = mysqli_query($link, $ready0);

        $skupina  = substr($trip, 0, -6);
        $ready1   = "INSERT INTO linky (skupina, route_id) VALUES ('$skupina', '$linka');";
        $aktualz1 = mysqli_query($link, $ready1);
        break;

    case "zastavky":
        $trip  = @$_POST['trip_id'];
        $pocet = @$_POST['pocet'];

        for ($y = 0; $y < $pocet; $y++) {
            $ind            = $y;
            $arrindex       = "arrive" . $ind;
            $arrival_time   = @$_POST[$arrindex];
            $depindex       = "leave" . $ind;
            $departure_time = @$_POST[$depindex];
            $rzmindex       = "rezim" . $ind;
            $rzm            = @$_POST[$rzmindex];
            $pickup_type    = substr($rzm, 0, 1);
            $drop_off_type  = substr($rzm, 1, 1);
            $seqindex       = "poradi" . $ind;
            $stop_sequence  = @$_POST[$seqindex];
            $nameindex      = "stopname" . $ind;
            $stop_name      = @$_POST[$nameindex];
            $stpidindex     = "stop_id" . $ind;
            $stop_id        = @$_POST[$stpidindex];
            $stp2idindex    = "stop2_id" . $ind;
            $stop2_id       = @$_POST[$stp2idindex];

            $skipindex = "skip" . ${$ind};
            $skip      = $_POST[$skipindex];

            $brkindex = "break" . ${$ind};
            $break    = $_POST[$brkindex];

            $shortname = substr($trip, 0, -8);
            if ($break == 1) {
                $query59 = "INSERT INTO break (vlak, stop_id) VALUES ('$shortname', '$stop_id');";
//                echo "$query59<br/>";
                $prikaz59 = mysqli_query($link, $query59);
                $query61  = "INSERT INTO routelist (CISLO7) VALUES ('$cislo7');";
//                echo "$query61<br/>";
                $prikaz61 = mysqli_query($link, $query61);
            }

            switch ($skip) {
                case 1:
                    $query58 = "DELETE FROM stoptime WHERE ((trip_id = '$trip') AND (stop_sequence = '$stop_sequence'));";
//                    echo "$query58<br/>";
                    $prikaz58 = mysqli_query($link, $query58);
                    $query65  = "INSERT INTO skip (shortname, stop_id) VALUES ('$shortname','$stop_id');";
//                    echo "$query65<br/>";
                    $prikaz65 = mysqli_query($link, $query65);

                    break;

                default:
                    $ready1   = "UPDATE stoptime SET arrival_time='$arrival_time', departure_time='$departure_time', pickup_type='$pickup_type', drop_off_type='$drop_off_type' WHERE ((trip_id ='$trip') AND (stop_sequence = '$stop_sequence'));";
                    $aktualz1 = mysqli_query($link, $ready1);

                    $ready2   = "UPDATE stop SET stop_name='$stop_name' WHERE (stop_id ='$stop_id');";
                    $aktualz2 = mysqli_query($link, $ready2);
                    break;
            }
        }
        break;

    case "grafikon":
        $trip   = $_POST['trip_id'];
        $grafi  = "";
        $invert = $_POST['invert'];
        $altern = $_POST['altern'];
        $proti  = @$_POST['proti'];

        switch ($invert) {
            case 1:
                for ($v = 0; $v < 365; $v++) {
                    $$ind  = $v;
                    $index = "grafikon" . ${$ind};
                    $mtrx  = $_POST[$index];

                    switch ($mtrx) {
                        case 1:
                            $grafi .= "0";
                            break;
                        case 0:
                            $grafi .= "1";
                            break;
                    }
                }
                break;

            default:
                for ($v = 0; $v < 365; $v++) {
                    $$ind  = $v;
                    $index = "grafikon" . ${$ind};
                    $mtrx  = $_POST[$index];
                    $grafi .= $mtrx;
                }
                break;
        }

        $denne = $_POST['denne'];
        if ($denne == 1) {
            $grafi = "";
            for ($i = 0; $i < 365; $i++) {
                $grafi .= "1";
            }
        }

        if ($altern == "1") {
            $pom84  = mysqli_fetch_row(mysqli_query($link, "SELECT matice FROM trip WHERE (trip_id = '$proti');"));
            $matice = $pom84[0];
            $grafi  = "";

            $grafikon = str_split($matice);
            for ($w = 0; $w < 365; $w++) {
                switch ($grafikon[$w]) {
                    case 0:
                        $grafi .= "1";
                        break;
                    case 1:
                        $grafi .= "0";
                        break;
                }
            }
        }

        $maticestart = mktime(0, 0, 0, 12, 10, 2017);
        $typkodu     = @$_POST['typkodu'];
        $datumod     = @$_POST['datumod'];
        $datumdo     = @$_POST['datumdo'];
        if ($datumdo == "") {
            $datumdo = $datumod;
        }

        switch ($typkodu) {
            case "0":
                break;
            case "1": // echo "jede od ".$datumod." do ".$datumdo."<br/>";
                $Dod    = substr($datumod, 0, 2);
                $Mod    = substr($datumod, 2, 2);
                $Yod    = substr($datumod, -4);
                $timeod = mktime(0, 0, 0, $Mod, $Dod, $Yod);
                $zacdnu = round(($timeod - $maticestart) / 86400);
                $Ddo    = substr($datumdo, 0, 2);
                $Mdo    = substr($datumdo, 2, 2);
                $Ydo    = substr($datumdo, -4);
                $timedo = mktime(0, 0, 0, $Mdo, $Ddo, $Ydo);
                $kondnu = round(($timedo - $maticestart) / 86400);

                for ($g = 0; $g < 365; $g++) {
                    if ($g >= $zacdnu && $g <= $kondnu) {$grafi[$g] = 1;}
                }
                break;

            case "4": // echo "nejede od ".$datumod." do ".$datumdo."<br/>";
                $Dod    = substr($datumod, 0, 2);
                $Mod    = substr($datumod, 2, 2);
                $Yod    = substr($datumod, -4);
                $timeod = mktime(0, 0, 0, $Mod, $Dod, $Yod);
                $zacdnu = round(($timeod - $maticestart) / 86400);
                $Ddo    = substr($datumdo, 0, 2);
                $Mdo    = substr($datumdo, 2, 2);
                $Ydo    = substr($datumdo, -4);
                $timedo = mktime(0, 0, 0, $Mdo, $Ddo, $Ydo);
                $kondnu = round(($timedo - $maticestart) / 86400);

                for ($g = 0; $g < 365; $g++) {
                    if ($g >= $zacdnu && $g <= $kondnu) {$grafi[$g] = 0;}
                }
                break;
        }

        $operace = "UPDATE trip SET matice='$grafi' WHERE (trip_id = '$trip');";
        $vykonej = mysqli_query($link, $operace) or die(mysqli_error());
        break;
}

echo "<table><tr><td>";
echo "<table>";
echo "<tr>";

$hlavicka_query = "SELECT route_id, trip_id, trip_headsign, direction_id, shape_id, wheelchair_accessible, bikes_allowed, active FROM trip WHERE (trip_id='$trip');";
$hlavicka      = mysqli_fetch_row(mysqli_query($link, $hlavicka_query));
$linka         = $hlavicka[0];
$trip_id       = $hlavicka[1];
$trip_headsign = $hlavicka[2];
$smer          = $hlavicka[3];
$shape         = $hlavicka[4];
$invalida      = $hlavicka[5];
$cyklo         = $hlavicka[6];
$aktif         = $hlavicka[7];
$shortname     = substr($trip_id, 0, -8);

echo "<td><a href = \"routeedit.php?id=$linka\">Zpět na linku</a><td>";
echo "<td><form method=\"get\" action=\"tripedit.php\" name=\"id\"><input type=\"text\" name=\"id\" value=\"\"><input type=\"submit\"></form><td>";
echo "<td><a href=\"tripdelete.php?trip=$trip_id\" target=\"_blank\">Smazat trip</a></td>";
echo "</tr><tr>";

echo "<form method=\"post\" action=\"tripedit.php\" name=\"hlava\"><input name=\"action\" value=\"hlava\" type=\"hidden\"><input name=\"trip_id\" value=\"$trip_id\" type=\"hidden\">";
echo "<td>$trip_id</td><td>Linka: <select name=\"route_id\">";

$query45 = "SELECT route_id, route_short_name, route_long_name FROM route ORDER BY route_short_name;";
if ($result45 = mysqli_query($link, $query45)) {
    while ($row45 = mysqli_fetch_row($result45)) {
        $roid     = $row45[0];
        $roshname = $row45[1];
        $rolgname = $row45[2];

        echo "<option value=\"$roid\"";
        if ($roid == $linka) {
            echo " SELECTED";
        }
        echo ">$roshname - $rolgname</option>";
    }
}
echo "</select></td><td>Směr: $trip_headsign<br />";
echo "<select name=\"smer\"><option value=\"0\"";
if ($smer == '0') {
    echo " SELECTED";
}
echo ">Odchozí</option><option value=\"1\"";
if ($smer == '1') {
    echo " SELECTED";
}
echo ">Příchozí</option></select></td>";
echo "<td>Invalida: <select name=\"invalida\"><option value=\"0\"";
if ($invalida == '0') {
    echo " SELECTED";
}
echo "></option><option value=\"1\"";
if ($invalida == '1') {
    echo " SELECTED";
}
echo ">Vlak vhodný pro přepravu</option><option value=\"2\"";
if ($invalida == '2') {
    echo " SELECTED";
}
echo ">Vlak neumožňuje přepravu</option></select><br />";
echo "Cyklo: <select name=\"cyklo\"><option value=\"0\"";
if ($cyklo == '0') {
    echo " SELECTED";
}
echo "></option><option value=\"1\"";
if ($cyklo == '1') {
    echo " SELECTED";
}
echo ">Vlak vhodný pro přepravu</option><option value=\"2\"";
if ($cyklo == '2') {
    echo " SELECTED";
}
echo ">Vlak neumožňuje přepravu</option></select>";
echo "</td>";
echo "<td>Aktivní <input type=\"checkbox\" name=\"aktif\" value=\"1\"";
if ($aktif == '1') {
    echo " CHECKED";
}
echo "></td><td><input type=\"submit\"></td></tr></form>";
echo "<tr><td colspan=\"5\">";

if (strpos($trip_id, 'F') !== false) {
    $vlak = substr($trip_id, 1, -2);
} else {
    $vlak = substr($trip_id, 0, -2);
}

$lomeni = substr($vlak, -1);
$cislo7 = $vlak . "/" . $lomeni;

$query86 = "SELECT POZNAM FROM kango.OBP WHERE ((CISLO7='$cislo7'));";
if ($result86 = mysqli_query($link, $query86)) {
    while ($row86 = mysqli_fetch_row($result86)) {
        $poznamka = $row86[0];

        echo "$poznamka<br />";
    }
}

echo "</td></tr>";
echo "</table>";
echo "<div class=\"popup\" onclick=\"myFunction()\">Regenerace<span class=\"popuptext\" id=\"myPopup\">";
$query265 = "SELECT id,trip_id,file FROM log WHERE shortname = '$shortname';";
if ($result265 = mysqli_query($link, $query265)) {
    while ($row265 = mysqli_fetch_row($result265)) {
        $id      = $row265[0];
        $skupina = substr($row265[1], 0, -6);
        $file    = $row265[2];

        echo "<a href=\"regen.php?logid=$id\" target=\"_blank\">$skupina $file</a><br/>";
    }
}
echo "</span></div>";
echo "<table>";
echo "<tr><td>";
echo "<table>";
echo "<tr><th>Stanice</th><th>Příjezd</th><th><Odjezd</th><th>Režim</th><th>S &nbsp;B</th></tr>";

echo "<form method=\"post\" action=\"tripedit.php\" name=\"zastavky\"><input name=\"action\" value=\"zastavky\" type=\"hidden\"><input name=\"trip_id\" value=\"$trip_id\" type=\"hidden\">";
$z = 0;

$query108 = "SELECT stoptime.stop_id,stoptime.arrival_time,stoptime.departure_time,stoptime.pickup_type,stoptime.drop_off_type,stoptime.stop_sequence, stop.stop_name FROM stoptime LEFT JOIN stop ON stoptime.stop_id = stop.stop_id WHERE (stoptime.trip_id = '$trip_id') ORDER BY stoptime.stop_sequence;";

if ($result108 = mysqli_query($link, $query108)) {
    while ($row108 = mysqli_fetch_row($result108)) {
        $stop_id        = $row108[0];
        $arrival_time   = $row108[1];
        $departure_time = $row108[2];
        $pickup_type    = $row108[3];
        $drop_off_type  = $row108[4];
        $stop_sequence  = $row108[5];
        $nazev_stanice  = $row108[6];

        echo "<tr><td><input name=\"stop_id$z\" value=\"$stop_id\" type=\"hidden\">";
        echo "<a href=\"stopedit.php?id=$stop_id\" target=\"_blank\">E </a>";
        echo "<input type=\"text\" name=\"stopname$z\" value=\"$nazev_stanice\"></td>";
        echo "<td><input type=\"text\" name=\"arrive$z\" value=\"$arrival_time\"></td>";
        echo "<td><input type=\"text\" name=\"leave$z\" value=\"$departure_time\"></td>";
        echo "<td><select name=\"rezim$z\"><option value=\"00\"></option>";
        echo "<option value=\"01\"";
        if ($drop_off_type == 1) {
            echo " SELECTED";
        }
        echo ">Pouze nástup</option>";
        echo "<option value=\"10\"";
        if ($pickup_type == 1) {
            echo " SELECTED";
        }
        echo ">Pouze výstup</option>";
        echo "<option value=\"22\"";
        if ($drop_off_type == 2) {
            echo " SELECTED";
        }
        echo ">Vlak nezastavuje</option>";
        echo "<option value=\"33\"";
        if ($drop_off_type == 3) {
            echo " SELECTED";
        }
        echo ">Zastavuje na znamení</option>";
        echo "<select></td>";
        echo "<td><input name=\"poradi$z\" value=\"$stop_sequence\" type=\"hidden\"><input type=\"checkbox\" name=\"skip$z\" value=\"1\"><input type=\"checkbox\" name=\"break$z\" value=\"1\"></td></tr>";
        $z = $z + 1;
    }
}

echo "<input type=\"hidden\" name=\"pocet\" value=\"$z-1\">";
echo "<input type=\"submit\"></form>";
echo "</table></td><td>";

echo "TRASA<br />";
$shape = substr($shape, 0, -1);
$body  = explode("|", $shape);
$cnt   = 1;
foreach ($body as $prujezd) {
    $query_bod = "SELECT stop_name FROM stop WHERE stop_id = '$prujezd';";
    $data_bod  = mysqli_fetch_row(mysqli_query($link, $query_bod));
    if ($data_bod) {
        $bod_name = $data_bod[0];
    } else {
        $bod_name = "";
    }
    echo "$cnt | $prujezd | $bod_name<br/>";
    $cnt = $cnt + 1;
}

echo "</td></tr>";
echo "</table>";

echo "</td></tr></table>";

echo "JÍZDY<br/>";
unset($datumy);
$query419 = "SELECT datum FROM jizdy WHERE trip_id = '$trip_id';";
if ($result419 = mysqli_query($link, $query419)) {
    while ($row419 = mysqli_fetch_row($result419)) {
        $datumy[] = $row419[0];
    }
}

$matice_start = date("Y-m-d", time());

echo "<table border=\"1\"><tr><td>";
for ($u = 0; $u < 365; $u++) {
    $datum         = strtotime($matice_start);
    $datum         = strtotime("+$u days", $datum);
    $datum_format  = date("d.m.", $datum);
    $datum_compare = date("Y-m-d", $datum);
    $denvtydnu     = date('w', $datum);
    if (in_array($datum_compare, $datumy)) {
        echo "<span style=\"background-color:green;\">";
    }
    echo "$datum_format<br />";
    if (in_array($datum_compare, $datumy)) {
        echo "</span>";
    }
    if ($denvtydnu == "0") {
        echo "</td><td>";
    }
}
echo "</td></tr></table>";

include 'footer.php';
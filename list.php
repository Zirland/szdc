<?php
$t = $_GET['t'];
include 'header.php';
switch ($t) {
    case 'ag':
        echo "<table>";
        echo "<tr>";
        echo "<th>ID</th><th>Název</th><th>URL</th><th>Telefon</th><th>E-mail</th><th></th>";
        echo "</tr>";
        echo "<tr>";
        $query = "SELECT * FROM agency ORDER BY agency_id";
        if ($result = mysqli_query($link, $query)) {
            while ($row = mysqli_fetch_row($result)) {
                $agency_id       = $row[0];
                $agency_name     = $row[1];
                $agency_url      = $row[2];
                $agency_timezone = $row[3];
                $agency_lang     = $row[4];
                $agency_phone    = $row[5];
                $agency_fare_url = $row[6];
                $agency_email    = $row[7];
                $agency_active   = $row[8];

                echo "<tr><td>$agency_id</td><td>$agency_name</td><td>$agency_url</td><td>$agency_phone</td><td>$agency_email</td><td><a href=\"edit.php?t=agency&id=$agency_id\">Editovat</a></td></tr>";
            }
            mysqli_free_result($result);
        }
        echo "<table>";
        break;

    case 'ro':
        echo "<table>";
        echo "<tr>";
        echo "<th>Přepravce</th><th>Linka</th><th>Trasa</th><th>Typ</th><th></th><th></th>";
        echo "</tr>";
        $query = "SELECT * FROM route WHERE (active = 1) ORDER BY agency_id DESC, route_short_name;";
        if ($result = mysqli_query($link, $query)) {
            while ($row = mysqli_fetch_row($result)) {
                $route_id         = $row[0];
                $agency_id        = $row[1];
                $route_short      = $row[2];
                $route_long       = $row[3];
                $route_type       = $row[4];
                $route_color      = $row[5];
                $route_text_color = $row[6];
                $route_active     = $row[7];
                $route_kraj       = $row[8];

                echo "<tr>";

                $ro_ag_pom = mysqli_fetch_row(mysqli_query($link, "SELECT agency_name FROM agency WHERE (agency_id = $agency_id);"));
                $ro_ag     = $ro_ag_pom['0'];
                echo "<td>$ro_ag</td>";

                echo "<td style=\"background-color: #$route_color; text-align: center;\"><span style=\"color: #$route_text_color;\">$route_short$route_kraj</td>";
                echo "<td";
                if ($route_active == "1") {
                    echo " style=\"background-color: #54FF00;\"";
                }
                echo ">$route_long</td>";

                switch ($route_type) {
                    case 0:
                        echo "<td>tramvaj</td>";
                        break;
                    case 1:
                        echo "<td>metro</td>";
                        break;
                    case 2:
                        echo "<td>vlak</td>";
                        break;
                    case 3:
                        echo "<td>autobus</td>";
                        break;
                    case 4:
                        echo "<td>přívoz</td>";
                        break;
                    case 5:
                        echo "<td>trolejbus</td>";
                        break;
                    case 6:
                        echo "<td>kabinová lanovka</td>";
                        break;
                    case 7:
                        echo "<td>kolejová lanovka</td>";
                        break;
                    default:
                        echo "<td></td>";
                        break;
                }
                echo "<td><a href=\"routeedit.php?id=$route_id\">Detaily</a></td>";
//                echo "<td><a href=\"match.php?route=$route_id\">Srovnání</a></td></tr>";
            }
            mysqli_free_result($result);
        }
        echo "<table>";
        break;
}

include 'footer.php';

<?php
date_default_timezone_set('Europe/Prague');

$files = glob("ftp.cisjr.cz/draha/celostatni/szdc/2021/2021-10/*.xml");
usort($files, function ($a, $b) {return filemtime($a) <=> filemtime($b);});

$pocet    = count($files);
$maxpocet = $pocet - 1;

if ($pocet > 0) {
    for ($i = $maxpocet; $i > 0; $i--) {
        //   $i        = $maxpocet;
        $file     = $files[$i];
        $filetime = date("d.M.Y H:i:s", filemtime($file));
        $nazev    = substr($file, 48);

        $handle = fopen($file, "r");
        $obsah  = fread($handle, filesize($file));
        fclose($handle);

        $xml  = simplexml_load_file($file);
        $type = $xml->getname();
        echo "$type | ";

        switch ($type) {
            case 'CZPTTCISMessage':
                echo $xml->CZPTTCreation . " | ";

                $planPA = $xml->Identifiers->PlannedTransportIdentifiers[0];
                $PA_id  = $planPA->ObjectType . "_" . $planPA->Company . "_" . $planPA->Core . "_" . $planPA->Variant . "_" . $planPA->TimetableYear . ".xml";

                echo "Plan: $PA_id | ";

                $relPA = $xml->Identifiers->RelatedPlannedTransportIdentifiers;
                if (count($relPA) > 0) {
                    $Rel_id = $relPA->ObjectType . "_" . $relPA->Company . "_" . $relPA->Core . "_" . $relPA->Variant . "_" . $relPA->TimetableYear . ".xml";

                    echo "Rel: $Rel_id";
                }
                echo " | ";

                break;
            case 'CZCanceledPTTMessage':
                echo $xml->CZPTTCancelation . " | ";

                $planPA = $xml->PlannedTransportIdentifiers[0];
                $PA_id  = $planPA->ObjectType . "_" . $planPA->Company . "_" . $planPA->Core . "_" . $planPA->Variant . "_" . $planPA->TimetableYear . ".xml";

                echo "Plan: $PA_id | ";

                break;
            default:
                echo "XXX | ";
                break;
        }

        echo "<br/>";
    }
}

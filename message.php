<?php
date_default_timezone_set('Europe/Prague');

$files = glob("ftp.cisjr.cz/draha/celostatni/szdc/2021/blok2/*.xml");
usort($files, function ($a, $b) {return filemtime($a) <=> filemtime($b);});

$pocet    = count($files);
$maxpocet = $pocet - 1;

if ($pocet > 0) {
    for ($i = $maxpocet; $i > 0; $i--) {
 //   $i        = $maxpocet;
    $file     = $files[$i];
    $filetime = date("d.M.Y H:i:s", filemtime($file));
    $nazev    = substr($file, 46);
    echo "File: $nazev | $filetime | ";

    $handle = fopen($file, "r");
    $obsah  = fread($handle, filesize($file));
    fclose($handle);

    $xml = simplexml_load_file($file);
    print_r($xml->getname());
    echo $xml->CZPTTCreation ."<br/>";
}}

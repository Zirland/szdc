<?php
$files = glob("ftp.cisjr.cz/draha/celostatni/szdc/2022/2021-12/*.xml");
usort($files, function ($a, $b) {return filemtime($a) <=> filemtime($b);});

$index = count($files) - 1;
$file  = $files[$index];
$nazev = substr($file, 48);

unlink($file);
echo "<meta http-equiv=\"refresh\" content=\"0;url='GVD.php'\">";

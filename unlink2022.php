<?php
$files = glob("ftp.cisjr.cz/draha/celostatni/szdc/2022/GVD2022/*.xml");
usort($files, function ($a, $b) {return filemtime($a) <=> filemtime($b);});

$index = 0;
$file  = $files[$index];
$nazev = substr($file, 48);

unlink($file);
echo "<meta http-equiv=\"refresh\" content=\"0;url='GVD2022.php'\">";

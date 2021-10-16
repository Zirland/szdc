<?php
$files = glob("ftp.cisjr.cz/draha/celostatni/szdc/2021/GVD2021/*.xml");
usort($files, function ($a, $b) {return filemtime($a) <=> filemtime($b);});

$file  = $files[0];
$nazev = substr($file, 48);

unlink($file);
echo "<meta http-equiv=\"refresh\" content=\"0;url='GVD.php'\">";

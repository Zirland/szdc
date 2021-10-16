#!/bin/bash

cd /Applications/MAMP/htdocs/szdc/

#curl http://localhost/szdc/cisti.php
#curl http://localhost/szdc/opravy.php

curl http://localhost/szdc/feed_agency.php
curl http://localhost/szdc/feed_jdf_start.php

curl 'http://localhost/szdc/feed_vlak.php?l1=0&l2=100'
curl 'http://localhost/szdc/feed_vlak.php?l1=100&l2=1000'
curl 'http://localhost/szdc/feed_vlak.php?l1=1000&l2=2000'
curl 'http://localhost/szdc/feed_vlak.php?l1=2000&l2=3000'
curl 'http://localhost/szdc/feed_vlak.php?l1=3000&l2=4000'
curl 'http://localhost/szdc/feed_vlak.php?l1=4000&l2=5000'
curl 'http://localhost/szdc/feed_vlak.php?l1=5000&l2=6000'
curl 'http://localhost/szdc/feed_vlak.php?l1=6000&l2=7000'
curl 'http://localhost/szdc/feed_vlak.php?l1=7000&l2=8000'
curl 'http://localhost/szdc/feed_vlak.php?l1=8000&l2=9000'
curl 'http://localhost/szdc/feed_vlak.php?l1=9000&l2=10000'
curl 'http://localhost/szdc/feed_vlak.php?l1=10000&l2=13000'
curl 'http://localhost/szdc/feed_vlak.php?l1=13000&l2=16000'
curl 'http://localhost/szdc/feed_vlak.php?l1=16000&l2=20000'
curl 'http://localhost/szdc/feed_vlak.php?l1=20000&l2=25000'
curl 'http://localhost/szdc/feed_vlak.php?l1=25000&l2=30000'
curl 'http://localhost/szdc/feed_vlak.php?l1=30000&l2=99000'
curl 'http://localhost/szdc/feed_vlak.php?l1=99000&l2=999999'
curl http://localhost/szdc/feed_close.php

zip trains *.txt

exit;

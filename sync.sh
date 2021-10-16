#!/bin/bash

wget -m -N ftp://ftp.cisjr.cz/draha/celostatni/szdc/2021/

cd ftp.cisjr.cz/draha/celostatni/szdc/2021/
find *.zip -newer /Applications/MAMP/htdocs/szdc/timestamp -exec 7z x {} \;
cd /Applications/MAMP/htdocs/szdc
touch timestamp

exit;

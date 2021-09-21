#!/bin/bash

wget -m -N ftp://ftp.cisjr.cz/draha/celostatni/szdc/2018/

cd ftp.cisjr.cz/draha/celostatni/szdc/2018/
find *.zip -newer /home/zirland/git/szdc/timestamp -exec 7z x {} \;
cd /home/zirland/git/szdc
touch timestamp

exit;

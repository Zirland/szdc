#!/bin/bash
CURR_YEAR=`date +"%Y"`
CURR_MON=`date +"%Y-%m"`

cd ftp.cisjr.cz/draha/celostatni/szdc/$CURR_YEAR/$CURR_MON
touch timestamp

cd ../..
cd 2022/$CURR_MON
touch timestamp

exit;

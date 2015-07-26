#!/usr/bin/env bash
#Dumps bob's WHMCS dev database for using in the test bootstrap
mysqldump --user=root --password=root --compact whmcs_v6.release > tests/files/whmcs_v6.sql
echo 'Done.'
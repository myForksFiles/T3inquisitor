#!/usr/bin/env bash
DATETIME=$(date +"%Y-%m-%d_%H%M%S")
SCRIPTPATH=$( dirname -- "$( readlink -f -- "$0"; )"; )
LOG=$SCRIPTPATH"/../../../var/log/"$DATETIME".composer_unused_scanner.txt"
CMD="$SCRIPTPATH/../../../vendor/bin/unused_scanner $SCRIPTPATH/../Classes/Utility/InsolitaUnusedScannerConfig.php"
#echo "$CMD $SCRIPTPATH/../Classes/Utility/InsolitaUnusedScannerConfig.php"   > /dev/null 2>&1
echo " >> $DATETIME

 "$CMD"

  "
exec $CMD

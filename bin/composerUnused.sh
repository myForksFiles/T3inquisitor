#!/usr/bin/env bash
DATETIME=$(date +"%Y-%m-%d_%H%M%S")
SCRIPTPATH=$( dirname -- "$( readlink -f -- "$0"; )"; )
LOG=$SCRIPTPATH"/../../../var/log/"$DATETIME".composer_unused.txt"
CMD="$SCRIPTPATH/../../../vendor/bin/composer-unused"
echo " >> $DATETIME

 "$CMD"

  "
exec $CMD
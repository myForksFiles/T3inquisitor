#!/usr/bin/env bash
#!/usr/bin/env bash
DATETIME=$(date +"%Y-%m-%d_%H%M%S")
SCRIPTPATH=$( dirname -- "$( readlink -f -- "$0"; )"; )
#url -s https://api.github.com/repos/fabpot/local-php-security-checker/releases/latest | grep -E
#      "browser_download_url(.+)linux_amd64" | cut -d : -f 2,3 | tr -d \" | wget -O
#      /var/www/html/var/t3i/bin/local-php-security-checker -qi -
LOG=$SCRIPTPATH"/../../../var/log/"$DATETIME".local-php-security-checker.txt"
CMD="$SCRIPTPATH/../../../vendor/t3i/bin/security-checker --format=markdown $SCRIPTPATH/../Classes/Utility/InsolitaUnusedScannerConfig.php"
#echo "$CMD $SCRIPTPATH/../Classes/Utility/InsolitaUnusedScannerConfig.php"   > /dev/null 2>&1
echo " >> $DATETIME

 "$CMD"

  "
exec $CMD

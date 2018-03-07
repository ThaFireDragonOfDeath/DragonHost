#!/bin/bash
#By Voldracarno Draconor (2017-2018)

runfile="/tmp/dh-cronscript-run"
crontime_jobmanager=60

if [ -f $runfile ]
    then
        rm $runfile
fi

counter_jobmanager=$crontime_jobmanager
while [ ! -f $runfile ]
do
    /bin/sleep 1
    ((counter_jobmanager--))
    if [ "$counter_jobmanager" -eq 0 ]
        then
            counter_jobmanager=$crontime_jobmanager
            php -c /srv/dragonhost/config/php/cli/php.ini -f /srv/dragonhost/framework/jobmanager_main.php
    fi
done

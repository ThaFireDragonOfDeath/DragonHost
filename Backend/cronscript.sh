#!/bin/bash
#By Voldracarno Draconor (2017-2018)

runfile="/tmp/dragonhost/cronscript-run"
crontime_jobmanager = 60

if [ -f $runfile ]
    then
        rm $runfile
fi

counter_jobmanager = $crontime_jobmanager
while [ ! -f $runfile ]
do
    /bin/sleep 1
    $counter_jobmanager--
    if [ $counter_jobmanager -eq 0 ]
        then
            $counter_jobmanager = $crontime_jobmanager
            #php script here
    fi
done

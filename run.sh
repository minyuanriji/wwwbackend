#!/bin/bash

basepath=$(cd `dirname $0`; pwd)
phpexe="/www/server/php/73/bin/php";

chmod a+x "$basepath/yii"


#command1="add-queue-task/execute"
#command2="user-relationship-link/maintant-job"
#command3="commission/maintant-job"
#command4="efps-transfer/maintant-job"
#command5="efps-pay-query/maintant-job"

commands=($command1 $command5)


for command in ${commands[@]}
do
        command="$phpexe $basepath/yii $command"

        ps -ef | grep "`echo $command`"|awk '{print $2}'|xargs kill -9

        result=$(ps -ef | grep "`echo $command`" | grep -v "grep")

        if [ ! -n "$result" ]
        then
          echo "Starting the process."
          str=$(nohup $command >/dev/null &)
          echo -e "\033[32mOk.\033[0m"
        else
          echo "The process has been started."
        fi

done


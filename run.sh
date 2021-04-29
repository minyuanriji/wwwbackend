#!/bin/bash

basepath=$(cd `dirname $0`; pwd)
chmod a+x "$basepath/yii"

commands[0]="/www/server/php/73/bin/php $basepath/yii add-queue-task/execute"
commands[1]="/www/server/php/73/bin/php $basepath/yii user-relationship-link/maintant-job"
commands[1]="/www/server/php/73/bin/php $basepath/yii commission/maintant-job"


for command in ${commands}
do

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
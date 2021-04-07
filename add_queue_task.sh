#!/bin/bash

basepath=$(cd `dirname $0`; pwd)
chmod a+x "$basepath/yii"

command="/www/server/php/73/bin/php $basepath/yii add-queue-task/execute"

result=$(ps -ef | grep "`echo $command`" | grep -v "grep")

if [ ! -n "$result" ]
then
  echo "Starting the process."
  str=$(nohup $command >/dev/null &)
  echo -e "\033[32mOk.\033[0m"
else
  echo "The process has been started."
fi
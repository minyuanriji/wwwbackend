@echo off

cd %~dp0
set PROJECT_PATH=%~dp0

::==================================================================
::队列监听程序
::start yii queue/listen -v

::==================================================================
::队列守候程序
::start yii add-queue-task/execute

::==================================================================
::用户关系链守候程序
start yii user-relationship-link/maintant-job

::==================================================================
::分佣守候程序
::start yii commission/maintant-job

::==================================================================
::cd shell
::start entry wdsp -s start
::start entry wtask -s start
::start entry wtimer -s start
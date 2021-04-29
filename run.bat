@echo off

cd %~dp0
set PROJECT_PATH=%~dp0
start yii queue/listen -v
cd shell
start entry wdsp -s start
start entry wtask -s start
start entry wtimer -s start
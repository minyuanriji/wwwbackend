@echo off

cd %~dp0
set PROJECT_PATH=%~dp0
start yii queue/listen -v
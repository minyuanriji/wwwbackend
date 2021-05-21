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
::start yii user-relationship-link/maintant-job

::==================================================================
::分佣守候程序
::start yii commission/maintant-job

::==================================================================
::易票联提现转账守护程序
::start yii efps-transfer/maintant-job

::==================================================================
::支付结果状态守护程序
::start yii efps-pay-query/maintant-job

::==================================================================
::订单自动结束守护程序
start yii order-auto-sale/maintant-job

::==================================================================
::cd shell
::start entry wdsp -s start
::start entry wtask -s start
::start entry wtimer -s start
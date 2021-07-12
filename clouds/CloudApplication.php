<?php
/**
 * @link:http://www.######.com/
 * @copyright: Copyright (c) #### ########
 * 云应用
 * Author: Mr.Lin
 * Email: 746027209@qq.com
 * Date: 2021-06-26 16:26
 */
namespace app\clouds;


use app\clouds\base\consts\Code;
use app\clouds\base\errors\CloudException;
use app\clouds\base\helpers\IdentityHelper;
use app\clouds\base\route\Route;
use app\clouds\base\tables\CloudUser;
use app\clouds\base\user\Identity;
use app\clouds\base\user\User;
use yii\web\Response;

class CloudApplication extends \yii\web\Application
{
    public function run()
    {

        \Yii::$app->setComponents([
            "cloudUser" => [
                'class' => 'yii\web\User',
                'identityClass' => 'app\clouds\user\Identity',
                'enableAutoLogin' => true,
            ]
        ]);

        //根据请求的类型返回对应的请求结果
        \Yii::$app->on(self::EVENT_AFTER_REQUEST, function ($event){
            if(\Yii::$app->getRequest()->getIsAjax()){
                \Yii::$app->getResponse()->format = Response::FORMAT_JSON;
            }else{
                \Yii::$app->getResponse()->format = Response::FORMAT_HTML;
            }
        });

        //TODO 测试
        IdentityHelper::login(new Identity(CloudUser::findOne(10012)));

        try {
            AppEngine::run($this, Route::parse());
            return parent::run();
        }catch (CloudException $e){
            $response = \Yii::$app->getResponse();
            if(\Yii::$app->getRequest()->getIsAjax()){
                $response->format = Response::FORMAT_JSON;
                $response->data['code']    = Code::ERROR;
                $response->data['message'] = $e->getMessage();
            }else{
                $response->format = Response::FORMAT_HTML;
                $response->content = $e->getMessage() . " " . $e->getCode();
            }
            $response->send();
            return $response->exitStatus;
        }
    }
}
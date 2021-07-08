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


use app\clouds\apps\AppEngine;
use app\clouds\consts\Code;
use app\clouds\errors\BaseException;
use app\clouds\errors\CloudException;
use app\clouds\errors\NotFound404;
use app\clouds\errors\RequestException;
use app\clouds\route\Route;
use app\clouds\user\User;
use yii\base\UnknownClassException;
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

        \Yii::$app->on(self::EVENT_AFTER_REQUEST, function ($event){
            if(\Yii::$app->getRequest()->getIsAjax()){
                \Yii::$app->getResponse()->format = Response::FORMAT_JSON;
            }else{
                \Yii::$app->getResponse()->format = Response::FORMAT_HTML;
            }
        });

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
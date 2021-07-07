<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-05-28
 * Time: 10:09
 */

namespace app\plugins;


use app\core\ApiCode;
use app\models\Mall;

class ApiController extends \app\controllers\api\ApiController
{

    public function init()
    {
        parent::init();
        $id = \Yii::$app->getSessionJxMallId();
        if (!$id) {
            \Yii::$app->response->data = [
                'code' => ApiCode::CODE_MALL_NOT_EXIST,
                'msg' => '商城不存在！',
            ];
            return false;
        }

        $mall = Mall::find()->where(['id' => $id, 'is_delete' => 0])->with('option')->one();
        if (!$mall) {
            \Yii::$app->response->data = [
                'code' => ApiCode::CODE_MALL_NOT_EXIST,
                'msg' => '商城不存在！',
            ];
            return false;

        }
        if ($mall->is_delete !== 0 || $mall->is_recycle !== 0) {
            \Yii::$app->response->data = [
                'code' => ApiCode::CODE_MALL_NOT_EXIST,
                'msg' => '商城不存在！',
            ];
            return false;
        }
        \Yii::$app->mall = $mall;
    }
}
<?php


namespace app\controllers\api\filters;

use app\core\ApiCode;
use app\models\User;
use yii\base\ActionFilter;

class BindMobileFilter extends ActionFilter{

    /**
     * @param \yii\base\Action $action
     * @return bool
     */
    public function beforeAction($action) {
        if (\Yii::$app->user->isGuest) {
            return true;
        }

        $user = User::findOne(\Yii::$app->user->id);

        if (!$user) {
            return true;
        }

        if(empty($user->mobile)){
            \Yii::$app->response->data = [
                'code' => ApiCode::CODE_BIND_MOBILE,
                'msg' => '请先绑定手机',
            ];
            return false;
        }
        return true;
    }
}
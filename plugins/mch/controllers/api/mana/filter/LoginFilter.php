<?php

namespace app\plugins\mch\controllers\api\mana\filter;

use app\core\ApiCode;
use app\models\Store;
use app\plugins\mch\controllers\api\mana\MchAdminController;
use app\plugins\mch\models\Mch;
use app\plugins\mch\models\MchAdminUser;
use yii\base\ActionFilter;

class LoginFilter extends ActionFilter
{
    public $ignore;
    public $only;

    /**
     * @param \yii\base\Action $action
     * @return bool
     */
    public function beforeAction($action){

        $route = \Yii::$app->requestedRoute;
        if (is_array($this->ignore) && in_array($route, $this->ignore)) {
            return true;
        }

        $mchToken = \Yii::$app->request->headers['mch-access-token'];
        $isLogin = false;
        if($mchToken){
            $adminUser = MchAdminUser::find()->with(["mch", "store"])->where([
                "access_token" => $mchToken
            ])->asArray()->one();
            if($adminUser && $adminUser['token_expired_at'] > time()){
                $isLogin = true;
            }
        }
        if (!$isLogin) {
            \Yii::$app->response->data = [
                'code' => ApiCode::CODE_MCH_NOT_LOGIN,
                'msg' => '请先登录。1',
            ];
            return false;
        }

        MchAdminController::$adminUser = $adminUser;

        return true;
    }
}

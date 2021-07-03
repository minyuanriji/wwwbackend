<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 登录过滤器
 * Author: zal
 * Date: 2020-04-17
 * Time: 20:01
 */

namespace app\controllers\api\filters;

use app\core\ApiCode;
use app\models\User;
use yii\base\ActionFilter;

class LoginFilter extends ActionFilter
{
    public $ignore;
    public $only;

    /**
     * @param \yii\base\Action $action
     * @return bool
     */
    public function beforeAction($action)
    {
        $id = $action->id;
        if (is_array($this->ignore) && in_array($id, $this->ignore)) {
            return true;
        }
        if (is_array($this->only) && !in_array($id, $this->only)) {
            return true;
        }
        if (!\Yii::$app->user->isGuest) {
            return true;
        }


       /* $headers = \Yii::$app->request->headers;

        if (isset($headers["x-access-token"])) {
            $accessToken = $headers["x-access-token"];
        } else {
            $accessToken = isset($headers['accessToken']) ? $headers['accessToken'] : "";
        }

        if (empty($accessToken)) {
            return $this;
        }

        $user = User::findIdentityByAccessToken($accessToken);
        if ($user) {
            //已经登录了，不再登录

            \Yii::$app->user->login($user);
            return true;
        }*/
        \Yii::$app->response->data = [
            'code' => ApiCode::CODE_NOT_LOGIN,
            'msg' => '请先登录。',
        ];

        return false;


    }
}

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

class CheckParentFilter extends ActionFilter
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
        
        if (\Yii::$app->user->isGuest) {
            return true;
        }

        $user = User::findOne(['id' => \Yii::$app->user->id]);

        if (!$user) {
            return true;
        }

        if(!$user->parent_id && !$user->is_boss){
            \Yii::$app->response->data = [
                'code' => ApiCode::CODE_BIND_PARENT,
                'msg' => '请先绑定推荐人',
            ];
            return false;
        }
        
        return true;
    }
}

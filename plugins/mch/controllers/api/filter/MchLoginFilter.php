<?php

namespace app\plugins\mch\controllers\api\filter;

use app\core\ApiCode;
use yii\base\ActionFilter;

class MchLoginFilter extends ActionFilter
{
    public $ignore;
    public $only;

    /**
     * @param \yii\base\Action $action
     * @return bool
     */
    public function beforeAction($action)
    {
        $route = \Yii::$app->requestedRoute;
        if (is_array($this->ignore) && in_array($route, $this->ignore)) {
            return true;
        }

        $mchToken = \Yii::$app->request->headers['Mch-Access-Token'];
        if (!$mchToken) {
            \Yii::$app->response->data = [
                'code' => ApiCode::CODE_MCH_NOT_LOGIN,
                'msg' => '请先登录。1',
            ];
            return false;
        }

        if (\Yii::$app->request->isPost) {
            $mchId = \Yii::$app->request->post('mch_id');
        } else {
            $mchId = \Yii::$app->request->get('mch_id');
        }
        $newMchToken = \Yii::$app->cache->get('MCHTOKENID' . $mchId);

        if (!$newMchToken || $newMchToken != $mchToken) {
            \Yii::$app->response->data = [
                'code' => ApiCode::CODE_MCH_NOT_LOGIN,
                'msg' => '请先登录。2',
            ];
            return false;
        }

        return true;
    }
}

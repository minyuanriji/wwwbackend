<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 基础控制器
 * Author: zal
 * Date: 2020-04-13
 * Time: 16:50
 */

namespace app\plugins;

use app\controllers\mall\MallController;
use app\core\ApiCode;
use app\models\Mall;
use Yii;

class Controller extends MallController
{
    public $enableCsrfValidation = false;
    public $layout = '/mall';
    public function init()
    {
        parent::init();
        if (\Yii::$app->request->isAjax) {
            \Yii::$app->response->headers
                ->set('Cache-Control', 'no-store, no-cache, must-revalidate')
                ->set('Expires', 'Thu, 19 Nov 1981 08:00:00 GMT')
                ->set('Pragma', 'no-cache');
        }
        $this->loadMall();
    }

    /**
     * @return $this
     */
    private function loadMall()
    {
        $id = \Yii::$app->getSessionJxMallId();
        $url = \Yii::$app->urlManager->createUrl('admin/index/index');
        if (!$id) {
            return $this->redirect($url)->send();
        }
        $mall = Mall::find()->where(['id' => $id, 'is_delete' => 0])->with('option')->one();
        if (!$mall) {
            return $this->redirect($url)->send();
        }
        if ($mall->is_delete !== 0 || $mall->is_recycle !== 0) {
            return $this->redirect($url)->send();
        }

        $newOptions = [];
        foreach ($mall['option'] as $item) {
            $newOptions[$item['key']] = $item['value'];
        }
        $mall->options = (object)$newOptions;

        \Yii::$app->mall = $mall;

        \Yii::$app->setMall($mall);


        return $this;
    }

    public function render($view, $params = [])
    {
        if (mb_stripos($view, '@') !== 0 && mb_stripos($view, '/') !== 0) {
            $view = '@app/plugins/' . $this->module->id . '/views/' . mb_strtolower($this->id) . '/' . $view;
        }
        return parent::render($view, $params);
    }

     /**
      * 请求成功统消息格式化处理
      * @Author bing
      * @DateTime 2020-10-27 15:43:16
      * @copyright: Copyright (c) 2020 广东七件事集团
      * @param string $msg
      * @param array $data
      * @param [type] $code
      * @return void
      */
    public function success($msg='success',$data=[],$code=ApiCode::CODE_SUCCESS){
        return $this->asJson(array(
            'code' => $code,
            'msg' => $msg,
            'data' => $data
        ));
    }

    /**
     * 请求失败消息格式化处理
     * @Author bing
     * @DateTime 2020-10-27 15:44:05
     * @copyright: Copyright (c) 2020 广东七件事集团
     * @param string $msg
     * @param array $data
     * @param [type] $code
     * @return void
     */
    public function error($msg='failed',$data=[],$code=ApiCode::CODE_FAIL){
        return $this->asJson(array(
            'code' => $code,
            'msg' => $msg,
            'data' => $data
        ));
    }
}

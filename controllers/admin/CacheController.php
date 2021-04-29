<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: zal
 * Date: 2020-04-11
 * Time: 14:12
 */

namespace app\controllers\admin;


use app\core\ApiCode;
use app\component\jobs\CleanCacheJob;

class CacheController extends AdminController
{

    public $layout='admin';

    public function actionClean()
    {

         $this->layout=\Yii::$app->request->get('_layout');

        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $form = \Yii::$app->request->post();
                if (isset($form['data']) && $form['data'] == 'true') {
                    \Yii::$app->cache->flush();
                    \Yii::$app->queue->delay(0)->push(new CleanCacheJob());
                }
                if (isset($form['file']) && $form['file'] == 'true') {
                    $path = \Yii::$app->basePath . '/web/temp';
                    if (file_exists($path)) {
                        remove_dir($path);
                    }
                }
                if (isset($form['update']) && $form['update'] == 'true') {
                    $path = \Yii::$app->runtimePath . '/plugin-package';
                    if (file_exists($path)) {
                        remove_dir($path);
                    }
                    $path = \Yii::$app->runtimePath . '/update-package';
                    if (file_exists($path)) {
                        remove_dir($path);
                    }
                }
                return [
                    'code' => ApiCode::CODE_SUCCESS,
                    'msg' => '清理成功。',
                    'data' => $form,
                ];
            }
        } else {
            return $this->render('clean');
        }
    }
}

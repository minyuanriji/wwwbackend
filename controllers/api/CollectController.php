<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-05-12
 * Time: 14:15
 */

namespace app\controllers\api;


use app\controllers\api\filters\LoginFilter;
use app\forms\api\collect\CollectForm;

/**
 * Class CollectController
 * @package app\controllers\api
 * @Notes相关收藏放在这个控制器中
 */
class CollectController extends ApiController
{

    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'login' => [
                'class' => LoginFilter::class,
            ],
        ]);
    }

    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-05-12
     * @Time: 15:20
     * @Note:添加
     * @return \yii\web\Response
     */
    public function actionAdd()
    {
        $form = new CollectForm();
        $form->attributes = $this->requestData;
        $headers = \Yii::$app->request->headers;
        if(isset($headers["x-stands-mall-id"]) && !empty($headers["x-stands-mall-id"]) && $headers["x-stands-mall-id"] != 5){
            $form->mall_id = $headers["x-stands-mall-id"];
        }else{
            $form->mall_id = \Yii::$app->mall->id;
        }
        return $this->asJson($form->save());
    }

    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-05-12
     * @Time: 15:21
     * @Note:收藏列表
     * @return \yii\web\Response
     */

    public function actionList()
    {
        $form = new CollectForm();
        $form->attributes = $this->requestData;
        return $this->asJson($form->getList());
    }

    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-05-12
     * @Time: 15:26
     * @Note:删除
     * @return \yii\web\Response
     */
    public function actionDelete()
    {
        $form = new CollectForm();
        $form->attributes = $this->requestData;
        return $this->asJson($form->delete());
    }


}
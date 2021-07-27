<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 名片
 * Author: zal
 * Date: 2020-07-06
 * Time: 15:47
 */


namespace app\plugins\business_card\controllers\mall;

use app\plugins\business_card\forms\api\BusinessCardForm;
use app\plugins\Controller;


class BusinessCardController extends Controller
{

    /**
     * @Author: 广东七件事 zal
     * Date: 2020-05-10
     * Time: 21:35
     * @Note: 名片列表
     * @return string|\yii\web\Response
     * @throws \Exception
     */
    public function actionIndex()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $form = new \app\plugins\business_card\forms\mall\BusinessCardForm();
                $form->attributes = \Yii::$app->request->post();
                return $this->asJson($form->getList());
            } else {
                $form = new \app\plugins\business_card\forms\mall\BusinessCardForm();
                $form->attributes = \Yii::$app->request->get();
                return $this->asJson($form->getList());
            }
        }
        return $this->render('index');
    }

    /**
     * 查看
     * @return string|\yii\web\Response
     */
    public function actionDetail(){
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $form = new BusinessCardForm();
                $form->attributes = \Yii::$app->request->post();
                return $this->asJson($form->getList());
            } else {
                $form = new BusinessCardForm();
                $form->attributes = \Yii::$app->request->get();
                return $this->asJson($form->my(2));
            }
        }
        return $this->render('detail');
    }


    public function actionDelete(){
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $form = new \app\plugins\business_card\forms\mall\BusinessCardForm();
                $form->attributes = \Yii::$app->request->post();
                return $this->asJson($form->delete());
            } else {
                $form = new BusinessCardForm();
                $form->attributes = \Yii::$app->request->get();
                return $this->asJson($form->my(2));
            }
        }
        return $this->render('detail');
    }
}
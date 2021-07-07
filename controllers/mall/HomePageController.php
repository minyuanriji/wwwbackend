<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 店铺管理-店铺设计-首页布局
 * Author: zal
 * Date: 2020-04-13
 * Time: 15:50
 */

namespace app\controllers\mall;

use app\core\ApiCode;
use app\forms\mall\home_page\GoodsForm;
use app\forms\mall\shop\DiyForm;
use app\forms\mall\shop\HomePageEditForm;
use app\forms\mall\shop\HomePageForm;
use app\helpers\SerializeHelper;

class HomePageController extends ShopManagerController
{

    /**
     * 设置
     * @return array|string|\yii\web\Response
     */
    public function actionSetting()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isGet) {
                $form = new HomePageForm();
                $res = $form->getDetail();

                return $this->asJson($res);
            } else {
                $form = new HomePageEditForm();
                $form->data = \Yii::$app->request->post('list');
                return $form->save();
            }
        } else {
            return $this->render('setting');
        }
    }

    public function actionOption()
    {
        $form = new HomePageForm();
        $res = $form->getOption();
        return $this->asJson($res);
    }


    public function actionDiy()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new DiyForm();
            if (\Yii::$app->request->isPost) {
                $form->attributes = \Yii::$app->request->post();
                return $this->asJson($form->savePageData());
            }
            if (\Yii::$app->request->isGet) {
                return $this->asJson(['code' => ApiCode::CODE_SUCCESS, 'msg' => '', 'data' => ['allComponents' => $form->allComponents(),'components'=>$form->getPageData()]]);
            }
        } else {
            return $this->render('diy');
        }

    }


    /**
     * 获取商品
     * @return \yii\web\Response
     */
    public function actionGetGoods()
    {
        $form = new GoodsForm();
        $form->attributes = \Yii::$app->request->get();
        $form->setSign(\Yii::$app->request->get('sign'));
        return $this->asJson($form->search());
    }

}

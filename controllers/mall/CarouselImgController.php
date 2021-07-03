<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 店铺管理-店铺设计-轮播图
 * Author: zal
 * Date: 2020-04-11
 * Time: 15:50
 */

namespace app\controllers\mall;


use app\forms\mall\shop\BannerForm;
use app\forms\mall\shop\MallBannerForm;

class CarouselImgController extends ShopManagerController
{
    /**
     * 店铺设计-轮播图首页
     * @return string|\yii\web\Response
     */
    public function actionMallBanner()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new MallBannerForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->getList());
        } else {
            return $this->render('mall-banner');
        }
    }

    /**
     * 修改商城关联轮播图
     * @return array|string
     * @throws \yii\db\Exception
     */
    public function actionMallBannerEdit()
    {
        if (\Yii::$app->request->isPost) {
            $form = new MallBannerForm();
            $form->attributes = \Yii::$app->request->post();
            return $form->save();
        }
    }

    /**
     * 加载轮播图数据
     * @return string|\yii\web\Response
     */
    public function actionBanner()
    {
        $form = new BannerForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->getList());
    }

    /**
     * 轮播图操作
     * @return string|\yii\web\Response
     */
    public function actionBannerEdit()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new BannerForm();
            if (\Yii::$app->request->isPost) {
                $form->attributes = \Yii::$app->request->post();
                return $form->save();
            } else {
                $form->attributes = \Yii::$app->request->get();
                return $this->asJson($form->getDetail());
            }
        } else {
            return $this->render('edit');
        }
    }

    /**
     * 删除
     * @return \yii\web\Response
     */
    public function actionBannerDestroy()
    {
        if ($ids = \Yii::$app->request->post('ids')) {
            $form = new BannerForm();
            $form->ids = $ids;
            return $this->asJson($form->destroy());
        }
    }
}
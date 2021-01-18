<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-04-16
 * Time: 14:11
 */

namespace app\controllers\mall;




use app\forms\mall\service\ServiceEditForm;
use app\forms\mall\service\ServiceForm;

class ServiceController extends MallController
{

    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-04-16
     * @Time: 14:25
     * @Note:服务列表
     * @return string|\yii\web\Response
     */
    public function actionIndex()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
            } else {
                $form = new ServiceForm();
                $form->attributes = \Yii::$app->request->get();
                $list = $form->search();

                return $this->asJson($list);
            }
        } else {
            return $this->render('index');
        }
    }

    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-04-16
     * @Time: 14:23
     * @Note:编辑服务
     * @return string|\yii\web\Response
     */
    public function actionEdit()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $form = new ServiceEditForm();
                $form->attributes = \Yii::$app->request->post('form');
                $res = $form->save();

                return $this->asJson($res);
            } else {
                $form = new ServiceForm();
                $form->attributes = \Yii::$app->request->get();
                $detail = $form->getDetail();

                return $this->asJson($detail);
            }
        } else {
            return $this->render('edit');
        }
    }

    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-04-16
     * @Time: 14:23
     * @Note:删除服务
     * @return \yii\web\Response
     */
    public function actionDelete()
    {
        $form = new ServiceForm();
        $form->attributes = \Yii::$app->request->post();
        $res = $form->delete();

        return $this->asJson($res);
    }

    /**
     * 默认开关
     * @return \yii\web\Response
     */
    public function actionSwitchChange()
    {
        $form = new ServiceForm();
        $form->attributes = \Yii::$app->request->post('form');
        $res = $form->switchChange();

        return $this->asJson($res);
    }

    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-04-16
     * @Time: 14:24
     * @Note:获取所有可选服务
     * @return \yii\web\Response
     */
    public function actionOptions()
    {
        $form = new ServiceForm();
        $res = $form->getOptionList();

        return $this->asJson($res);
    }
}
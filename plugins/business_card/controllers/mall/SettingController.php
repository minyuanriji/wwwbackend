<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-05-08
 * Time: 16:09
 */

namespace app\plugins\business_card\controllers\mall;


use app\plugins\Controller;
use app\plugins\business_card\forms\mall\BusinessCardSettingForm;


class SettingController extends Controller
{
    public function actionIndex()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $form = new BusinessCardSettingForm();
                $form->attributes = \Yii::$app->request->post();
                $form->company_img = \Yii::$app->request->post("company_img");
                return $this->asJson($form->save());
            } else {
                $form = new BusinessCardSettingForm();
                return $this->asJson($form->search());
            }
        }
        return $this->render('index');
    }

    public function actionTag(){
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $form = new BusinessCardSettingForm();
                $form->tag_list = \Yii::$app->request->post();
                return $this->asJson($form->addTag());
            } elseif (\Yii::$app->request->isGet) {
                $form = new BusinessCardSettingForm();
                $form->attributes = \Yii::$app->request->get();
                return $this->asJson($form->searchTag());
            }
        }
        return $this->render('tag');
    }

    public function actionPoster()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isGet) {
                $form = new BusinessCardSettingForm();
                $res = $form->getDetail();
                return $this->asJson($res);
            } else {
                $form = new BusinessCardSettingForm();
                $form->data = \Yii::$app->request->post('form');
                return $form->savePoster();
            }
        }
        return $this->render('poster');
    }
}

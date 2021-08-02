<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 部门
 * Author: zal
 * Date: 2020-05-08
 * Time: 16:10
 */

namespace app\plugins\business_card\controllers\mall;

use app\core\ApiCode;
use app\plugins\business_card\forms\mall\BusinessCardDepartmentEditForm;
use app\plugins\business_card\forms\mall\BusinessCardDepartmentForm;
use app\plugins\Controller;

class BusinessCardDepartmentController extends Controller
{

    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-05-12
     * @Time: 9:52
     * @Note:等级列表
     * @return string|\yii\web\Response
     */
    public function actionIndex()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {

            } elseif (\Yii::$app->request->isGet) {
                $form = new BusinessCardDepartmentForm();
                $form->attributes = \Yii::$app->request->get();
                return $this->asJson($form->getList());
            }
        }
        return $this->render('index');
    }


    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-05-12
     * @Time: 9:23
     * @Note:编辑
     * @return string|\yii\web\Response
     */
    public function actionEdit()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $form = new BusinessCardDepartmentEditForm();
                $form->attributes = \Yii::$app->request->post('form');
                $form->id = \Yii::$app->request->post('id');
                return $this->asJson($form->save());
            } elseif (\Yii::$app->request->isGet) {
                $form = new BusinessCardDepartmentEditForm();
                $form->id = \Yii::$app->request->get("id");
                return $this->asJson($form->getDetail());
            }
        }
        return $this->render('edit');

    }

    /**
     * @Author: 广东七件事 zal
     * @Date: 2020-07-09
     * @Time: 14:52
     * @Note:删除
     * @return \yii\web\Response
     */
    public function actionDelete()
    {
        if (\Yii::$app->request->isAjax) {

            if (\Yii::$app->request->isPost) {
                $form = new BusinessCardDepartmentEditForm();
                $form->attributes = \Yii::$app->request->post();
                return $this->asJson($form->save());
            }
        }
    }

}
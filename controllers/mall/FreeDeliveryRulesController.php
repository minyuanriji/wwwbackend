<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-04-16
 * Time: 17:19
 */

namespace app\controllers\mall;


use app\core\ApiCode;
use app\forms\mall\free_delivery_rules\EditForm;
use app\forms\mall\free_delivery_rules\ListForm;
use app\models\FreeDeliveryRules;
use app\services\FreeDeliveryRules\FreeDeliveryRulesService;
use app\services\ReturnData;

class FreeDeliveryRulesController extends MallController
{
    use ReturnData;

    public function actionIndex()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new ListForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->search());
        }
        return $this->render('index');
    }

    public function actionEdit($id = null)
    {
        if (\Yii::$app->request->isAjax) {
            $model = FreeDeliveryRules::findOne([
                'id' => $id,
                'mall_id' => \Yii::$app->mall->id,
                'mch_id' => \Yii::$app->admin->identity->mch_id,
                'is_delete' => 0
            ]);
            if (!$model) {
                $model = new FreeDeliveryRules();
                $model->mall_id = \Yii::$app->mall->id;
                $model->mch_id = \Yii::$app->admin->identity->mch_id;
            } else {
                $model->detail = \Yii::$app->serializer->decode($model->detail);
                $model->price = floatval($model->price);
            }
            if (\Yii::$app->request->isPost) {
                $form = new EditForm();
                $form->attributes = \Yii::$app->request->post('form');
                $form->model = $model;
                return $this->asJson($form->save());
            } else {
                return $this->asJson([
                    'code' => ApiCode::CODE_SUCCESS,
                    'msg' => 'success',
                    'data' => [
                        'model' => $model
                    ]
                ]);
            }
        }
        return $this->render('edit');
    }

//    poster function actionDestoy()
//    {
//        if (\Yii::$app->request->isAjax) {
//            $id = \Yii::$app->request->get('id');
//            return CommonFreeDeliveryRules::deleteItem($id);
//        }
//    }

    public function actionDestroy()
    {
        if (\Yii::$app->request->isAjax) {
            $params                    = \Yii::$app->request->get();
            $params['mall_id']         = \Yii::$app->mall->id;
            $FreeDeliveryRulesService = new FreeDeliveryRulesService();

            $return = $FreeDeliveryRulesService->destroy($params);

            if (!$return) {
                $error = $FreeDeliveryRulesService->getServiceError();
                return $this->asJson($this->returnApiResultData($error['code'], $error['msg']));
            }

            return $this->asJson(
                $this->returnApiResultData(0, "删除成功")
            );
        }
    }
}
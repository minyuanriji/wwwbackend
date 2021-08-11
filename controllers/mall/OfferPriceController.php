<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-04-16
 * Time: 17:01
 */

namespace app\controllers\mall;


use app\core\ApiCode;
use app\logic\OptionLogic;
use app\forms\mall\offer\EditForm;
use app\models\Option;

class OfferPriceController extends MallController
{
    public function actionIndex()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $form = new EditForm();
                $form->attributes = \Yii::$app->request->post('form');
                return $this->asJson($form->save());
            } else {
                $res = OptionLogic::get(
                    Option::NAME_OFFER_PRICE,
                    \Yii::$app->mall->id,
                    Option::GROUP_APP,
                    [
                        'is_enable' => 0,
                        'total_price' => 0
                    ],
                    \Yii::$app->admin->identity->mch_id
                );
                $res['is_enable'] = $res ? intval($res['is_enable']) : 0;
                $res['total_price'] = $res ? floatval($res['total_price']) : 0;
                $res['is_total_price'] = $res && isset($res['is_total_price']) ? floatval($res['is_total_price']) : 1;
                return $this->asJson([
                    'code' => ApiCode::CODE_SUCCESS,
                    'msg' => '',
                    'data' => [
                        'model' => $res
                    ]
                ]);
            }
        }
        return $this->render('index');
    }
}
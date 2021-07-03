<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-04-17
 * Time: 15:43
 */

namespace app\controllers\mall;


use app\core\ApiCode;
use app\forms\mall\area_limit\EditForm;
use app\logic\OptionLogic;
use app\models\Option;


/**
 * Class TerritorialLimitationController
 * @package app\controllers\mall
 * @Notes 区域限制
 */
class AreaLimitController extends MallController
{

    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-04-17
     * @Time: 15:44
     * @Note:
     * @return mixed
     */
    public function actionIndex()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $form = new EditForm();
                $form->attributes = \Yii::$app->request->post('form');
                return $this->asJson($form->save());
            } else {
                $model = OptionLogic::get(
                    Option::NAME_TERRITORIAL_LIMITATION,
                    \Yii::$app->mall->id,
                    Option::GROUP_APP,
                    [
                        'is_enable' => 0
                    ],
                    \Yii::$app->admin->identity->mch_id
                );
                $model['is_enable'] = intval($model['is_enable']);
                return $this->asJson([
                    'code' => ApiCode::CODE_SUCCESS,
                    'msg' => 'success',
                    'data' => [
                        'model' => $model
                    ]
                ]);
            }
        }
        return $this->render('index');
    }

}
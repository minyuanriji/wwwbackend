<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-04-16
 * Time: 10:49
 */


namespace app\controllers\mall;

use app\controllers\mall\MallController;
use app\core\ApiCode;
use app\forms\common\postage\CommonPostageRules;
use app\forms\common\postage\PostageRulesEditForm;
use app\forms\mall\postage\PostageRulesListForm;
use app\models\PostageRules;
use app\controllers\business\PostageRules as PostageRulesBus;

class PostageRulesController extends MallController
{
    // 运费列表
    public function actionIndex()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new PostageRulesListForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->search());
        }
        return $this->render('index');
    }

    // 运费模板编辑
    public function actionEdit($id = null)
    {
        if (\Yii::$app->request->isAjax) {
            $model = PostageRules::findOne([
                'is_delete' => 0,
                'mall_id' => \Yii::$app->mall->id,
                'mch_id' => \Yii::$app->admin->identity->mch_id,
                'id' => $id
            ]);
            if (!$model) {
                $model = new PostageRules();
                $model->mall_id = \Yii::$app->mall->id;
                $model->mch_id = \Yii::$app->admin->identity->mch_id;
            } else {
                $model->detail = $model->decodeDetail();
            }
            if (\Yii::$app->request->isPost) {
                $form = new PostageRulesEditForm();
                $form->attributes = \Yii::$app->request->post('form');
                $form->model = $model;
                return $this->asJson($form->save());
            } else {
                return $this->asJson([
                    'code' => ApiCode::CODE_SUCCESS,
                    'msg' => 'success',
                    'data' => [
                        'model' => $model,
                    ]
                ]);
            }
        }
        return $this->render('edit');
    }

    // 设置运费默认
    public function actionStatus($id = null)
    {
        if (\Yii::$app->request->isAjax) {
            return $this->asJson(CommonPostageRules::setStatus($id));
        } else {
            throw new \Exception('请求错误');
        }
    }

    public function actionDelete($id = null)
    {
        if (\Yii::$app->request->isAjax) {
            return $this->asJson(CommonPostageRules::deleteItem($id));
        } else {
            throw new \Exception('请求错误');
        }
    }

    public function actionAllList()
    {
        $form = new PostageRulesListForm();
        return $this->asJson($form->allList());
    }

    public function actionExpressList(){
        $data = \Yii::$app->request->get();
        $data = (new PostageRulesBus()) -> getExpressData($data['id']);
        $result = [
            'code' => 0,
            'data' => [
                'list' => $data
            ]
        ];
        return $this -> asJson($result);
    }






}

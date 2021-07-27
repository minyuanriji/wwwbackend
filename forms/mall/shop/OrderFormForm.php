<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 下单表单
 * Author: zal
 * Date: 2020-04-14
 * Time: 10:16
 */

namespace app\forms\mall\shop;

use app\core\ApiCode;
use app\forms\common\FormCommon;
use app\models\BaseModel;
use app\models\Form;

class OrderFormForm extends BaseModel
{
    public $keyword;
    public $page;
    public $id;

    public function rules()
    {
        return [
            [['keyword'], 'trim'],
            [['keyword'], 'string'],
            [['page'], 'integer']
        ];
    }

    public function getDetail()
    {
        $default = $this->getDefault();
        try {
            $commonForm = FormCommon::getInstance();
            $model = $commonForm->getDetail($this->id);
            $model = [
                'id' => $model->id,
                'name' => $model->name,
                'value' => $model->value,
                'status' => $model->status,
            ];
            if ($model['value']) {
                foreach ($model['value'] as &$item) {
                    $item['is_required'] = (int)$item['is_required'];
                }
                unset($item);
            } else {
                $model = $default;
            }
        } catch (\Exception $exception) {
            $model = $default;
        }
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'detail' => $model,
            ]
        ];
    }

    public function getDefault()
    {
        return [
            'name' => '',
            'status' => 0,
            'value' => [],
        ];
    }

    public function getList()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }
        $list = Form::find()->where([
            'is_delete' => 0, 'mall_id' => \Yii::$app->mall->id, 'mch_id' => 0,
        ])->keyword($this->keyword, ['like', 'name', $this->keyword])
            ->page($pagination, 20, $this->page)
            ->select('id,status,name,is_default,is_delete')->all();
        if ($this->page == 1 && (!$list || empty($list))) {
            $commonForm = FormCommon::getInstance();
            $list = $commonForm->setOldData();
        }

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '',
            'data' => [
                'list' => $list,
                'pagination' => $pagination,
            ]
        ];
    }

    public function getAllList()
    {
        $list = Form::find()->where([
            'is_delete' => 0, 'mall_id' => \Yii::$app->mall->id, 'mch_id' => 0, 'status' => 1
        ])->select('id,name')->all();
        if ($this->page == 1 && (!$list || empty($list))) {
            $commonForm = FormCommon::getInstance();
            $list = $commonForm->setOldData();
        }
        array_unshift($list, [
            'id' => 0,
            'name' => '默认表单'
        ]);

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '',
            'data' => [
                'list' => $list,
            ]
        ];
    }
}

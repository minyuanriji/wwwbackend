<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 版权新增或编辑表单
 * Author: zal
 * Date: 2020-04-14
 * Time: 10:16
 */

namespace app\forms\mall\common;

use app\core\ApiCode;
use app\models\BaseModel;

class RechargeForm extends BaseModel
{
    public $id;
    public $keyword;
    public $mall_id;
    public $name;
    public $pay_price;
    public $give_money;
    public $is_delete;
    public $give_score;

    public function rules()
    {
        return [
            [['name'], 'string', 'max' => 255],
            [['id', 'mall_id', 'is_delete', 'give_score'], 'integer'],
            [['pay_price', 'give_money'], 'number'],
            [['is_delete', 'give_money', 'give_score'], 'default', 'value' => 0],
            [['keyword'], 'string'],
            [['keyword'], 'default', 'value' => 0],
        ];
    }


    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'mall_id' => 'mall ID',
            'name' => '名称',
            'pay_price' => '支付价格',
            'give_money' => '赠送价格',
            'is_delete' => '删除',
            'give_score' => '赠送积分',
        ];
    }

    //GET
    public function getList()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        };

        $query = Recharge::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0,
        ]);

        $list = $query->keyword($this->keyword, ['like', 'name', $this->keyword])
            ->orderBy('id DESC,created_at DESC')
            ->asArray()
            ->all();

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'list' => $list,
            ]
        ];
    }

    //DELETE
    public function destroy()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }
        
        $model = Recharge::findOne([
            'id' => $this->id,
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0
        ]);
        if (!$model) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => '数据不存在或已删除',
            ];
        }
        $model->is_delete = 1;
        $model->deleted_at = date('Y-m-d H:i:s');
        $model->save();
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '删除成功'
        ];
    }

    //DELETE
    public function getDetail()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }
        $list = Recharge::findOne([
            'mall_id' => \Yii::$app->mall->id,
            'id' => $this->id
        ]);

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'list' => (object)$list,
            ]
        ];
    }

    //SAVE
    public function save()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        $model = Recharge::findOne([
            'mall_id' => \Yii::$app->mall->id,
            'id' => $this->id
        ]);
        if (!$model) {
            $model = new Recharge();
        }

        $model->attributes = $this->attributes;
        $model->mall_id = \Yii::$app->mall->id;
        if ($model->save()) {
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功'
            ];
        } else {
            return $this->responseErrorInfo($model);
        }
    }
}

<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-04-17
 * Time: 16:22
 */

namespace app\forms\mall\printer;


use app\core\ApiCode;
use app\core\BasePagination;
use app\forms\mall\shop\StoreForm;
use app\models\BaseModel;
use app\models\Printer;
use app\models\PrinterSetting;
use yii\base\DynamicModel;
use yii\helpers\ArrayHelper;

class PrinterSettingEditForm extends BaseModel
{
    public $printer_id;
    public $is_attr;
    public $block_id; //打印模板ID
    public $type;
    public $id;
    public $status;
    public $store_id;

    public function rules()
    {
        return [
            [['printer_id', 'is_attr', 'type', 'status', 'store_id'], 'required'],
            [['printer_id', 'is_attr', 'block_id', 'id', 'status', 'store_id'], 'integer'],
            [['type'], 'trim'],
            [['block_id'], 'default', 'value' => 0]
        ];
    }


    public function attributeLabels()
    {
        return [
            'printer_id' => '打印机ID',
            'is_attr' => '是否打印规格 ',
            'type' => '打印方式',
            'block_id' => '打印模板ID',
            'status' => '是否启用',
            'store_id' => '门店'
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        $model = PrinterSetting::findOne([
            'mall_id' => \Yii::$app->mall->id,
            'mch_id' => \Yii::$app->admin->identity->mch_id,
            'id' => $this->id,
        ]);
        if (!$model) {
            $model = new PrinterSetting();
        }
        $model->attributes = $this->attributes;
        $type = $this->type;

        if ($this->validateType($type['order'], $type['pay'], $type['confirm'])) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => '数据不合法'
            ];
        }

        $model->type = json_encode($type);
        $model->mall_id = \Yii::$app->mall->id;
        $model->mch_id = \Yii::$app->admin->identity->mch_id;
        if ($model->save()) {
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功'
            ];
        } else {
            return $this->responseErrorInfo($model);
        }
    }

    private function validateType($order, $pay, $confirm)
    {
        $model = DynamicModel::validateData(compact('order', 'pay', 'confirm'), [
            [['order', 'pay', 'confirm'], 'required'],
        ]);
        return $model->hasErrors();
    }
}
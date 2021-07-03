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

class OrderFormUpdate extends BaseModel
{
    public $id;
    public $status;
    public $is_default;
    public $is_delete;

    public function rules()
    {
        return [
            [['id'], 'required'],
            [['id', 'status', 'is_default', 'is_delete'], 'integer'],
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }
        try {
            $commonForm = FormCommon::getInstance();
            $model = $commonForm->getDetail($this->id);
            $model->value = json_encode($model->value, JSON_UNESCAPED_UNICODE);
            $model->attributes = $this->attributes;
            if (!$model->save()) {
                return $this->responseErrorInfo($model);
            }
            if ($model->is_default == 1) {
                Form::updateAll(['is_default' => 0], [
                    'AND',
                    ['mall_id' => \Yii::$app->mall->id],
                    ['!=', 'id', $model->id]
                ]);
            }
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '修改成功'
            ];
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $exception->getMessage(),
            ];
        }
    }
}

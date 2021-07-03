<?php

namespace app\plugins\mch\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\mch\models\MchCash;

class CashApplyForm extends BaseModel
{
    public $mall;

    public $id;
    public $status;
    public $content;

    public function rules()
    {
        return [
            [['id', 'status',], 'required'],
            [['id', 'status',], 'integer'],
            ['status', 'in', 'range' => [1, 2, 3]],
            ['content', 'trim'],
            ['content', 'string'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'content' => '备注'
        ];
    }

    public function remark()
    {
        if (!isset($this->content)) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => '请填写备注'
            ];
        }

        $mchCash = MchCash::findOne([
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0,
            'id' => $this->id,
        ]);
        if (!$mchCash) {
            throw new \Exception('记录不存在');
        }

        $mchCash->content = $this->content;
        if ($mchCash->save()) {
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功'
            ];
        } else {
            return $this->responseErrorMsg($mchCash);
        }
    }

    public function save()
    {
        try {
            $form = new CashEditForm();
            $form->attributes = $this->attributes;
            if ($this->status == 1) {
                $form->transfer_type = 0;
                $res = $form->save();
            } elseif ($this->status == 2) {
                $form->transfer_type = 1;
                $res = $form->transfer();
            } elseif ($this->status == 3) {
                $res = $form->save();
            } else {
                throw new \Exception('错误的状态');
            }

            return $res;
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $exception->getMessage()
            ];
        }
    }
}

<?php

namespace app\plugins\mch\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\mch\models\MchMallSetting;

class MchMallSettingEditForm extends BaseModel
{
    public $id;
    public $is_distribution;
    public $mch_id;

    public function rules()
    {
        return [
            [['id', 'is_distribution', 'mch_id'], 'integer'],
            [['is_distribution', 'mch_id'], 'required'],
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        try {
            if ($this->id) {
                $model = MchMallSetting::findOne($this->id);

                if (!$model) {
                    throw new \Exception('商户设置异常');
                }
            } else {
                $model = new MchMallSetting();
                $model->mall_id = \Yii::$app->mall->id;
                $model->mch_id = $this->mch_id;
            }

            $model->is_distribution = $this->is_distribution;
            $res = $model->save();

            if (!$res) {
                throw new \Exception($this->responseErrorMsg($model));
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功',
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $e->getMessage(),
                'error' => [
                    'line' => $e->getLine()
                ]
            ];
        }
    }
}

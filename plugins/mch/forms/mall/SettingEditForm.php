<?php

namespace app\plugins\mch\forms\mall;

use app\core\ApiCode;
use app\forms\common\CommonOption;
use app\logic\OptionLogic;
use app\models\BaseModel;
use app\models\Option;
use app\plugins\mch\models\Mch;

class SettingEditForm extends BaseModel
{
    public $form;

    public function rules()
    {
        return [
            [['form'], 'required']
        ];
    }

    public function attributeLabels()
    {
        return [];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->responseErrorMsg();
        }

        try {
            if (!isset($this->form['form_data'])) {
                $this->form['form_data'] = [];
            }
            /*$option = CommonOption::set(
                Option::NAME_MCH_MALL_SETTING,
                $this->form,
                \Yii::$app->mall->id,
                Option::GROUP_APP
            );*/

            $res = OptionLogic::set(
                Option::NAME_MCH_MALL_SETTING,
                $this->form,
                \Yii::$app->mall->id,
                Option::GROUP_APP
            );

            if (!$res) {
                throw new \Exception('保存失败');
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功'
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

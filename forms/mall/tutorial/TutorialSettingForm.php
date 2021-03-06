<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: xay
 */
namespace app\forms\mall\tutorial;

use app\core\ApiCode;
use app\logic\OptionLogic;
use app\models\BaseModel;
use app\models\Option;

class TutorialSettingForm extends BaseModel
{
    public $status;
    public $url;

    public function rules()
    {
        return [
            [['status'], 'required'],
            [['status'], 'integer'],
            [['url'], 'default', 'value' => ''],
        ];
    }


    public function attributeLabels()
    {
        return [
            'status' => '开启教程',
            'url' => '背景图片',
        ];
    }

    public function get()
    {
        $default = [
            'status' => '0',
            'url' => '',
        ];
        $setting = OptionLogic::get(Option::NAME_TUTORIAL, 0, Option::GROUP_ADMIN, $default);

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => $setting,
        ];
    }

    public function set()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        $data = [
            'status' => $this->status,
            'url' => $this->url,
        ];

        $option = OptionLogic::set(Option::NAME_TUTORIAL, $data, 0, Option::GROUP_ADMIN);
        if ($option) {
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功'
            ];
        } else {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => '保存失败'
            ];
        }
    }
}

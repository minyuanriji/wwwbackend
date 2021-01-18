<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 注册协议
 * Author: zal
 * Date: 2020-11-02
 * Time: 15:16
 */

namespace app\forms\mall\member;

use app\core\ApiCode;
use app\logic\AppConfigLogic;
use app\logic\OptionLogic;
use app\models\BaseModel;
use app\models\Option;

class RegisterAgreeForm extends BaseModel
{
    public $data;
    public $mall_id;

    public function rules()
    {
        return [
            [['data'], 'safe'],
            [['mall_id'], 'integer']
        ];
    }

    public function getDetail()
    {
        $option = AppConfigLogic::getRegisterAgree();
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'detail' => $option,
            ]
        ];
    }

    public function getDefault()
    {
        return [
            'content' => '',
        ];
    }

    public function save()
    {
        try {
            if (!$this->data) {
                throw new \Exception('请输入form参数数据');
            }
            $mallId = $this->mall_id ? $this->mall_id : \Yii::$app->mall->id;
            $res = OptionLogic::set(Option::NAME_REGISTER_AGREE, $this->data, $mallId, Option::GROUP_APP);

            if (!$res) {
                throw new \Exception('保存失败');
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功',
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $e->getMessage(),
            ];
        }
    }
}

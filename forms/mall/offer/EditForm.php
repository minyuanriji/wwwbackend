<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-04-16
 * Time: 17:03
 */

namespace app\forms\mall\offer;


use app\core\ApiCode;
use app\logic\OptionLogic;
use app\models\BaseModel;
use app\models\Option;

class EditForm extends BaseModel
{
    public $is_enable;
    public $total_price;
    public $detail;
    public $is_total_price;

    public function rules()
    {
        return [
            [['is_enable', 'is_total_price'], 'integer'],
            ['total_price', 'number', 'min' => 0],
            ['detail', function ($attr, $param) {
                foreach ($this->$attr as $item) {
                    if ($item['total_price'] < 0) {
                        $this->addError('起送金额不能小于0');
                    }
                }
            }]
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        $data = [
            'is_enable' => $this->is_enable,
            'total_price' => $this->total_price,
            'is_total_price' => $this->is_total_price,
            'detail' => $this->detail ?: []
        ];

        $res = OptionLogic::set(
            Option::NAME_OFFER_PRICE,
            $data,
            \Yii::$app->mall->id,
            Option::GROUP_APP,
            \Yii::$app->admin->identity->mch_id
        );
        if ($res) {
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
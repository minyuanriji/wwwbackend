<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-04-17
 * Time: 14:48
 */

namespace app\forms\mall\express;
use app\core\ApiCode;

use app\logic\OptionLogic;
use app\models\BaseModel;
use app\models\Option;
use app\validators\MobileValidator;

class SenderOptionForm extends BaseModel
{
    const DEFAULT_OPTION = [
        'company' => '',
        'name' => '',
        'tel' => '',
        'mobile' => '',
        'zip_code' => '',
        'province' => '',
        'city' => '',
        'district' => '',
        'address' => '',
    ];

    public $company;
    public $name;
    public $tel;
    public $mobile;
    public $zip_code;
    public $province;
    public $city;
    public $district;
    public $address;

    public function rules()
    {
        return [
            [['name', 'province', 'city', 'district', 'address'], 'required'],
            [['company', 'name', 'tel', 'mobile', 'zip_code', 'province', 'city', 'district', 'address'], 'string', 'max' => 255],
            [['mobile', 'zip_code'], 'default', 'value' => ''],
            [['mobile'], MobileValidator::className()],
        ];
    }

    public function attributeLabels()
    {
        return [
            'company' => '发件人公司',
            'name' => '发件人名称',
            'tel' => '发件人电话',
            'mobile' => '发件人手机',
            'zip_code' => '发件人邮政编码',
            'province' => '发件人省',
            'city' => '发件人市',
            'district' => '发件人区',
            'address' => '发件人详细地址',
        ];
    }

    public function getList()
    {
        return OptionLogic::get(
            Option::NAME_DELIVERY_DEFAULT_SENDER,
            \Yii::$app->mall->id,
            Option::GROUP_APP,
            senderOptionForm::DEFAULT_OPTION,
            \Yii::$app->admin->identity->mch_id
        );
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        };

        if (!$this->tel && !$this->mobile) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => '联系方式不能为空'
            ];
        }
        $data = [
            'company' => $this->company,
            'name' => $this->name,
            'tel' => $this->tel,
            'mobile' => $this->mobile,
            'zip_code' => $this->zip_code,
            'province' => $this->province,
            'city' => $this->city,
            'district' => $this->district,
            'address' => $this->address,
        ];
        OptionLogic::set(
            Option::NAME_DELIVERY_DEFAULT_SENDER,
            $data,
            \Yii::$app->mall->id,
            Option::GROUP_APP,
            \Yii::$app->admin->identity->mch_id
        );
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '保存成功',
        ];
    }
}

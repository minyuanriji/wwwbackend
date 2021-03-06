<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-04-16
 * Time: 18:29
 */


namespace app\forms\mall\express;

use app\core\ApiCode;
use app\models\BaseModel;
use app\models\Delivery;
use app\validators\MobileValidator;


class ExpressEditForm extends BaseModel
{
    public $id;
    public $express_id;
    public $customer_account;
    public $customer_pwd;
    public $month_code;
    public $outlets_name;
    public $outlets_code;
    public $company;
    public $name;
    public $tel;
    public $mobile;
    public $zip_code;
    public $province;
    public $city;
    public $district;
    public $address;
    public $template_size;
    public $is_sms;
    public $is_goods;

    public function rules()
    {
        return [
            [['name', 'province', 'city', 'district', 'address'], 'required'],
            [['id', 'express_id', 'is_sms', 'is_goods'], 'integer'],
            [['template_size', 'customer_account', 'customer_pwd', 'month_code', 'outlets_name', 'outlets_code',
                'company', 'name', 'tel', 'mobile', 'zip_code', 'province', 'city', 'district',
                'address'], 'string', 'max' => 255],
            [['express_id', 'is_sms', 'is_goods'], 'default', 'value' => 0],
            [['mobile', 'zip_code', 'template_size', 'company', 'customer_account', 'customer_pwd',
                'month_code', 'outlets_name', 'outlets_code'], 'default', 'value' => ''],
            [['mobile'], MobileValidator::className()],
        ];
    }


    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'express_id' => '快递公司id',
            'customer_account' => '电子面单客户账号',
            'customer_pwd' => '电子面单密码',
            'month_code' => '月结编码',
            'outlets_name' => '网点名称',
            'outlets_code' => '网点编码',
            'company' => '发件人公司',
            'name' => '发件人名称',
            'tel' => '发件人电话',
            'mobile' => '发件人手机',
            'zip_code' => '发件人邮政编码',
            'province' => '发件人省份',
            'city' => '发件人市',
            'district' => '发件人区',
            'address' => '发件人详细地址',
            'template_size' => '快递鸟电子面单模板规格',
            'is_sms' => '是否订阅短信',
            'is_goods' => '是否打印商品',
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        $model = Delivery::findOne([
            'mall_id' => \Yii::$app->mall->id,
            'mch_id' => \Yii::$app->admin->identity->mch_id,
            'id' => $this->id,
        ]);
        if (!$model) {
            $model = new Delivery();
        }
        if (!$this->tel && !$this->mobile) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => '发件人电话或手机不能为空'
            ];
        }

        $model->attributes = $this->attributes;
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
}

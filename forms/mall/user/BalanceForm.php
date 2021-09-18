<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 用户余额
 * Author: zal
 * Date: 2020-04-15
 * Time: 16:45
 */

namespace app\forms\mall\user;

use app\core\ApiCode;
use app\models\BaseModel;
use app\models\User;

class BalanceForm extends BaseModel
{
    public $user_id;
    public $type;
    public $price;
    public $pic_url;
    public $remark;

    public function rules()
    {
        return [
            [['price', 'type', 'user_id'], 'required'],
            [['type', 'user_id'], 'integer'],
            [['pic_url', 'remark'], 'string', 'max' => 255],
            [['price'], 'number', 'min' => 0.01, 'max' => 999999999],
        ];
    }

    public function attributeLabels()
    {
        return [
            'user_id' => '用户ID',
            'type' => '类型',
            'price' => '金额',
            'pic_url' => '图片',
            'remark' => '备注'
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        };


        try {
            $admin = \Yii::$app->admin->identity;

            $user = User::find()->alias('u')->where([
                'u.id' => $this->user_id,
                'u.mall_id' => \Yii::$app->mall->id
            ])->one();

            if (!$user) {
                throw new \Exception('用户不存在');
            }

            $custom_desc = [
                'remark' => $this->remark,
                'pic_url' => $this->pic_url,
            ];

            if ($this->type == 1) {
                $desc = "管理员： " . $admin->username . " 后台操作账号："
                    . $user->nickname . " 余额充值：" . $this->price . "元";
                \Yii::$app->currency->setUser($user)->balance->add(
                    (float)$this->price,
                    $desc,
                    json_encode($custom_desc),
                    'admin',
                    $admin->id
                );
            } else {
                $desc = "管理员： " . $admin->username . " 后台操作账号："
                    . $user->nickname . " 余额扣除：" . $this->price . "元";
                \Yii::$app->currency->setUser($user)->balance->sub(
                    (float)$this->price,
                    $desc,
                    json_encode($custom_desc),
                    'admin',
                    $admin->id
                );
            }

            return [
                'code' => 0,
                'msg' => 'success'
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

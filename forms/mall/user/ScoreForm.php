<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 门店
 * Author: zal
 * Date: 2020-04-15
 * Time: 18:45
 */

namespace app\forms\mall\user;

use app\core\ApiCode;
use app\models\BaseModel;
use app\models\User;

class ScoreForm extends BaseModel
{
    public $user_id;
    public $type;
    public $num;
    public $pic_url;
    public $remark;
    public function rules()
    {
        return [
            [['num', 'type', 'user_id'], 'required'],
            [['type', 'user_id'], 'integer'],
            [['pic_url', 'remark'], 'string', 'max' => 255],
            [['num'], 'integer', 'min' => 1, 'max' => 999999999],
        ];
    }
    public function attributeLabels()
    {
        return [
            'user_id' => '用户ID',
            'type' => '类型',
            'num' => '积分',
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
            $user = User::findOne(['id' => $this->user_id, 'mall_id' => \Yii::$app->mall->id]);
            $custom_desc = [
                'remark' => $this->remark,
                'pic_url' => $this->pic_url,
            ];
            if ($this->type == 1) {
                $desc = "管理员： " . $admin->username . " 后台操作账号：" . $user->nickname . " 积分充值：" . $this->num . " 积分";
                \Yii::$app->currency->setUser($user)->score->add((int)$this->num, $desc, json_encode($custom_desc));
            } else {
                $desc = "管理员： " . $admin->username . " 后台操作账号：" . $user->nickname . " 积分扣除：" . $this->num . " 积分";
                \Yii::$app->currency->setUser($user)->score->sub((int)$this->num, $desc, json_encode($custom_desc));
            }
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '处理成功'
            ];
        }catch(\Exception $e) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $e->getMessage(),
            ];
        }
    }
}

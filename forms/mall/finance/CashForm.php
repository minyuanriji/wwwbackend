<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 提现申请
 * Author: zal
 * Date: 2020-04-16
 * Time: 10:45
 */

namespace app\forms\mall\finance;

use app\core\ApiCode;

use app\helpers\SerializeHelper;
use app\logic\OptionLogic;
use app\models\BaseModel;
use app\models\Cash;

use app\models\Mall;
use app\models\Option;
use app\models\RemitLog;
use app\models\User;
use app\models\UserInfo;
use yii\base\Exception;

/**
 * @property Mall $mall
 */
class CashForm extends BaseModel
{
    public $mall;

    public $id;
    public $status;
    public $content;

    public function rules()
    {
        return [
            [['id', 'status'], 'required'],
            [['id', 'status'], 'integer'],
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

    public function save()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }
        
        $cash = Cash::findOne(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0, 'id' => $this->id]);
        if (!$cash) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => '提现记录不存在'
            ];
        }

        if ($cash->status == 2) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => '提现已打款'
            ];
        }

        if ($cash->status == 3) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => '提现已被驳回'
            ];
        }

        if ($this->status <= $cash->status) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => '状态错误, 请刷新重试'
            ];
        }
        

        $t = \Yii::$app->db->beginTransaction();
        try {
            switch ($this->status) {
                case 1:
                    $this->validateCash($cash);
                    break;
                case 2:
                    $this->remit($cash);
                    break;
                case 3:
                    $this->reject($cash);
                    break;
                default:
                    throw new \Exception('错误的提现类型');
            }
            $t->commit();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '处理成功'
            ];
        } catch (\Exception $exception) {
            $t->rollBack();
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $exception->getMessage()
            ];
        }
    }

    /**
     * 申请
     * @param Cash $cash
     * @throws \Exception
     * @return bool
     */
    private function validateCash($cash)
    {
        if (!$cash->content) {
            $content = [];
        } else {
            $content = SerializeHelper::decode($cash->content);
        }
        $cash->status = 1;

        $content['validate_content'] = $this->content ?: '审核通过';
        $cash->content = SerializeHelper::encode($content);
        if (!$cash->save()) {
            throw new \Exception($this->responseErrorMsg($cash));
        }
        return true;
    }

    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-05-30
     * @Time: 11:22
     * @Note:开始打款
     * @param $cash
     * @return bool
     * @throws \Exception
     * @var Cash $cash
     */
    private function remit($cash)
    {
        /**
         * @var Cash $cash
         *
         */
        if ($cash->type === 'auto') {
            /**
             * @var UserInfo $userInfo ;
             */
            $userInfo = UserInfo::find()->where(['user_id' => $cash->user_id, 'is_delete' => 0])->andWhere(['platform' => [User::PLATFORM_WECHAT]])->one();
            if (!$userInfo) {
                throw new \Exception('该用户不存在微信相关信息！');
            }
            $payment = \Yii::$app->wechat->payment;
            $wechatPaySetting = OptionLogic::get(Option::NAME_PAYMENT, $cash->mall_id, Option::GROUP_APP);
            if (!$wechatPaySetting) {
                throw new \Exception('系统未开启微信支付！');
            }
            if (!$wechatPaySetting['wechat_status']) {
                throw new \Exception('系统未开启微信支付！');
            }
            try {
                $res = $payment->transfer->toBalance([
                    'partner_trade_no' => $wechatPaySetting['wechat_mch_id'].'x'.$cash->id, // 商户订单号，需保持唯一性(只能是字母或者数字，不能包含有符号)
                    'openid' => $userInfo->openid,
                    'check_name' => 'NO_CHECK', // NO_CHECK：不校验真实姓名, FORCE_CHECK：强校验真实姓名
                    're_user_name' => '', // 如果 check_name 设置为FORCE_CHECK，则必填用户真实姓名
                    'amount' => $cash->fact_price * 100, // 企业付款金额，单位为分
                    'desc' => '收益提现', // 企业付款操作说明信息。必填
                ]);
            } catch (Exception $e) {
                throw new \Exception($e->getMessage());
            }
            if ($res['result_code'] == 'FAIL') {
                throw new \Exception($res['err_code_des']);
            }
        }
        // 保存提现信息
        if ($cash->content) {
            $content = SerializeHelper::decode($cash->content);
        } else {
            $content = [];
        }
        $cash->status = 2;
        $content['remit_at'] = date('Y-m-d H:i:s', time());
        $content['remit_content'] = $this->content;
        $cash->content = \Yii::$app->serializer->encode($content);
        if (!$cash->save()) {
            throw new \Exception($this->responseErrorMsg($cash));
        }
        $log = new RemitLog();
        $log->user_id = $cash->user_id;
        $log->mall_id = $cash->mall_id;
        $log->content = '用户收益汇款';
        $log->operator_id = \Yii::$app->admin->identity->id;
        $log->price = $cash->fact_price;
        $log->type=$cash->type;
        $log->save();
        return true;
    }

    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-05-30
     * @Time: 11:22
     * @Note:拒绝
     * @param $cash
     * @return bool
     * @throws \Exception
     */
    private function reject($cash)
    {
        if (!$this->content) {
            throw new \Exception('请填写备注');
        }
        if (!$cash->content) {
            $content = [];
        } else {
            $content = SerializeHelper::decode($cash->content);
        }
        $cash->status = 3;
        $content['reject_content'] = $this->content;
        $cash->content = SerializeHelper::encode($content);
        if (!$cash->save()) {
            throw new \Exception($this->responseErrorMsg($cash));
        }
        return true;
    }
}

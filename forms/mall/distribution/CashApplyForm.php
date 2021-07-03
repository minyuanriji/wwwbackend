<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 提现申请
 * Author: zal
 * Date: 2020-04-16
 * Time: 10:45
 */

namespace app\forms\mall\distribution;

use app\core\ApiCode;
use app\forms\common\distribution\DistributionCashCommon;
use app\forms\common\distribution\DistributionLevelCommon;
use app\forms\common\template\tplmsg\WithdrawErrorTemplate;
use app\forms\common\template\tplmsg\WithdrawSuccessTemplate;
use app\models\BaseModel;
use app\models\DistributionCash;
use app\models\Mall;

/**
 * @property Mall $mall
 */
class CashApplyForm extends BaseModel
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

        $this->mall = \Yii::$app->mall;
        $distributionCash = DistributionCash::findOne(['mall_id' => $this->mall->id, 'is_delete' => 0, 'id' => $this->id]);

        if (!$distributionCash) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => '提现记录不存在'
            ];
        }

        if ($distributionCash->status == 2) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => '提现已打款'
            ];
        }

        if ($distributionCash->status == 3) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => '提现已被驳回'
            ];
        }

        if ($this->status <= $distributionCash->status) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => '状态错误, 请刷新重试'
            ];
        }

        $t = \Yii::$app->db->beginTransaction();
        try {
            switch ($this->status) {
                case 1:
                    $this->apply($distributionCash);
                    break;
                case 2:
                    $this->remit($distributionCash);
                    $commonDistributionLevel = DistributionLevelCommon::getInstance();
                    // 打款触发分销商等级提升
                    $commonDistributionLevel->userId = $distributionCash->user_id;
                    $commonDistributionLevel->levelDistribution(DistributionLevelCommon::TOTAL_CASH);
                    break;
                case 3:
                    $this->reject($distributionCash);
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
     * @param DistributionCash $distributionCash
     * @throws \Exception
     * @return bool
     */
    private function apply($distributionCash)
    {
        $extra = \Yii::$app->serializer->decode($distributionCash->extra);
        $distributionCash->status = 1;
        $extra['apply_at'] = date('Y-m-d H:i:s', time());
        $extra['apply_content'] = $this->content ?: '申请通过';
        $distributionCash->extra = \Yii::$app->serializer->encode($extra);
        if (!$distributionCash->save()) {
            throw new \Exception($this->responseErrorMsg($distributionCash));
        }
        return true;
    }

    /**
     * 保存提现信息
     * @param DistributionCash $distributionCash
     * @throws \Exception
     * @return bool
     */
    private function remit($distributionCash)
    {
        // 保存提现信息
        $extra = \Yii::$app->serializer->decode($distributionCash->extra);
        $distributionCash->status = 2;
        $extra['remittance_at'] = date('Y-m-d H:i:s', time());
        $extra['remittance_content'] = $this->content;
        $distributionCash->extra = \Yii::$app->serializer->encode($extra);
        if (!$distributionCash->save()) {
            throw new \Exception($this->responseErrorMsg($distributionCash));
        }

        // 提现打款
        $form = new DistributionCashCommon();
        $form->shareCash = $distributionCash;
        $remit = $form->remit();

        // 发送模板消息
        try {
            $serviceCharge = $distributionCash->price * $distributionCash->service_fee_rate / 100;
            $tplMsg = new WithdrawSuccessTemplate([
                'page' => 'pages/share/cash-detail/cash-detail',
                'user' => $distributionCash->user,
                'remark' => $this->content ? $this->content : '提现成功',
                'price' => $distributionCash->price,
                'serviceChange' => price_format($serviceCharge),
                'type' => $distributionCash->getTypeText2($distributionCash->type)
            ]);
            $tplMsg->send();
        } catch (\Exception $exception) {
            \Yii::error($exception);
        }

        return true;
    }

    /**
     * 拒绝
     * @param DistributionCash $distributionCash
     * @throws \Exception
     * @return bool
     */
    private function reject($distributionCash)
    {
        if (!$this->content) {
            throw new \Exception('请填写备注');
        }
        // 保存提现信息
        $extra = \Yii::$app->serializer->decode($distributionCash->extra);
        $distributionCash->status = 3;
        $extra['reject_at'] = date('Y-m-d H:i:s', time());
        $extra['reject_content'] = $this->content;
        $distributionCash->extra = \Yii::$app->serializer->encode($extra);
        if (!$distributionCash->save()) {
            throw new \Exception($this->responseErrorMsg($distributionCash));
        }

        // 拒绝打款
        $form = new DistributionCashCommon();
        $form->shareCash = $distributionCash;
        $reject = $form->reject();

        // 发送模板消息
        try {
            $tplMsg = new WithdrawErrorTemplate([
                'page' => 'pages/share/cash-detail/cash-detail',
                'user' => $distributionCash->user,
                'remark' => $extra['reject_content'] ? $extra['reject_content'] : '拒绝提现',
                'price' => $distributionCash->price,
            ]);
            $tplMsg->send();
        } catch (\Exception $exception) {
            \Yii::error($exception);
        }
        return true;
    }
}

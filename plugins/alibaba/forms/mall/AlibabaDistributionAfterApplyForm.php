<?php

namespace app\plugins\alibaba\forms\mall;

use app\core\ApiCode;
use app\forms\efps\EfpsMchCashTransfer;
use app\models\BaseModel;
use app\plugins\alibaba\models\AlibabaDistributionOrderDetail;
use app\plugins\alibaba\models\AlibabaDistributionOrderRefund;

class AlibabaDistributionAfterApplyForm extends BaseModel
{

    public $id;
    public $act;
    public $content;
    public $refund_express;

    public function rules()
    {
        return [
            [['id', 'act'], 'required'],
            [['id', 'refund_express'], 'integer'],
            [['content'], 'safe']
        ];
    }

    public function save()
    {

        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        $t = \Yii::$app->getDb()->beginTransaction();
        try {
            $result = [];
            $OrderDetail = AlibabaDistributionOrderDetail::findOne($this->id);
            if (!$OrderDetail || $OrderDetail->is_delete) {
                throw new \Exception("售后订单不存在");
            }

            if ($this->act == "agree") { //确认
                if ($OrderDetail->refund_status == 'agree') {
                    throw new \Exception("不能重复确认操作！");
                }
                $refundArray = $OrderDetail->agreeRefund(false, $this->content, $this->refund_express);
                $result = $refundArray;
            } elseif ($this->act == "refused") { //拒绝
                if ($OrderDetail->refund_status == 'finished')
                    throw new \Exception("无法拒绝操作");

                $OrderDetail->refund_status = 'refused';
                $OrderDetail->remark = $this->content;
                if (!$OrderDetail->save())
                    throw new \Exception(json_encode($OrderDetail->getErrors()));

            }
            $t->commit();
            return $this->returnApiResultData(ApiCode::CODE_SUCCESS, '操作成功', $result);
        } catch (\Exception $e) {
            $t->rollBack();
            return $this->returnApiResultData(ApiCode::CODE_FAIL, $e->getMessage());
        }
    }
}
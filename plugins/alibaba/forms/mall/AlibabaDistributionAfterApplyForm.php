<?php

namespace app\plugins\alibaba\forms\mall;

use app\core\ApiCode;
use app\forms\efps\EfpsMchCashTransfer;
use app\models\BaseModel;
use app\plugins\alibaba\models\AlibabaDistributionOrderDetail;

class AlibabaDistributionAfterApplyForm extends BaseModel
{

    public $id;
    public $act;
    public $content;

    public function rules()
    {
        return [
            [['id', 'act'], 'required'],
            [['id'], 'integer'],
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
            $OrderDetail = AlibabaDistributionOrderDetail::findOne($this->id);
            if (!$OrderDetail || $OrderDetail->is_delete) {
                throw new \Exception("售后订单不存在");
            }

            if ($this->act == "agree") { //确认
                if ($OrderDetail->refund_status == 'agree') {
                    throw new \Exception("不能重复确认操作！");
                }
                $OrderDetail && $OrderDetail->agreeRefund(false, $this->content);

            } elseif ($this->act == "refused") { //拒绝
                if ($OrderDetail->refund_status == 'finished')
                    throw new \Exception("无法拒绝操作");

                $OrderDetail->refund_status = 'refused';
                if (!$OrderDetail->save())
                    throw new \Exception(json_encode($OrderDetail->getErrors()));

            } elseif ($this->act == "paid") {

            }

            $t->commit();

            return $this->returnApiResultData(ApiCode::CODE_SUCCESS, '操作成功');
        } catch (\Exception $e) {
            $t->rollBack();
            return $this->returnApiResultData(ApiCode::CODE_FAIL, $e->getMessage());
        }
    }
}
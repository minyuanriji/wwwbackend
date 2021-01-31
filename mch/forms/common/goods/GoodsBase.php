<?php
namespace app\mch\forms\common\goods;

use app\core\ApiCode;
use app\models\Goods;
use app\plugins\mch\models\MchGoods;

class GoodsBase extends \app\forms\common\goods\GoodsBase {

    public function batchUpdateStatus(){
        if (!$this->validate()) {
            return $this->responseErrorMsg();
        }

        $isGoodsAudit = 1;
        try {
            /*  $mchPlugin = \Yii::$app->plugin->getPlugin('mch');
              if ($mchPlugin) {
                  $mchSetting = (new MchSettingForm())->search();
                  $isGoodsAudit = $mchSetting['is_goods_audit'];
              }*/
        } catch (\Exception $exception) {
        }

        //商品需要审核
        if ($isGoodsAudit) {
            $res = $this->setMchGoodsApplyStatus();
        } else {
            if ($this->is_all) {
                $where = [
                    'mall_id'   => \Yii::$app->mall->id,
                    'mch_id'    => \Yii::$app->mchAdmin->identity->mchModel->id,
                    'sign'      => $this->plugin_sign,
                    'is_delete' => 0,
                ];
            } else {
                $where = [
                    'mall_id' => \Yii::$app->mall->id,
                    'mch_id'  => \Yii::$app->mchAdmin->identity->mchModel->id,
                    'sign'    => $this->plugin_sign,
                    'id'      => $this->batch_ids,
                ];
            }

            $res = Goods::updateAll(['status' => $this->status], $where);

            // 如果是多商户 则需更新商户商品状态
            $this->updateMchGoodsStatus();
        }

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '更新成功',
            'data' => [
                'num' => $res
            ]
        ];
    }

    private function setMchGoodsApplyStatus(){
        if ($this->is_all) {
            $where = [
                'mall_id'   => \Yii::$app->mall->id,
                'mch_id'    => \Yii::$app->mchAdmin->identity->mchModel->id,
                'is_delete' => 0,
                'status'    => [0, 3]
            ];
        } else {
            $where = [
                'mall_id'   => \Yii::$app->mall->id,
                'mch_id'    => \Yii::$app->mchAdmin->identity->mchModel->id,
                'goods_id'  => $this->batch_ids,
                'status'    => [0, 3]
            ];
        }

        $res = MchGoods::updateAll([
            'status' => 1,
            'remark' => '申请上架'
        ], $where);

        // 有更新再发送模板消息
        if ($res) {
            //$this->sendMpTplMsg();
        }

        return $res;
    }

}

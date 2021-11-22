<?php

namespace app\plugins\mch\forms\api\mana;

use app\core\ApiCode;
use app\models\BaseModel;
use app\models\clerk\ClerkData;
use app\models\Goods;
use app\models\Store;
use app\models\User;
use app\plugins\giftpacks\models\Giftpacks;
use app\plugins\giftpacks\models\GiftpacksItem;
use app\plugins\giftpacks\models\GiftpacksOrder;
use app\plugins\giftpacks\models\GiftpacksOrderItem;
use app\plugins\mch\controllers\api\mana\MchAdminController;
use app\plugins\mch\models\Mch;
use app\plugins\mch\models\MchCheckoutOrder;

class MchManaOrderBillListForm extends BaseModel {

    public $page;
    public $status; //状态：1(已支付), 0(未支付)
    public $mch_id;
    public $keyword;

    public function rules(){
        return [
            [['status'], 'required'],
            [['page', 'status', 'mch_id'], 'integer'],
            [['keyword'], 'string']
        ];
    }

    public function getList(){

        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        try {
            $query = MchCheckoutOrder::find()->alias('mco')
                ->innerJoin(['u' => User::tableName()], 'mco.pay_user_id=u.id');

            $query->andWhere(["mco.id" => $this->mch_id ?: MchAdminController::$adminUser['mch_id']])
                ->andWhere(['and', ['!=', 'u.mobile', ''], ['IS NOT', 'u.mobile', NULL], ['u.is_delete' => 0]]);

            $query->andWhere(['mco.is_pay' => $this->status]);

            //关键词搜索
            if(!empty($this->keyword)){
                $query->andWhere(['mco.order_no' => $this->keyword]);
            }

            $selects = [
                "mco.id", "mco.mch_id", "mco.order_no", "mco.order_price", "mco.pay_price", "mco.is_pay", "mco.pay_user_id", "mco.pay_at", "mco.score_deduction_price", "mco.integral_deduction_price", "mco.created_at", "mco.integral_fee_rate", "mco.store_id", "mco.pay_type", "u.nickname", "u.avatar_url", "u.mobile",
            ];
            $list = $query->orderBy("mco.id DESC")->select($selects)->page($pagination, 10, $this->page)->asArray()->all();
            if($list){
                foreach($list as &$item){
                    $item['created_at'] = date("Y-m-d H:i:s", $item['created_at']);
                    $item['mobile'] = '***' . substr($item['mobile'], -4);
                }
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'list'       => $list,
                    'pagination' => $pagination
                ]
            ];
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }
    }

}
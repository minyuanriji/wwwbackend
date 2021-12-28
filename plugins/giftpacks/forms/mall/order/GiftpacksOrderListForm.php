<?php

namespace app\plugins\giftpacks\forms\mall\order;

use app\core\ApiCode;
use app\models\BaseModel;
use app\models\IntegralLog;
use app\models\User;
use app\plugins\commission\models\CommissionGiftpacksPriceLog;
use app\plugins\giftpacks\models\Giftpacks;
use app\plugins\giftpacks\models\GiftpacksOrder;
use app\plugins\giftpacks\models\GiftpacksOrderItem;
use app\plugins\shopping_voucher\models\ShoppingVoucherLog;
use app\plugins\shopping_voucher\models\ShoppingVoucherSendLog;

class GiftpacksOrderListForm extends BaseModel
{
    public $page;
    public $keyword;
    public $kw_type;
    public $start_time;
    public $end_time;
    public $status;
    public $pack_id;
    public $pack_item_id;

    public function rules()
    {
        return [
            [['page'], 'integer'],
            [['keyword', 'status', 'kw_type'], 'string'],
            [['pack_id', 'pack_item_id', 'start_time', 'end_time'], 'safe']
        ];
    }

    public function getList()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        try {

            $query = GiftpacksOrder::find()->alias('go')->where(["go.is_delete" => 0])
                    ->leftJoin(["svl" => ShoppingVoucherLog::tableName()], "svl.source_type = 'from_giftpacks_order' AND svl.source_id = go.id")
                    ->leftJoin(["g" => Giftpacks::tableName()], "go.pack_id = g.id")
                    ->leftJoin(["u" => User::tableName()], "go.user_id = u.id");

            if (!empty($this->keyword) && !empty($this->kw_type)) {
                switch ($this->kw_type)
                {
                    case 'order_id':
                        $query->andWhere(['go.id' => $this->keyword]);
                        break;
                    case 'order_no':
                        $query->andWhere(["LIKE", "go.order_sn", $this->keyword]);
                        break;
                    case 'gift_name':
                        $query->andWhere(["LIKE", "g.title", $this->keyword]);
                        break;
                    case 'user_id':
                        $query->andWhere(['go.user_id' => $this->keyword]);
                        break;
                    default:
                }
            }

            if ($this->start_time && $this->end_time) {
                $query->andWhere([
                    'and',
                    ['>=', 'go.pay_at', $this->start_time],
                    ['<=', 'go.pay_at', $this->end_time],
                ]);
            }

            if(in_array($this->status, ['wait_send', 'has_send']) && $this->pack_id && $this->pack_item_id){
                //根据大礼包商品是否已发放、未发放来筛选订单
                $query->andWhere([
                    "go.pack_id"    => $this->pack_id,
                    "go.pay_status" => "paid"
                ]);
                $subSql = "select count(*) from {{%plugin_giftpacks_order_item}} where order_id=go.id and pack_item_id='{$this->pack_item_id}'";
                if($this->status == "wait_send"){
                    $query->andWhere("({$subSql})=0");
                }else{
                    $query->andWhere("({$subSql})>0");
                }
            }elseif ($this->status) {
                $query->keyword($this->status == 'paid', ['go.pay_status' => $this->status])
                    ->keyword($this->status == 'refund', ['go.pay_status' => $this->status])
                    ->keyword($this->status == 'refunding', ['go.pay_status' => $this->status])
                    ->keyword($this->status == 'unpaid', ['go.pay_status' => $this->status]);
            }

            $select = ['go.*', "IFNULL(svl.money, 0) as got_shopping_voucher_num", "g.title", "g.cover_pic", "g.descript", "g.price", "g.integral_enable", "g.integral_give_num", "g.score_enable", "g.score_give_settings", "u.nickname"];

            $list = $query->groupBy("go.id")->select($select)->orderBy("go.id DESC")->page($pagination)->asArray()->all();
            if ($list) {
                foreach ($list as &$value) {
                    if ($value['score_give_settings']) {
                        $value['score_give_settings'] = json_decode($value['score_give_settings'], true);
                    }
                    $value['share_profit'] = CommissionGiftpacksPriceLog::find()->alias('cg')
                        ->leftJoin(["u" => User::tableName()], "cg.user_id = u.id")
                        ->where(['order_id' => $value['id']])
                        ->select(['cg.id', 'cg.order_id', 'cg.pack_id', 'cg.user_id', 'cg.price', 'cg.status', 'u.nickname'])
                        ->asArray()
                        ->all();

                    if ($value['integral_enable']) {
                        $value['is_integral'] = IntegralLog::findOne(['source_type' => 'giftpacks_order', 'source_id' => $value['id']]) ? 1 : 0;
                    } else {
                        $value['is_integral'] = 0;
                    }

                   $voucherResult = ShoppingVoucherSendLog::find()->andWhere([
                        'source_type' => 'from_giftpacks_order',
                        'source_id' => $value['id'],
                   ])->one();
                   if ($voucherResult) {
                       $value['voucher_num'] = $voucherResult->money;
                       $value['voucher_status'] = $voucherResult->status;
                   } else {
                       $value['voucher_num'] = 0;
                       $value['voucher_status'] = 'invalid';
                   }
                }
            }

            return $this->returnApiResultData(
                ApiCode::CODE_SUCCESS,
                '',
                [
                    'list' => $list ?: [],
                    'pagination' => $pagination
                ]
            );
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $e->getMessage()
            ];
        }
    }
}
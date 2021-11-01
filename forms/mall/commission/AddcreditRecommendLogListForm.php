<?php

namespace app\forms\mall\commission;

use app\core\ApiCode;
use app\models\BaseModel;
use app\models\Goods;
use app\models\GoodsWarehouse;
use app\models\OrderDetail;
use app\models\Store;
use app\models\User;
use app\plugins\addcredit\models\AddcreditOrder;
use app\plugins\commission\models\CommissionAddcreditPriceLog;
use app\plugins\commission\models\CommissionGoodsPriceLog;
use app\plugins\commission\models\CommissionHotelPriceLog;
use app\plugins\commission\models\CommissionStorePriceLog;
use app\plugins\hotel\models\HotelOrder;
use app\plugins\hotel\models\Hotels;
use app\plugins\mch\models\Mch;
use app\plugins\mch\models\MchCheckoutOrder;

class AddcreditRecommendLogListForm extends BaseModel
{
    public $page;
    public $limit;
    public $start_date;
    public $end_date;
    public $keyword;
    public $keyword_1;
    public $status;

    public function rules()
    {
        return [
            [['page', 'limit', 'status', 'keyword_1'], 'integer'],
            [['keyword', 'start_date', 'end_date'], 'trim'],
        ];
    }

    public function getList()
    {
        if (!$this->validate()) return $this->responseErrorInfo();

        $query = CommissionAddcreditPriceLog::find()->alias('ca')->where([
            'ca.mall_id' => \Yii::$app->mall->id,
        ]);

        $query->innerJoin(['ao' => AddcreditOrder::tableName()], 'ao.id=ca.addcredit_order_id')
            ->innerJoin(["u" => User::tableName()], "u.id=cs.user_id")
            ->andWhere(['and', ['!=', 'u.mobile', ''], ['IS NOT', 'u.mobile', NULL], ['u.is_delete' => 0]]);

        $query->keyword($this->keyword_1 == 1 && $this->keyword, ["ao.mobile" => $this->keyword])
            ->keyword($this->keyword_1 == 2 && $this->keyword, ["u.mobile" => $this->keyword])
            ->keyword($this->keyword_1 == 3 && $this->keyword, ["LIKE", "u.nickname", $this->keyword])
            ->keyword($this->keyword_1 == 4 && $this->keyword, ["ao.order_no" => $this->keyword]);

        if ($this->start_date && $this->end_date) {
            $query->andWhere(['<', 'ca.created_at', strtotime($this->end_date)])
                ->andWhere(['>', 'ca.created_at', strtotime($this->start_date)]);
        }

        if ($this->status === '' || $this->status == -2) {
        } else {
            $query->andWhere(['ca.status' => $this->status]);
        }

        $select = ['ca.*', 'u.nickname', 'u.avatar_url', 'u.role_type', 'u.mobile'];
        $list = $query->select($select)->page($pagination, $this->limit)->orderBy('ca.id desc')->asArray()->all();
        if ($list) {
            foreach ($list as &$item) {
                $item['order_no']       = '';
                $item['pay_user_name']  = '';
                $item['order_price']    = '';
                $item['pay_price']      = '';
                $item['integral_deduction_price']      = '';
                $addcredit_order = AddcreditOrder::findOne($item['addcredit_order_id']);
                if ($addcredit_order) {
                    $item['order_no'] = $addcredit_order->order_no;
                    $item['order_price'] = $addcredit_order->order_price;
                    $item['pay_price'] = $addcredit_order->pay_price;
                    $item['integral_deduction_price']      = $addcredit_order->integral_deduction_price;
                    $item['mobile']      = $addcredit_order->mobile;

                    $user = User::findOne($addcredit_order->user_id);
                    $item['pay_user_name'] = $user ? $user->nickname : '';

                }
            }
        }
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'list' => $list,
                'pagination' => $pagination
            ]
        ];
    }
}
<?php

namespace app\forms\mall\commission;

use app\core\ApiCode;
use app\models\BaseModel;
use app\models\Goods;
use app\models\GoodsWarehouse;
use app\models\OrderDetail;
use app\models\Store;
use app\models\User;
use app\plugins\commission\models\CommissionGoodsPriceLog;
use app\plugins\commission\models\CommissionHotelPriceLog;
use app\plugins\commission\models\CommissionStorePriceLog;
use app\plugins\hotel\models\HotelOrder;
use app\plugins\hotel\models\Hotels;
use app\plugins\mch\models\Mch;
use app\plugins\mch\models\MchCheckoutOrder;

class HotelRecommendLogListForm extends BaseModel
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

        $query = CommissionHotelPriceLog::find()->alias('cs')->where([
            'cs.mall_id' => \Yii::$app->mall->id,
        ]);

        $query->innerJoin(['ho' => HotelOrder::tableName()], 'cs.hotel_order_id = ho.id')
            ->innerJoin(['h' => Hotels::tableName()], 'ho.hotel_id = h.id')
            ->innerJoin(["u" => User::tableName()], "u.id=cs.user_id")
            ->andWhere(['and', ['!=', 'u.mobile', ''], ['IS NOT', 'u.mobile', NULL], ['u.is_delete' => 0]]);

        $query->keyword($this->keyword_1 == 1 && $this->keyword, ["LIKE", "h.name", $this->keyword])
                ->keyword($this->keyword_1 == 2 && $this->keyword, ["u.mobile" => $this->keyword])
                ->keyword($this->keyword_1 == 3 && $this->keyword, ["LIKE", "u.nickname", $this->keyword])
                ->keyword($this->keyword_1 == 4 && $this->keyword, ["ho.order_no" => $this->keyword]);

        if ($this->start_date && $this->end_date) {
            $query->andWhere(['<', 'cs.created_at', strtotime($this->end_date)])
                ->andWhere(['>', 'cs.created_at', strtotime($this->start_date)]);
        }

        if ($this->status === '' || $this->status == -2) {
        } else {
            $query->andWhere(['cs.status' => $this->status]);
        }

        $select = ['cs.*', 'u.nickname', 'u.avatar_url', 'u.role_type', 'u.mobile'];
        $list = $query->select($select)->page($pagination, $this->limit)->orderBy('cs.id desc')->asArray()->all();

        if ($list) {
            foreach ($list as &$item) {
                $item['order_no']       = '';
                $item['pay_user_name']  = '';
                $item['order_price']    = '';
                $item['pay_price']      = '';
                $item['hotel_name']     = '';
                $item['thumb_url']      = '';
                $item['integral_deduction_price']      = '';
                $hotel_order = HotelOrder::findOne($item['hotel_order_id']);
                if ($hotel_order) {
                    $item['order_no']                   = $hotel_order->order_no;
                    $item['order_price']                = $hotel_order->order_price;
                    $item['pay_price']                  = $hotel_order->pay_price;
                    $item['integral_deduction_price']   = $hotel_order->integral_deduction_price;

                    $user = User::findOne($hotel_order->user_id);
                    $item['pay_user_name'] = $user ? $user->nickname : '';

                    $hotels = Hotels::findOne($hotel_order->hotel_id);
                    if ($hotels) {
                        $item['hotel_name'] = $hotels->name;
                        $item['thumb_url'] = $hotels->thumb_url ?: 'https://www.mingyuanriji.cn/web/static/header-logo.png';
                    }
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
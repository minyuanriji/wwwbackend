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
    public $status;

    public function rules()
    {
        return [
            [['page', 'limit', 'status'], 'integer'],
            [['keyword', 'start_date', 'end_date'], 'trim'],
        ];
    }

    public function getList()
    {
        if (!$this->validate()) return $this->responseErrorInfo();

        $query = CommissionHotelPriceLog::find()->alias('cs')->where([
            'cs.mall_id' => \Yii::$app->mall->id,
        ])->joinwith(['user' => function ($query) {
            if ($this->keyword) {
                $query->where(['like', 'nickname', $this->keyword]);
            }
        }])->orderBy('id desc');

        if ($this->start_date && $this->end_date) {
            $query->andWhere(['<', 'cs.created_at', strtotime($this->end_date)])
                ->andWhere(['>', 'cs.created_at', strtotime($this->start_date)]);
        }

        if ($this->status === '' || $this->status == -2) {
        } else {
            $query->andWhere(['cs.status' => $this->status]);
        }
        $list = $query->page($pagination, $this->limit)->asArray()->all();
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
                    $item['order_no'] = $hotel_order->order_no;
                    $item['order_price'] = $hotel_order->order_price;
                    $item['pay_price'] = $hotel_order->pay_price;
                    $item['integral_deduction_price']      = $hotel_order->integral_deduction_price;

                    $user = User::findOne($hotel_order->user_id);
                    $item['pay_user_name'] = $user ? $user->nickname : '';

                    $hotels = Hotels::findOne($hotel_order->hotel_id);
                    if ($hotels) {
                        $item['hotel_name'] = $hotels->name;
                        $item['thumb_url'] = $hotels->thumb_url;
                    }
                }
                $item['identity'] = '';
                if (isset($item['user']['role_type'])) {
                    if ($item['user']['role_type'] == 'store') {
                        $item['identity'] = '门店';
                    } elseif ($item['user']['role_type'] == 'partner') {
                        $item['identity'] = '合伙人';
                    } elseif ($item['user']['role_type'] == 'branch_office') {
                        $item['identity'] = '分公司';
                    } elseif ($item['user']['role_type'] == 'user') {
                        $item['identity'] = '普通用户';
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
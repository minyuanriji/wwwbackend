<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 提现列表
 * Author: zal
 * Date: 2020-04-16
 * Time: 10:45
 */

namespace app\forms\mall\finance;

use app\helpers\SerializeHelper;
use app\models\BaseModel;
use app\models\Cash;
use app\models\User;

class CashListForm extends BaseModel
{
    public $page;
    public $limit;
    public $status;
    public $start_date;
    public $end_date;
    public $keyword;
    public $platform;
    public $fields;
    public $flag;
    public $user_id;
    public $mall_id;

    public function rules()
    {
        return [
            [['status'], 'required'],
            [['page', 'limit', 'status', 'user_id'], 'integer'],
            [['fields'], 'safe'],
            [['flag'], 'string'],
            [['keyword'], 'trim'],
        ];
    }

    public function search()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }
        $pagination = null;
        $this->mall_id = \Yii::$app->mall->id;
        $query = Cash::find()
            ->where(['mall_id' => $this->mall_id, 'is_delete' => 0])
            ->with(['user'])
            ->keyword($this->status >= 0, ['status' => $this->status])
            ->keyword($this->user_id, ['user_id' => $this->user_id]);
        $query->orderBy(['status' => SORT_ASC, 'created_at' => SORT_DESC]);
        if ($this->keyword && empty($this->user_id)) {
            $subQuery = User::find()->select('id')->where(['like', 'nickname', $this->keyword])
                ->andWhere(['mall_id' => $this->mall_id]);
            $query = $query->andWhere(['in', 'user_id', $subQuery]);
        }
        $list = $query->page($pagination, $this->limit, $this->page)->all();
        $newList = [];
        /* @var Cash[] $list */
        foreach ($list as $item) {
            $serviceCharge = round($item->price * $item->service_fee_rate / 100, 2);
            $extra = $item->extra ? SerializeHelper::decode($item->extra) : [];
            $newItem = [
                'id' => $item->id,
                'order_no' => $item->order_no,
                'type' => $item->type,
                'status' => $item->status,
                'extra' => $extra,
                'created_at' => $item->created_at,
                'updated_at' => $item->updated_at,
                'content'=>$item->content?SerializeHelper::decode($item->content):[],
                'user' => [
                    'avatar' => $item->user->avatar_url,
                    'nickname' => $item->user->nickname,
                ],
                'cash' => [
                    'price' => round($item->price, 2),
                    'service_fee_rate' => $serviceCharge,
                    'fact_price' => round($item->fact_price, 2)
                ],
            ];
            $newList[] = $newItem;
        }
        return [
            'code' => 0,
            'data' => [
                'list' => $newList,
                'pagination' => $pagination,
            ]
        ];
    }
}

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

use app\core\ApiCode;
use app\forms\mall\export\CashExport;
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
            [['page', 'limit', 'status', 'user_id'], 'integer'],
            [['fields'], 'safe'],
            [['keyword', 'start_date', 'end_date', 'flag'], 'trim'],
        ];
    }

    public function search()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }
        $currentApply = 0;
        $currentActual = 0;
        $pagination = null;
        $this->mall_id = \Yii::$app->mall->id;
        $query = Cash::find()->alias('c')
            ->where(['c.mall_id' => $this->mall_id, 'c.is_delete' => 0])
            ->innerJoin(["u" => User::tableName()], "u.id=c.user_id")
            ->andWhere(['and', ['<>', 'u.mobile', ''], ['IS NOT', 'u.mobile', NULL], ['u.is_delete' => 0]])
            ->keyword($this->status >= 0, ['c.status' => $this->status])
            ->keyword($this->user_id, ['c.user_id' => $this->user_id])
            ->select('c.*,u.nickname,u.mobile,u.avatar_url');

        $query->orderBy(['c.status' => SORT_ASC, 'c.created_at' => SORT_DESC]);
        if ($this->keyword) {
            $query->andWhere(['or', ['like', 'u.nickname', $this->keyword], ['like', 'u.mobile', $this->keyword]]);
        }
        if ($this->start_date && $this->end_date) {
            $query->andWhere(['<', 'c.updated_at', strtotime($this->end_date)])
                ->andWhere(['>', 'c.updated_at', strtotime($this->start_date)]);
        }
        if ($this->flag == "EXPORT") {
            $new_query = clone $query;
            $exp = new CashExport();
            $exp->fieldsKeyList = $this->fields;
            $exp->export($new_query, 'c.');
            return false;
        }
        $applyQuery = clone $query;
        $applyMoney = $applyQuery->sum('c.price');
        $actualQuery = clone $query;
        $actualMoney = $actualQuery->andWhere(['c.status' => 2])->sum('c.fact_price');
        $list = $query->page($pagination, $this->limit, $this->page)->asArray()->all();
        $newList = [];
        foreach ($list as $item) {
            $serviceCharge = round($item['price'] * $item['service_fee_rate'] / 100, 2);
            $extra = $item['extra'] ? SerializeHelper::decode($item['extra']) : [];
            $newItem = [
                'id' => $item['id'],
                'order_no' => $item['order_no'],
                'type' => $item['type'],
                'status' => $item['status'],
                'is_transmitting' => $item['is_transmitting'],
                'extra' => $extra,
                'created_at' => $item['created_at'],
                'updated_at' => $item['updated_at'],
                'content'=>$item['content']?SerializeHelper::decode($item['content']):[],
                'user_id' => $item['user_id'],
                'user' => [
                    'avatar' => $item['avatar_url'],
                    'nickname' => $item['nickname'],
                ],
                'cash' => [
                    'price' => round($item['price'], 2),
                    'service_fee_rate' => $serviceCharge,
                    'fact_price' => round($item['fact_price'], 2)
                ],
            ];
            $currentApply += $item['price'];
            if ($item['status'] == 2) {
                $currentActual += $item['fact_price'];
            }

            $newList[] = $newItem;
        }
        return $this->returnApiResultData(ApiCode::CODE_SUCCESS, '', [
            'list' => $newList,
            'export_list' => (new CashExport())->fieldsList(),
            'Statistics' => [
                'applyMoney' => $applyMoney ?: 0,
                'actualMoney' => $actualMoney ?: 0,
                'currentApply' => round($currentApply, 2),
                'currentActual' => round($currentActual, 2),
            ],
            'pagination' => $pagination,
        ]);
    }
}

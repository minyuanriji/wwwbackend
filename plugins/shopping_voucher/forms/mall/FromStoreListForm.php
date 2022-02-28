<?php

namespace app\plugins\shopping_voucher\forms\mall;

use app\core\ApiCode;
use app\helpers\CityHelper;
use app\models\BaseModel;
use app\models\Store;
use app\models\User;
use app\plugins\mch\models\Mch;
use app\plugins\shopping_voucher\models\ShoppingVoucherFromStore;

class FromStoreListForm extends BaseModel {

    public $page;
    public $limit;
    public $parent;
    public $keyword;
    public $district;
    public $date;
    public $income_unit;
    public $income_min;
    public $cash_unit;
    public $cash_min;
    public $transfer_rate_max;
    public $transfer_rate_min;
    public $give_value_min;
    public $give_value_max;
    public $income_stat_date;
    public $send_stat_date;

    public function rules(){
        return [
            [['page', 'limit'], 'integer'],
            [['parent', 'keyword', 'parent'], 'trim'],
            [['income_unit', 'cash_unit'], 'string'],
            [['name','district', 'date', 'income_min', 'cash_min', 'transfer_rate_max', 'transfer_rate_min',
              'give_value_min', 'give_value_max', 'income_stat_date', 'send_stat_date'], 'safe']
        ];
    }

    public function getList(){

        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        try {

            $query = ShoppingVoucherFromStore::find()->alias("ss")->where(["ss.is_delete" => 0]);
            $query->innerJoin(["s" => Store::tableName()], "s.id=ss.store_id");
            $query->innerJoin(["m" => Mch::tableName()], "m.id=ss.mch_id");
            $query->innerJoin(["u" => User::tableName()], "u.id=m.user_id");
            $query->leftJoin(["p" => User::tableName()], "p.id=u.parent_id");

            //推荐人
            if($this->parent){
                $query->andWhere([
                    "OR",
                    ["p.id" => (int)$this->parent],
                    ["LIKE", "p.nickname", $this->parent],
                    ["LIKE", "p.mobile", $this->parent]
                ]);
            }

            //关键词
            if($this->keyword){
                $query->andWhere([
                    "OR",
                    ["ss.mch_id" => (int)$this->keyword],
                    ["LIKE", "ss.name", $this->keyword],
                    ["LIKE", "m.mobile", $this->keyword]
                ]);
            }

            //地区搜索
            if($this->district){
                if(isset($this->district[0])){
                    $query->andWhere(["s.province_id" => $this->district[0]]);
                }
                if(isset($this->district[1])){
                    $query->andWhere(["s.city_id" => $this->district[1]]);
                }
                if(isset($this->district[2])){
                    $query->andWhere(["s.district_id" => $this->district[2]]);
                }
            }
            //加入日期
            if($this->date){
                $query->andWhere([
                    "AND",
                    [">", "m.created_at", strtotime($this->date[0])],
                    ["<", "m.created_at", strtotime($this->date[1])]
                ]);
            }
            //最小折扣
            if(is_numeric($this->transfer_rate_min) && $this->transfer_rate_min >= 0){
                $val = 100 - ($this->transfer_rate_min/10) * 100;
                $query->andWhere(["<=", "m.transfer_rate", $val]);
            }
            //最大折扣
            if(is_numeric($this->transfer_rate_max) && $this->transfer_rate_max >= 0){
                $val = 100 - ($this->transfer_rate_max/10) * 100;
                $query->andWhere([">=", "m.transfer_rate", $val]);
            }
            //最小赠送比例
            if(is_numeric($this->give_value_min) && $this->give_value_min >= 0){
                $query->andWhere([">=", "ss.give_value", $this->give_value_min]);
            }
            //最大赠送比例
            if(is_numeric($this->give_value_max) && $this->give_value_max >= 0){
                $query->andWhere(["<=", "ss.give_value", $this->give_value_max]);
            }

            $selects = ["ss.*", "m.transfer_rate", "s.mobile", "s.address", "s.province_id", "s.city_id", "s.district_id",
                "p.nickname as parent_nickname"];

            //收入统计
            $sql = "select sum(money) FROM {{%plugin_mch_account_log}} WHERE mch_id=m.id AND type=1 ";
            if($this->income_stat_date){
                $sql .= " AND created_at>='".strtotime($this->income_stat_date[0])."' AND created_at<'".strtotime($this->income_stat_date[1])."'";
            }
            $selects[] = "IFNULL(({$sql}), 0) as total_income";

            //赠红包统计
            $sql = "select sum(money) from jxmall_plugin_shopping_voucher_send_log l inner join jxmall_plugin_mch_checkout_order mco on mco.id=l.source_id where l.source_type='from_mch_checkout_order' and l.`status`='success' and mco.store_id=s.id";
            if($this->send_stat_date){
                $sql .= " AND l.created_at>='".strtotime($this->send_stat_date[0])."' AND l.created_at<'".strtotime($this->send_stat_date[1])."'";
            }
            $selects[] = "IFNULL(({$sql}), 0) as total_send";

            $query->orderBy("ss.id DESC");

            $list = $query->select($selects)->page($pagination, $this->limit, $this->page)->asArray()->all();

            if($list){
                foreach($list as &$item){
                    $city = CityHelper::reverseData($item['district_id'], $item['city_id'], $item['province_id']);
                    $item['province'] = isset($city['province']['name']) ? $city['province']['name'] : "";
                    $item['city'] = isset($city['city']['name']) ? $city['city']['name'] : "";
                    $item['district'] = isset($city['district']['name']) ? $city['district']['name'] : "";
                    $item['address'] = str_replace($item['province'], "", $item['address']);
                    $item['address'] = str_replace($item['city'], "", $item['address']);
                    $item['address'] = str_replace($item['district'], "", $item['address']);
                    $item['start_at'] = date("Y-m-d", $item['start_at'] ? $item['start_at'] : time());

                    $transfer_rate = (100 - $item['transfer_rate']) / 100 * 10;
                    if (strpos($transfer_rate, '.')) {
                        $item['transfer_rate'] = (float)substr($transfer_rate, 0, strpos($transfer_rate, '.') + 3);
                    } else {
                        $item['transfer_rate'] = $transfer_rate;
                    }
                }
            }

            return $this->returnApiResultData(ApiCode::CODE_SUCCESS, '', [
                'list'       => $list ? $list : [],
                'pagination' => $pagination
            ]);
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }
    }

}
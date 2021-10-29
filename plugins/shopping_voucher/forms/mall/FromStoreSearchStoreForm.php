<?php

namespace app\plugins\shopping_voucher\forms\mall;

use app\core\ApiCode;
use app\helpers\CityHelper;
use app\models\BaseModel;
use app\models\Store;
use app\models\User;
use app\plugins\mch\models\Mch;
use app\plugins\mch\models\MchAccountLog;
use app\plugins\shopping_voucher\models\ShoppingVoucherFromStore;

class FromStoreSearchStoreForm extends BaseModel {

    public $parent;
    public $id;
    public $name;
    public $district;
    public $date;
    public $income_unit;
    public $income_min;
    public $cash_unit;
    public $cash_min;
    public $page;
    public $transfer_rate_max;
    public $transfer_rate_min;
    public $is_new;

    public function rules(){
        return [
            [['id', 'page', 'is_new'], 'integer'],
            [['parent'], 'trim'],
            [['income_unit', 'cash_unit'], 'string'],
            [['name','district', 'date', 'income_min', 'cash_min', 'transfer_rate_max', 'transfer_rate_min'], 'safe']
        ];
    }

    public function getList(){
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        try {

            $query = Mch::find()->alias("m");
            $query->innerJoin(["s" => Store::tableName()], "s.mch_id=m.id");
            $query->innerJoin(["u" => User::tableName()], "u.id=m.user_id");
            $query->leftJoin(["p" => User::tableName()], "p.id=u.parent_id");
            $query->leftJoin(["ss" => ShoppingVoucherFromStore::tableName()], "s.id=ss.store_id");
            $query->orderBy("m.id DESC");
            $query->where([
                "m.review_status" => 1,
                "m.is_delete" => 0
            ]);

            //推荐人
            if($this->parent){
                $query->andWhere([
                    "OR",
                    ["p.id" => (int)$this->parent],
                    ["LIKE", "p.nickname", $this->parent],
                    ["LIKE", "p.mobile", $this->parent]
                ]);
            }

            //指定商户ID
            if($this->id){
                $query->andWhere(["m.id" => $this->id]);
            }
            //按名称模糊搜索
            if($this->name){
                $query->andWhere(["LIKE", "s.name", $this->name]);
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

            //收入
            if(is_numeric($this->income_min) && $this->income_min >= 0){
                if($this->income_unit == "day"){ //日收入（前一日）
                    $startTime = strtotime(date("Y-m-d", strtotime("-1 days")));
                    $endTime = $startTime + 3600 * 24;
                }elseif($this->income_unit == "month"){ //月收入（上一个月）
                    $startTime = strtotime(date("Y-m", strtotime("-1 months")));
                    $endTime = strtotime(date("Y-m"));
                }else{  //年收入（上一年）
                    $startTime = strtotime(date("Y", strtotime("-1 years")) . "-01-01 00:00:00");
                    $endTime = strtotime(date("Y") . "-01-01 00:00:00");
                }
                $subSql = "IFNULL((select sum(money) FROM {{%plugin_mch_account_log}} WHERE mch_id=m.id AND type=1 AND created_at>='{$startTime}' AND created_at<'{$endTime}'), 0) >= '{$this->income_min}'";
                $query->andWhere($subSql);
            }
            //支出
            if(is_numeric($this->cash_min) && $this->cash_min >= 0){
                if($this->cash_unit == "day"){ //日收入（前一日）
                    $startTime = strtotime(date("Y-m-d", strtotime("-1 days")));
                    $endTime = $startTime + 3600 * 24;
                }elseif($this->cash_unit == "month"){ //月收入（上一个月）
                    $startTime = strtotime(date("Y-m", strtotime("-1 months")));
                    $endTime = strtotime(date("Y-m"));
                }else{  //年收入（上一年）
                    $startTime = strtotime(date("Y", strtotime("-1 years")) . "-01-01 00:00:00");
                    $endTime = strtotime(date("Y") . "-01-01 00:00:00");
                }
                $subSql = "IFNULL((select sum(money) FROM {{%plugin_mch_account_log}} WHERE mch_id=m.id AND type=2 AND created_at>='{$startTime}' AND created_at<'{$endTime}'), 0) >= '{$this->cash_min}'";
                $query->andWhere($subSql);
            }

            if($this->is_new){
                $subQuery = ShoppingVoucherFromStore::find()->where(["is_delete" => 0])->select(["mch_id"]);
                $query->andWhere(["NOT IN", "m.id", $subQuery]);
            }

            $selects = ["m.id", "s.mall_id", "s.id as store_id",  "s.name", "s.cover_url", "s.mobile", "s.address", "s.province_id", "s.city_id", "s.district_id",
                "m.created_at", "m.account_money", "m.transfer_rate", "p.nickname as parent_nickname"];
            $selects = array_merge($selects, ["ss.give_value", "ss.give_type"]);

            $query->select($selects);

            $list = $query->page($pagination, 10, $this->page)->asArray()->all();
            if($list){
                foreach($list as &$item){
                    $city = CityHelper::reverseData($item['district_id'], $item['city_id'], $item['province_id']);
                    $item['province'] = isset($city['province']['name']) ? $city['province']['name'] : "";
                    $item['city'] = isset($city['city']['name']) ? $city['city']['name'] : "";
                    $item['district'] = isset($city['district']['name']) ? $city['district']['name'] : "";
                    $item['address'] = str_replace($item['province'], "", $item['address']);
                    $item['address'] = str_replace($item['city'], "", $item['address']);
                    $item['address'] = str_replace($item['district'], "", $item['address']);
                    $item['transfer_rate'] = floatval((100 - $item['transfer_rate'])/100) * 10;
                    $item['created_at'] = date("Y-m-d", $item['created_at']);
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
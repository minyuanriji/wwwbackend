<?php

namespace app\plugins\smart_shop\forms\api\kpi_admin;

use app\core\ApiCode;
use app\models\BaseModel;
use app\models\User;
use app\plugins\smart_shop\models\KpiNewOrder;

class KpiAdminStatNewOrderForm extends BaseModel{

    public $merchant_id;
    public $store_id;

    public $mobile;
    public $start_time;
    public $end_time;
    public $limit = 10;
    public $page;

    public function rules(){
        return [
            [['merchant_id'], 'required'],
            [['mobile', 'start_time', 'end_time'], 'trim'],
            [['page', 'limit', 'store_id'], 'integer']
        ];
    }

    public function getList(){

        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        try {

            $query = KpiNewOrder::find()->alias("kno")
                ->innerJoin(["u" => User::tableName()], "u.id=kno.inviter_user_id")
                ->groupBy("kno.inviter_user_id");

            $query->where(["kno.merchant_id" => $this->merchant_id]);
            if($this->store_id){
                $query->where(["kno.store_id" => $this->store_id]);
            }

            if(!$this->start_time || !$this->end_time){
                $this->start_time = time() - 3600 * 24 * 30;
                $this->end_time = time();
            }else{
                $this->start_time = strtotime($this->start_time);
                $this->end_time = strtotime($this->end_time);
            }

            $query->andWhere([">", "kno.created_at", $this->start_time]);
            $query->andWhere(["<", "kno.created_at", $this->end_time]);

            if($this->mobile){
                $query->andWhere(["u.mobile" => $this->mobile]);
            }

            $selects = ["u.id as user_id", "u.mobile", "u.nickname", "u.avatar_url"];
            $selects[] = "count(kno.inviter_user_id) as num";
            $selects[] = "sum(kno.point) as total_point";

            $list = $query->select($selects)->orderBy("num DESC")->asArray()
                ->page($pagination, $this->limit, $this->page)->all();
            if($list){
                foreach($list as $key => $item){
                    $list[$key]['date'] = date("Y年m月d日", $this->start_time) . " 至 " . date("Y年m月d日", $this->end_time);
                }
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'list'       => $list ? $list : [],
                    'pagination' => $pagination,
                ]
            ];
        }catch (\Exception $e){
            return $this->returnApiResultData(ApiCode::CODE_FAIL, $e->getMessage());
        }
    }
}
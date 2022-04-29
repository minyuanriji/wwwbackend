<?php

namespace app\plugins\smart_shop\forms\api\kpi_admin;

use app\core\ApiCode;
use app\models\BaseModel;
use app\models\User;
use app\plugins\smart_shop\models\KpiLinkCoupon;
use app\plugins\smart_shop\models\KpiLinkGoods;

class KpiAdminStatShareForm extends BaseModel{

    public $merchant_id;
    public $store_id;
    public $type;

    public $mobile;
    public $start_time;
    public $end_time;
    public $limit = 10;
    public $page;

    public function rules(){
        return [
            [['merchant_id'], 'required'],
            [['mobile', 'start_time', 'end_time', 'type'], 'trim'],
            [['page', 'limit', 'store_id'], 'integer']
        ];
    }

    public function getList(){

        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        try {

            if($this->type == "coupon"){
                $query = KpiLinkCoupon::find()->alias("kls")
                    ->innerJoin(["u" => User::tableName()], "u.id=kls.inviter_user_id")
                    ->groupBy("kls.inviter_user_id");
            }else{
                $query = KpiLinkGoods::find()->alias("kls")
                    ->innerJoin(["u" => User::tableName()], "u.id=kls.inviter_user_id")
                    ->groupBy("kls.inviter_user_id");
            }


            $query->where(["kls.merchant_id" => $this->merchant_id]);
            if($this->store_id){
                $query->where(["kls.store_id" => $this->store_id]);
            }

            if(!$this->start_time || !$this->end_time){
                $this->start_time = time() - 3600 * 24 * 30;
                $this->end_time = time();
            }else{
                $this->start_time = strtotime($this->start_time);
                $this->end_time = strtotime($this->end_time);
            }

            $query->andWhere([">", "kls.created_at", $this->start_time]);
            $query->andWhere(["<", "kls.created_at", $this->end_time]);

            if($this->mobile){
                $query->andWhere(["u.mobile" => $this->mobile]);
            }

            $selects = ["u.id as user_id", "u.mobile", "u.nickname", "u.avatar_url"];
            $selects[] = "count(kls.inviter_user_id) as num";
            $selects[] = "sum(kls.point) as total_point";

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
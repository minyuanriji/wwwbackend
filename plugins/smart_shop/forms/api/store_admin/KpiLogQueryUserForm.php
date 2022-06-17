<?php

namespace app\plugins\smart_shop\forms\api\store_admin;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\smart_shop\models\KpiLinkGoods;
use app\plugins\smart_shop\models\KpiUser;

class KpiLogQueryUserForm extends BaseModel{

    public $merchant_id;
    public $store_id;
    public $page;
    public $start_time;
    public $end_time;


    public function rules(){
        return [
            [['merchant_id', 'store_id'], 'required'],
            [['page'], 'integer'],
            [['start_time', 'end_time'], 'trim']
        ];
    }

    public function getList(){
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        try {

            //统计日期范围
            if(!$this->start_time || !$this->end_time){
                $startTime = strtotime(date("Y-m-d") . " 00:00:00") - 3600 * 24 * 7;
                $endTime   = $this->start_time + 3600 * 24 - 1;
            }else{
                $startTime = strtotime($this->start_time);
                $endTime = strtotime($this->end_time);
            }

            $query = KpiUser::find()->alias("ku");
            $query->andWhere([
                "AND",
                ["ku.mall_id" => \Yii::$app->mall->id],
                ["ku.is_delete" => 0],
                ["ku.ss_mch_id" => $this->merchant_id],
                ["ku.ss_store_id" => $this->store_id]
            ]);
            $query->select(["ku.id", "ku.user_id", "ku.realname", "ku.mobile"]);
            $query->orderBy("ku.user_id DESC");
            $list = $query->asArray()->page($pagination, 10, $this->page)->all();
            if($list){
                foreach($list as $key => $row){
                    //统计直接分享数量
                    $cond = [
                        "AND",
                        ["inviter_user_id" => $row['user_id']],
                        ["store_id"        => $this->store_id],
                        ["merchant_id"     => $this->merchant_id],
                        [">", "created_at", $startTime],
                        ["<", "created_at", $endTime]
                    ];
                    $row['total_share'] = (int)KpiLinkGoods::find()->andWhere($cond)->count();
                    //直接分享获得积分数量
                    $row['total_share_point'] = (int)KpiLinkGoods::find()->andWhere($cond)->sum("point");
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
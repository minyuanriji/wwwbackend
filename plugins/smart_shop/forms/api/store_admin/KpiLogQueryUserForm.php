<?php

namespace app\plugins\smart_shop\forms\api\store_admin;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\smart_shop\models\KpiLinkGoods;
use app\plugins\smart_shop\models\KpiNewOrder;
use app\plugins\smart_shop\models\KpiRegister;
use app\plugins\smart_shop\models\KpiUser;

class KpiLogQueryUserForm extends BaseModel{

    public $merchant_id;
    public $store_id;
    public $page;
    public $goods_id;
    public $start_time;
    public $end_time;


    public function rules(){
        return [
            [['merchant_id', 'store_id'], 'required'],
            [['page', 'goods_id'], 'integer'],
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
                $endTime = strtotime(date("Y-m-d") . " 00:00:00")  + 3600 * 24 - 1;
                $startTime = $endTime - 3600 * 24 * 7 + 1;
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
                    $row = $this->countLinkGoods($row, $startTime, $endTime);
                    $row = $this->countNewOrder($row, $startTime, $endTime);

                    if($this->goods_id){
                        $row['total_register_point_1'] = 0;
                        $row['total_register_point_2'] = 0;
                    }else{
                        $row = $this->countRegister($row, $startTime, $endTime);

                    }

                    $row['total_point_1'] = $row['total_share_1'] + $row['total_register_point_1'] + $row['total_new_order_point_1'];
                    $row['total_point_2'] = $row['total_share_2'] + $row['total_register_point_2'] + $row['total_new_order_point_2'];
                    $list[$key] = $row;
                }
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'list'       => $list ? $list : [],
                    'pagination' => $pagination,
                    'start_time' => date("Y-m-d", $startTime),
                    'end_time'   => date("Y-m-d", $endTime),
                ]
            ];
        }catch (\Exception $e){
            return $this->returnApiResultData(ApiCode::CODE_FAIL, [
                'error' => $e->getMessage(),
                'file'  => $e->getFile(),
                'line'  => $e->getLine()
            ]);
        }
    }

    /**
     * 新订单统计
     * @param $row
     * @param $startTime
     * @param $endTime
     * @return mixed
     */
    private function countNewOrder($row, $startTime, $endTime){
        $commonWhere = [
            "AND",
            ["store_id"        => $this->store_id],
            ["merchant_id"     => $this->merchant_id],
            [">", "created_at", $startTime],
            ["<", "created_at", $endTime],
        ];

        if($this->goods_id){
            $commonWhere[] = ["source_table" => "cyorder"];
            $commonWhere[] = "FIND_IN_SET('".$this->goods_id."', goods_id_list)";
        }

        //直接下单数
        $row['total_new_order_1'] = (int)KpiNewOrder::find()->andWhere(array_merge($commonWhere, [
            ["inviter_user_id" => $row['user_id']],
            "inviter_user_id=source_user_id"
        ]))->count();

        //直接下单获得积分数
        $row['total_new_order_point_1'] = (float)KpiNewOrder::find()->andWhere(array_merge($commonWhere, [
            ["inviter_user_id" => $row['user_id']],
            "inviter_user_id=source_user_id"
        ]))->sum("point");

        //间接下单数
        $row['total_new_order_2'] = (int)KpiNewOrder::find()->andWhere(array_merge($commonWhere, [
            ["inviter_user_id" => $row['user_id']],
            "inviter_user_id<>source_user_id"
        ]))->count();

        //间接下单获得积分数
        $row['total_new_order_point_2'] = (float)KpiNewOrder::find()->andWhere(array_merge($commonWhere, [
            ["inviter_user_id" => $row['user_id']],
            "inviter_user_id<>source_user_id"
        ]))->sum("point");

        return $row;
    }

    /**
     * 注册统计
     * @param $row
     * @param $startTime
     * @param $endTime
     */
    private function countRegister($row, $startTime, $endTime){
        $commonWhere = [
            "AND",
            ["store_id"        => $this->store_id],
            ["merchant_id"     => $this->merchant_id],
            [">", "created_at", $startTime],
            ["<", "created_at", $endTime]
        ];

        //直接注册数
        $row['total_register_1'] = (int)KpiRegister::find()->andWhere(array_merge($commonWhere, [
            ["inviter_user_id" => $row['user_id']],
            "inviter_user_id=source_user_id"
        ]))->count();

        //直接注册获得积分数
        $row['total_register_point_1'] = (float)KpiRegister::find()->andWhere(array_merge($commonWhere, [
            ["inviter_user_id" => $row['user_id']],
            "inviter_user_id=source_user_id"
        ]))->sum("point");

        //间接注册数
        $row['total_register_2'] = (int)KpiRegister::find()->andWhere(array_merge($commonWhere, [
            ["inviter_user_id" => $row['user_id']],
            "inviter_user_id<>source_user_id"
        ]))->count();

        //间接注册获得积分数
        $row['total_register_point_2'] = (float)KpiRegister::find()->andWhere(array_merge($commonWhere, [
            ["inviter_user_id" => $row['user_id']],
            "inviter_user_id<>source_user_id"
        ]))->sum("point");

        return $row;
    }

    /**
     * 分享统计
     * @param $row
     * @param $startTime
     * @param $endTime
     * @return array
     */
    private function countLinkGoods($row, $startTime, $endTime){

        $commonWhere = [
            "AND",
            ["store_id"        => $this->store_id],
            ["merchant_id"     => $this->merchant_id],
            [">", "created_at", $startTime],
            ["<", "created_at", $endTime]
        ];

        if($this->goods_id){
            $commonWhere[] = ["source_table" => "goods"];
            $commonWhere[] = ["goods_id" => $this->goods_id];
        }

        //直接分享数
        $row['total_share_1'] = (int)KpiLinkGoods::find()->andWhere(array_merge($commonWhere, [
            ["inviter_user_id" => $row['user_id']],
            "inviter_user_id=source_user_id"
        ]))->count();

        //直接分享获得积分数
        $row['total_share_point_1'] = (float)KpiLinkGoods::find()->andWhere(array_merge($commonWhere, [
            ["inviter_user_id" => $row['user_id']],
            "inviter_user_id=source_user_id"
        ]))->sum("point");

        //间接分享数
        $row['total_share_2'] = (int)KpiLinkGoods::find()->andWhere(array_merge($commonWhere, [
            ["inviter_user_id" => $row['user_id']],
            "inviter_user_id<>source_user_id"
        ]))->count();

        //间接分享获得积分数
        $row['total_share_point_2'] = (float)KpiLinkGoods::find()->andWhere(array_merge($commonWhere, [
            ["inviter_user_id" => $row['user_id']],
            "inviter_user_id<>source_user_id"
        ]))->sum("point");

        return $row;
    }
}
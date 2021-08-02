<?php

namespace app\forms\api\clerkCenter;


use app\core\ApiCode;
use app\models\BaseModel;
use app\models\clerk\ClerkData;
use app\models\clerk\ClerkLog;
use app\plugins\giftpacks\models\GiftpacksItem;
use app\plugins\giftpacks\models\GiftpacksOrderItem;

class ClerkGetLogForm extends BaseModel{

    public $page;

    public function rules(){
        return [
            [['page'], 'integer']
        ];
    }

    public function getList(){

        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {

            $query = ClerkLog::find()->alias("cl")
                        ->innerJoin(["cd" => ClerkData::tableName()], "cd.id=cl.clerk_data_id");

            $selects = ["cd.source_id", "cd.source_type", "cl.created_at", "cl.remark"];

            //核销大礼包物品
            $query->leftJoin(["goi" => GiftpacksOrderItem::tableName()], "goi.id=cd.source_id AND cd.source_type='giftpacks_order_item'");
            $query->leftJoin(["gpi" => GiftpacksItem::tableName()], "gpi.id=goi.pack_item_id");
            $selects = array_merge($selects, [
                "gpi.name as pack_item_name"
            ]);

            $query->select($selects);
            $query->orderBy("cl.id DESC");
            $query->where(["cl.user_id" => \Yii::$app->user->id]);

            $rows = $query->page($pagination, 10, max(1, (int)$this->page))
                          ->asArray()->all();
            $list = [];
            if($rows){
                foreach($rows as $row){
                    $item['created_at'] = date("Y-m-d H:i:s", $row['created_at']);
                    $item['descript'] = "";
                    if($row['source_type'] == "giftpacks_order_item"){
                        $item['descript'] = "核销大礼包“".$row['pack_item_name']."”";
                    }elseif($row['source_type'] == "mch_baopin_order"){
                        $item['descript'] = "核销爆品订单[ID:".$row['source_id']."]";
                    }elseif($row['source_type'] == "mch_normal_order"){
                        $item['descript'] = "核销商品订单[ID:".$row['source_id']."]";
                    }
                    $list[] = $item;
                }
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'list'       => $list ? $list : [],
                    'pagination' => $pagination
                ]
            ];
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }
    }
}
<?php

namespace app\plugins\smart_shop\forms\api;

use app\core\ApiCode;
use app\models\BaseModel;
use app\models\User;
use app\plugins\smart_shop\models\KpiLinkGoods;
use app\plugins\smart_shop\models\KpiNewOrder;
use app\plugins\smart_shop\models\KpiRegister;
use app\plugins\smart_shop\models\KpiUser;

class KpiAwardLogForm  extends BaseModel{

    public $page;
    public $mobile;
    public $type;
    public $startTime;
    public $endTime;

    public function rules(){
        return [
            [['mobile'], 'required'],
            [['page'], 'integer'],
            [['type', 'startTime', 'endTime'], 'trim']
        ];
    }

    public function getList(){
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        try {

            if($this->type == "register"){
                $query = KpiRegister::find();
            }elseif($this->type == "new_order"){
                $query = KpiNewOrder::find();
            }else{
                $query = KpiLinkGoods::find();
            }

            $query->andWhere(["IN", "inviter_user_id", User::find()->select("id")->where(["mobile" => $this->mobile])]);

            if(!empty($this->startTime) && !empty($this->endTime)){
                $query->andWhere([
                    "AND",
                    [">=", "created_at", strtotime($this->startTime . " 00:00:00")],
                    ["<=", "created_at", strtotime($this->endTime . " 00:00:00") + 3600 * 24 - 1]
                ]);
            }

            $selects = ["inviter_user_id", "mobile", "store_id", "point", "created_at"];
            
            $list = $query->select($selects)->asArray()->orderBy("id DESC")->page($pagination, 10, $this->page)->all();
            foreach($list as $key => $row){
                $row['created_at'] = date("Y-m-d H:i:s", $row['created_at']);
                $list[$key] = $row;
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'list'       => $list ? $list : [],
                    'pagination' => $pagination,
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
}
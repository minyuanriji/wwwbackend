<?php

namespace app\plugins\smart_shop\forms\api;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\smart_shop\models\KpiLinkGoods;
use app\plugins\smart_shop\models\KpiNewOrder;
use app\plugins\smart_shop\models\KpiRegister;
use app\plugins\smart_shop\models\KpiUser;

class KpiAwardLogForm  extends BaseModel{

    public $page;
    public $mobile;

    public function rules(){
        return [
            [['mobile'], 'required'],
            [['page'], 'integer'],
        ];
    }

    public function getList(){
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        try {

            $sqls = [
                "(select inviter_user_id, store_id, point, created_at from {{%plugin_smartshop_kpi_new_order}})",
                "(select inviter_user_id, store_id, point, created_at from {{%plugin_smartshop_kpi_link_goods}})",
                "(select inviter_user_id, store_id, point, created_at from {{%plugin_smartshop_kpi_register}})"
            ];

            $selects = ["inviter_user_id", "store_id", "point", "created_at"];
            $query = KpiNewOrder::find()->select($selects)
                ->union(KpiLinkGoods::find()->select($selects))
                ->union(KpiRegister::find()->select($selects))
                ->where(["mobile" => $this->mobile]);

            $list = $query->asArray()->orderBy("id DESC")->page($pagination, 10, $this->page)->all();
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
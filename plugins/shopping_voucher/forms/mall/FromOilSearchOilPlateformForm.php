<?php

namespace app\plugins\shopping_voucher\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\oil\models\OilPlateforms;
use app\plugins\shopping_voucher\models\ShoppingVoucherFromOil;

class FromOilSearchOilPlateformForm extends BaseModel {

    public $id;
    public $name;
    public $page;

    public function rules(){
        return [
            [['id', 'page'], 'integer'],
            [['name'], 'safe']
        ];
    }

    public function getList(){
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        try {

            $query = OilPlateforms::find()->alias("op");
            $query->leftJoin(["svfo" => ShoppingVoucherFromOil::tableName()], "op.id=svfo.plat_id");
            $query->orderBy("op.id DESC");
            $query->where([
                "op.is_delete" => 0
            ]);

            //指定平台ID
            if($this->id){
                $query->andWhere(["op.id" => $this->id]);
            }

            //按名称模糊搜索
            if($this->name){
                $query->andWhere(["LIKE", "op.name", $this->name]);
            }

            $selects = ["op.id", "op.id as plat_id", "op.mall_id",  "op.name", "op.created_at"];
            $selects = array_merge($selects, ["svfo.first_give_value", "svfo.first_give_type",
                "svfo.second_give_value", "svfo.second_give_type"
            ]);

            $query->select($selects);

            $list = $query->page($pagination, 100, $this->page)->asArray()->all();
            if($list){
                foreach($list as &$item){
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
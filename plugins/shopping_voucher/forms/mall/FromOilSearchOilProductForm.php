<?php

namespace app\plugins\shopping_voucher\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\oil\models\OilProduct;
use app\plugins\shopping_voucher\models\ShoppingVoucherFromOil;

class FromOilSearchOilProductForm extends BaseModel {

    public $plat_id;
    public $id;
    public $name;
    public $page;

    public function rules(){
        return [
            [['plat_id'], 'required'],
            [['id', 'page'], 'integer'],
            [['name'], 'safe']
        ];
    }

    public function getList(){
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        try {

            $query = OilProduct::find()->alias("opp");
            $query->leftJoin(["svfo" => ShoppingVoucherFromOil::tableName()], "opp.id=svfo.product_id AND svfo.plat_id='{$this->plat_id}'");
            $query->orderBy("opp.id DESC");
            $query->where([
                "opp.is_delete" => 0
            ]);

            //指定产品ID
            if($this->id){
                $query->andWhere(["opp.id" => $this->id]);
            }

            //按名称模糊搜索
            if($this->name){
                $query->andWhere(["LIKE", "opp.name", $this->name]);
            }

            $selects = ["opp.id", "opp.plat_id", "opp.mall_id",  "opp.name", "opp.price", "opp.created_at"];
            $selects = array_merge($selects, ["svfo.first_give_value", "svfo.first_give_type",
                "svfo.second_give_value", "svfo.second_give_type"
            ]);

            $query->select($selects);

            $list = $query->page($pagination, 10, $this->page)->asArray()->all();
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
<?php

namespace app\plugins\shopping_voucher\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\giftpacks\models\Giftpacks;
use app\plugins\shopping_voucher\models\ShoppingVoucherFromGiftpacks;

class FromGiftpacksSearchGiftpacksForm extends BaseModel {

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

            $query = Giftpacks::find()->alias("gp");
            $query->leftJoin(["svfgp" => ShoppingVoucherFromGiftpacks::tableName()], "gp.id=svfgp.pack_id");
            $query->orderBy("gp.id DESC");
            $query->where([
                "gp.is_delete" => 0
            ]);

            //指定商品ID
            if($this->id){
                $query->andWhere(["gp.id" => $this->id]);
            }

            //按名称模糊搜索
            if($this->name){
                $query->andWhere(["LIKE", "gp.title", $this->name]);
            }

            $selects = ["gp.id", "gp.id as pack_id", "gp.mall_id",  "gp.title", "gp.cover_pic",  "gp.created_at"];
            $selects = array_merge($selects, ["svfgp.give_value", "svfgp.give_type", "svfgp.recommender"]);

            $query->select($selects);

            $list = $query->page($pagination, 10, $this->page)->asArray()->all();
            if($list){
                foreach($list as &$item){
                    $item['recommender'] = !empty($item['recommender']) ? @json_decode($item['recommender'], true) : '';
                    $item['created_at']  = date("Y-m-d", $item['created_at']);
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
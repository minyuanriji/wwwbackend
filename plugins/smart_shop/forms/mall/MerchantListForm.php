<?php

namespace app\plugins\smart_shop\forms\mall;

use app\core\ApiCode;
use app\models\Store;
use app\plugins\mch\models\Mch;
use app\plugins\sign_in\forms\BaseModel;
use app\plugins\smart_shop\models\Merchant;

class MerchantListForm extends BaseModel{

    public $page;

    public $name;
    public $mch_id;
    public $mobile;

    public function rules(){
        return [
            [['page', 'mch_id'], 'integer'],
            [['name', 'mobile'], 'trim']
        ];
    }

    public function getList(){
        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {

            $query = Merchant::find()->alias("mc")
                ->innerJoin(["m" => Mch::tableName()], "m.id=mc.bsh_mch_id")
                ->innerJoin(["s" => Store::tableName()], "s.mch_id=m.id");

            $query->andWhere(["mc.is_delete" => 0]);

            if($this->name){
                $query->andWhere(["LIKE", "s.name", $this->name]);
            }

            if($this->mch_id){
                $query->andWhere(["m.id" => $this->mch_id]);
            }

            if($this->mobile){
                $query->andWhere(["m.mobile" => $this->mobile]);
            }

            $query->orderBy("mc.id DESC");

            $selects = ["mc.*", "m.mobile", "s.name", "s.cover_url", "m.transfer_rate"];

            $list = $query->select($selects)->asArray()->page($pagination, 10, $this->page)->all();

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'list' => $list ? $list : [],
                    'pagination' => $pagination,
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
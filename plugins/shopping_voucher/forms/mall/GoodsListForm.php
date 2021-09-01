<?php
namespace app\plugins\shopping_voucher\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\shopping_voucher\models\shopping_voucherGoods;
use app\plugins\Shopping_voucher\models\ShoppingVoucherLog;
use app\plugins\Shopping_voucher\models\VoucherGoods;

class GoodsListForm extends BaseModel{

    public $page;
    public $keyword;
    public $sort_prop;
    public $sort_type;

    public function rules(){
        return array_merge(parent::rules(), [
            [['page'], 'integer'],
            [['keyword', 'sort_prop', 'sort_type'], 'safe']
        ]);
    }

    public function getList(){
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        $pagination = null;
        $query = VoucherGoods::find()->alias('vg')
                 ->leftJoin("{{%goods}} g", "g.id=vg.goods_id")
                 ->leftJoin("{{%goods_warehouse}} gw", "gw.id=g.goods_warehouse_id");
        if (!empty($this->keyword)) {
            $query->andWhere([
                'or',
                ['LIKE', 'g.id', $this->keyword],
                ['LIKE', 'gw.name', $this->keyword]
            ]);
        }

        $orderBy = null;
        if(!empty($this->sort_prop)){
            $this->sort_type = (int)$this->sort_type;
            if($this->sort_prop == "goods_id"){
                $orderBy = "vg.goods_id " . (!$this->sort_type ? "DESC" : "ASC");
            }elseif($this->sort_prop == "goods_name"){
                $orderBy = "gw.name " . (!$this->sort_type? "DESC" : "ASC");
            }
        }

        if(empty($orderBy)){
            $orderBy = "vg.id " . (!$this->sort_type   ? "DESC" : "ASC");
        }

        $query->orderBy($orderBy);

        $select = ["vg.id", "vg.goods_id", "gw.name", "gw.cover_pic", "vg.created_at",
             "vg.updated_at"];
        $list = $query->select($select)->asArray()->page($pagination)->all();

        return $this->returnApiResultData(ApiCode::CODE_SUCCESS, '', [
            'list' => $list ?: [],
            'pagination' => $pagination,
        ]);
    }

}
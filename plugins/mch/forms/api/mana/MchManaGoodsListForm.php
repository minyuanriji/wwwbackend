<?php

namespace app\plugins\mch\forms\api\mana;

use app\core\ApiCode;
use app\forms\api\APICacheDataForm;
use app\forms\api\ICacheForm;
use app\models\BaseModel;
use app\models\Goods;
use app\models\GoodsWarehouse;
use function Webmozart\Assert\Tests\StaticAnalysis\integer;

class MchManaGoodsListForm extends BaseModel implements ICacheForm {

    public $page;
    public $mch_id;

    public function rules(){
        return [
            [['mch_id'], 'required'],
            [['page'], 'default', 'value' => 1]
        ];
    }

    /**
     * @return APICacheDataForm
     */
    public function getSourceDataForm(){
        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {

            $query = Goods::find()->alias('g');
            $query->innerJoin(["gw" => GoodsWarehouse::tableName()], "gw.id=g.goods_warehouse_id");
            $query->andWhere([
                'g.is_delete'  => 0,
                'g.is_recycle' => 0,
                'g.status'     => 1,
                'g.mch_id'     => $this->mch_id
            ]);
            $selects = ["g.id", "g.goods_warehouse_id", "gw.name", "gw.cover_pic", "gw.original_price", "g.price", "gw.unit", "g.goods_stock"];
            $query->select($selects);
            $list = $query->orderBy(['g.sort' => SORT_ASC, 'g.id' => SORT_DESC])
                          ->groupBy('g.goods_warehouse_id')->page($pagination, 10, $this->page)->asArray()->all();
            if($list){
                foreach($list as &$item){

                }
            }
            return new APICacheDataForm([
                "sourceData" => [
                    'code' => ApiCode::CODE_SUCCESS,
                    'data' => [
                        'list' => $list ? $list : [],
                        'pagination' => $pagination,
                    ]
                ]
            ]);
        }catch (\Exception $e){
            return ['code' => ApiCode::CODE_FAIL, 'msg' => $e->getMessage()];
        }
    }

    /**
     * @return array
     */
    public function getCacheKey(){
        $keys = [$this->mch_id, max(1, intval($this->page))];
        return $keys;
    }
}
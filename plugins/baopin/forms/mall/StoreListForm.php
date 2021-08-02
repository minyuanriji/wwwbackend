<?php
namespace app\plugins\baopin\forms\mall;


use app\core\ApiCode;
use app\models\BaseModel;
use app\models\Store;
use app\plugins\baopin\models\BaopinMchGoods;

class StoreListForm  extends BaseModel{

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

        $query = Store::find()->alias("s");
        $query->leftJoin(["bmg" => BaopinMchGoods::tableName()], "bmg.store_id=s.id");
        $query->groupBy("s.id");

        if (!empty($this->keyword)) {
            $query->andWhere([
                'or',
                ["s.id" => (int)$this->keyword],
                ["s.mch_id" => (int)$this->keyword],
                ['LIKE', 's.name', $this->keyword]
            ]);
        }

        $select = ["s.*", "count(bmg.store_id) as number"];

        $orderBy = null;
        if(!empty($this->sort_prop)){
            $this->sort_type = (int)$this->sort_type;
            if($this->sort_prop == "id"){
                $orderBy = "s.id " . (!$this->sort_type ? "DESC" : "ASC");
            }
        }

        if(empty($orderBy)){
            $orderBy = "number " . (!$this->sort_type   ? "DESC" : "ASC");
        }

        $query->orderBy($orderBy);

        $list = $query->select($select)->asArray()->page($pagination)->all();
        if($list){
            foreach($list as &$item){
                $item['number'] = (int)$item['number'];
            }
        }

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'list'       => $list ? $list : [],
                'pagination' => $pagination,
            ]
        ];
    }
}
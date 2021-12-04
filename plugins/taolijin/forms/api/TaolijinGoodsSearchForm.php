<?php
namespace app\plugins\taolijin\forms\api;

use app\core\ApiCode;
use app\core\BasePagination;
use app\forms\api\APICacheDataForm;
use app\forms\api\ICacheForm;
use app\models\BaseModel;
use app\plugins\taolijin\forms\common\AliAccForm;
use app\plugins\taolijin\models\TaolijinGoods;
use app\plugins\taolijin\models\TaolijinGoodsCatRelation;
use lin010\taolijin\Ali;

class TaolijinGoodsSearchForm extends BaseModel implements ICacheForm {

    public $page;
    public $cat_id;

    public function rules(){
        return [
            [['cat_id'], 'required'],
            [['page', 'cat_id'], 'integer']
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

            /*$query = TaolijinGoods::find()->alias("g")->where(["g.is_delete" => 0, "g.status" => 1]);
            if($this->cat_id){
                $query->innerJoin(["gr" => TaolijinGoodsCatRelation::tableName()], "gr.goods_id=g.id");
                $query->andWhere([
                    "AND",
                    ["gr.cat_id" => $this->cat_id],
                    ["gr.is_delete" => 0]
                ]);
            }

            $selects = ["g.id", "g.deduct_integral", "g.price", "g.name", "g.cover_pic", "g.unit", "g.gift_price", "g.ali_type", "g.virtual_sales"];

            $list = $query->orderBy("g.id DESC")->select($selects)->asArray()->page($pagination, 20, $this->page)->all();

            if($list){
                $aliTexts = ["jd" => "京东", "ali" => "阿里巴巴"];
                foreach($list as &$item){
                    $item['sales'] = sprintf("已售%s%s", 0 + $item['virtual_sales'], $item['unit']);
                    unset($item['virtual_sales']);
                    $item['ali_text'] = isset($aliTexts[$item['ali_type']]) ? $aliTexts[$item['ali_type']] : "";
                }
            }*/

            $pageSize = 12;

            $acc = AliAccForm::get("ali");

            $ali = new Ali($acc->app_key, $acc->secret_key);
            $res = $ali->material->search([
                "page_size"   => (string)$pageSize,
                "page_no"     => (string)$this->page,
                "adzone_id"   => $acc->adzone_id,
                "sort"        => "total_sales_desc",
                "material_id" => "6268",
                "q"           => "玩具",
                "cat"         => ""
            ]);
            if(!empty($res->code)){
                throw new \Exception($res->msg);
            }
            $data = $res->getData();

            $pagination = new BasePagination(['totalCount' => $data['count'], 'pageSize' => $pageSize, 'page' => $this->page]);

            return new APICacheDataForm([
                "sourceData" => [
                    'code' => ApiCode::CODE_SUCCESS,
                    'msg' => '请求成功',
                    'data' => [
                        'list'       => $this->onlySales($ali, $data['list']),
                        'pagination' => $pagination,
                    ]
                ]
            ]);
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }
    }

    /**
     * 过滤掉非营销主推商品
     * @param Ali $acc
     * @param $list
     */
    private function onlySales(Ali $ali, $list){
        if(!$list) return [];

        $num_iids = [];
        foreach($list as $item){
            $num_iids[] = $item['item_id'];
        }
        $res = $ali->item->infoGet([
            "num_iids" => implode(",", $num_iids)
        ]);
        if(!empty($res->code)){
            throw new \Exception($res->msg);
        }

        $allowItemIds = [];
        $results = $res->getResult();
        foreach($results as $result){
            if(isset($result['material_lib_type'])){
                $types = explode(",", $result['material_lib_type']);
                if(in_array(1, $types)){
                    $allowItemIds[] = $result['num_iid'];
                }
            }
        }
        $newList = [];
        foreach($list as $item){
            if(in_array($item['item_id'], $allowItemIds)){
                $newList[] = [
                    'item_id'       => $item['item_id'],
                    'url'           => $item['url'],
                    'pict_url'      => $item['pict_url'],
                    'volume'        => $item['volume'],
                    'reserve_price' => $item['reserve_price']
                ];
            }
        }
        return $newList;
    }

    /**
     * @return array
     */
    public function getCacheKey(){
        return [$this->page, $this->cat_id, $this->is_login, $this->login_uid];
    }
}
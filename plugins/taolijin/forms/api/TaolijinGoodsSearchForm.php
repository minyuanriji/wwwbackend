<?php
namespace app\plugins\taolijin\forms\api;

use app\core\ApiCode;
use app\core\BasePagination;
use app\forms\api\APICacheDataForm;
use app\forms\api\ICacheForm;
use app\models\BaseModel;
use app\plugins\taolijin\forms\common\AliAccForm;
use app\plugins\taolijin\models\TaolijinCats;
use app\plugins\taolijin\models\TaolijinGoods;
use app\plugins\taolijin\models\TaolijinGoodsCatRelation;
use app\plugins\taolijin\models\TaolijinUserAliBind;
use lin010\taolijin\Ali;

class TaolijinGoodsSearchForm extends BaseModel implements ICacheForm {

    public $page;
    public $cat_id;

    public function rules(){
        return [
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

            return new APICacheDataForm([
                "sourceData" => [
                    'code' => ApiCode::CODE_SUCCESS,
                    'data' => $this->searchAli()
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
     * 搜索淘宝联盟
     * @return array
     * @throws \Exception
     */
    private function searchAli(){
        $searchOption = [];

        $catModel = $this->cat_id ? TaolijinCats::findOne($this->cat_id) : null;
        if($catModel){
            if(!$catModel){
                throw new \Exception("类目不存在");
            }

            $params = $catModel->getParams();

            if(!empty($params['material_id']))   $searchOption['material_id'] = $params['material_id'];
            if(!empty($params['q']))             $searchOption['q'] = $params['q'];
            if(!empty($params['start_tk_rate'])) $searchOption['start_tk_rate'] = $params['start_tk_rate'];
            if(!empty($params['end_tk_rate']))   $searchOption['end_tk_rate'] = $params['end_tk_rate'];
        }

        $pageSize = 12;

        $acc = AliAccForm::get("ali");
        $inviteCode = $acc->getAliInviteCode();
        if(empty($inviteCode)){
            throw new \Exception("联盟邀请码未生成！请联系客服进行处理");
        }

        $returnData = [
            'ali_id'        => $acc->id,
            'ali_type'      => 'ali',
            'no_special_id' => 0,
            'list'          => null,
            'pagination'    => null,
        ];

        //私域用户关系获取
        $bindData = TaolijinUserAliBind::findOne([
            "ali_id"      => $acc->id,
            "user_id"     => $this->login_uid,
            "invite_code" => $inviteCode
        ]);
        if($bindData){
            $ali = new Ali($acc->app_key, $acc->secret_key);
            $res = $ali->material->search(array_merge($searchOption, [
                "page_size"   => (string)$pageSize,
                "page_no"     => (string)$this->page,
                "adzone_id"   => $acc->adzone_id,
                "special_id"  => $bindData->special_id
            ]));
            if(!empty($res->code)){
                throw new \Exception($res->msg);
            }
            $data = $res->getData();

            $pagination = new BasePagination(['totalCount' => $data['count'], 'pageSize' => $pageSize, 'page' => $this->page]);

            $returnData['list']       = $this->aliOnlySales($ali, $data['list']);
            $returnData['pagination'] = $pagination;
        }else{
            $returnData['no_special_id'] = 1;
        }

        return $returnData;
    }

    /**
     * 过滤掉非淘宝联盟营销主推商品
     * @param Ali $acc
     * @param $list
     */
    private function aliOnlySales(Ali $ali, $list){
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
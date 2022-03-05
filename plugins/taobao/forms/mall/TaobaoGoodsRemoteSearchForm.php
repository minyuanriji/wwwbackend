<?php

namespace app\plugins\taobao\forms\mall;

use app\core\ApiCode;
use app\core\BasePagination;
use app\models\BaseModel;
use app\plugins\taobao\models\TaobaoAccount;
use lin010\taolijin\Ali;
use lin010\taolijin\ali\taobao\tbk\material\TbkDgMaterialOptionalResponse;

class TaobaoGoodsRemoteSearchForm extends BaseModel {

    public $page;
    public $limit = 10;
    public $account_id;
    public $keyword;

    public function rules(){
        return [
            [['account_id'], 'required'],
            [['page', 'limit'], 'integer'],
            [['keyword'], 'trim']
        ];
    }

    public function search(){

        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        try {

            $account = TaobaoAccount::findOne($this->account_id);
            if(!$account || $account->is_delete){
                throw new \Exception("应用账号不存在");
            }

            $searchOption['q'] = !empty($this->keyword) ? $this->keyword : "衣服";

            $ali = new Ali($account->app_key, $account->app_secret);
            $res = $ali->material->search(array_merge($searchOption, [
                "page_size"   => (string)$this->limit,
                "page_no"     => (string)$this->page,
                "adzone_id"   => $account->adzone_id,
                "special_id"  => $account->special_id,
                "has_coupon"  => "true"
            ]));
            if(!$res || !($res instanceof TbkDgMaterialOptionalResponse)){
                throw new \Exception("淘宝联盟接口请求失败");
            }

            if($res->code){
                throw new \Exception($res->msg);
            }

            $data = $res->getData();

            $pagination = new BasePagination(['totalCount' => $data['count'], 'pageSize' => $this->limit, 'page' => $this->page - 1]);
            $list = $this->aliOnlySales($ali, $data['list']);

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'list' => $list,
                    'pagination' => $pagination
                ]
            ];
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage(),
                'error' => [
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]
            ];
        }

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
                $types = !is_array($result['material_lib_type']) ? explode(",", $result['material_lib_type']) : $result['material_lib_type'];
                if(in_array(1, $types)){
                    $allowItemIds[] = $result['num_iid'];
                }
            }
        }
        $newList = [];
        foreach($list as $item){
            if(in_array($item['item_id'], $allowItemIds)){
                $newList[] = $item;
            }
        }
        return $newList;
    }
}
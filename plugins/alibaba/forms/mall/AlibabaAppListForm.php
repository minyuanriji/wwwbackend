<?php

namespace app\plugins\alibaba\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\alibaba\models\AlibabaApp;
use app\plugins\alibaba\models\AlibabaDistributionGoodsCategory;
use app\plugins\alibaba\models\AlibabaDistributionGoodsList;

class AlibabaAppListForm extends BaseModel{

    public $page;
    public $id;

    public function rules(){
        return [
            [['page', 'id'], 'integer']
        ];
    }

    public function getList(){
        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {

            $categoryNum = (int)AlibabaDistributionGoodsCategory::find()->where([
                "is_delete" => 0
            ])->count();

            $query = AlibabaApp::find()->where(["is_delete" => 0]);

            if($this->id){
                $query->andWhere(["id" => $this->id]);
            }

            $orderBy = "id DESC";
            $query->orderBy($orderBy);

            $list = $query->asArray()->page($pagination, 20, $this->page)->all();
            if($list){
                foreach($list as &$item){
                    $item['token_info'] = !empty($item['token_info']) ? @json_decode($item['token_info'], true) : [];
                    $item['is_access_token_expired'] = !empty($item['access_token']) && $item['token_expired_at'] < time() ? true : false;
                    $item['is_refresh_token_expired'] = !empty($item['refresh_token']) && $item['refresh_expired_at'] < time() ? true : false;
                    $item['category_num'] = $categoryNum;
                    $item['goods_num'] = (int)AlibabaDistributionGoodsList::find()->where([
                        "is_delete" => 0,
                        "app_id" => $item['id']
                    ])->count();
                }
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'list' => $list ? $list : [],
                    'pagination' => $pagination
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
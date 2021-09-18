<?php

namespace app\plugins\alibaba\forms\mall;

use app\core\ApiCode;
use app\core\BasePagination;
use app\models\BaseModel;
use app\plugins\alibaba\models\AlibabaApp;
use lin010\alibaba\c2b2b\api\GetGoodsList;
use lin010\alibaba\c2b2b\api\GetGoodsListForUserChoosed;
use lin010\alibaba\c2b2b\api\GetGoodsListForUserChoosedResponse;
use lin010\alibaba\c2b2b\api\GetGoodsListResponse;
use lin010\alibaba\c2b2b\Distribution;

class AlibabaDistributionAlibabaGoodsGearchForm extends BaseModel{

    public $page;
    public $app_id;
    public $biztype;
    public $keyword;
    public $offerIds;

    public function rules(){
        return [
            [['app_id'], 'required'],
            [['page', 'app_id'], 'integer'],
            [['biztype', 'keyword', 'offerIds'], 'safe']
        ];
    }

    public function getList(){

        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        try {

            $pageSize = 10;

            $app = AlibabaApp::findOne($this->app_id);
            if(!$app || $app->is_delete){
                throw new \Exception("应用不存在");
            }
            $distribution = new Distribution($app->app_key, $app->secret);

            $options = [];
            if($this->biztype){
                $options['biztype'] = $this->biztype;
            }
            if($this->keyword){
                $options['keyWords'] = $this->keyword;
            }
            if($this->offerIds){
                $options['offerIds'] = $this->offerIds;
            }

            $res = $distribution->requestWithToken(new GetGoodsList(array_merge([
                "page" => $this->page,
                "pageSize" => $pageSize
            ], $options)), $app->access_token);

            if(!$res instanceof GetGoodsListResponse){
                throw new \Exception("返回结果异常");
            }
            if($res->error){
                throw new \Exception($res->error);
            }

            $list = $res->goodsList;
            if($list){
                foreach($list as &$item){
                    $item['enable'] = (int)$item['enable'];
                    $item['channelPrice'] = isset($item['channelPrice']) ? $item['channelPrice'] : '-';
                    $item['superBuyerPrice'] = isset($item['superBuyerPrice']) ? $item['superBuyerPrice'] : '-';
                }
            }

            $pagination = new BasePagination(['totalCount' => $res->totalCount, 'pageSize' => $pageSize, 'page' => $this->page]);

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
                'msg'  => $e->getMessage()
            ];
        }

    }

}
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
    public $groupId;

    public function rules(){
        return [
            [['app_id'], 'required'],
            [['page', 'app_id', 'groupId'], 'integer'],
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

            $totalCount = 0;
            if($this->biztype && $this->biztype == "my"){ //个人选品库
                $res = $distribution->requestWithToken(new GetGoodsListForUserChoosed([
                    "pageNo"   => $this->page,
                    "pageSize" => $pageSize,
                    "groupId"  => $this->groupId,
                    "title"    => $this->keyword ? $this->keyword : ""
                ]), $app->access_token);
                if(!$res instanceof GetGoodsListForUserChoosedResponse){
                    throw new \Exception("[GetGoodsListForUserChoosedResponse]返回结果异常");
                }
                if($res->error){
                    throw new \Exception($res->error);
                }
                $totalCount = $res->totalCount;

                $offerIds = [];
                foreach($res->rows as $row){
                    $offerIds[] = $row['feedId'];
                }
                $options['offerIds'] = $offerIds ? implode(",", $offerIds) : "-1";
                $options['page'] = 1;
            }else{
                $options['page'] = $this->page;
                if($this->biztype){
                    $options['biztype'] = $this->biztype;
                }
                if($this->keyword){
                    $options['keyWords'] = $this->keyword;
                }
            }

            $res = $distribution->requestWithToken(new GetGoodsList(array_merge([
                "pageSize" => $pageSize
            ], $options)), $app->access_token);

            if(!$res instanceof GetGoodsListResponse){
                throw new \Exception("[GetGoodsListResponse]返回结果异常");
            }
            if($res->error){
                throw new \Exception($res->error);
            }

            $list = $res->goodsList;

            $totalCount = $totalCount ? $totalCount : $res->totalCount;

            if($list){
                foreach($list as &$item){
                    $item['enable'] = (int)$item['enable'];
                    $item['channelPrice'] = isset($item['channelPrice']) ? $item['channelPrice'] : '-';
                    $item['superBuyerPrice'] = isset($item['superBuyerPrice']) ? $item['superBuyerPrice'] : '-';
                }
            }

            $pagination = new BasePagination(['totalCount' => $totalCount, 'pageSize' => $pageSize, 'page' => $this->page]);

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
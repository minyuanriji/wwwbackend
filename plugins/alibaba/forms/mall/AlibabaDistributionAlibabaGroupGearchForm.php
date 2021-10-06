<?php

namespace app\plugins\alibaba\forms\mall;

use app\core\ApiCode;
use app\core\BasePagination;
use app\models\BaseModel;
use app\plugins\alibaba\models\AlibabaApp;
use lin010\alibaba\c2b2b\api\GetGroupListForUserChoosed;
use lin010\alibaba\c2b2b\api\GetGroupListForUserChoosedResponse;
use lin010\alibaba\c2b2b\Distribution;

class AlibabaDistributionAlibabaGroupGearchForm extends BaseModel{

    public $page;
    public $app_id;

    public function rules(){
        return [
            [['app_id'], 'required'],
            [['page', 'app_id'], 'integer']
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

            $res = $distribution->requestWithToken(new GetGroupListForUserChoosed([
                "pageNo"   => $this->page,
                "pageSize" => $pageSize
            ]), $app->access_token);
            if(!$res instanceof GetGroupListForUserChoosedResponse){
                throw new \Exception("[GetGroupListForUserChoosedResponse]返回结果异常");
            }
            if($res->error){
                throw new \Exception($res->error);
            }

            $list = $res->rows;
            $pagination = new BasePagination(['totalCount' => $res->totalCount, 'pageSize' => $pageSize, 'page' => $this->page]);

            if($list){
                foreach($list as &$item){
                    $item['createTime'] = preg_replace("/^(\d{4})(\d{2})(\d{2}).*/", "$1-$2-$3", $item['createTime']);
                }
            }

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
<?php

namespace app\plugins\taobao\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\taobao\models\TaobaoAccount;
use lin010\taolijin\Ali;
use lin010\taolijin\ali\taobao\tbk\publisher\TbkScPublisherInfoGetResponse;

class TaobaoPublisherListForm  extends BaseModel {


    public $account_id;
    public $session;

    public function rules(){
        return [
            [['account_id', 'session'], 'required']
        ];
    }


    public function getList(){

        try {

            $account = TaobaoAccount::findOne($this->account_id);
            if(!$account || $account->is_delete){
                throw new \Exception("应用账号不存在");
            }

            $ali = new Ali($account->app_key, $account->app_secret);
            $res = $ali->publisher->get($this->session, [
                "info_type"    => 2,
                "relation_app" => "common"
            ]);

            if(!$res || !($res instanceof TbkScPublisherInfoGetResponse)){
                throw new \Exception("淘宝联盟接口请求失败");
            }

            $result = $res->getResult();

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'list' => $result['list']
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
<?php

namespace app\plugins\addcredit\plateform\sdk\qyj_sdk;

use app\core\ApiCode;
use app\plugins\addcredit\forms\common\AccessToken;
use app\plugins\addcredit\models\AddcreditPlateforms;
use app\plugins\addcredit\forms\common\Request;
use app\plugins\addcredit\plateform\result\QueryResult;
use yii\base\BaseObject;

class QueryOrderAction extends BaseObject
{
    public $AddcreditOrder;

    /**
     * 第三方需要参数
     *  userid	            String	是	账户ID
        out_trade_nums	    String	是	商户订单号，多个用英文,分割
        sign	            String	是	签名

        返回结果：字段名	        类型	        描述
                order_number    string      系统订单号
                out_trade_num   string      商户订单号
                create_time     string      下单时间
                mobile          string      手机号
                product_id      string      产品ID
                state           string      充值状态：0充值中 ，1充值成功，2充值失败
     * */
    public function run ()
    {
        $QueryResult = new QueryResult();
        try {
            $AddcreditPlateformsInfo = AddcreditPlateforms::findOne($this->AddcreditOrder->plateform_id);
            if (!$AddcreditPlateformsInfo) {
                throw new \Exception("无法获取ADDCREDIT ID " . $this->AddcreditOrder->plateform_id . " 平台信息");
            }
            $plateforms_param = json_decode($AddcreditPlateformsInfo->json_param, true);
            $post_param = [
                'app_id'       => $plateforms_param['id'],
                'order_num'    => $this->AddcreditOrder->qyj_order_num,
                'sign'         => strtolower(md5($plateforms_param['id'] . "!@#" . $plateforms_param['secret_key']))
            ];
            $cacheKey = md5($this->params['orderInfo']['appId'] . $plateforms_param['secret_key']);
            $token = AccessToken::getAccessToken($cacheKey, $this->params['orderInfo']['appId'], $plateforms_param['secret_key']);
            if (!$token || $token['code'] == ApiCode::CODE_FAIL) {
                throw new \Exception($token['msg'] ?? '获取access_token失败');
            }
            $response = Request::execute(Config::ORDER_QUERY, $post_param, $token);
            $parseArray = json_decode($response, true);
            if (!isset($parseArray['code'])) {
                throw new \Exception("解析数据错误", ApiCode::CODE_FAIL);
            }

            if ($parseArray['code'] != Code::OVERALL_SITUATION_SUCCESS) {
                throw new \Exception($parseArray['msg']);
            }

            $QueryResult->code = QueryResult::CODE_SUCC;
            $QueryResult->response_content = $response;
            $QueryResult->request_data = json_encode($post_param);

        } catch (\Exception $e) {
            $QueryResult->code = QueryResult::CODE_FAIL;
            $QueryResult->message = $e->getMessage();
        }

        return $QueryResult;
    }


}
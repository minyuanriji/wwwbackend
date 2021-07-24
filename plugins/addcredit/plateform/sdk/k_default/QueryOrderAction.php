<?php

namespace app\plugins\addcredit\plateform\sdk\k_default;

use app\core\ApiCode;
use app\plugins\addcredit\models\AddcreditPlateforms;
use app\plugins\addcredit\forms\common\Request;
use app\plugins\addcredit\plateform\result\QueryResult;
use yii\base\BaseObject;

class QueryOrderAction extends BaseObject
{
    public $AddcreditOrder;

    public function run ()
    {
        $QueryResult = new QueryResult();

        try {
            $AddcreditPlateformsInfo = AddcreditPlateforms::findOne($this->AddcreditOrder->plateform_id);
            if (!$AddcreditPlateformsInfo) {
                throw new \Exception("无法获取ADDCREDIT ID " . $this->AddcreditOrder->plateform_id . " 平台信息", ApiCode::CODE_FAIL);
            }
            $plateforms_param = json_decode($AddcreditPlateformsInfo->json_param);
            $post_param = [
                'szAgentId'         => $plateforms_param->id,
                'szOrderId'         => $this->AddcreditOrder->order_no,
                'szVerifyString'    => md5("szAgentId=" . $plateforms_param->id . "&szOrderId=" . $this->AddcreditOrder->order_no . "&szKey=" . $plateforms_param->secret_key)
            ];
            $response = Request::execute(Config::ORDER_QUERY, $post_param);
            print_r($response);die;
            $parseArray = @json_decode($response, true);
            if (!isset($parseArray['code'])) {
                throw new \Exception("解析数据错误", ApiCode::CODE_FAIL);
            }

            if ($parseArray['nRtn'] != Code::QUERY_SUCCESS) {
                if (isset($parseArray['szRtnCode'])) {
                    throw new \Exception(Msg::QueryMsg()[$parseArray['nRtn']], ApiCode::CODE_FAIL);
                } else {
                    throw new \Exception("未知错误 " . $parseArray['nRtn'], ApiCode::CODE_FAIL);
                }
            }

            $QueryResult->code = QueryResult::CODE_SUCC;
            $QueryResult->response_content = $response;

        } catch (\Exception $e) {
            $QueryResult->code = QueryResult::CODE_FAIL;
            $QueryResult->message = $e->getMessage();
        }

        return $QueryResult;
    }


}
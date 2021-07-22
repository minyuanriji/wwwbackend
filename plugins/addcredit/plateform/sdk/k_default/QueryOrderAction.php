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
                'mchid'     => $plateforms_param['mch_id'],
                'orderid'   => $this->AddcreditOrder->id,
                'sign'      => md5($plateforms_param['mch_id'] . $this->AddcreditOrder->id . $plateforms_param[''])
            ];
            $response = Request::execute(Config::ORDER_QUERY, $post_param);
            $parseArray = @json_decode($response, true);
            if (!isset($parseArray['code'])) {
                throw new \Exception("解析数据错误", ApiCode::CODE_FAIL);
            }

            if ($parseArray['code'] != Code::QUERY_SUCCESS) {
                if (isset($parseArray['msg'])) {
                    throw new \Exception($parseArray['msg'] . " " . $parseArray['code'], ApiCode::CODE_FAIL);
                } else {
                    throw new \Exception("未知错误 " . $parseArray['code'], ApiCode::CODE_FAIL);
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
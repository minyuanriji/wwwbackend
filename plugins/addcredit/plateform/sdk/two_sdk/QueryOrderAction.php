<?php

namespace app\plugins\addcredit\plateform\sdk\two_sdk;

use app\core\ApiCode;
use app\plugins\addcredit\models\AddcreditPlateforms;
use app\plugins\addcredit\forms\common\Request;
use app\plugins\addcredit\plateform\result\QueryResult;
use yii\base\BaseObject;

class QueryOrderAction extends BaseObject
{
    public $AddcreditOrder;

    /**
     * 第三方需要参数
     *  mch_id	        String(16)	是	商户ID
        platform_no	    int	        是	所属平台        所属的供应商平台，如：20000 (话单)
        no	            String(32)	是	订单号码        用于关联话单订单号
        sign	        String	    是	签名          sign = md5(mch_id + platform_no + no + 商户密钥) (全部转大写)

        返回结果：字段名	    变量名	    必填	    类型	        示例值	    描述
                code	    返回状态码	true	int	        0	        非0表示错误
                message	    错误码消息	true	string	    Success	    返回数据的消息
                data	    返回数据	    true	int	        object	    返回话单数据模型，其中关键字段 status（订单状态）-8 待获取， 0 待付款 1 待入账 -1 已超时 2 付款中 3 已入账 4 已退款 -5 已失败 -6 释放中
     * */
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
                'mch_id'         => $plateforms_param->id,
                'platform_no'    => '',
                'no'             => $this->AddcreditOrder->order_no,
                'sign'           => strtoupper(md5($plateforms_param->id . '' . $this->AddcreditOrder->order_no . '')),
            ];
            $response = Request::execute(Config::ORDER_QUERY, $post_param);
            $parseArray = @json_decode($response, true);
            if (!isset($parseArray['code'])) {
                throw new \Exception("解析数据错误", ApiCode::CODE_FAIL);
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
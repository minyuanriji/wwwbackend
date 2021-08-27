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

    /* *
     *  参数名 类型 是否必填 参数说明
        mchid 字符串 是 商户 ID
        orderid 字符串 是 充值订单号
        sign 字符串 是 MD5 签名（参考查询签名）

        参数名 类型 参数说明
        code 字符串 100 表示成功，其他表示失败
        order_id 字符串 平台订单号
        out_order_id 字符串 官方订单号
        tel 字符串 充值手机号
        price 整数 充值金额
        pay_time 字符串 支付时间（2021-01-28 00:00:00）
        status 字符串 1 未支付 2 支付中 3 已支付 4 支付失败
        arrival 字符串 0未到账 1到账中 2已到账 3已退款
     * */
    public function run ()
    {
        $QueryResult = new QueryResult();

        try {
            $AddcreditPlateformsInfo = AddcreditPlateforms::findOne($this->AddcreditOrder->plateform_id);
            if (!$AddcreditPlateformsInfo) {
                throw new \Exception("无法获取ADDCREDIT ID " . $this->AddcreditOrder->plateform_id . " 平台信息");
            }
            $plateforms_param = json_decode($AddcreditPlateformsInfo->json_param);
            $post_param = [
                'mchid'   => $plateforms_param->id,
                'orderid' => $this->AddcreditOrder->order_no,
                'sign'    => md5($plateforms_param->id . $this->AddcreditOrder->order_no . $plateforms_param->secret_key)
            ];
            $response = Request::execute(Config::ORDER_QUERY, $post_param);
            $parseArray = @json_decode($response, true);
            if (!isset($parseArray['code'])) {
                throw new \Exception("解析数据错误");
            }
            if ($parseArray['code'] != Code::QUERY_SUCCESS) {
                throw new \Exception(Msg::QueryMsg()[$parseArray['code']]);
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
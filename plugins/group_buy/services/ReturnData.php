<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 文件描述
 * Author: xuyaoxiang
 * Date: 2020/9/12
 * Time: 9:52
 */

namespace app\plugins\group_buy\services;

use app\core\ApiCode;

trait ReturnData
{
    private $result;

    /**
     * 返回错误数据数组
     * @param array $model
     * @return array
     */
    protected function responseErrorInfo($model = [])
    {
        if (!$model) {
            $model = $this;
        }
        $msg = isset($model->errors) ? current($model->errors)[0] : '数据异常！';

        return [
            'code' => ApiCode::CODE_FAIL,
            'msg'  => $msg
        ];
    }

    /**
     * 返回错误数据文本信息
     * @param array $model
     * @return string
     */
    protected function responseErrorMsg($model = null)
    {
        if (!$model) {
            $model = $this;
        }
        $msg = isset($model->errors) ? current($model->errors)[0] : '数据异常！';
        return $msg;
    }


    /**
     * 组装返回的数组
     * @param $code
     * @param $msg
     * @param array $data
     * @return array
     */
    protected function returnApiResultData($code = 999, $msg = null, $data = [])
    {
        if ($code == 0) {
            if (empty($msg)) {
                $msg = "数据请求成功";
            }
        } else {
            if (empty($msg)) {
                $msg = "数据请求失败";
            }
        }
        $this->result["code"] = $code;
        $this->result["msg"]  = $msg;
        if (!empty($data)) {
            $this->result["data"] = $data;
        }
        //系统错误或数据验证错误
        if ($code == 999) {
            $this->result = $this->responseErrorInfo($data);
        }
        return $this->result;
    }
}
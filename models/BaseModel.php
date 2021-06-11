<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 基础model
 * Author: zal
 * Date: 2020-04-08
 * Time: 15:12
 */

namespace app\models;

use app\controllers\api\ApiController;
use app\core\ApiCode;

class BaseModel extends \yii\base\Model
{
    const YES = 1;
    const NO = 0;
    const MY_Mall_ID = 5;

    const IS_DELETE_YES = self::YES;
    const IS_DELETE_NO = self::NO;

    protected $sign = '';

    public $result = [];

    public $is_login     = 0;
    public $login_uid    = 0;
    public $base_mall_id = 0;
    public $is_front     = 0; //是否前端调用

    /**
     * 返回错误数据数组
     * @param array $model
     * @return array
     */
    public function responseErrorInfo($model = [])
    {
        if (!$model) {
            $model = $this;
        }
        $msg = isset($model->errors) ? current($model->errors)[0] : '数据异常！';

        return [
            'code' => ApiCode::CODE_FAIL,
            'msg' => $msg
        ];
    }

    /**
     * 返回错误数据文本信息
     * @param array $model
     * @return string
     */
    public function responseErrorMsg($model = null)
    {
        if (!$model) {
            $model = $this;
        }
        $msg = isset($model->errors) ? current($model->errors)[0] : '数据异常！';
        return $msg;
    }

    public function setSign($val)
    {
        $this->sign = $val;
        return $this;
    }

    /**
     * 组装返回的数组
     * @param $code
     * @param $msg
     * @param array $data
     * @return array
     */
    public function returnApiResultData($code = 999,$msg = null,$data = []){
        if($code == 0){
            if(empty($msg)){
                $msg = "数据请求成功";
            }
        }else{
            if(empty($msg)){
                $msg = "数据请求失败";
            }
        }
        $this->result["code"]  = $code;
        $this->result["msg"] = $msg;
        if(!empty($data)){
            $this->result["data"] = $data;
        }
        //系统错误或数据验证错误
        if($code == 999){
            $this->result = $this->responseErrorInfo($data);
        }

        $this->result['city_data'] = ApiController::$cityData;

        return $this->result;
    }

    /**
     * 获取分页数据
     * @param $pagination
     * @return array
     */
    public function getPaginationInfo($pagination){
        $pageData = [];
        $pageData["total_count"] = $pagination->total_count;
        $pageData["page_count"] = $pagination->page_count;
        $pageData["pageSize"] = $pagination->pageSize;
        $pageData["current_page"] = $pagination->current_page;
        return $pageData;
    }
}

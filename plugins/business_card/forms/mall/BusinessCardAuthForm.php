<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: zal
 * Date: 2020-07-28
 * Time: 19:30
 */

namespace app\plugins\business_card\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\models\User;
use app\plugins\business_card\cache\BusinessCardCache;
use app\plugins\business_card\models\BusinessCard;
use app\plugins\business_card\models\BusinessCardAuth;
use app\plugins\business_card\models\BusinessCardPosition;
use app\plugins\business_card\models\BusinessCardRole;
use app\plugins\business_card\models\BusinessCardSetting;

class BusinessCardAuthForm extends BaseModel
{

    public $keywords;
    public $limit = 20;
    public $page = 1;

    public $department_id;
    public $form_data;

    public function rules()
    {
        return [
            [['limit','page','department_id'], 'integer'],
            [['keywords'], 'string'],
            [['keywords'], 'trim'],
        ];
    }

    /**
     * 获取列表
     * @return array
     */
    public function getList()
    {
        $params = $returnData = [];
        $params["mall_id"] = \Yii::$app->mall->id;
        if(!empty($this->keywords)){
            $params["keywords"] = $this->keywords;
        }
        $params["page"] = $this->page;
        $params["limit"] = $this->limit;
        $fields = ['id','head_img','user_id','full_name','position_id','department_id','email','mobile','wechat_qrcode','company_name'];
        $list = BusinessCard::getData($params,$fields);
        $businessCardSetting = BusinessCardSetting::getData(\Yii::$app->mall->id);
        foreach ($list["list"] as $value){
            if($value["user_id"] != \Yii::$app->user->id){
                $value["department_name"] = isset($value["department"]["name"]) ? $value["department"]["name"] : "";
                $value["position_name"] = isset($value["position"]["name"]) ? $value["position"]["name"] : "";
                $value["company_name"] = isset($businessCardSetting[BusinessCardSetting::COMPANY_NAME]) ? $businessCardSetting[BusinessCardSetting::COMPANY_NAME] : "";
                $value["company_address"] = isset($businessCardSetting[BusinessCardSetting::COMPANY_ADDRESS]) ? $businessCardSetting[BusinessCardSetting::COMPANY_ADDRESS] : "";
                if(isset($value["department"])){
                    unset($value["department"]);
                }
                if(isset($value["position"])){
                    unset($value["position"]);
                }
                $returnData[] = $value;
            }
        }
        $list["list"] = $returnData;
        return $this->returnApiResultData(ApiCode::CODE_SUCCESS, "", $list);
    }

    public function save(){
        $params = $this->form_data;
        if(!isset($params["department_id"]) || !isset($params["role_id"]) || !isset($params["position_id"]) || !isset($params["user_ids"])){
            return $this->returnApiResultData(ApiCode::CODE_FAIL, "缺少参数");
        }
        if(empty($params["role_id"])){
            return $this->returnApiResultData(ApiCode::CODE_FAIL, "请选择角色");
        }
        if(empty($params["position_id"])){
            return $this->returnApiResultData(ApiCode::CODE_FAIL, "请选择职位");
        }
        $mall_id = \Yii::$app->mall->id;
        $returnData = $data = [];
        foreach ($params["user_ids"] as $key=>$value){
            $data["mall_id"] = $mall_id;
            $data["user_id"] = $value;
            $data["department_id"] = $params["department_id"];
            $data["position_id"] = $params["position_id"];
            $data["role_id"] = $params["role_id"];
            $data["created_at"] = time();
            $selectParams = $data;
            unset($selectParams["role_id"]);
            $result = BusinessCardAuth::getData($selectParams);
            if(!empty($result)){
                return $this->returnApiResultData(ApiCode::CODE_FAIL, "有员工已添加过权限");
            }
            $returnData[] = $data;
        }
        $businessCardAuthModel = new BusinessCardAuth();
        $result = $businessCardAuthModel->batchAdd($returnData);
        $code = ApiCode::CODE_FAIL;
        $msg = "添加失败";
        if($result !== false){
            $code = ApiCode::CODE_SUCCESS;
            $msg = "添加成功";
        }
        return [
            'code' => $code,
            'msg' => $msg,
            'data' => [
            ]
        ];
    }

    public function edit(){
        $params = $this->form_data;
        if(!isset($params["department_id"]) || !isset($params["role_id"]) || !isset($params["position_id"])){
            return $this->returnApiResultData(ApiCode::CODE_FAIL, "缺少参数");
        }
        $mall_id = \Yii::$app->mall->id;
        $data = [];
        $data["id"] = $params["id"];
        $data["mall_id"] = $mall_id;
        $data["department_id"] = $params["department_id"];
        $data["position_id"] = $params["position_id"];
        $data["role_id"] = $params["role_id"];
        $result = BusinessCardAuth::operateData($data);
        $code = ApiCode::CODE_FAIL;
        $msg = "添加失败";
        if($result !== false){
            $code = ApiCode::CODE_SUCCESS;
            $msg = "添加成功";
        }
        return [
            'code' => $code,
            'msg' => $msg,
            'data' => [
            ]
        ];
    }

    /**
     * 用户列表
     * @return array
     */
    public function getUser()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }
        $params = $authUserIds = [];
        //获取该部门下已经存在的用户id
        $params["department_id"] = $this->department_id;
        $authList = BusinessCardAuth::getData($params,["user_id"]);
        if(!empty($authList)){
            foreach ($authList as $value){
                $authUserIds[] = $value["user_id"];
            }
        }
        $list = User::find()->alias('u')
                ->where(['u.is_delete' => 0, 'u.mall_id' => \Yii::$app->mall->id,])
                ->keyword($this->keywords !== '', ['like', 'u.nickname', $this->keywords])
                ->andWhere(["not in","id",$authUserIds])
            ->page($pagination, $this->limit, $this->page)->select('u.id,u.nickname')->all();
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '',
            'data' => [
                'list' => $list,
                "pagination" => $this->getPaginationInfo($pagination)
            ]
        ];
    }

    /**
     * 获取指定部门下的职位列表
     * @return array
     */
    public function getPositionList(){
        $params = [];
        $params["bcpid"] = $this->department_id;
        $list = BusinessCardPosition::getData($params);
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '',
            'data' => [
                'list' => $list,
            ]
        ];
    }

    /**
     * 用户列表
     * @return array
     */
    public function getAuthUserList()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }
        $params = [];
        $params["department_id"] = $this->department_id;
        $params["user"] = 1;
        $params["position"] = 1;
        $params["department"] = 1;
        $params["page"] = $this->page;
        $params["limit"] = $this->limit;
        $list = BusinessCardAuth::getData($params);
        if(!empty($list["list"])){
            foreach ($list["list"] as &$value){
                $roleId = $value["role_id"];
                $roles = BusinessCardRole::getInfo($roleId,$value["mall_id"]);
                $value["role_name"] = $roles->name;
                $value["created_at"] = date("Y-m-d H:i:s",$value["created_at"]);
            }
        }

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '',
            'data' => [
                'list' => $list["list"],
                "pagination" => $this->getPaginationInfo($list["pagination"])
            ]
        ];
    }

}
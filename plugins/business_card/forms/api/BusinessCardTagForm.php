<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 分销佣金接口处理类
 * Author: zal
 * Date: 2020-05-26
 * Time: 10:30
 */

namespace app\plugins\business_card\forms\api;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\business_card\forms\common\BusinessCardTrackLogCommon;
use app\plugins\business_card\forms\common\Common;
use app\plugins\business_card\models\BusinessCard;
use app\plugins\business_card\models\BusinessCardTag;
use app\plugins\business_card\models\BusinessCardTrackLog;

class BusinessCardTagForm extends BaseModel
{

    public $id = 0;
    public $bcid;
    public $name;
    public $user_id;
    public $num;

    public function rules()
    {
        return [
            [['id','bcid','num'], 'integer'],
            [['name'], 'string'],
            [['name'], 'trim'],
        ];
    }

    /**
     * 添加标签
     * @return array
     */
    public function add(){
        $data = [];
        if(empty($this->name) || empty($this->bcid)){
            return $this->returnApiResultData(ApiCode::CODE_FAIL,"缺少参数");
        }
        $total = BusinessCardTag::getCount(["bcid" => $this->bcid,"count" => 1]);
        if($total > 10){
            return $this->returnApiResultData(ApiCode::CODE_FAIL,"最多添加10个自定义标签");
        }
        //检测名字是否重复
        $result = BusinessCardTag::getData(["bcid" => $this->bcid,"name" => $this->name,"is_diy" => BusinessCardTag::IS_DIY_YES],['id']);
        if(!empty($result)){
            return $this->returnApiResultData(ApiCode::CODE_FAIL,"标签名已经存在");
        }

        /** @var BusinessCard $businessCrad */
        $businessCrad = BusinessCard::getOneData($this->bcid);
        if(empty($businessCrad) || $businessCrad->is_delete == BusinessCard::YES){
            return $this->returnApiResultData(ApiCode::CODE_FAIL,"数据不存在");
        }

        $data["name"] = $this->name;
        $data["bcid"] = $this->bcid;
        $data["mall_id"] = \Yii::$app->mall->id;
        $data["user_id"] = $businessCrad->user_id;
        $data["add_user_id"] = \Yii::$app->user->id;
        $data["is_diy"] = BusinessCardTag::IS_DIY_YES;
        $result = BusinessCardTag::operateData($data);
        if ($result !== false) {
            return $this->returnApiResultData(ApiCode::CODE_SUCCESS,"创建成功");
        } else {
            return $this->returnApiResultData(999,"");
        }
    }

    /**
     * 删除标签
     * @return array
     */
    public function delTag(){
        $data = [];
        $data["id"] = $this->id;
        $data["is_delete"] = 1;
        $result = BusinessCardTag::operateData($data);
        if ($result !== false) {
            return $this->returnApiResultData(ApiCode::CODE_SUCCESS,"操作成功");
        } else {
            return $this->returnApiResultData(ApiCode::CODE_FAIL,"失败");
        }
    }

    /**
     * 点赞
     * @return array
     */
    public function like(){
        $data = [];
        $businessCardTag = BusinessCardTag::getData(["id" => $this->id]);
        if(empty($businessCardTag)){
            return $this->returnApiResultData(ApiCode::CODE_FAIL,"数据不存在");
        }
        //是否点赞
        $this->num = Common::checkIsLike(\Yii::$app->user->id,$this->id,"tag");
        $data["id"] = $this->id;
        $data["likes"] = $this->num;
        $msg = "点赞";
        if($this->num == -1){
            $msg = "取消点赞";
        }
        $result = BusinessCardTag::operateData($data);
        if ($result !== false) {
            //是否点赞，记录过轨迹
            $trackLog = BusinessCardTrackLogCommon::isExist($businessCardTag["user_id"],$businessCardTag["id"],BusinessCardTrackLog::TRACK_TYPE_LIKE_TAG);
            if($this->num == -1){
                BusinessCardTrackLog::operateData(["id" => $trackLog["id"],"is_delete" => BusinessCardTrackLog::IS_DELETE_YES]);
            }
            $isLike = !empty($trackLog) ? true : false;

            if(!$isLike){
                BusinessCardTrackLogCommon::addTrackLog($businessCardTag["user_id"],$this->id,BusinessCardTrackLog::TRACK_TYPE_LIKE_TAG);
            }
            return $this->returnApiResultData(ApiCode::CODE_SUCCESS,$msg."成功");
        } else {
            return $this->returnApiResultData(999,"");
        }
    }

}
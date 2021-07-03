<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-05-21
 * Time: 19:30
 */

namespace app\plugins\business_card\forms\mall;


use app\core\ApiCode;
use app\models\BaseModel;
use app\models\User;
use app\plugins\business_card\models\BusinessCard;
use app\plugins\business_card\models\BusinessCardSetting;
use app\plugins\business_card\models\BusinessCardTag;
use app\plugins\distribution\models\Distribution;

class BusinessCardForm extends BaseModel
{
    public $id;
    public $keywords;
    public $limit = 20;
    public $page = 1;

    public function rules()
    {
        return [
            [['limit','page','id'], 'integer'],
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
        $params["limit"] = $this->limit;
        $params["page"] = $this->page;
        $fields = ['id','head_img','user_id','full_name','position_id','department_id','email','mobile','wechat_qrcode','company_name'];
        $list = BusinessCard::getData($params,$fields);
        $businessCardSetting = BusinessCardSetting::getData(\Yii::$app->mall->id);
        foreach ($list["list"] as $value){
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
        $list["list"] = $returnData;
        $list["pagination"] = $this->getPaginationInfo($list["pagination"]);
        return $this->returnApiResultData(ApiCode::CODE_SUCCESS, "", $list);
    }

    public function delete()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        /* @var BusinessCard $businessCardModel */
        $businessCardModel = BusinessCard::find()->where(['id' => $this->id, 'is_delete' => 0, 'mall_id' => \Yii::$app->mall->id])->one();

        if (!$businessCardModel) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => '名片不存在'
            ];
        }

        $t = \Yii::$app->db->beginTransaction();
        try{
            $businessCardModel->is_delete = 1;
            if ($businessCardModel->save()) {
                $result = BusinessCardTag::updateAll(["is_delete" => BusinessCardTag::IS_DELETE_YES,"deleted_at" => time()],['bcid' => $this->id]);
                if ($result === false) {
                    throw new \Exception("删除失败！");
                }
            } else {
                throw new \Exception("删除失败");
            }
            $t->commit();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '删除成功'
            ];
        }catch (\Exception $ex){
            $t->rollBack();
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $ex->getMessage()
            ];
        }
    }

}
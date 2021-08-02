<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 职位操作
 * Author: zal
 * Date: 2020-07-09
 * Time: 14:53
 */

namespace app\plugins\business_card\forms\mall;

use app\core\ApiCode;
use app\logic\CommonLogic;
use app\models\BaseModel;
use app\plugins\business_card\models\BusinessCardPosition;

class BusinessCardPositionEditForm extends BaseModel
{
    public $id;
    public $name;
    public $sort;
    public $bcpid = 0;
    public $is_delete;

    public function rules()
    {
        return [
            [['name','bcpid'], 'required'],
            [['name'], 'trim'],
            [['id','bcpid','is_delete'], 'integer'],
            [['name'], 'string'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'name' => '职位名称',
            'bcpid' => '部门id',
            'is_delete' => 'Is delete',
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }
        if(empty($this->bcpid)){
            return $this->returnApiResultData(ApiCode::CODE_FAIL,"请选择部门");
        }
        try {
            $addData = $this->attributes;
            if(empty($this->id)){
                $department = BusinessCardPosition::getData(['is_delete' => 0, 'mall_id' => \Yii::$app->mall->id, 'name' => $this->name,"bcpid" => $this->bcpid]);
                if (!empty($department)) {
                    return $this->returnApiResultData(ApiCode::CODE_FAIL,"职位名称已经存在");
                }
                $addData["mall_id"] = \Yii::$app->mall->id;
            }
            $result = BusinessCardPosition::operateData($addData);
            if (!$result) {
                throw new \Exception($this->responseErrorMsg($result));
            } else {
                return $this->returnApiResultData(ApiCode::CODE_SUCCESS,"操作成功");
            }
        } catch (\Exception $exception) {
            return $this->returnApiResultData(ApiCode::CODE_FAIL,CommonLogic::getExceptionMessage($exception));
        }
    }

    public function getDetail()
    {
        $positionModel = BusinessCardPosition::getData(['is_delete' => 0, 'id' => $this->id]);
        if (empty($positionModel)) {
            return $this->returnApiResultData(ApiCode::CODE_FAIL,"职位不存在");
        }
        return $this->returnApiResultData(ApiCode::CODE_SUCCESS,"ok",["detail" => $positionModel]);
    }

}
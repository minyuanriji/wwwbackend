<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 部门操作
 * Author: zal
 * Date: 2020-07-09
 * Time: 14:53
 */

namespace app\plugins\business_card\forms\mall;

use app\core\ApiCode;
use app\helpers\SerializeHelper;
use app\logic\CommonLogic;
use app\models\BaseModel;
use app\plugins\business_card\models\BusinessCardDepartment;

class BusinessCardDepartmentEditForm extends BaseModel
{
    public $id;
    public $name;
    public $sort;
    public $pid = 0;
    public $is_delete;

    public function rules()
    {
        return [
            [['name'], 'required'],
            [['name'], 'trim'],
            [['id','pid','sort','is_delete'], 'integer'],
            [['name'], 'string'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'name' => '部门名称',
            'pid' => '父级id',
            'sort' => '排序',
            'is_delete' => 'Is delete',
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }
        try {
            $addData = $this->attributes;
            if(empty($this->id)){
                $department = BusinessCardDepartment::getData(['is_delete' => 0, 'mall_id' => \Yii::$app->mall->id, 'name' => $this->name,"pid" => $this->pid]);
                if (!empty($department)) {
                    return $this->returnApiResultData(ApiCode::CODE_FAIL,"部门名称已经存在");
                }
                $addData["mall_id"] = \Yii::$app->mall->id;
            }

            $result = BusinessCardDepartment::operateData($addData);
            if (!$result) {
                throw new \Exception($this->responseErrorMsg());
            } else {
                return $this->returnApiResultData(ApiCode::CODE_SUCCESS,"操作成功");
            }
        } catch (\Exception $exception) {
            return $this->returnApiResultData(ApiCode::CODE_FAIL,CommonLogic::getExceptionMessage($exception));
        }
    }

    public function getDetail()
    {
        $departmentModel = BusinessCardDepartment::getData(['is_delete' => 0, 'id' => $this->id]);
        if (empty($departmentModel)) {
            return $this->returnApiResultData(ApiCode::CODE_FAIL,"部门不存在");
        }
        return $this->returnApiResultData(ApiCode::CODE_SUCCESS,"ok",["detail" => $departmentModel]);
    }

}
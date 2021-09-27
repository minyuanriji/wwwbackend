<?php
/*
 * @link:http://www.@copyright: Copyright (c) @Author: Mr.Lin
 * @Email: 746027209@qq.com
 * @Date: 2021-07-06 14:13
 */

namespace app\plugins\alibaba\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\alibaba\models\AlibabaDistributionGoodsSku;

class AlibabaDistributionDeleteSkuForm extends BaseModel
{

    public $id;

    public function rules()
    {
        return [
            [['id'], 'required'],
            [['id'], 'integer']
        ];
    }

    public function delete()
    {

        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        try {
            $category = AlibabaDistributionGoodsSku::findOne($this->id);
            if (!$category)
                throw new \Exception("规格不存在");

            $category->is_delete = 1;
            $category->updated_at = time();
            if (!$category->save()) {
                throw new \Exception($this->responseErrorMsg($category));
            }

            return $this->returnApiResultData(ApiCode::CODE_SUCCESS, '删除成功');
        } catch (\Exception $e) {
            return $this->returnApiResultData(ApiCode::CODE_FAIL, $e->getMessage());
        }

    }
}
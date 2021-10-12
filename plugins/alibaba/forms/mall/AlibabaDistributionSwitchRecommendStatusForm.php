<?php

namespace app\plugins\alibaba\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\alibaba\models\AlibabaDistributionGoodsList;
use app\plugins\alibaba\models\AlibabaDistributionGoodsSku;

class AlibabaDistributionSwitchRecommendStatusForm extends BaseModel
{

    public $id;

    public function rules()
    {
        return [
            [['id'], 'required'],
            [['id'], 'integer']
        ];
    }

    public function switch()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }
        try {
            $goods = AlibabaDistributionGoodsList::findOne(['id' => $this->id, 'is_delete' => 0]);
            if (!$goods)
                throw new \Exception('该商品已删除！');

            if ($goods->is_recommend) {
                $goods->is_recommend = 0;
            } else {
                $goods->is_recommend = 1;
            }
            if (!$goods->save()) {
                throw new \Exception($this->responseErrorMsg($goods));
            }
            return $this->returnApiResultData(ApiCode::CODE_SUCCESS);
        } catch (\Exception $e) {
            return $this->returnApiResultData(ApiCode::CODE_FAIL, $e->getMessage());
        }

    }
}
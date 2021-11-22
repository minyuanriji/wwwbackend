<?php

namespace app\plugins\alibaba\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\alibaba\models\AlibabaDistributionGoodsList;
use app\plugins\alibaba\models\AlibabaDistributionGoodsSku;
use app\plugins\alibaba\models\AlibabaDistributionGoodsWarn;

class AlibabaDistributionAbnormalTransactionForm extends BaseModel
{
    public $page;
    public $keyword;
    public $status;// 1、已处理 0、未处理 -1、全部

    public function rules()
    {
        return [
            [['page', 'status'], 'integer'],
            [['keyword'], 'string'],
        ];
    }

    public function getList()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        try {

            $query = AlibabaDistributionGoodsWarn::find()->alias('ag')
                ->leftJoin(["agl" => AlibabaDistributionGoodsList::tableName()], "ag.goods_id = agl.id")
                ->leftJoin(["as" => AlibabaDistributionGoodsSku::tableName()], "as.id = ag.sku_id");

            if (!empty($this->keyword)) {
                $query->andWhere([
                    'or',
                    ["LIKE", "agl.name", $this->keyword],
                    ["LIKE", "agl.id", $this->keyword],
                ]);
            }

            if ($this->status >= 0) {
                $query->andWhere(['ag.flag' => $this->status]);
            }

            $select = ['ag.id', "ag.goods_id", "ag.created_at", 'ag.flag', "ag.sku_id", "ag.remark", "agl.name", "agl.price", "agl.cover_url", "as.name as sku_name"];

            $list = $query->select($select)->orderBy("ag.id DESC")->page($pagination, 10)->asArray()->all();

            return $this->returnApiResultData(
                ApiCode::CODE_SUCCESS,
                '',
                [
                    'list' => $list ?: [],
                    'pagination' => $pagination
                ]
            );
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $e->getMessage()
            ];
        }
    }
}
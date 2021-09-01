<?php

namespace app\plugins\shopping_voucher\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\models\Store;
use app\plugins\Shopping_voucher\models\VoucherMch;

class StoreListForm extends BaseModel
{

    public $page;
    public $keyword;
    public $sort_prop;
    public $sort_type;

    public function rules()
    {
        return array_merge(parent::rules(), [
            [['page'], 'integer'],
            [['keyword', 'sort_prop', 'sort_type'], 'safe']
        ]);
    }

    public function getList()
    {

        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        $query = VoucherMch::find()->alias('vm');
        $query->leftJoin(["s" => Store::tableName()], "vm.mch_id=s.mch_id");

        if (!empty($this->keyword)) {
            $query->andWhere([
                'or',
                ["s.id" => (int)$this->keyword],
                ["s.mch_id" => (int)$this->keyword],
                ['LIKE', 's.name', $this->keyword]
            ]);
        }

        $select = ["s.name", "s.cover_url", 'vm.ratio', 'vm.id', "vm.mch_id"];

        $orderBy = null;
        if (!empty($this->sort_prop)) {
            $this->sort_type = (int)$this->sort_type;
            if ($this->sort_prop == "id") {
                $orderBy = "s.id " . (!$this->sort_type ? "DESC" : "ASC");
            }
        }

        if (empty($orderBy)) {
            $orderBy = "s.id " . (!$this->sort_type ? "DESC" : "ASC");
        }

        $list = $query->select($select)
            ->andWhere(['vm.is_delete' => 0])
            ->orderBy($orderBy)
            ->page($pagination)
            ->asArray()
            ->all();

        return $this->returnApiResultData(ApiCode::CODE_SUCCESS, '', [
            'list' => $list ?: [],
            'pagination' => $pagination,
        ]);
    }
}
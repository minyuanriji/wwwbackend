<?php

namespace app\plugins\shopping_voucher\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\models\Store;

class MchListForm extends BaseModel
{
    public $keyword;

    public function rules()
    {
        return [
            [['keyword'], 'safe']
        ];
    }

    public function getList()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        $query = Store::find()->alias('s');

        if (!empty($this->keyword)) {
            $query->andWhere([
                'or',
                ["s.id" => (int)$this->keyword],
                ["s.mch_id" => (int)$this->keyword],
                ['LIKE', 's.name', $this->keyword]
            ]);
        }

        $select = ["id", "mch_id", "name"];

        $query->orderBy("id DESC");

        $list = $query->select($select)->asArray()->limit(10)->all();
        return $this->returnApiResultData(ApiCode::CODE_SUCCESS, '', [
            'list' => $list ?: [],
        ]);
    }
}
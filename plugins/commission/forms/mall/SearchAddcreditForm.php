<?php

namespace app\plugins\commission\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\models\Store;
use app\plugins\addcredit\models\AddcreditPlateforms;
use app\plugins\mch\models\Mch;

class SearchAddcreditForm extends BaseModel
{
    public $page;
    public $keyword;

    public function rules()
    {
        return array_merge(parent::rules(), [
            [['page'], 'integer'],
            [['keyword'], 'safe']
        ]);
    }

    public function search()
    {

        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        $query = AddcreditPlateforms::find();

        if (!empty($this->keyword)) {
            $query->andWhere([
                ["LIKE", "name", $this->keyword]
            ]);
        }

        $query->orderBy(['id' => SORT_DESC]);

        $select = ["id", "name"];

        $list = $query->select($select)->asArray()->page($pagination, 10, $this->page)->all();

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'list' => $list ? $list : [],
                'pagination' => $pagination,
            ]
        ];
    }
}
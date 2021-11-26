<?php

namespace app\plugins\mch\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\models\Store;
use app\plugins\mch\models\Mch;
use app\plugins\mch\models\MchGroupItem;

class MchGroupItemListForm extends BaseModel{

    public $group_id;
    public $keyword;
    public $keyword1;
    public $page;

    public function rules(){
        return [
            [['group_id'], 'required'],
            [['keyword', 'keyword1'], 'string'],
            [['page'], 'default', 'value' => 1],
        ];
    }

    public function getList(){

        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {

            $query = MchGroupItem::find()->alias("mgi")
                ->innerJoin(["m" => Mch::tableName()], "m.id=mgi.mch_id")
                ->innerJoin(["s" => Store::tableName()], "s.id=mgi.store_id")
                ->where(["mgi.group_id" => $this->group_id]);

            if(!empty($this->keyword)){
                $query->andWhere([
                    "OR",
                    ["m.id" => $this->keyword],
                    ["m.mobile" => $this->keyword],
                    ["LIKE", "s.name", $this->keyword]
                ]);
            }

            $query->select(["mgi.*", "m.mobile", "s.name", "s.cover_url"]);

            $list = $query->orderBy("mgi.id DESC")->page($pagination, 20, $this->page)->asArray()->all();

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'list'       => $list,
                    'pagination' => $pagination
                ]
            ];
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }
    }
}
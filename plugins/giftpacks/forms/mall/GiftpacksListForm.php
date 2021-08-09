<?php
namespace app\plugins\giftpacks\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\giftpacks\models\Giftpacks;

class GiftpacksListForm extends BaseModel{

    public $page;
    public $keyword;
    public $sort_prop;
    public $sort_type;

    public function rules(){
        return array_merge(parent::rules(), [
            [['page'], 'integer'],
            [['keyword', 'sort_prop', 'sort_type'], 'safe']
        ]);
    }

    public function getList() {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        try {

            $query = Giftpacks::find()->where(["is_delete" => 0]);

            if(!empty($this->keyword)){
                $query->andWhere(["LIKE", "title", $this->keyword]);
            }

            $orderBy = null;
            if(!empty($this->sort_prop)){

            }

            if(empty($orderBy)){
                $orderBy = "id " . (!$this->sort_type   ? "DESC" : "ASC");
            }

            $list = $query->orderBy($orderBy)->page($pagination, 20)->asArray()->all();
            if($list){
                foreach($list as &$item){
                    $item['expired_at'] = date("Y-m-d H:i:s", $item['expired_at']);
                    $item['group_expire_time'] = (int)($item['group_expire_time']/3600);
                }
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'list' => $list ? $list : [],
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
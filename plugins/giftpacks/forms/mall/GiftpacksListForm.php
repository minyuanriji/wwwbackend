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
                $scoreGiveSettings = [
                    "is_permanent" => 0,
                    "integral_num" => 0,
                    "period"       => 1,
                    "period_unit"  => "month",
                    "expire"       => 30
                ];
                foreach($list as &$item){
                    $item['pic_url'] = !empty($item['pic_url']) ? @json_decode($item['pic_url'], true) : [];
                    if(!is_array($item['pic_url'])){
                        $item['pic_url'] = [];
                    }
                    $item['expired_at'] = date("Y-m-d H:i:s", $item['expired_at']);
                    $item['group_expire_time'] = (int)($item['group_expire_time']/3600);
                    $item['score_give_settings'] = array_merge($scoreGiveSettings,
                            !empty($item['score_give_settings']) ? (array)@json_decode($item['score_give_settings']) : []);
                    $item['score_give_settings']['is_permanent'] = (int)$item['score_give_settings']['is_permanent'];
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
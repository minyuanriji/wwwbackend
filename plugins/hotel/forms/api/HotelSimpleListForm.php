<?php
namespace app\plugins\hotel\forms\api;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\hotel\forms\api\hotel_search\HotelSearchForm;
use app\plugins\hotel\models\Hotels;

class HotelSimpleListForm extends BaseModel{

    public $page;
    public $lng;
    public $lat;
    public $search_id;

    public function rules(){
        return [
            [['page'], 'integer'],
            [['lng', 'lat', 'search_id'], 'string']
        ];
    }

    public function getList(){

        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {

            $query = Hotels::find()->alias("ho")->where([
                "ho.is_delete"  => 0,
                "ho.is_open"    => 1,
                "ho.is_booking" => 1,
                "ho.mall_id"    => \Yii::$app->mall->id
            ]);

            $selects = ["ho.id", "ho.thumb_url", "ho.name", "ho.type", "ho.cmt_grade", "ho.cmt_num", "ho.price"];

            if(!empty($this->search_id)){
                $form = new HotelSearchForm();
                $foundHotelIds = $form->getFoundHotelIds($this->search_id);
                $query->andWhere(["IN", "id", $foundHotelIds ? $foundHotelIds : []]);
            }

            $rows = $query->select($selects)->page($pagination, 10, max(1, (int)$this->page))
                          ->asArray()->all();

            foreach($rows as &$row){
                $row['type_text'] = static::getTypeText($row['type']);
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'list'       => $rows ? $rows : [],
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

    private static function getTypeText($type){
        $typeTexts = ['luxe' => '豪华型', 'comfort' => '舒适型', 'eco' => '经济型'];
        return isset($typeTexts[$type]) ? $typeTexts[$type] : "";
    }
}
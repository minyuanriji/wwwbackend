<?php

namespace app\plugins\giftpacks\forms\api;

use app\core\ApiCode;
use app\helpers\CityHelper;
use app\models\BaseModel;
use app\models\Store;
use app\plugins\giftpacks\models\Giftpacks;
use app\plugins\giftpacks\models\GiftpacksItem;

class GiftpacksListForm extends BaseModel{

    public $page;
    public $city_id;
    public $district_id;
    public $keywords;

    public function rules(){
        return [
            [['page', 'city_id', 'district_id'], 'integer'],
            [['keywords'], 'string'],
        ];
    }

    public function getList(){

        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {

            $query = Giftpacks::find()->alias("gf")->where(["gf.is_delete" => 0])
                        ->leftJoin(['gi' => GiftpacksItem::tableName()], "gf.id=gi.pack_id")
                        ->leftJoin(['s' => Store::tableName()], "s.id=gi.store_id");

            $selects = ["gf.*"];
            $selects[] = "(IFNULL((select count(*) from {{%plugin_giftpacks_order}} where pay_status='paid' AND is_delete=0 AND pack_id=gf.id), 0) + IFNULL((select sum(user_num) from {{%plugin_giftpacks_group}} where status='sharing' AND pack_id=gf.id), 0)) as sold_num";

            if ($this->keywords) {
                $query->andWhere(['like', 'gf.title', $this->keywords]);
            }

            if ($this->district_id) {
                $query->andWhere(['s.district_id' => $this->district_id]);
                $cityInfo = CityHelper::reverseData($this->district_id);
                $cityName = $cityInfo['district']['name'] ?? '';
            } else {
                if ($this->city_id) {
                    $query->andWhere(['s.city_id' => $this->city_id]);
                    $cityInfo = CityHelper::reverseData(0, $this->city_id);
                    $cityName = $cityInfo['city']['name'] ?? '';
                }
            }

            $list = $query->select($selects)->page($pagination, 10, max(1, (int)$this->page))
                ->orderBy("gf.updated_at DESC")->asArray()->all();
            if($list){
                foreach($list as &$item){
                    $item['max_stock'] = (int)$item['max_stock'];
                    $item['item_num']  = (int)GiftpacksDetailForm::availableItemsQueryByPackId($item['id'])->count();
                    $item['sold_num']  += $item['id'] == 13 ? 1000 : 300;
                }
            }

            return $this->returnApiResultData(ApiCode::CODE_SUCCESS, '', [
                'list'       => $list ?: [],
                'pagination' => $pagination,
                'city_name'  => $cityName,
            ]);
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }

    }

}
<?php

namespace app\plugins\mch\forms\api\mana;

use app\core\ApiCode;
use app\helpers\CityHelper;
use app\models\BaseModel;
use app\models\Store;
use app\plugins\mch\controllers\api\mana\MchAdminController;
use app\plugins\mch\models\Mch;
use app\plugins\mch\models\MchGroup;
use app\plugins\mch\models\MchGroupItem;

class MchManaGroupItemListForm extends BaseModel{

    public $page;
    public $mch_id;

    public function rules(){
        return [
            [['mch_id'], 'integer'],
            [['page'], 'default', 'value' => 1]
        ];
    }

    public function getList(){

        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {

            $mchId = $this->mch_id ? $this->mch_id : MchAdminController::$adminUser['mch_id'];
            $mchGroup = MchGroup::findOne([
                "mch_id" => $mchId
            ]);
            if(!$mchGroup || $mchGroup->is_delete){
                throw new \Exception("商户[ID{$mchId}]非连锁总店");
            }

            $query = MchGroupItem::find()->alias("mgi")
                ->innerJoin(["m" => Mch::tableName()], "m.id=mgi.mch_id")
                ->innerJoin(["s" => Store::tableName()], "s.id=mgi.store_id")
                ->where(["mgi.group_id" => $mchGroup->id]);
            $query->andWhere("mgi.mch_id <> '{$mchId}'");

            $query->select(["mgi.*", "m.mobile", "s.name", "s.cover_url",
                "s.address", "s.province_id", "s.city_id", "s.district_id"
            ]);

            $list = $query->orderBy("mgi.id DESC")->page($pagination, 20, $this->page)->asArray()->all();
            if($list){
                foreach($list as &$item){

                    if(!preg_match("/^https?:\/\//i", trim($item['cover_url']))){
                        $item['cover_url'] =  $this->host_info . "/web/static/header-logo.png";
                    }

                    $city = CityHelper::reverseData($item['district_id'], $item['city_id'], $item['province_id']);
                    $item['province'] = !empty($city['province']['name']) ? $city['province']['name'] : "";
                    $item['city'] = !empty($city['city']['name']) ? $city['city']['name'] : "";
                    $item['district'] = !empty($city['district']['name']) ? $city['district']['name'] : "";
                }
            }

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
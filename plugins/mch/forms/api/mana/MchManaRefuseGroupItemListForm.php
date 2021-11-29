<?php

namespace app\plugins\mch\forms\api\mana;

use app\core\ApiCode;
use app\helpers\CityHelper;
use app\models\BaseModel;
use app\models\Store;
use app\plugins\mch\controllers\api\mana\MchAdminController;
use app\plugins\mch\models\Mch;
use app\plugins\mch\models\MchApply;
use app\plugins\mch\models\MchGroup;
use app\plugins\mch\models\MchGroupItem;

class MchManaRefuseGroupItemListForm extends BaseModel{

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
            $list = [];
            $mchId = $this->mch_id ? : MchAdminController::$adminUser['mch_id'];
            $mchGroup = MchGroup::findOne([
                "mch_id" => $mchId
            ]);
            if(!$mchGroup || $mchGroup->is_delete){
                throw new \Exception("商户[ID{$mchId}]非连锁总店");
            }

            $mchApplyResult = MchApply::find()
                ->where(['mch_group_id' => $mchGroup->id, 'status' => 'refused'])
                ->orderBy('id DESC')->page($pagination)->All();

            if ($mchApplyResult) {
                foreach ($mchApplyResult as &$item) {
                    $item['json_apply_data'] = json_decode($item['json_apply_data'], true);
                    $param['store_name'] = $item['json_apply_data']['store_name'];
                    $param['store_logo'] = 'https://www.mingyuanriji.cn/web/static/header-logo.png';
                    $param['mobile'] = $item['mobile'];
                    $param['remark'] = $item['remark'];
                    $city = CityHelper::reverseData($item['json_apply_data']['store_district_id'], $item['json_apply_data']['store_city_id'], $item['json_apply_data']['store_province_id']);
                    $param['province'] = !empty($city['province']['name']) ? $city['province']['name'] : "";
                    $param['city'] = !empty($city['city']['name']) ? $city['city']['name'] : "";
                    $param['district'] = !empty($city['district']['name']) ? $city['district']['name'] : "";
                    $param['store_address'] = $item['json_apply_data']['store_address'];
                    $param['id'] = $item['id'];
                    $list[] = $param;
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
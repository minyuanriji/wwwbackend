<?php

namespace app\plugins\perform_distribution\forms\mall;

use app\core\ApiCode;
use app\helpers\CityHelper;
use app\models\BaseModel;
use app\plugins\perform_distribution\models\PerformDistributionRegion;
use app\plugins\perform_distribution\models\PerformDistributionUser;

class RegionListForm extends BaseModel{

    public $keyword;
    public $limit = 10;
    public $page = 1;

    public function rules(){
        return [
            [['keyword'], 'trim'],
            [['keyword'], 'string'],
            [['limit', 'page'], 'integer']
        ];
    }

    public function getList(){
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        try {

            $query = PerformDistributionRegion::find()->alias("pdr")
                ->where([
                    'pdr.is_delete' => 0,
                    'pdr.mall_id'   => \Yii::$app->mall->id
                ]);

            if ($this->keyword) {
                $query->andWhere([
                    "OR",
                    ['like', 'pdr.name', $this->keyword]
                ]);
            }

            $list = $query->select('pdr.*')
                ->page($pagination, $this->limit, $this->page)
                ->orderBy("pdr.id DESC")->asArray()->all();

            foreach ($list as $key => $item) {
                $city = CityHelper::reverseData($item['district_id'], $item['city_id'], $item['province_id']);
                $item['province']   = $city['province'] ? $city['province']['name'] : "";
                $item['city']       = $city['city'] ? $city['city']['name'] : "";
                $item['district']   = $city['district'] ? $city['district']['name'] : "";
                $item['member_num'] = (int)PerformDistributionUser::find()->where([
                    'region_id' => $item['id'],
                    'is_delete' => 0,
                    'mall_id'   => $item['mall_id']
                ])->count();
                $list[$key] = $item;
            }

            return $this->returnApiResultData(ApiCode::CODE_SUCCESS, null, [
                'list'       => $list ? $list : [],
                'pagination' => $pagination
            ]);
        }catch (\Exception $e){
            return $this->returnApiResultData(ApiCode::CODE_FAIL, $e->getMessage());
        }
    }
}
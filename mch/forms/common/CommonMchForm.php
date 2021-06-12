<?php

namespace app\mch\forms\common;

use app\controllers\api\ApiController;
use app\forms\common\goods\CommonGoodsStatistic;
use app\forms\common\order\CommonOrderStatistic;
use app\models\BaseModel;
use app\models\DistrictData;
use app\plugins\mch\models\Mch;
use app\plugins\mch\models\MchVisitLog;
use app\plugins\mch\Plugin;

class CommonMchForm extends BaseModel{

    public $page;
    //
    public $id;
    public $is_review_status;

    public $cat_id;
    public $keyword;
    public $effect;

    public function rules(){
        return [
            [['cat_id', 'page'], 'integer'],
            [['keyword'], 'string'],
            [['effect'], 'safe'],
        ];
    }



    /*
     *  string  effect  功能   {nearby附近:获取最近5km的数据  intelligence智能排序:根据用户点击次数排序  screen 筛选}
     * */
    public function getList(){

        $cityId = \Yii::$app->request->headers->get("x-city-id");
        $districtData = intval($cityId) > 0 ? DistrictData::getDistrict((int)$cityId) : null;

        $longitude = ApiController::$commonData['city_data']['longitude'];
        $latitude = ApiController::$commonData['city_data']['latitude'];


        $query = Mch::find()->where([
            'm.mall_id'       => \Yii::$app->mall->id,
            'm.is_delete'     => 0,
            'm.review_status' => Mch::REVIEW_STATUS_CHECKED,
        ])->alias("m");

        $query->leftJoin("{{%store}} ss", "ss.mch_id=m.id");
        $query->leftJoin("{{%user}} u", "u.mch_id=m.id");

        if ($this->keyword) {
            $keyword = addslashes($this->keyword);
            $query->andWhere("(ss.name LIKE '%".$keyword."%' OR u.nickname LIKE '%".$keyword."%')");
        }

        if($districtData){
            $query->andWhere(["ss.city_id" => intval($cityId)]);
        }

        if($this->cat_id){
            $query->andWhere(["m.mch_common_cat_id" => $this->cat_id]);
        }

        $selects = ["m.id", "m.mall_id", "m.status", "m.is_recommend", "m.mch_common_cat_id"];
        $selects[] = "ST_Distance_sphere(point(longitude, latitude), point({$longitude}, {$latitude})) as distance_mi";

        $query->select($selects);

        $list = $query->andWhere(['>', "ST_Distance_sphere(point(longitude, latitude), point({$longitude}, {$latitude}))", 0])
            ->orderBy("distance_mi ASC")
            ->with('store', 'category')
            ->page($pagination, 15, max(1, (int)$this->page))
            ->asArray()->all();
        if($list){
//            $new_list = [];
            foreach($list as $key => &$item){
                $item['distance_format'] = "0m";
                if(empty($item['distance_mi'])) {
                    /*$new_list[$key] = $item;
                    unset($list[$key]);*/
                    continue;
                }
                if($item['distance_mi'] < 1000){
                    $item['distance_format'] = intval($item['distance_mi']) . "m";
                }else if($item['distance_mi'] >= 1000){
                    $item['distance_format'] = round(($item['distance_mi']/1000), 1) . "km";
                }
            }
//            $list = array_merge_recursive($list,$new_list);
            if (isset($this->effect) && $this->effect == 'nearby') {
                foreach ($list as $key => $list_val) {
                    if($item['distance_mi'] > 5000){
                        unset($list[$key]);
                    }
                }
            }
            if (isset($this->effect) && $this->effect == 'intelligence') {
                $model = MchVisitLog::find()->where([
                    'mall_id' => \Yii::$app->mall->id,
                    'user_id' => \Yii::$app->user->id,
                ])->asArray()->all();
                if ($model) {
//                    $model_list = [];
                    $new_model = array_combine(array_column($model,'mch_id'),$model);
                    foreach ($list as $key => $value) {
                        $list[$key]['num'] = 0;
                        if (isset($new_model[$value['id']])) {
                            $list[$key]['num'] = (int)$new_model[$value['id']]['num'];
                        }

                        /*if(empty($value['distance_mi'])) {
                            $model_list[$key] = $value;
                            unset($list[$key]);
                            continue;
                        }*/
                    }
                    array_multisort(
                        array_column($list,'num'),SORT_DESC ,
                        array_column($list,'distance_mi'),SORT_ASC ,
                        $list);
                    /*$list = array_merge_recursive($list,$model_list);*/
                }
            }
        }
        return [
            'list' => $list,
            'pagination' => $pagination
        ];
        /*$count_num = count($list);
        $list = array_slice($list, ((int)$this->page - 1) * 15,15);
        return [
            'list' => $list,
            'pagination' => [
                'current_page' => (int)$this->page,
                'pageSize' => 15,
                'page_count' => ceil($count_num / 15),
                'total_count' => $count_num,
            ]
        ];*/
    }

    /**
     * @param string $type mall--后台数据|api--小程序端接口数据
     * @return array
     * @throws \Exception
     * 获取首页布局的数据
     */
    public function getHomePage($type)
    {
        if ($type == 'mall') {
            $baseUrl = \Yii::$app->request->hostInfo . \Yii::$app->request->baseUrl;
            $plugin = new Plugin();
            return [
                'list' => [
                    [
                        'key' => $plugin->getName(),
                        'name' => '好店推荐',
                        'relation_id' => 0,
                        'is_edit' => 0
                    ]
                ],
                'bgUrl' => [
                    $plugin->getName() => [
                        'bg_url' => $baseUrl . '/statics/img/mall/home_block/yuyue-bg.png',
                    ]
                ],
                'key' => $plugin->getName()
            ];
        } elseif ($type == 'api') {
            /* @var Mch[] $list*/
            $list = Mch::find()->with('store')->where([
                'mall_id' => \Yii::$app->mall->id,
                'is_delete' => 0,
                'review_status' => 1,
                'status' => 1,
                'is_recommend' => 1
            ])->orderBy(['sort' => SORT_ASC, 'created_at' => SORT_DESC])
                ->limit(20)
                ->all();
            $newList = [];
            foreach ($list as $item) {
                $newList[] = [
                    'name' => $item->store->name,
                    'cover_url' => $item->store->cover_url,
                    'mch_id' => $item->id,
                    'id' => $item->id,
                    'picUrl' => $item->store->cover_url,
                ];
            }
            return $newList;
        } else {
            throw new \Exception('无效的数据');
        }
    }

    public function getDetail()
    {
        $query = Mch::find()->where([
            'id' => $this->id,
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0,
        ]);
        if (!$this->is_review_status) {
            $query->andWhere(['review_status' => 1]);
        }

        /** @var Mch $detail */
        $detail = $query->with('user.userInfo', 'mchUser', 'store', 'category')->one();
        if (!$detail) {
            throw new \Exception('商户不存在');
        }

        $detail->form_data = !$detail->form_data ?: \Yii::$app->serializer->decode($detail->form_data);
        $detail->store->pic_url = !$detail->store->pic_url ?: \Yii::$app->serializer->decode($detail->store->pic_url);

        return $detail;
    }
}

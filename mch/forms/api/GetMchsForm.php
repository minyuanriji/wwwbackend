<?php
namespace app\mch\forms\api;


use app\controllers\api\ApiController;
use app\forms\api\APICacheDataForm;
use app\forms\api\ICacheForm;
use app\helpers\APICacheHelper;
use app\models\BaseModel;
use app\models\DistrictData;
use app\plugins\mch\models\Mch;
use app\plugins\mch\models\MchVisitLog;

class GetMchsForm extends BaseModel implements ICacheForm {

    public $page;
    public $id;
    public $is_review_status;

    public $cat_id;
    public $keyword;
    public $effect;
    public $city_id;
    public $longitude;
    public $latitude;

    private $pagination;

    public function rules(){
        return [
            [['cat_id', 'page'], 'integer'],
            [['keyword'], 'string'],
            [['effect', 'city_id'], 'safe'],
        ];
    }

    public function getCacheKey(){
        $rawSql = $this->getQuery()->createCommand()->getRawSql();
        $keys[] = md5(strtolower($rawSql));
        return $keys;
    }

    /**
     * @return APICacheDataForm
     */
    public function getSourceDataForm(){

        $query = $this->getQuery();

        $list = $query->asArray()->all();

        if($list){

            foreach($list as $key => &$item){
                $item['distance_format'] = "0m";
                if(empty($item['distance_mi'])) {
                    continue;
                }
                if($item['distance_mi'] < 1000){
                    $item['distance_format'] = intval($item['distance_mi']) . "m";
                }else if($item['distance_mi'] >= 1000){
                    $item['distance_format'] = round(($item['distance_mi']/1000), 1) . "km";
                }
            }

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
                    $new_model = array_combine(array_column($model,'mch_id'),$model);
                    foreach ($list as $key => $value) {
                        $list[$key]['num'] = 0;
                        if (isset($new_model[$value['id']])) {
                            $list[$key]['num'] = (int)$new_model[$value['id']]['num'];
                        }
                    }

                    $numColumn = array_column($list,'num');
                    array_multisort(
                        $numColumn,SORT_DESC ,
                        array_column($list,'distance_mi'),SORT_ASC ,
                        $list);
                }
            }
        }

        return new APICacheDataForm([
            "sourceData" => [
                'list'       => $list,
                'pagination' => $this->pagination
            ]
        ]);
    }

    public function getQuery(){

        $districtData = intval($this->city_id) > 0 ?
                            DistrictData::getDistrict((int)$this->city_id) : null;

        $query = Mch::find()->where([
            'm.mall_id'       => \Yii::$app->mall->id,
            'm.is_delete'     => 0,
            'm.review_status' => Mch::REVIEW_STATUS_CHECKED,
            'm.status'        => 1,
        ])->alias("m");

        $query->leftJoin("{{%store}} ss", "ss.mch_id=m.id");
        $query->leftJoin("{{%user}} u", "u.mch_id=m.id");

        if ($this->keyword) {
            $keyword = addslashes($this->keyword);
            $query->andWhere("(ss.name LIKE '%".$keyword."%' OR u.nickname LIKE '%".$keyword."%')");
        }

        if($districtData){
            $query->andWhere(["ss.city_id" => intval($this->city_id)]);
        }

        if($this->cat_id){
            $query->andWhere(["m.mch_common_cat_id" => $this->cat_id]);
        }

        $selects = ["m.id", "m.mall_id", "m.status", "m.is_recommend", "m.mch_common_cat_id"];
        $selects[] = "ST_Distance_sphere(point(longitude, latitude), point(".$this->longitude.", ".$this->latitude.")) as distance_mi";

        $query->select($selects);
        $query->andWhere(['>', "ST_Distance_sphere(point(longitude, latitude), point(".$this->longitude.", ".$this->latitude."))", 0]);
        $query->orderBy("distance_mi ASC");
        $query->with('store', 'category');
        $query->page($this->pagination, 15, max(1, (int)$this->page));

        return $query;
    }
}
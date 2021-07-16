<?php
namespace app\plugins\mch\forms\mall;

use app\core\ApiCode;
use app\helpers\CityHelper;
use app\models\BaseModel;
use app\models\User;
use app\plugins\mch\models\Mch;
use app\plugins\mch\models\MchApply;
use app\plugins\mch\models\MchCommonCat;

class MchReviewForm extends BaseModel{

    public $page;
    public $review_status;
    public $keyword;

    public function rules(){
        return [
            [['review_status'], 'required'],
            [['keyword'], 'string'],
            [['page'], 'default', 'value' => 1],
        ];
    }

    public function getList(){

        try {

            $query = MchApply::find()->alias("ma");
            $query->innerJoin(["u" => User::tableName()], "u.id=ma.user_id");
            $query->innerJoin(["p" => User::tableName()], "p.id=u.parent_id");

            if($this->review_status == "special_discount"){ //特殊折扣申请
                $query->andWhere(["ma.is_special_discount" => 1]);
            }else{
                $query->andWhere(["ma.status" => $this->review_status]);
            }

            if(!empty($this->keyword)){
                $query->andWhere([
                    "OR",
                    ["LIKE", "ma.realname", $this->keyword],
                    ["LIKE", "ma.mobile", $this->keyword],
                    ["ma.user_id" => $this->keyword],
                    ["LIKE", "u.nickname", $this->keyword]
                ]);
            }

            $selects = ["ma.*", "p.role_type as parent_role_type", "p.nickname as parent_nickname"];
            $selects[] = "p.mobile as parent_mobile";
            $selects[] = "u.nickname";
            $query->select($selects);
            $query->orderBy("ma.id DESC");

            $list = $query->page($pagination, 20, (int)$this->page)->asArray()->all();
            foreach($list as &$row){
                $applyData = !empty($row['json_apply_data']) ? json_decode($row['json_apply_data'], true) : [];
                $row = array_merge($row, $applyData);
                $row['bind_mobile'] = !empty($row['bind_mobile']) ? $row['bind_mobile'] : $row['mobile'];
                $city = CityHelper::reverseData($row['store_district_id'], $row['store_city_id'], $row['store_province_id']);
                $row['province'] = !empty($city['province']) ? $city['province']['name'] : "";
                $row['city'] = !empty($city['city']) ? $city['city']['name'] : "";
                $row['district'] = !empty($city['district']) ? $city['district']['name'] : "";
                unset($row['json_apply_data']);
            }

            $cats = MchCommonCat::find()->where([
                "is_delete" => 0
            ])->asArray()->orderBy("sort ASC")->all();

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '请求成功',
                'data' => [
                    'list'       => $list,
                    'pagination' => $pagination,
                    'cats'       => $cats
                ]
            ];
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }
    }

    public function getDetail()
    {
        try {
            $detail = Mch::find()->where([
                'id' => $this->id,
                'mall_id' => \Yii::$app->mall->id,
                'is_delete' => 0,
            ])->with('user.userInfo')->asArray()->one();
            if (!$detail) {
                throw new \Exception('商户不存在');
            }

            $detail['address'] = \Yii::$app->serializer->decode($detail['address']);

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '请求成功',
                'data' => [
                    'detail' => $detail,
                ]
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $e->getMessage(),
            ];
        }
    }

    public function destroy()
    {
        try {
            $detail = Mch::find()->where([
                'id' => $this->id,
                'mall_id' => \Yii::$app->mall->id,
                'is_delete' => 0,
            ])->one();
            if (!$detail) {
                throw new \Exception('商户不存在');
            }

            $detail->is_delete = 1;
            $res = $detail->save();
            if (!$res) {
                throw new \Exception($this->responseErrorMsg($detail));
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '删除成功',
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $e->getMessage(),
            ];
        }
    }
}

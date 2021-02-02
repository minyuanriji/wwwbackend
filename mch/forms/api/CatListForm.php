<?php
namespace app\mch\forms\api;

use app\core\ApiCode;
use app\events\StatisticsEvent;
use app\models\BaseModel;
use app\models\GoodsCats;
use app\models\StatisticsBrowseLog;
use app\plugins\mch\models\Mch;

class CatListForm extends BaseModel{

    public $mch_id;

    public function rules(){
        return [
            [['mch_id'], 'required'],
            [['mch_id'], 'integer'],
            [['mch_id'], 'default', 'value' => 0],
        ];
    }

    public function search(){

        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        try {
            if (!$this->mch_id || empty(Mch::findOne($this->mch_id))) {
                throw new \Exception('多商户不存在');
            }

            global $commWhere, $selects;
            $commWhere = [
                'mch_id'    => $this->mch_id,
                'status'    => 1,
                'is_show'   => 1,
            ];
            $selects = ["id", "mall_id", "mch_id", "parent_id", "name", "pic_url"];
            $list = GoodsCats::find()->where(array_merge($commWhere, [
                'mall_id'   => \Yii::$app->mall->id,
                'is_delete' => 0,
            ]))->with(['child' => function ($query){
                global $commWhere, $selects;
                $query->with(['child' => function ($query){
                    global $commWhere, $selects;
                    $query->select($selects)->andWhere($commWhere)->orderBy('sort ASC');
                }])->select($selects)->andWhere($commWhere)->orderBy('sort ASC');
            }])->select($selects)->orderBy('sort ASC')->asArray()->all();

            \Yii::$app->trigger(StatisticsBrowseLog::EVEN_STATISTICS_LOG, new StatisticsEvent(['mall_id'=>\Yii::$app->mall->id,'browse_type'=>1,'user_id'=>\Yii::$app->user->id,'user_ip'=>$_SERVER['REMOTE_ADDR']]) );
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '请求成功',
                'data' => [
                    'list' => $list
                ]
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $e->getMessage(),
            ];
        }
    }
}
<?php
namespace app\mch\forms\api;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\mch\models\MchAccountLog;

class MchAccountLogListForm extends BaseModel{

    public $mch_id;

    public function rules(){
        return [
            [['mch_id'], 'required'],
        ];
    }

    public function getList(){

        if (!$this->validate()) {
            return $this->responseErrorMsg();
        }

        $query = MchAccountLog::find()->alias('mal')->where([
            'mal.mall_id' => \Yii::$app->mall->id,
            'mal.mch_id'  => $this->mch_id,
        ]);

        $list = $query->page($pagination)
                      ->orderBy(['mal.created_at' => SORT_DESC])
                      ->asArray()
                      ->all();
        foreach($list as &$item){
            $item['format_date'] = date("Y-m-d H:i", $item['created_at']);
        }

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg'  => "请求成功",
            'data' => [
                'list' => $list,
                'pagination' => $pagination
            ]
        ];
    }
}

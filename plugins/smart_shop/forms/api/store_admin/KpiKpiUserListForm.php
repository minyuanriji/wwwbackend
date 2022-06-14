<?php

namespace app\plugins\smart_shop\forms\api\store_admin;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\smart_shop\models\KpiUser;

class KpiKpiUserListForm extends BaseModel{

    public $merchant_id;
    public $store_id;
    public $page;
    public $keyword;

    public function rules(){
        return [
            [['merchant_id', 'store_id'], 'required'],
            [['page'], 'integer'],
            [['keyword'], 'trim']
        ];
    }

    public function getList(){
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        try {

            $query = KpiUser::find()->where([
                "mall_id"      => \Yii::$app->mall->id,
                "ss_mch_id"    => $this->merchant_id,
                "ss_store_id"  => $this->store_id,
                "is_delete"    => 0
            ])->orderBy("id DESC");

            if($this->keyword){
                $query->andWhere([
                    "OR",
                    ["LIKE", "realname", $this->keyword],
                    ["LIKE", "mobile", $this->keyword]
                ]);
            }

            $list = $query->asArray()->page($pagination, 10, $this->page)->all();
            if($list){
                foreach($list as $key => $row){
                    $list[$key]['created_at'] = date("Y-m-d H:i:s", $row['created_at']);
                    $list[$key]['updated_at'] = date("Y-m-d H:i:s", $row['updated_at']);
                }
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'list'       => $list ? $list : [],
                    'pagination' => $pagination,
                ]
            ];
        }catch (\Exception $e){
            return $this->returnApiResultData(ApiCode::CODE_FAIL, $e->getMessage());
        }
    }
}
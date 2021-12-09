<?php

namespace app\plugins\taolijin\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\models\User;
use app\plugins\taolijin\forms\common\AliAccForm;
use app\plugins\taolijin\helpers\AliOrderHelper;
use app\plugins\taolijin\models\TaolijinAli;
use app\plugins\taolijin\models\TaolijinOrders;
use lin010\taolijin\Ali;

class TaoLiJinOrderListForm extends BaseModel{

    public $ali_type;
    public $page;
    public $keyword;
    public $sort_prop;
    public $sort_type;

    public function rules(){
        return array_merge(parent::rules(), [
            [['ali_type'], 'required'],
            [['page'], 'integer'],
            [['keyword', 'sort_prop', 'sort_type'], 'safe']
        ]);
    }


    public function getList(){

        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        try {

            $query = TaolijinOrders::find()->alias("o")->where(["o.is_delete" => 0])
                ->innerJoin(["u" => User::tableName()], "u.id=o.user_id")
                ->innerJoin(["ali" => TaolijinAli::tableName()], "ali_id=o.ali_id");

            $query->orderBy("o.id DESC");
            $selects = ["o.*", "u.nickname", "u.avatar_url", "ali.name as ali_name", "ali.ali_type"];
            $list = $query->select($selects)->asArray()->page($pagination, 20, $this->page)->all();
            if($list){
                foreach($list as &$item){
                    $statusInfo = TaolijinOrders::getStatusInfo($item['order_status'], $item['pay_status']);
                    $item['status_i'] = $statusInfo;
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
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage(),
                'error' => [
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]
            ];
        }
    }

}
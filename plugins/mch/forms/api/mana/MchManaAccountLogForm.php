<?php

namespace app\plugins\mch\forms\api\mana;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\mch\controllers\api\mana\MchAdminController;
use app\plugins\mch\models\MchAccountLog;

class MchManaAccountLogForm extends BaseModel{

    public $page;
    public $type;
    public $created_at;

    public function rules(){
        return [
            [['page'], 'integer'],
            [['type','created_at'], 'string']
        ];
    }

    public function getList(){

        if (!$this->validate()) {
            return $this->responseErrorMsg();
        }

        try {

            $query = MchAccountLog::find()->alias('mal')->where([
                'mal.mall_id' => MchAdminController::$adminUser['mall_id'],
                'mal.mch_id'  => MchAdminController::$adminUser['mch_id'],
            ]);

            if(!empty($this->type) && in_array(strtoupper($this->type), ["IN", "OUT"])){
                if(strtoupper($this->type) == "IN"){ //收入
                    $query->andWhere(["mal.type" => 1]);
                }else{ //支出
                    $query->andWhere(["mal.type" => 2]);
                }
            }

            if ($this->created_at) {
                $query->andWhere('FROM_UNIXTIME(mal.created_at,"%Y年%m月")="'.$this->created_at.'"');
            }

            $list = $query->page($pagination, 10, $this->page)
                        ->orderBy(['mal.created_at' => SORT_DESC])
                        ->asArray()
                        ->all();
            foreach($list as &$item){
                $item['format_date'] = date('m月d日 H:i', $item['created_at']);
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg'  => "请求成功",
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
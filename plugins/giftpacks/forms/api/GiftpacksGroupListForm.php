<?php

namespace app\plugins\giftpacks\forms\api;


use app\core\ApiCode;
use app\models\BaseModel;
use app\models\User;
use app\plugins\giftpacks\models\Giftpacks;
use app\plugins\giftpacks\models\GiftpacksGroup;

class GiftpacksGroupListForm extends BaseModel{

    public $page;
    public $pack_id;

    public function rules(){
        return [
            [['page', 'pack_id'], 'required']
        ];
    }

    public function getList(){

        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        try {

            $giftpacks = Giftpacks::findOne($this->pack_id);
            if(!$giftpacks || $giftpacks->is_delete){
                throw new \Exception("大礼包不存在");
            }

            $query = GiftpacksGroup::find()->alias("gg")
                ->innerJoin(["u" => User::tableName()], "u.id=gg.user_id");
            $query->andWhere([
                "AND",
                ["IN", "gg.status", ["success", "sharing"]],
                ["gg.pack_id" => $giftpacks->id],
                //[">", "gg.expired_at", time()],
                //[">", "gg.need_num", "gg.user_num"]
            ]);
            $selects = ["gg.id", "gg.status", "gg.need_num", "gg.user_num", "gg.expired_at",
                "gg.created_at", "gg.user_id", "u.nickname", "u.avatar_url"
            ];
            $selects[] = "IF(gg.expired_at > '".time()."' AND gg.status='sharing' , 1, 0) as displaysort";
            $query->select($selects);

            $query->orderBy("displaysort DESC, gg.updated_at DESC");
            $list = $query->page($pagination, 10, max(1, (int)$this->page))->asArray()->all();
            if($list){
                foreach($list as &$log){
                    if($log['status'] == "sharing"){
                        if($log['need_num'] <= $log['user_num']){
                            $log['status'] = "success";
                        }elseif($log['expired_at'] < time()){
                            $log['status'] = "closed";
                        }
                    }
                    $log['created_at']     = date("Y-m-d H:i:s");
                    $log['still_need_num'] = intval($log['need_num']) - intval($log['user_num']);
                    unset($log['need_num']);
                    unset($log['user_num']);
                }
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'list'       => $list ? $list : [],
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
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
                ->innerJoin(["u" => User::tableName()], "u.id=gg.user_id")
                ->orderBy("gg.updated_at DESC");
            $query->andWhere([
                "AND",
                ["gg.status" => "success"],
                ["gg.pack_id" => $giftpacks->id],
                //[">", "gg.expired_at", time()],
                //[">", "gg.need_num", "gg.user_num"]
            ]);
            $selects = ["gg.id", "gg.need_num", "gg.user_num", "gg.expired_at",
                "gg.created_at", "gg.user_id", "u.nickname", "u.avatar_url"
            ];
            $query->select($selects);

            $list = $query->page($pagination, 10, max(1, (int)$this->page))->asArray()->all();
            if($list){
                foreach($list as &$log){
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
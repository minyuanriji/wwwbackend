<?php

namespace app\plugins\mch\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\models\Store;
use app\models\User;
use app\plugins\mch\models\Mch;
use app\plugins\mch\models\MchGroup;

class MchGroupForm extends BaseModel{

    public $page;
    public $keyword;

    public function rules(){
        return [
            [['page'], 'safe'],
            [['keyword'], 'trim']
        ];
    }

    public function getList(){

        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {

            $query = MchGroup::find()->alias("mg")->where([
                "mg.is_delete" => 0,
                "m.is_delete"  => 0
            ]);
            $query->innerJoin(["m" => Mch::tableName()], "m.id=mg.mch_id");
            $query->innerJoin(["s" => Store::tableName()], "s.id=mg.store_id");

            if(!empty($this->keyword)){
                $query->andWhere([
                    "OR",
                    ["m.mobile" => $this->keyword],
                    ["LIKE", "s.name", $this->keyword]
                ]);
            }

            $query->select(["mg.*", "m.user_id", "m.mobile", "s.name", "s.cover_url"]);

            $list = $query->orderBy("mg.id DESC")->page($pagination, 20, $this->page)->asArray()->all();

            if ($list) {
                foreach ($list as &$item) {
                    if ($item['user_id']) {
                        $userResult = User::findOne($item['user_id']);
                        if ($userResult) {
                            $item['user_name'] = $userResult->nickname;
                            $item['avatar_url'] = $userResult->avatar_url;
                        } else {
                            $item['user_name'] = '';
                            $item['avatar_url'] = '';
                        }
                    } else {
                        $item['user_name'] = '';
                        $item['avatar_url'] = '';
                    }

                    if (empty($item['cover_url'])) {
                        $item['cover_url'] = 'https://dev.mingyuanriji.cn/web/static/header-logo.png';
                    }
                }
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
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
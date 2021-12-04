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
    public $keyword1;

    public function rules(){
        return [
            [['page'], 'safe'],
            [['keyword', 'keyword1'], 'trim']
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

            if (!empty($this->keyword1) && !empty($this->keyword)) {
                switch ($this->keyword1)
                {
                    case 'mch_mobile':
                        $query->andWhere(["m.mobile" => $this->keyword]);
                        break;
                    case 'store_name':
                        $query->andWhere(['and', ["LIKE", "s.name", $this->keyword]]);
                        break;
                    case 'user_id':
                        $query->andWhere(['m.user_id' => $this->keyword]);
                        break;
                    case 'mch_id':
                        $query->andWhere(["m.id" => $this->keyword]);
                        break;
                    default:
                }
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
<?php
namespace app\plugins\mch\forms\mall;


use app\core\ApiCode;
use app\models\BaseModel;
use app\models\Store;
use app\models\User;
use app\plugins\commission\models\CommissionRules;
use app\plugins\mch\models\Mch;

class MchDistributionListForm extends BaseModel{

    public $page;
    public $keyword;

    public function rules(){
        return [
            [['keyword'], 'string'],
            [['page'], 'default', 'value' => 1],
        ];
    }

    public function getList(){

        $query = Store::find()->alias("s");
        $query->innerJoin(["m" => Mch::tableName()], "m.id=s.mch_id");
        $query->innerJoin(["u" => User::tableName()], "u.mch_id=m.id");
        $query->leftJoin(["cr" => CommissionRules::tableName()], "cr.item_id=s.id AND cr.item_type='checkout'");

        $query->select(["s.id", "cr.id as rule_id", "s.name", "s.cover_url", "u.id as user_id", "u.nickname", "u.avatar_url"]);

        if ($this->keyword) {
            $query->andWhere([
                "OR",
                ["u.id" => (int)$this->keyword],
                ["s.id" => (int)$this->keyword],
                ["LIKE", "u.nickname", $this->keyword],
                ["LIKE", "s.name", $this->keyword]
            ]);
        }
        $list = $query->orderBy(['s.created_at' => SORT_DESC])->page($pagination, 20, max(1, (int)$this->page))->asArray()->all();
        if($list){
            foreach($list as &$item){
                $item['rule_id'] = (int)$item['rule_id'];
            }
        }

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'list' => $list ? $list : [],
                'pagination' => $pagination
            ]
        ];
    }
}
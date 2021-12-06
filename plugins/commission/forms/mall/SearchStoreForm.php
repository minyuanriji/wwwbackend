<?php
namespace app\plugins\commission\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\models\Store;
use app\plugins\mch\models\Mch;


class SearchStoreForm extends BaseModel {
    public $page;
    public $keyword;

    public function rules(){
        return array_merge(parent::rules(), [
            [['page'], 'integer'],
            [['keyword'], 'safe']
        ]);
    }

    public function search(){

        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        $query = Store::find()->alias("s")->leftJoin("{{%plugin_mch}} m", "m.id=s.mch_id");
        $query->where([
            "s.is_delete" => 0,
            "m.is_delete" => 0,
            "m.review_status" => Mch::REVIEW_STATUS_CHECKED
        ]);

        if(!empty($this->keyword)){
            $query->andWhere([
                "OR",
                ["LIKE", "s.name", $this->keyword],
                ["s.id" => (int)$this->keyword],
                ["m.id" => (int)$this->keyword]
            ]);
        }

        $query->orderBy(['s.id' => SORT_DESC]);

        $select = ["s.id", "s.name", "s.cover_url as cover_pic", "s.created_at",  "s.updated_at"];

        $list = $query->select($select)->asArray()->page($pagination, 10, $this->page)->all();

        if ($list) {
            foreach ($list as &$item) {
                if (empty($item['cover_pic'])) {
                    $item['cover_pic'] = \Yii::$app->params['store_default_avatar'];
                }
            }
        }

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'list'       => $list ? $list : [],
                'pagination' => $pagination,
            ]
        ];
    }
}
<?php
namespace app\plugins\baopin\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\models\Goods;
use app\plugins\baopin\models\BaopinGoods;

class GoodsListForm extends BaseModel{

    public $page;
    public $keyword;

    public function rules(){
        return array_merge(parent::rules(), [
            [['page'], 'integer'],
            [['keyword'], 'safe']
        ]);
    }

    public function getList(){
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        $pagination = null;
        $query = BaopinGoods::find()->alias('bg')
                 ->leftJoin("{{%goods}} g", "g.id=bg.goods_id")
                 ->leftJoin("{{%goods_warehouse}} gw", "gw.id=g.goods_warehouse_id");
        if (!empty($this->keyword)) {
            $query->andWhere([
                'or',
                ['LIKE', 'g.id', $this->keyword],
                ['LIKE', 'gw.name', $this->keyword]
            ]);
        }

        $query->orderBy(['bg.id' => SORT_DESC]);

        $select = ["bg.id", "bg.goods_id", "gw.name", "gw.cover_pic", "bg.created_at",
            "g.enable_score", "bg.updated_at", "g.score_setting", "g.enable_integral", "g.integral_setting"];
        $list = $query->select($select)->asArray()->page($pagination)->all();
        if($list){
            foreach($list as $key => $row){

                //赠送积分设置
                $scoreSetting                    = !empty($row['score_setting']) ? @json_decode($row['score_setting'], true) : [];
                $scoreSetting['expire']          = isset($scoreSetting['expire']) ? $scoreSetting['expire'] : -1;
                $scoreSetting['integral_num']    = isset($scoreSetting['integral_num']) ? (int)$scoreSetting['integral_num'] : 0;
                $scoreSetting['period']          = isset($scoreSetting['period']) ? (int)$scoreSetting['period'] : 0;
                $scoreSetting['give_score']      = isset($scoreSetting['give_score']) ? (int)$scoreSetting['give_score'] : 0;
                $scoreSetting['give_score_type'] = isset($scoreSetting['give_score_type']) ? (string)$scoreSetting['give_score_type'] : "1";

                $editableScore['enable_score']  = isset($row['enable_score']) && $row['enable_score'] ? "1" : "0";
                $editableScore['is_permanent']  = $scoreSetting['expire'] == -1 ? "1" : "0";
                $editableScore = array_merge($editableScore, $scoreSetting);

                //赠送购物券设置
                $integralSetting                 = !empty($row['integral_setting']) ? @json_decode($row['integral_setting'], true) : [];
                $integralSetting['expire']       = -1;
                $integralSetting['integral_num'] = isset($integralSetting['integral_num']) ? (int)$integralSetting['integral_num'] : 0;
                $integralSetting['period']       = isset($integralSetting['period']) ? (int)$integralSetting['period'] : 0;

                $editableIntegral['enable_integral'] = isset($row['enable_integral']) && $row['enable_integral'] ? 1 : 0;
                $editableIntegral['is_permanent']    = $integralSetting['expire'] == -1 ? "1" : "0";
                $editableIntegral = array_merge($editableIntegral, $integralSetting);

                $list[$key]['editable_score']    = $editableScore;
                $list[$key]['editable_integral'] = $editableIntegral;
            }
        }

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'list' => $list ? $list : [],
                'pagination' => $pagination,
            ]
        ];
    }

}
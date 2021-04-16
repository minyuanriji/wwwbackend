<?php
namespace app\forms\api\order;


use app\core\ApiCode;
use app\models\BaseModel;
use app\models\OrderClerk;

class OrderClerkLogForm extends BaseModel{

    public $page;

    public function rules(){
        return [
            [['page'], 'integer']
        ];
    }

    public function get(){

        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {

            $query = OrderClerk::find()->alias("oc");
            $query->innerJoin("{{%order}} o", "o.id=oc.order_id");
            $query->innerJoin("{{%user}} u", "u.id=o.user_id");
            $query->innerJoin("{{%order_detail}} od", "od.order_id=o.id");
            $query->where([
                "o.clerk_id"   => \Yii::$app->user->id,
                "oc.is_delete" => 0
            ]);
            $query->asArray()->page($pagination, 10, max(1, $this->page));
            $query->select(["o.id as order_id", "o.order_no", "oc.created_at",
                "u.nickname", "u.avatar_url", "od.goods_info"
            ]);
            $rows = $query->orderBy("oc.id DESC")->all();
            if($rows){
                foreach($rows as &$row){
                    $row['goods_info'] = @json_decode($row['goods_info']);
                    $row['format_date'] = date("Y-m-d H:i", $row['created_at']);
                }
            }

            return [
                'code' => ApiCode::CODE_FAIL,
                'data' => [
                    'list'       => $rows ? $rows : [],
                    'pagination' => $pagination
                ],
                'msg'  => '查询成功'
            ];
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }
    }
}
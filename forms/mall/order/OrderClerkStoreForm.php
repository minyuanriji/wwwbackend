<?php
namespace app\forms\mall\order;


use app\core\ApiCode;
use app\models\BaseModel;
use app\models\EfpsMchReviewInfo;
use app\models\Order;
use app\models\OrderClerk;
use app\models\Store;
use app\models\User;
use app\plugins\mch\models\Mch;

class OrderClerkStoreForm extends BaseModel{

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

        $query = Store::find()->alias("s");
        $query->innerJoin(["m" => Mch::tableName()], "m.id=s.mch_id");
        $query->innerJoin(["u" => User::tableName()], "u.mch_id=m.id");
        $query->leftJoin(["emri" => EfpsMchReviewInfo::tableName()], "emri.mch_id=m.id");

        $subSql = "(select count(*) from {{%order_detail}} od 
	                inner join {{%order}} o on o.id=od.order_id 
                    inner join {{%order_clerk}} oc on oc.order_id=o.id 
	                left join {{%order_clerk_express}} oce on oce.express_detail_id=od.id 
	                where oc.is_delete=0 AND o.store_id=s.id and oce.id  is null) as num";

        $query->select(["s.mch_id", "s.name", "s.id as store_id", "s.mobile",
            "emri.paper_registerAddress as address", $subSql
        ]);

        if (!empty($this->keyword)) {
            $query->andWhere([
                'or',
                ['s.id' => (int)$this->keyword],
                ['m.id' => (int)$this->keyword],
                ['s.mobile' => $this->keyword],
                ['LIKE', 'u.nickname', $this->keyword],
                ['LIKE', 's.name', $this->keyword]
            ]);
        }

        $query->orderBy("num DESC");

        $list = $query->asArray()->page($pagination, 10, max(1, (int)$this->page))->all();
        if($list) {
            foreach ($list as &$item) {

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
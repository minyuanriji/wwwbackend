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
        $query->leftJoin(["o" => Order::tableName()], "o.clerk_id=u.id");
        $query->leftJoin(["oc" => OrderClerk::tableName()], "oc.order_id=o.id");
        $query->groupBy("s.id");

        $query->select(["s.mch_id", "s.name", "s.id as store_id", "s.mobile", "count(oc.id) as num",
            "emri.paper_registerAddress as address"
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
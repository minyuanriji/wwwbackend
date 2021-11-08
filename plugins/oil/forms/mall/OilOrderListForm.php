<?php

namespace app\plugins\oil\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\models\User;
use app\plugins\oil\models\OilOrders;
use app\plugins\oil\models\OilPlateforms;
use app\plugins\oil\models\OilProduct;

class OilOrderListForm extends BaseModel {

    public $status;
    public $keyword;
    public $date_start;
    public $date_end;

    public function rules()
    {
        return [
            [['status', 'keyword', 'date_start', 'date_end'], 'string']
        ];
    }

    public function getList()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }
        try {
            $query = OilOrders::find()->alias("o");
            $query->innerJoin(["p" => OilProduct::tableName()], "p.id=o.product_id");
            $query->innerJoin(["f" => OilPlateforms::tableName()], "f.id=p.plat_id");
            $query->innerJoin(["u" => User::tableName()], "u.id=o.user_id");

            if (!empty($this->keyword)) {
                $query->andWhere([
                    "OR",
                    ["LIKE", "o.mobile", $this->keyword],
                    ["LIKE", "o.order_no", $this->keyword],
                    ["LIKE", "u.nickname", $this->keyword],
                    ["o.user_id" => $this->keyword],
                ]);
            }

            if (!empty($this->date_start) && !empty($this->date_end)) {
                $query->andFilterWhere(['between', 'o.pay_at', $this->date_start, $this->date_end]);
            }

            $query->orderBy("o.id DESC");

            $selects = ["o.*", "u.nickname", "f.name as plat_name", "p.price as product_price"];

            $list = $query->select($selects)->page($pagination, 10)->asArray()->all();
            if ($list) {
                foreach ($list as &$row) {
                    $statusInfo = OilOrders::getStatusInfo($row['order_status'], $row['pay_status'], $row['created_at']);
                    $row['status_text'] = $statusInfo['text'];
                    $row['real_status'] = $statusInfo['status'];
                }
            }
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'list' => $list ? $list : [],
                    'pagination' => $pagination
                ]
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $e->getMessage()
            ];
        }
    }

}
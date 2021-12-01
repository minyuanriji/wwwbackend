<?php

namespace app\plugins\alibaba\forms\mall;

use app\core\ApiCode;
use app\forms\mall\export\AlibabaOrderExport;
use app\models\BaseModel;
use app\models\IntegralLog;
use app\models\User;
use app\plugins\alibaba\models\AlibabaDistributionGoodsList;
use app\plugins\alibaba\models\AlibabaDistributionGoodsSku;
use app\plugins\alibaba\models\AlibabaDistributionOrder;
use app\plugins\alibaba\models\AlibabaDistributionOrderDetail;
use app\plugins\alibaba\models\AlibabaDistributionOrderDetail1688;
use app\plugins\commission\models\CommissionGiftpacksPriceLog;
use app\plugins\giftpacks\models\Giftpacks;
use app\plugins\giftpacks\models\GiftpacksOrder;
use yii\base\BaseObject;

class AlibabaDistributionOrderListForm extends BaseModel
{
    public $page;
    public $keyword;
    public $start_time;
    public $end_time;
    public $status;
    public $fields;
    public $flag;

    public function rules()
    {
        return [
            [['page'], 'integer'],
            [['keyword', 'status'], 'string'],
            [['start_time', 'end_time', 'fields', 'flag'], 'safe']
        ];
    }

    public function getList()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        try {

            $query = AlibabaDistributionOrder::find()->alias('ao')->where(["ao.is_delete" => 0, "ao.is_recycle" => 0])
                ->leftJoin(["ad" => AlibabaDistributionOrderDetail::tableName()], "ad.order_id = ao.id")
                ->leftJoin(["ag" => AlibabaDistributionGoodsList::tableName()], "ag.id = ad.goods_id")
                ->leftJoin(["u" => User::tableName()], "ao.user_id = u.id")
                ->leftJoin(["aod" => AlibabaDistributionOrderDetail1688::tableName()], "aod.order_id = ao.id");

            if (!empty($this->keyword)) {
                $query->andWhere([
                    'or',
                    ["LIKE", "ao.id", $this->keyword],
                    ["LIKE", "ao.order_no", $this->keyword],
                    ["LIKE", "ag.name", $this->keyword],
                    ["LIKE", "u.nickname", $this->keyword],
                    ["LIKE", "u.mobile", $this->keyword],
                ]);
            }

            if ($this->start_time && $this->end_time) {
                $query->andWhere([
                    'and',
                    ['>=', 'ao.pay_at', strtotime($this->start_time)],
                    ['<=', 'ao.pay_at', strtotime($this->end_time)],
                ]);
            }

            if ($this->status) {
                $query->keyword($this->status == 'paid', ['ao.is_pay' => 1, 'ao.sale_status' => 0, 'ad.is_refund' => 0, "ad.refund_status" => 'none'])
                    ->keyword($this->status == 'unpaid', ['ao.is_pay' => 0])
                    ->keyword($this->status == 'closed', ['ao.is_closed' => 1]);
            }

            $select = ['ao.*', "ag.name as goods_name", "ag.cover_url", 'ad.*', "u.nickname", "aod.status", "aod.do_error"];

            $query->select($select);

            if ($this->flag == "EXPORT") {
                $new_query = clone $query;
                $exp = new AlibabaOrderExport();
                $exp->fieldsKeyList = $this->fields;
                $exp->export($new_query, 'ao.');
                return false;
            }
            $list = $query->orderBy("ao.id DESC")->page($pagination)->asArray()->all();

            if ($list) {
                foreach ($list as &$item) {
                    $item['sku_labels'] = json_decode($item['sku_labels'], true);
                    $item['total_shopping_voucher_price'] = $item['shopping_voucher_decode_price'] + $item['shopping_voucher_express_use_num'];
                }
            }

            return $this->returnApiResultData(
                ApiCode::CODE_SUCCESS,
                '',
                [
                    'list' => $list ?: [],
                    'pagination' => $pagination,
                    'export_list' => (new AlibabaOrderExport())->fieldsList(),
                ]
            );
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $e->getMessage()
            ];
        }
    }
}
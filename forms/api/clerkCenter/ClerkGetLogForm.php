<?php

namespace app\forms\api\clerkCenter;

use app\core\ApiCode;
use app\models\BaseModel;
use app\models\clerk\ClerkData;
use app\models\clerk\ClerkLog;
use app\models\Order;
use app\models\User;
use app\plugins\giftpacks\models\Giftpacks;
use app\plugins\giftpacks\models\GiftpacksItem;
use app\plugins\giftpacks\models\GiftpacksOrder;
use app\plugins\giftpacks\models\GiftpacksOrderItem;

class ClerkGetLogForm extends BaseModel
{

    public $page;

    public function rules()
    {
        return [
            [['page'], 'integer']
        ];
    }

    public function getList()
    {

        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        try {
            $query = ClerkLog::find()->alias("cl")
                ->innerJoin(["cd" => ClerkData::tableName()], "cd.id=cl.clerk_data_id");

            $selects = ["cd.source_id", "cd.source_type", "cl.created_at", "cl.remark"];

            //核销大礼包物品
            $query->leftJoin(["goi" => GiftpacksOrderItem::tableName()], "goi.id=cd.source_id AND cd.source_type='giftpacks_order_item'");
            $query->leftJoin(["gpi" => GiftpacksItem::tableName()], "gpi.id=goi.pack_item_id");
            $selects = array_merge($selects, [
                "goi.order_id",
                "gpi.pack_id",
                "gpi.name as pack_item_name",
            ]);

            $query->select($selects);
            $query->orderBy("cl.id DESC");
            $query->where(["cl.user_id" => \Yii::$app->user->id]);

            $rows = $query->page($pagination, 10, max(1, (int)$this->page))
                ->asArray()->all();
            $list = [];
            if ($rows) {
                foreach ($rows as $row) {
                    $item['created_at'] = date("Y-m-d H:i:s", $row['created_at']);
                    $item['descript'] = "";
                    $item['pay_user_name'] = '';
                    $item['pay_user_id'] = 0;
                    $item['mobile'] = '';
                    if ($row['source_type'] == "giftpacks_order_item") {
                        $item['descript'] = "核销大礼包“" . $row['pack_item_name'] . "”";
                        $giftPacks = Giftpacks::findOne($row['pack_id']);
                        if (!$giftPacks) throw new \Exception('大礼包不存在');

                        $gitfPacksOrder = GiftpacksOrder::findOne($row['order_id']);
                        if (!$gitfPacksOrder) throw new \Exception('大礼包订单不存在');

                        $user = User::findOne($gitfPacksOrder->user_id);
                        if (!$user) throw new \Exception('用户不存在');

                        $item['pay_user_name'] = $user->nickname;
                        $item['mobile'] = $user->mobile;
                        $item['pay_user_id'] = $gitfPacksOrder->user_id;
                    } else {
                        $item['descript'] = "核销订单[ID:" . $row['source_id'] . "]";

                        $orderUser = $this->getOrderUser($row['source_id']);
                        if ($orderUser) {
                            $item = array_merge($item, $orderUser);
                        }
                    }
                    $list[] = $item;
                }
            }
            return $this->returnApiResultData(ApiCode::CODE_SUCCESS, '', [
                'list' => $list ?: [],
                'pagination' => $pagination
            ]);
        } catch (\Exception $e) {
            return $this->returnApiResultData(ApiCode::CODE_FAIL, $e->getMessage());
        }
    }

    public function getOrderUser($order_id)
    {
        $order = Order::find()->where(['id' => $order_id])->with('user')->one();
        if (!$order)
            return false;

        return ['pay_user_name' => $order->user->nickname, 'pay_user_id' => $order->user->id, 'mobile' => $order->user->mobile];
    }
}
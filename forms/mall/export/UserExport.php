<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 导出用户数据
 * Author: zal
 * Date: 2020-04-15
 * Time: 16:45
 */

namespace app\forms\mall\export;

use app\core\CsvExport;
use app\forms\common\CommonMallMember;
use app\models\Order;
use app\models\UserCard;
use app\models\UserCoupon;

class UserExport extends BaseExport
{
    public function fieldsList()
    {
        return [
            [
                'key' => 'platform',
                'value' => '所属平台',
            ],
            [
                'key' => 'id',
                'value' => '用户ID',
            ],
            [
                'key' => 'platform_user_id',
                'value' => '平台标识ID',
            ],
            [
                'key' => 'nickname',
                'value' => '昵称',
            ],
            [
                'key' => 'mobile',
                'value' => '绑定手机号',
            ],
            [
                'key' => 'remark',
                'value' => '备注',
            ],
            [
                'key' => 'created_at',
                'value' => '加入时间',
            ],
            [
                'key' => 'member_level',
                'value' => '会员身份',
            ],
            [
                'key' => 'order_count',
                'value' => '订单数',
            ],
            [
                'key' => 'coupon_count',
                'value' => '优惠券总数',
            ],
            [
                'key' => 'card_count',
                'value' => '卡券总数',
            ],
            [
                'key' => 'score',
                'value' => '积分',
            ],
            [
                'key' => 'balance',
                'value' => '余额',
            ],
            [
                'key' => 'consume_count',
                'value' => '总消费',
            ],
        ];
    }

    public function export($query)
    {
        $cardQuery = UserCard::find()->where(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0])->andWhere('user_id = u.id')->select('count(1)');
        $couponQuery = UserCoupon::find()->where(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0,'is_failure' => UserCoupon::NO])->andWhere('user_id = u.id')->select('count(1)');
        $orderQuery = Order::find()->where(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0])->andWhere('user_id = u.id')->select('count(1)');
        // TODO 消费总额要加上其它消费、如充值等
        $consumeCount = Order::find()->where(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0, 'is_confirm' => 1])->andWhere('user_id = u.id')->select('sum(total_pay_price)');

        $list = $query->with(['userInfo'])
            ->select([
                'u.*',
                'coupon_count' => $couponQuery,
                'card_count' => $cardQuery,
                'order_count' => $orderQuery,
                'consume_count' => $consumeCount,
            ])
            ->asArray()
            ->all();


        $this->transform($list);
        $this->getFields();
        $dataList = $this->getDataList();

        $fileName = '用户列表' . date('YmdHis');
        (new CsvExport())->export($dataList, $this->fieldsNameList, $fileName);
    }

    protected function transform($list)
    {
        $newList = [];
        $number = 1;
        $members = CommonMallMember::getAllMember();
        foreach ($list as $item) {
            $arr = [];
            $arr['number'] = $number++;
            $arr['platform'] = $this->getPlatform($item['userInfo']['platform']);
            $arr['id'] = $item['id'];
            $arr['unionid'] = $item['userInfo']['unionid'];
            $arr['nickname'] = $item['nickname'];
            $arr['mobile'] = $item['mobile'];
            $arr['remark'] = $item['userInfo']['remark'];
            $arr['created_at'] = $this->getDateTime($item['created_at']);

            $memberLevel = $item['level'];
            if ($memberLevel > 0) {
                foreach ($members as $member) {
                    if ($member['level'] == $memberLevel) {
                        $arr['member_level'] = $member['name'];
                        break;
                    }
                }
            } elseif ($memberLevel == 0 || $memberLevel == -1) {
                $arr['member_level'] = 'VIP会员';
            } else {
                $arr['member_level'] = '未知';
            }
            $arr['order_count'] = (int)$item['order_count'];
            $arr['card_count'] = (int)$item['card_count'];
            $arr['coupon_count'] = (int)$item['coupon_count'];
            $arr['score'] = (int)$item['score'];
            $arr['balance'] = (float)$item['balance'];
            $arr['consume_count'] = (int)$item['consume_count'];
            $newList[] = $arr;
        }

        $this->dataList = $newList;
    }
}

<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 分销商订单
 * Author: zal
 * Date: 2020-04-16
 * Time: 10:45
 */

namespace app\forms\mall\distribution;


use app\core\ApiCode;
use app\forms\common\distribution\DistributionOrderCommon;
use app\forms\mall\export\DistributionOrderExport;
use app\models\BaseModel;
use app\models\Mall;
use app\models\User;

/**
 * Class OrderForm
 * @package app\forms\mall\share
 * @property Mall $mall
 */
class OrderForm extends BaseModel
{
    public $mall;
    public $user_id;
    public $order_no;
    public $nickname;
    public $page;
    public $limit;
    public $parent_id;
    public $date_start;
    public $date_end;

    public $fields;
    public $flag;
    public $status;
    public $goods_name;
    public $platform;
    public $send_type;

    public function rules()
    {
        return [
            [['page', 'limit', 'parent_id', 'user_id', 'send_type'], 'integer'],
            ['page', 'default', 'value' => 1],
            ['limit', 'default', 'value' => 20],
            [['order_no', 'nickname', 'date_start', 'date_end', 'status', 'goods_name'], 'trim'],
            [['order_no', 'nickname', 'date_start', 'date_end', 'flag', 'goods_name', 'platform'], 'string'],
            [['fields'], 'safe'],
        ];
    }

    public function search()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        $form = new DistributionOrderCommon();
        $form->mall = \Yii::$app->mall;
        $form->parentId = $this->parent_id;
        $form->userId = $this->user_id;
        $form->page = $this->page;
        $form->limit = $this->limit;
        $form->order_no = $this->order_no;
        $form->nickname = $this->nickname;
        $form->date_start = $this->date_start;
        $form->date_end = $this->date_end;
        $form->goods_name = $this->goods_name;
        $form->flag = $this->flag;
        $form->fields = $this->fields;
        $form->sign = $this->status;
        $form->platform = $this->platform;
        $form->send_type = $this->send_type;
        $orderList = $form->getList();
        $list = $form->search($orderList);
        $parentId = [];
        foreach ($list as $item) {
            if (!in_array($item['first_parent_id'], $parentId)) {
                $parentId[] = $item['first_parent_id'];
            }
            if (!in_array($item['second_parent_id'], $parentId)) {
                $parentId[] = $item['second_parent_id'];
            }
            if (!in_array($item['third_parent_id'], $parentId)) {
                $parentId[] = $item['third_parent_id'];
            }
        }
        /* @var User[] $parent */
        $parent = User::find()->where(['id' => $parentId])->with('share')->all();
        foreach ($list as $index => &$item) {
            $first = null;
            $second = null;
            $third = null;
            foreach ($parent as $value) {
                if ($value->id == $item['first_parent_id']) {
                    $first = $value;
                }
                if ($value->id == $item['second_parent_id']) {
                    $second = $value;
                }
                if ($value->id == $item['third_parent_id']) {
                    $third = $value;
                }
            }
            $item['first_parent'] = [
                'nickname' => $first->nickname,
                'name' => $first->distribution ? $first->distribution->name : '',
                'mobile' => $first->distribution ? $first->distribution->mobile : '',
            ];
            $item['second_parent'] = $second ? [
                'nickname' => $second->nickname,
                'name' => $second->distribution ? $second->distribution->name : '',
                'mobile' => $second->distribution ? $second->distribution->mobile : '',
            ] : null;
            $item['third_parent'] = $third ? [
                'nickname' => $third->nickname,
                'name' => $third->distribution ? $third->distribution->name : '',
                'mobile' => $third->distribution ? $third->distribution->mobile : '',
            ] : null;
        }
        unset($item);

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '',
            'data' => [
                'list' => $list,
                'pagination' => $form->pagination,
                'export_list' => (new DistributionOrderExport())->fieldsList(),
            ]
        ];
    }
}

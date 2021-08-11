<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-04-16
 * Time: 12:24
 */

namespace app\forms\mall\member;

use app\core\ApiCode;
use app\helpers\SerializeHelper;
use app\models\BaseModel;
use app\models\MemberBenefit;
use app\models\MemberLevel;

class MemberLevelEditForm extends BaseModel
{
    public $level;
    public $name;
    public $pic_url;
    public $bg_pic_url;
    public $money;
    public $auto_update;
    public $discount;
    public $status;
    public $is_purchase;
    public $price;
    public $benefits;
    public $rules;
    public $id;
    public $member_level;
    public $isNewRecord;
    public $upgrade_type_goods;
    public $upgrade_type_condition;
    public $goods_type;
    public $goods_list;
    public $goods_warehouse_ids;
    public $buy_compute_way;

    public function rules()
    {
        return [
            [['name', 'pic_url', 'bg_pic_url', 'level', 'discount', 'status', 'is_purchase',
                'auto_update'], 'required'],
            [['goods_warehouse_ids', 'goods_list'], 'trim'],
            [['pic_url', 'bg_pic_url', 'name', 'money', 'discount', 'price',], 'string'],
            [['id', 'level', 'status', 'is_purchase', 'auto_update','upgrade_type_condition', 'upgrade_type_goods', 'goods_type','buy_compute_way'], 'integer'],
            [['benefits', 'rules'], 'safe'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'benefits' => '会员权益',
            'price' => '购买会员价格',
            'money' => '会员自动升级消费金额',
            'upgrade_type_goods' => '开启商品升级',
            'upgrade_type_condition' => '开启条件升级',
            'goods_warehouse_ids' => '商品仓库ID',
            'goods_type' => '购物方式',
            'goods_list' => '商品列表',
            'buy_compute_way'=> '升级方式，1=>付款 2=>完成'
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }
        if ($this->discount < 0.1 || $this->discount > 10) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => '会员折扣请输入 0.1 ~ 10之间的值'
            ];
        }
        if ($this->upgrade_type_condition && !$this->money) {
            throw new \Exception('请填写会员自动升级金额');
        }
        if ($this->upgrade_type_goods && !$this->goods_type) {
            throw new \Exception('请选择购物方式');
        }
        if ($this->is_purchase && !$this->price) {
            throw new \Exception('请填写会员购买金额');
        }

        $transaction = \Yii::$app->db->beginTransaction();
        try {
            if ($this->id) {
                $member_level = MemberLevel::findOne(['mall_id' => \Yii::$app->mall->id, 'id' => $this->id]);

                if (!$member_level) {
                    throw new \Exception('数据异常,该条数据不存在');
                }
            } else {
                $member_level = new MemberLevel();
            }

            $this->member_level = $member_level;
            $this->isNewRecord = $member_level->isNewRecord;
            $member_level->name = $this->name;
            $member_level->mall_id = \Yii::$app->mall->id;
            $member_level->pic_url = $this->pic_url;
            $member_level->bg_pic_url = $this->bg_pic_url;
            $member_level->level = $this->level;
            $member_level->auto_update = $this->auto_update;
            $member_level->money = $this->money ?: 0;
            $member_level->discount = $this->discount;
            $member_level->status = $this->status;
            $member_level->is_purchase = $this->is_purchase;
            $member_level->price = $this->price ?: 0;
            $member_level->rules = $this->rules;
            !empty($this->buy_compute_way) ? $member_level->buy_compute_way = $this->buy_compute_way : '';

            $member_level->goods_type = $this->goods_type ?: 0;
            $member_level->upgrade_type_goods = $this->upgrade_type_goods ?: 0;
            $member_level->upgrade_type_condition = $this->upgrade_type_condition ?: 0;

            $member_level->goods_list = SerializeHelper::encode($this->goods_list);
            $member_level->goods_warehouse_ids = SerializeHelper::encode($this->goods_warehouse_ids);

            $res = $member_level->save();

            if (!$res) {
                throw new \Exception($this->responseErrorMsg($member_level));
            }
            /**
             * 设置会员权益
             */
            $this->setBenefits();
            $transaction->commit();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功',
            ];
        } catch (\Exception $e) {
            $transaction->rollBack();
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $e->getMessage(),
                'error' => [
                    'line' => $e->getLine(),
                ]
            ];
        }
    }

    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-04-16
     * @Time: 13:10
     * @Note:设置会员权益
     * @throws \yii\db\Exception
     */
    private function setBenefits()
    {
        if (!$this->isNewRecord) {
            $res = MemberBenefit::updateAll([
                'is_delete' => 1,
            ], [
                'level_id' => $this->member_level->id
            ]);
        }

        if ($this->benefits) {
            foreach ($this->benefits as $item) {
                if (!$item['title']) {
                    throw new \Exception('请完善会员权益标题');
                }
                if (!$item['pic_url']) {
                    throw new \Exception('请添加会员权益图标');
                }
                if (!$item['content']) {
                    throw new \Exception('请完善会员权益内容');
                }
            }
        }

        if ($this->benefits) {
            $attributes = [];
            foreach ($this->benefits as $k => $item) {
                $benefit = MemberBenefit::findOne([
                    'id' => $item['id']
                ]);
                if ($benefit) {
                    $benefit->is_delete = 0;
                    $benefit->title = $item['title'];
                    $benefit->content = $item['content'];
                    $benefit->pic_url = $item['pic_url'];
                    $res = $benefit->save();
                    if (!$res) {
                        throw new \Exception($this->responseErrorMsg($benefit));
                    }
                } else {
                    $attributes[] = [
                        $this->member_level->id, $item['title'], $item['content'], $item['pic_url']
                    ];
                }
            }
            $query = \Yii::$app->db->createCommand();
            $res = $query->batchInsert(MemberBenefit::tableName(), [
                'level_id', 'title', 'content', 'pic_url'
            ], $attributes)
                ->execute();

            if ($res != count($attributes)) {
                throw new \Exception('保存失败, 会员权益数据异常');
            }
        }
    }

}
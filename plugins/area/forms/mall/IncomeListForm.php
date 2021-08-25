<?php

namespace app\plugins\area\forms\mall;

use app\helpers\ArrayHelper;
use app\models\CommonOrderDetail;
use app\models\PriceLog;
use app\models\User;
use app\plugins\area\forms\common\AreaLevelCommon;
use app\plugins\area\models\Area;
use app\plugins\area\models\AreaAgent;
use app\plugins\area\models\AreaLevel;
use app\plugins\area\models\AreaPriceLogType;
use app\plugins\area\models\AreaSetting;
use app\models\BaseModel;

class IncomeListForm extends BaseModel
{
    public $keyword;
    public $platform;
    public $limit = 10;
    public $page = 1;
    public $sort;
    public $fields;
    public $flag;

    public function rules()
    {
        return [
            [['keyword', 'platform'], 'trim'],
            [['keyword', 'platform', 'flag'], 'string'],
            [['limit', 'page'], 'integer'],
            [['fields'], 'safe'],
            [['sort'], 'default', 'value' => ['p.created_at' => SORT_DESC]],
        ];
    }

    public function getList()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }
        $mall = \Yii::$app->mall;
        $pagination = null;
        $query = PriceLog::find()
            ->alias('p')
            ->with(['user'])
            ->with(['commonOrderDetail'])
            ->where(['p.is_delete' => 0, 'p.mall_id' => $mall->id])
            ->leftJoin(['u' => User::tableName()], 'u.id = p.user_id');
        if ($this->keyword) {
            $query->andWhere([
                'or',
                ['like', 'u.username', $this->keyword],
                ['like', 'u.nickname', $this->keyword]
            ]);
        }

        $query->andWhere(['p.sign'=>'area']);

        $list = $query->page($pagination, $this->limit, $this->page)
            ->orderBy('id desc')->all();
        $newList = [];
        foreach ($list as $item) {

            $user = $item->user ?: null;

            if ($user) {
                $newItem['id'] =$item['id'];
                $newItem['user_id'] = $user->id;
                $newItem['nickname'] = $user->nickname;
                $newItem['avatar_url'] = $user->avatar_url;
                $newItem['parent_name'] = $user->parent ? $user->parent->nickname : '平台';
                $area = AreaAgent::findOne(['user_id' => $user->id, 'is_delete' => 0]);
                if (!$area) {
                    $newItem['area_level_name'] = '默认等级';
                } else {
                    $newItem['area_level_name'] =AreaAgent::LEVEL[$area->level];
                }
                $common_order_detail = $item->commonOrderDetail;
                $newItem['order_no'] = $common_order_detail->order_no;
                $newItem['goods_price'] = $common_order_detail->price;
                $newItem['price'] = $item->price;
                $newItem['created_at'] = date('Y-m-d H:i:s', $item->created_at);
                $newItem['is_price'] = $item->is_price ? '已发放' : '待发放';

                if ($common_order_detail->status == 0) {
                    $newItem['status'] = '未完成';
                }

                if ($common_order_detail->status == 1) {
                    $newItem['status'] = '已完成';
                }

                if ($common_order_detail->status == -1) {
                    $newItem['status'] = '无效';
                }
            }
            $newList[] = $newItem;
        }
        return [
            'code' => 0,
            'msg' => '',
            'data' => [
                'list' => $newList,
                'pagination' => $pagination,

            ]
        ];
    }
}
<?php
/**
 * Created by PhpStorm.
 * User: kaifa
 * Date: 2020-05-10
 * Time: 17:57
 */

namespace app\plugins\area\forms\mall;


use app\helpers\ArrayHelper;
use app\models\DistrictData;
use app\models\Town;
use app\models\User;

use app\plugins\area\models\AreaAgent;

use app\models\BaseModel;
use function Sodium\add;

class AreaListForm extends BaseModel
{

    public $keyword;
    public $platform;
    public $limit = 10;
    public $page = 1;
    public $sort;
    public $level;
    public $fields;
    public $flag;

    public function rules()
    {
        return [
            [['keyword', 'platform'], 'trim'],
            [['keyword', 'platform', 'flag'], 'string'],
            [['limit', 'page', 'level'], 'integer'],
            [['fields'], 'safe'],
            [['sort'], 'default', 'value' => ['d.created_at' => SORT_DESC]],
        ];
    }

    public function getList()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        $mall = \Yii::$app->mall;
        $pagination = null;
        $query = AreaAgent::find()->alias('d')->with(['user'])
            ->where(['d.is_delete' => 0, 'd.mall_id' => $mall->id])
            ->leftJoin(['u' => User::tableName()], 'u.id = d.user_id');
        if ($this->keyword) {
            $query->andWhere([
                'or',
                ['like', 'u.username', $this->keyword],
                ['like', 'u.nickname', $this->keyword]
            ]);
        }
        if ($this->level) {
            $query->andWhere(['d.level' => $this->level]);
        }
        $list = $query->page($pagination, $this->limit, $this->page)
            ->orderBy($this->sort)->all();
        $newList = [];

        $address_list = DistrictData::getArr();

        /* @var AreaAgent[] $list */
        foreach ($list as $item) {
            $newItem = ArrayHelper::toArray($item);
            /* @var User $user */
            $user = $item->user;
            $newItem = array_merge($newItem, [
                'nickname' => $user->nickname,
                'avatar_url' => $user->avatar_url,
                'parent_name' => $user->parent ? $user->parent->nickname : '平台',
            ]);
            $newItem['userInfo'] = ArrayHelper::toArray($item->user);
            $newItem['level_name'] = AreaAgent::LEVEL[$item->level];


            $province = '';
            $newItem['province'] = '未知';
            $newItem['city'] = '未知';
            $newItem['district'] = '未知';
            $newItem['town'] = '未知';
            foreach ($address_list as $address) {
                if ($address['level'] == 'province') {
                    if ($address['id'] == $item->province_id) {
                        $newItem['province'] = $address['name'];
                    }
                }
                if ($address['level'] == 'city') {
                    if ($address['id'] == $item->city_id) {
                        $newItem['city'] = $address['name'];
                    }
                }
                if ($address['level'] == 'district') {
                    if ($address['id'] == $item->district_id) {
                        $newItem['district'] = $address['name'];
                    }
                }
            }

            $town = Town::findOne($item->town_id);
            if ($town) {
                $newItem['town'] = $town->name;
            }


            $newItem['address']=$newItem['province'].','.$newItem['city'].','.$newItem['district'].','.$newItem['town'];

            $newList[] = $newItem;
        }

        return [
            'code' => 0,
            'msg' => '',
            'data' => [
                'list' => $newList,
                'pagination' => $pagination
            ]
        ];
    }
}
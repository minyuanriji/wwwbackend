<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: zal
 * Date: 2020-04-01
 * Time: 21:49
 */

namespace app\forms\common\card;


use app\models\Mall;
use app\models\Model;
use app\models\User;
use app\models\UserCard;

/**
 * @property Mall $mall
 * @property User $user
 */
class UserCardListCommon extends Model
{
    public $mall;
    public $user;
    public $user_id;
    public $page;
    public $limit;
    public $status;
    public $date;
    public $isArray = false;
    public $clerk_id;

    /**
     * @return array
     * 获取某个用户的卡券列表
     */
    public function getUserCardList()
    {
        $query = UserCard::find()->where(['mall_id' => $this->mall->id, 'is_delete' => 0])
            ->with(['clerk', 'store'])->keyword($this->clerk_id, ['clerk_id' => $this->clerk_id])
            ->keyword($this->user_id, ['user_id' => $this->user_id])
            ->orderBy(['created_at' => SORT_DESC]);

        switch ($this->status) {
            case 1:
                $query->andWhere(['is_use' => 0])->andWhere(['>', 'end_at', mysql_timestamp()]);
                break;
            case 2:
                $query->andWhere(['is_use' => 1]);
                break;
            case 3:
                $query->andWhere(['is_use' => 0])->andWhere(['<=', 'end_at', mysql_timestamp()]);
                break;
            default:
        }

        $list = $query->page($pagination, $this->limit)->asArray($this->isArray)->all();
        return [
            'list' => $list,
            'pagination' => $pagination
        ];
    }
}

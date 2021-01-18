<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 分销商团队
 * Author: zal
 * Date: 2020-04-16
 * Time: 10:45
 */

namespace app\forms\mall\distribution;


use app\forms\common\distribution\DistributionTeamCommon;
use app\models\BaseModel;
use app\models\User;

class TeamForm extends BaseModel
{
    public $status;
    public $id;

    public function rules()
    {
        return [
            [['status', 'id'], 'integer']
        ];
    }

    public function search()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        $form = new DistributionTeamCommon();
        $form->mall = \Yii::$app->mall;
        $res = $form->info($this->id, $this->status);

        $list = User::find()->alias('u')->where(['u.id' => $res, 'mall_id' => \Yii::$app->mall->id, 'is_delete' => 0])
            ->with(['userInfo'])->all();

        $newList = [];
        /* @var User[] $list*/
        foreach ($list as $item) {
            $newItem = [
                'nickname' => $item->nickname,
                'junior_at' => $item->user->junior_at
            ];
            $newList[] = $newItem;
        }

        return [
            'code' => 0,
            'msg' => '',
            'data' => [
                'list' => $newList
            ]
        ];
    }
}

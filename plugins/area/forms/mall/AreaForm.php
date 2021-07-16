<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-05-21
 * Time: 19:30
 */

namespace app\plugins\area\forms\mall;


use app\core\ApiCode;
use app\models\BaseModel;
use app\models\User;
use app\plugins\area\models\Area;
use app\plugins\area\models\AreaAgent;

class AreaForm extends BaseModel
{

    public $id;
    public $reason;

    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['reason'], 'string'],
            [['reason'], 'trim'],
        ];
    }

    public function delete()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        /* @var AreaAgent $area */
        $area = AreaAgent::find()->with(['userInfo'])
            ->where(['id' => $this->id, 'is_delete' => 0, 'mall_id' => \Yii::$app->mall->id])->one();

        if (!$area) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => '分销商不存在'
            ];
        }

        $t = \Yii::$app->db->beginTransaction();

        $area->is_delete = 1;
        $area->delete_reason = $this->reason;

        $area->deleted_at = time();
        if ($area->save()) {
            $user = User::findOne(['id' => $area->user_id]);
            $user->is_inviter = 0;
            $parentId = $area->user->parent_id;
            if ($user->save()) {
                User::updateAll(
                    ['parent_id' => 0],
                    ['or',
                        ['parent_id' => $area->user_id],
                        ['user_id' => $area->user_id]
                    ]
                );
                $t->commit();

                return [
                    'code' => ApiCode::CODE_SUCCESS,
                    'msg' => '删除成功'
                ];
            } else {
                $t->rollBack();
                return $this->responseErrorInfo($user);
            }
        } else {
            $t->rollBack();
            return $this->responseErrorInfo($area);
        }

    }

}
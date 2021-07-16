<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-04-16
 * Time: 11:48
 */

namespace app\forms\mall\member;


use app\core\ApiCode;
use app\core\BasePagination;
use app\forms\common\member\CommonMemberLevel;
use app\models\BaseModel;
use app\models\MemberLevel;
use app\models\MemberRights;
use app\models\User;

/**
 * Class MemberLevelForm
 * @package app\forms\mall\member
 * @Notes

 */
class MemberLevelForm extends BaseModel
{
    public $id;
    public $page;
    public $keyword;


    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['page'], 'default', 'value' => 1],
            [['keyword'], 'string']
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => '会员ID',
        ];
    }

    public function getList()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        $query = MemberLevel::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0,
        ]);

        if ($this->keyword) {
            $query->andWhere(['like', 'name', $this->keyword]);
        }

        /**
         * @var BasePagination $pagination
         */
        $list = $query->page($pagination)->orderBy(['level' => SORT_ASC])->asArray()->all();

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'list' => $list,
                'pagination' => $pagination
            ]
        ];
    }

    /**
     * 获取会员等级列表
     * @return array
     */
    public function getOptionList()
    {
        $list = MemberLevel::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0,
        ])->asArray()->all();

        $leves = [];
        foreach ($list as $item) {
            $leves[] = $item['level'];
        }

        $levelList = [];
        for ($i = 1; $i <= 100; $i++) {
            if (in_array($i, $leves)) {
                $levelList[] = [
                    'name' => '等级' . $i,
                    'level' => $i,
                    'disabled' => true,
                ];
            } else {
                $levelList[] = [
                    'name' => '等级' . $i,
                    'level' => $i,
                    'disabled' => false,
                ];
            }
        }

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'list' => $levelList
            ]
        ];
    }


    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-04-16
     * @Time: 11:50
     * @Note:获取所有会员等级
     * @return array
     */
    public function getAllMemberLevel()
    {
        $list = CommonMemberLevel::getAllMemberLevel();
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'list' => $list
            ]
        ];
    }

    public function getDetail()
    {
        $detail = CommonMemberLevel::getDetail($this->id);
        if ($detail) {
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '请求成功',
                'data' => [
                    'detail' => $detail,
                ]
            ];
        }

        return [
            'code' => ApiCode::CODE_FAIL,
            'msg' => '请求失败',
        ];
    }

    public function destroy()
    {
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $member = MemberLevel::findOne(['mall_id' => \Yii::$app->mall->id, 'id' => $this->id]);

            if (!$member) {
                return [
                    'code' => ApiCode::CODE_FAIL,
                    'msg' => '数据异常,该条数据不存在'
                ];
            }

            $member->is_delete = 1;
            $res = $member->save();
            if (!$res) {
                throw new \Exception($this->responseErrorMsg($member));
            }

//            $res = MemberRights::updateAll([
//                'is_delete' => 1,
//            ], [
//                'member_id' => $member->id
//            ]);

            $user = User::find()->where([
                'is_delete' => 0,
                'mall_id' => \Yii::$app->mall->id,
                'level' => $member->level
            ])->one();
            if ($user) {
                throw new \Exception('有用户属于该会员！无法删除');
            }

            $transaction->commit();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '删除成功',
            ];

        } catch (\Exception $e) {
            $transaction->rollBack();
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $e->getMessage(),
                'error' => [
                    'line' => $e->getLine(),
                    'msg' => $e->getMessage(),
                ]
            ];
        }
    }

    public function switchStatus()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        try {
            $member = MemberLevel::findOne($this->id);
            if (!$member) {
                return [
                    'code' => ApiCode::CODE_FAIL,
                    'msg' => '数据异常,该条数据不存在'
                ];
            }

            if ($member->status) {
                $user = User::find()->where([
                    'is_delete' => 0,
                    'mall_id' => \Yii::$app->mall->id,
                    'level' => $member->level
                ])->one();
                if ($user) {
                    throw new \Exception('有用户属于该会员！无法禁用');
                }
            }

            $member->status = $member->status ? 0 : 1;
            $res = $member->save();
            if (!$res) {
                $this->responseErrorInfo($member);
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '更新成功'
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $e->getMessage()
            ];
        }
    }
}
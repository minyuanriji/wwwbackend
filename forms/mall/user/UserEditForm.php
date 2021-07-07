<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 用户操作
 * Author: zal
 * Date: 2020-04-15
 * Time: 18:45
 */

namespace app\forms\mall\user;

use app\core\ApiCode;
use app\events\RelationChangeEvent;
use app\events\UserInfoEvent;
use app\forms\common\UserRoleTypeEditForm;
use app\handlers\RelationHandler;
use app\handlers\UserRelationChangeHandler;
use app\logic\UserLogic;
use app\models\BaseModel;
use app\models\User;
use app\models\UserRelationshipLink;

class UserEditForm extends BaseModel
{
    public $id;
    public $member_level;
    public $role_type;
    public $money;

    public $is_blacklist;
    public $remark;
    public $contact_way;
    public $parent_id;
    public $is_inviter;
    public $is_examine;

    public function rules()
    {
        return [
            [['parent_id', 'is_blacklist', 'id', 'member_level', 'is_inviter', 'is_examine'], 'integer'],
            [['money'], 'number'],
            [['contact_way', 'remark'], 'string', 'max' => 255],
            [['role_type'], 'string']
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'parent_id' => '上级id',
            'member_level' => '等级',
            'is_blacklist' => '是否黑名单',
            'contact_way' => '联系方式',
            'remark' => '备注',
            'money' => '佣金',
            'is_inviter' => '是否是推广者',
            'is_examine' => '是否拥有审核资格'
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        /* @var User $form */
        $form = User::find()->alias('u')
            ->where(['u.id' => $this->id])
            ->one();

        if (!$form) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => '数据为空'
            ];
        }

        //设置上级推荐人
        if($this->parent_id){
            try {
                if ($this->id == $this->parent_id) {
                    throw new \Exception("自己不能设置自己为父级id");
                }

                $parentUser = User::findOne($this->parent_id);
                if(!$parentUser){
                    throw new \Exception("上级推荐人不存在");
                }

                $parentLink = UserRelationshipLink::findOne(["user_id" => $parentUser->id]);
                $userLink = UserRelationshipLink::findOne(["user_id" => $this->id]);
                if(!$parentLink || !$userLink){
                    throw new \Exception("用户关系链异常");
                }

                if($parentLink->left > $userLink->left && $parentLink->right < $userLink->right){
                    throw new \Exception("上级推荐人不能变更为团队下级，必须是平级和上级");
                }
            }catch (\Exception $e){
                return [
                    'code' => ApiCode::CODE_FAIL,
                    'msg' => $e->getMessage()
                ];
            }

        }

        $beforeParentId = $form->parent_id;
        $form->is_blacklist = $this->is_blacklist;
        $form->parent_id = $this->parent_id;
        $form->junior_at = time();
        $form->level = $this->member_level;

        //用户角色类型修改
        if($form->role_type != $this->role_type){
            $userRoleTypeEditForm = new UserRoleTypeEditForm([
                "id"          => $form->id,
                "role_type"   => $this->role_type,
                "action"      => UserRoleTypeEditForm::ACTION_FORCE,
                "source_type" => "admin",
                "source_id"   => \Yii::$app->admin->id,
                "content"     => "管理员操作"
            ]);
            $userRoleTypeEditForm->save();
        }

        //推广资格被选中
        if ($this->is_inviter && $form->is_inviter == User::IS_INVITER_NO) {
            $form->setInviter();
        }else if(!$this->is_inviter && $form->is_inviter == User::IS_INVITER_YES){
            //推广资格被取消
            \Yii::$app->trigger(RelationHandler::INVITER_STATUS_CHANGE, new UserInfoEvent([
                'user_id' => $this->id,
                'mall_id' => $form->mall_id,
                'is_inviter' => $this->is_inviter
            ]));
            $form->is_inviter = $this->is_inviter;
        }
        $form->is_examine = $this->is_examine;
        $t = \Yii::$app->db->beginTransaction();
        try {
            if (!$form->save()) {
                throw new \Exception($this->responseErrorMsg($form));
            }
            $t->commit();
            \Yii::$app->trigger(UserRelationChangeHandler::CHANGE_PARENT, new RelationChangeEvent([
                'mall' => \Yii::$app->mall,
                'beforeParentId' => $beforeParentId,
                'parentId' => $this->parent_id,
                'userId' => $form->id
            ]));
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功'
            ];
        } catch (\Exception $e) {
            $t->rollBack();
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => "File:" . $e->getFile() . ";" . $e->getLine() . ";" . $e->getMessage()
            ];
        }
    }
}

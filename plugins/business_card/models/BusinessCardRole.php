<?php

namespace app\plugins\business_card\models;

use app\models\BaseActiveRecord;
use app\models\Order;
use app\models\OrderDetail;
use app\models\User;
use Yii;

/**
 * 角色
 * This is the model class for table "{{%plugin_business_card_role}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property string $name
 * @property int $created_at
 * @property int $updated_at
 * @property int $deleted_at
 * @property User $user
 * @property OrderDetail $orderDetail
 */
class BusinessCardRole extends BaseActiveRecord
{
    const ID_BOSS = 1;
    const ID_SUPERVISOR = 2;
    const ID_EMPLOYEE = 3;

    public static $ids = [
        self::ID_BOSS => "boss",
        self::ID_SUPERVISOR => "主管",
        self::ID_EMPLOYEE => "员工"
    ];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%plugin_business_card_role}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'name'], 'required'],
            [['mall_id', 'is_delete'], 'integer'],
            [['name'], 'string'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'mall_id' => 'Mall ID',
            'name' => '角色名',
            'is_delete' => 'Is Delete',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
        ];
    }

    /**
     * 获取角色信息
     * @param $roleId
     * @param $mallId
     * @return BusinessCardRole|null
     */
    public static function getInfo($roleId,$mallId){
        $roles = self::findOne($roleId);
        if(empty($roles)){
            $roles = new BusinessCardRole();
            $roles->mall_id = $mallId;
            $roles->id = $roleId;
            $roles->name = self::$ids[$roleId];
            $roles->save();
        }
        return $roles;
    }
}

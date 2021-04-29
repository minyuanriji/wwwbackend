<?php

namespace app\models;

use Yii;
use yii\db\Migration;

/**
 * This is the model class for table "{{%core_action_log}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $admin_id 操作人
 * @property string $name 名称
 * @property string $model 模型名称
 * @property string $module 模块名称
 * @property int $model_id 模型ID
 * @property string $operate_ip 操作ip
 * @property string $before_update 更新之前的数据
 * @property string $after_update 更新之后的数据
 * @property int $created_at 创建时间
 * @property int $is_delete
 * @property string $remark
 * @property Admin $admin
 */
class AdminOperateLog extends BaseActiveRecord
{

    public $isLog = false;

    public function safeUp()
    {
        $tableOptions = "COMMENT = '操作日志' CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB";
        $migration = new Migration();
        $migration->createTable(
            '{{%admin_operate_log}}',
            [
                'id'=> $migration->primaryKey(11),
                'mall_id'=> $migration->tinyInteger(11)->notNull()->defaultValue(0)->comment('0:get;1post'),
                'admin_id'=> $migration->tinyInteger(11)->notNull()->defaultValue('0')->comment('操作人ID'),
                'name'=> $migration->string(255)->notNull()->defaultValue('')->comment('名称'),
                'model'=> $migration->string(255)->notNull()->defaultValue('')->comment('模型'),
                'module'=> $migration->string(50)->notNull()->defaultValue('')->comment('模块'),
                'model_id'=> $migration->tinyInteger(11)->notNull()->defaultValue('')->comment('模型ID'),
                'operate_ip'=> $migration->string(15)->defaultValue('')->comment('操作ip'),
                'created_at'=> $migration->integer(11)->notNull()->defaultValue(0)->comment('创建时间'),
                'before_update'=> $migration->string(5000)->notNull()->defaultValue('')->comment('更新前内容'),
                'after_update'=> $migration->string(5000)->notNull()->defaultValue('')->comment('更新后内容'),
                'created_at'=> $migration->string(50)->notNull()->defaultValue('')->comment('创建时间'),
                'is_delete'=> $migration->tinyInteger(1)->notNull()->defaultValue(0)->comment('是否删除'),
                'remark'=> $migration->string(255)->notNull()->defaultValue('')->comment('备注'),
            ],$tableOptions
        );
        $migration->createIndex('mall_id','{{%common_operation_log}}',['mall_id'],false);
        $migration->createIndex('admin_id','{{%common_operation_log}}',['admin_id'],false);
        $migration->createIndex('model','{{%common_operation_log}}',['model'],false);
        $migration->createIndex('model_id','{{%common_operation_log}}',['model_id'],false);

    }

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%admin_operate_log}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'admin_id', 'model_id', 'before_update', 'after_update', 'created_at'], 'required'],
            [['mall_id', 'admin_id', 'model_id', 'is_delete'], 'integer'],
            [['name','before_update', 'after_update', 'model','remark','operate_ip','module'], 'string'],
            [['created_at'], 'safe'],
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
            'admin_id' => '操作人id',
            'model_id' => '模型id',
            'model' => '模型',
            'module' => '模块',
            'name' => '名称',
            'operate_ip' => '操作ip',
            'before_update' => '更新前内容',
            'after_update' => '更新后内容',
            'created_at' => 'Created At',
            'is_delete' => 'Is Delete',
            'remark' => 'Remark',
        ];
    }

    public function getAdmin()
    {
        return $this->hasOne(Admin::className(), ['id' => 'admin_id']);
    }
}

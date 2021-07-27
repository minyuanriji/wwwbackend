<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%attachment_info}}".
 *
 * @property int $id ID
 * @property int $mall_id 商城ID
 * @property int $admin_id 管理员ID
 * @property string $url 真实url
 * @property string $type image、doc、video
 * @property string $name 文件名称
 * @property int $size 文件大小
 * @property int $from 来源1后台2前台
 * @property int $is_delete 是否删除
 * @property int $is_recycle 是否加入回收站
 * @property int $group_id 分组ID
 * @property string|null $thumb_url 缩略图
 * @property int $mch_id 多商户ID
 * @property int $deleted_at 删除时间
 * @property int $created_at 新增时间
 */
class AttachmentInfo extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%attachment_info}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'url', 'type', 'name', 'created_at'], 'required'],
            [['mall_id', 'is_delete','size', 'created_at', 'group_id', 'mch_id','admin_id','is_recycle','deleted_at','from'], 'integer'],
            [['url', 'thumb_url'], 'string', 'max' => 2048],
            [['type'], 'string', 'max' => 8],
            [['name'], 'string', 'max' => 128],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'mall_id' => '商城ID',
            'admin_id' => '管理员ID',
            'url' => '真实url',
            'type' => 'image、doc、video',
            'name' => '文件名称',
            'size' => '文件大小',
            'from' => '来源',
            'is_delete' => '是否删除',
            'created_at' => '新增时间',
            'group_id' => '分组ID',
            'thumb_url' => '缩略图',
            'mch_id' => '多商户ID',
            'deleted_at' => '删除时间',
            'is_recycle' => '是否加入回收站',
        ];
    }
}

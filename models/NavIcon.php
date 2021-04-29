<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 导航图标
 * Author: zal
 * Date: 2020-04-13
 * Time: 10:12
 */

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%nav_icon}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property string $name 导航名称
 * @property string $url 导航链接
 * @property string $icon_url 导航图标
 * @property string $sort 排序
 * @property string $params 排序
 * @property string $open_type 排序
 * @property int $status 状态：0.隐藏|1.显示
 * @property int $created_at
 * @property int $updated_at
 * @property int $deleted_at
 * @property int $is_delete
 */
class NavIcon extends BaseActiveRecord
{

    /** @var int 显示 */
    const STATUS_DIS = 1;
    /** @var int 隐藏 */
    const STATUS_HIDE = 0;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%nav_icon}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'created_at', 'updated_at', 'deleted_at'], 'required'],
            [['mall_id', 'status', 'is_delete', 'sort'], 'integer'],
            [['created_at', 'updated_at', 'deleted_at', 'params'], 'safe'],
            [['name', 'open_type'], 'string', 'max' => 65],
            [['url', 'icon_url'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'mall_id' => '商城 ID',
            'name' => '导航名称',
            'url' => '导航链接',
            'icon_url' => '导航图标',
            'sort' => '排序',
            'open_type' => '链接类型',
            'params' => '导航属性',
            'status' => '导航状态',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
            'is_delete' => 'Is Delete',
        ];
    }
}

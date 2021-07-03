<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%material_article}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property string $content
 * @property string $thumb_media_id
 * @property int $updated_at
 * @property int $created_at
 * @property int $deleted_at
 * @property string $title
 * @property string $cover_pic
 * @property int $is_delete
 * @property string $media_id
 * @property string|null $source_url 原文链接
 * @property string|null $article_desc
 * @property string $author
 * @property string|null $thumb_url
 * @property string|null $url
 */
class MaterialArticle extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%material_article}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'content', 'thumb_media_id', 'title', 'cover_pic', 'media_id'], 'required'],
            [['mall_id', 'updated_at', 'created_at', 'deleted_at', 'is_delete'], 'integer'],
            [['content'], 'string'],
            [['thumb_media_id', 'title', 'media_id', 'author'], 'string', 'max' => 64],
            [['cover_pic', 'source_url', 'article_desc', 'thumb_url','url'], 'string', 'max' => 255],
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
            'content' => 'Content',
            'thumb_media_id' => 'Thumb Media ID',
            'updated_at' => 'Updated At',
            'created_at' => 'Created At',
            'deleted_at' => 'Deleted At',
            'title' => 'Title',
            'cover_pic' => 'Cover Pic',
            'is_delete' => 'Is Delete',
            'media_id' => 'Media ID',
            'source_url' => '原文链接',
            'article_desc' => 'Article Desc',
            'author' => 'author',
            'thumb_url' => 'thumb_url',
            'url'=>'url'
        ];
    }
}
<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-04-23
 * Time: 10:58
 */

namespace app\forms\mall\wechat;


use app\core\ApiCode;
use app\models\BaseModel;
use EasyWeChat\Kernel\Messages\Article;
use app\models\MaterialArticle;


class MaterialArticleEditForm extends BaseModel
{
    public $content;
    public $thumb_media_id;
    public $title;
    public $cover_pic;
    public $article_desc;
    public $source_url;
    public $author;
    public $media_id;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['content', 'thumb_media_id', 'title', 'cover_pic'], 'required'],
            [['content'], 'string'],
            [['thumb_media_id', 'title', 'author', 'media_id'], 'string', 'max' => 64],
            [['cover_pic', 'source_url', 'article_desc'], 'string', 'max' => 255],
        ];
    }

    public function save()
    {
        $app = \Yii::$app->wechat->getApp();
        $article = new Article([
            'title' => $this->title,
            'thumb_media_id' => $this->thumb_media_id,
            'author' => $this->author, // 作者
            'show_cover' => 1, // 是否在文章内容显示封面图片
            'digest' => $this->article_desc,
            'content' => $this->content,
            'source_url' => $this->source_url,
        ]);

        $material_artcle = MaterialArticle::findOne(['media_id' => $this->media_id, 'mall_id' => \Yii::$app->mallId, 'is_delete' => 0]);
        if (!$material_artcle) {
            $material_artcle = new MaterialArticle();
            $res = $app->material->uploadArticle($article);
            $media_id = $res['media_id'];
        } else {
            $media_id = $this->media_id;
            $res = $app->material->updateArticle($this->media_id, $article);
        }
        if ($res) {
            $material_artcle->attributes = $this->attributes;
            $material_artcle->media_id = $media_id;
            $material_artcle->mall_id = \Yii::$app->mallId;
            $res = $app->material->get($media_id);
            $news_item = $res['news_item'];
            foreach ($news_item as $item) {
                $material_artcle->url = $item->url;
                $material_artcle->thumb_url = $item['thumb_url'];
            }
            if ($material_artcle->save()) {
                return [
                    'code' => ApiCode::CODE_SUCCESS,
                    'msg' => '保存成功',
                ];
            }

        }
        return [
            'code' => ApiCode::CODE_FAIL,
            'msg' => '保存失败',
            'error' => $this->responseErrorMsg($material_artcle)
        ];
    }
}
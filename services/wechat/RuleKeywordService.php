<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-04-24
 * Time: 10:36
 */

namespace app\services\wechat; //注意小写
use app\models\Material;
use app\models\MaterialArticle;
use app\models\ReplyRule;
use app\models\RuleKeyword;
use EasyWeChat\Kernel\Messages\Image;
use EasyWeChat\Kernel\Messages\News;
use EasyWeChat\Kernel\Messages\NewsItem;
use EasyWeChat\Kernel\Messages\Text;
use EasyWeChat\Kernel\Messages\Video;
use EasyWeChat\Kernel\Messages\Voice;
use yii\base\Component;

class RuleKeywordService extends Component
{
    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-04-24
     * @Time: 14:25
     * @Note:匹配关键词
     * @param $content
     * @return bool|Image|Text|Voice
     */
    public static function match($content)
    {
        $keyword = RuleKeyword::find()->where([
            'or',
            ['and', '{{type}} = :typeMatch', '{{keyword}} = :keyword'], // 直接匹配关键字
            ['and', '{{type}} = :typeInclude', 'INSTR(:keyword, {{keyword}}) > 0'], // 包含关键字
        ])->addParams([
            ':keyword' => $content,
            ':typeMatch' => RuleKeyword::TYPE_MATCH,
            ':typeInclude' => RuleKeyword::TYPE_INCLUDE,
        ])
            ->andWhere(['is_delete' => 0,'status'=>1])
            ->orderBy('id desc')
            ->one();
        if (!$keyword) {
            return false;
        }

        /* @var $model ReplyRule */
        $model = ReplyRule::find()
            ->where(['id' => $keyword->rule_id])
            ->one();

        switch ($model->reply_type) {
            // 文字回复
            case  ReplyRule::RULE_TYPE_TEXT :
                return new Text($model->content);
                break;
            // 图文回复
            case  ReplyRule::RULE_TYPE_ARTICLE:
                $article_list = MaterialArticle::find()->where(['media_id' => $model->content, 'is_delete' => 0, 'mall_id' => \Yii::$app->mallId])->all();
                $newsList = [];
                /**
                 * @var MaterialArticle $article
                 */
                foreach ($article_list as $article) {
                    $newsList[] = new NewsItem([
                        'title' => $article->title,
                        'description' => $article->article_desc,
                        'url' => $article->url,
                        'image' => $article->thumb_url,
                    ]);
                }
                return new News($newsList);
                break;
            // 图片回复
            case  ReplyRule::RULE_TYPE_IMAGE :
                return new Image($model->content);
                break;
            // 视频回复
            case ReplyRule::RULE_TYPE_VIDEO :
                $material = Material::findOne(['is_delete' => 0, 'media_id' => $model->content]);
                if ($material) {
                    return new Video($model->content, [
                        'title' => $material->name,
                        'description' => $material->material_desc,
                    ]);
                }
                return new Text('未找到您想要内容！');
                break;
            // 语音回复
            case ReplyRule::RULE_TYPE_VOICE :
                $material = Material::findOne(['is_delete' => 0, 'media_id' => $model->content]);
                if ($material) {
                    return new Voice($model->content);
                }
                return new Text('未找到您想要内容！');
                break;
            default :
                return false;
                break;
        }
    }
}
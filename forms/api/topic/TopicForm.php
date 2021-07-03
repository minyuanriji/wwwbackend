<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-05-27
 * Time: 11:20
 */

namespace app\forms\api\topic;


use app\core\ApiCode;
use app\core\BasePagination;
use app\helpers\ArrayHelper;
use app\helpers\SerializeHelper;
use app\models\BaseModel;
use app\models\Topic;
use app\models\TopicFavorite;
use app\models\TopicType;

class TopicForm extends BaseModel
{
    public $page;
    public $limit;
    public $id;
    public $type;
    public $is_love;

    public function rules()
    {
        return [
            [['id', 'limit', 'type'], 'integer',],
            [['limit',], 'default', 'value' => 10],
            [['is_love'], 'string'],
        ];
    }

    public function getTypeList()
    {
        if (!$this->validate()) {
            return $this->returnApiResultData();
        }

        $list = TopicType::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0,
            'status' => 1
        ])->orderBy('sort ASC,id DESC')
            ->asArray()
            ->all();

        return $this->returnApiResultData(
            ApiCode::CODE_SUCCESS,
            '',
            [
                'list' => $list
            ]);
    }

    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-06-01
     * @Time: 16:06
     * @Note:搜索
     * @return array
     */
    public function getList()
    {
        if (!$this->validate()) {
            return $this->returnApiResultData();
        }

        $query = Topic::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0
        ]);

        if ($this->type == '-1') {
            $query = $query->andWhere(['is_chosen' => 1]);
        } elseif ($this->type) {
            $query = $query->andWhere(['type' => $this->type]);
        }

        /**
         * @var BasePagination $pagination ;
         */
        $list = $query->orderBy("sort ASC, id DESC")->page($pagination, $this->limit, $this->page)->all();

        array_walk($list, function (&$item) {
            $item = $item->toArray();
            $read_count = intval($item['read_count'] + $item['virtual_read_count']);
            $goods_class = 'class="goods-link"';
            $goods_count = mb_substr_count($item['content'], $goods_class);
            $item['read_count'] = $read_count < 10000 ? $read_count . '人浏览' : intval($read_count / 10000) . '万+人浏览';
            $item['goods_count'] = $goods_count ? $goods_count . '件宝贝' : '';
            $item['pic_list'] = $item['pic_list'] ? \Yii::$app->serializer->decode($item['pic_list']) : [];
            unset($item['content']);
        });
        unset($item);

        return $this->returnApiResultData(ApiCode::CODE_SUCCESS, '', [
            'list' => $list,
            'pagination' => [
                'total_count' => $pagination->total_count,
                'page_count' => $pagination->page_count,
                'pageSize' => $pagination->pageSize,
                'current_page' => $pagination->current_page
            ]]);
    }

    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-06-01
     * @Time: 16:06
     * @Note:详情
     * @return array
     */
    public function getDetail()
    {
        if (!$this->validate()) {
            return $this->returnApiResultData();
        }
        $query = Topic::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'id' => $this->id,
            'is_delete' => 0,
        ])->with(['favorite' => function ($query) {
            try {
                $user_id = \Yii::$app->user->identity->id;
            } catch (\Exception $e) {
                $user_id = 0;
            }
            $query->where(['user_id' => $user_id, 'is_delete' => 0]);
        }]);

        $topic = $query->one();
        if (!$topic) {
            return $this->returnApiResultData(ApiCode::CODE_SUCCESS, '文章不存在');
        }
        $topic->read_count++;
        $topic->save();
        $favorite = (!\Yii::$app->user->isGuest && $topic->favorite) ? ArrayHelper::toArray($topic->favorite) : [];
        $topic = ArrayHelper::toArray($topic);
        $read_count = intval($topic['read_count'] + $topic['virtual_read_count']);
        $goods_class = 'class="goods-link"';
        $goods_count = mb_substr_count($topic['content'], $goods_class);
        $topic['is_love'] = count($favorite) == 0 ? 'no_love' : 'love';
        $topic['read_count'] = $read_count < 10000 ? $read_count . '人浏览' : intval($read_count / 10000) . '万+人浏览';
        $topic['goods_count'] = $goods_count ? $goods_count . '件宝贝' : '';
        if ($topic['detail']) {
            $topic['detail'] = SerializeHelper::decode($topic['detail']);
        } else {
            $topic['detail'] = [
                [
                    'id' => 'image-text',
                    'data' => [
                        'content' => $topic['content']
                    ]
                ]
            ];
        }

        return $this->returnApiResultData(
            ApiCode::CODE_SUCCESS,
            '',
            [
                'topic' => $topic
            ]);
    }

    public function favorite()
    {
        if (!$this->validate()) {
            return $this->returnApiResultData();
        }
        $model = TopicFavorite::findOne([
            'user_id' => \Yii::$app->user->identity->id,
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0,
            'topic_id' => $this->id,
        ]);
        if (!$this->is_love && $model) {
            $model->is_delete = 1;
            $model->deleted_at = time();
            $model->save();
            return $this->returnApiResultData(ApiCode::CODE_SUCCESS, '不喜欢');
        }

        if ($this->is_love) {
            if (!$model) {
                $model = new TopicFavorite();
                $model->mall_id = \Yii::$app->mall->id;
                $model->user_id = \Yii::$app->user->identity->id;
                $model->topic_id = $this->id;
            }
            if ($model->save()) {
                return $this->returnApiResultData(ApiCode::CODE_SUCCESS, '喜欢');

            }
            return $this->responseErrorMsg($model);
        }
        return $this->returnApiResultData(ApiCode::CODE_SUCCESS, '喜欢');
    }
}

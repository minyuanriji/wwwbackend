<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 文章
 * Author: zal
 * Date: 2020-04-14
 * Time: 11:19
 */

namespace app\forms\mall\shop;

use app\core\ApiCode;
use app\models\Article;
use app\models\BaseModel;

class ArticleForm extends BaseModel
{
    public $id;
    public $title;
    public $content;
    public $sort;
    public $is_delete;
    public $page_size;
    public $status;
    public $keyword;
    
    public function rules()
    {
        return [
            [['sort', 'is_delete', 'id', 'page_size', 'status'], 'integer'],
            [['content'], 'string'],
            [['title', 'keyword'], 'string', 'max' => 255],
            [['keyword', ], 'default', 'value' => ''],
            [['is_delete', 'sort', 'status'], 'default', 'value' => 0],
            [['page_size'], 'default', 'value' => 10],
            [['sort'], 'integer', 'min' => 0, 'max' => 999999999],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => '标题',
            'content' => '内容',
            'sort' => '排序：升序',
            'is_delete' => 'Is Delete',
        ];
    }

    //GET
    public function getList()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        $list = Article::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0,
        ])
        ->keyword($this->keyword, ['like', 'title', $this->keyword])
        ->orderBy('sort ASC,id DESC')
        ->page($pagination)
        ->asArray()
        ->all();

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'list' => $list,
                'pagination' => $pagination,
            ]
        ];
    }

    //DELETE
    public function destroy()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        $model = Article::findOne([
            'id' => $this->id,
            'mall_id' => \Yii::$app->mall->id,
        ]);
        if (!$model) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => '数据不存在或已经删除',
            ];
        }
        $model->is_delete = 1;
        $model->deleted_at = date('Y-m-d H:i:s');
        $model->save();
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '删除成功'
        ];
    }

    //DETAIL
    public function getDetail()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        $articles = Article::find()->where(['mall_id' => \Yii::$app->mall->id, 'id' => $this->id])->asArray()->one();
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => $articles
        ];
    }

    public function switchStatus()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        $model = Article::findOne([
            'id' => $this->id,
            'mall_id' => \Yii::$app->mall->id,
        ]);
        if (!$model) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => '数据不存在或已经删除',
            ];
        }
        $model->status = $this->status;
        if ($model->save()) {
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '切换成功'
            ];
        } else {
            return $this->responseErrorInfo($model);
        }
    }
    //SAVE
    public function save()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        $model = Article::findOne([
            'mall_id' => \Yii::$app->mall->id,
            'id' => $this->id,
        ]);
        if (!$model) {
            $model = new Article();
        }
        $model->attributes = $this->attributes;
        $model->mall_id = \Yii::$app->mall->id;

        $model->article_cat_id = 0;//TODO
        if ($model->save()) {
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功'
            ];
        } else {
            return $this->responseErrorInfo($model);
        }
    }
}

<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 视频
 * Author: zal
 * Date: 2020-04-14
 * Time: 16:55
 */

namespace app\forms\mall\shop;

use app\core\ApiCode;
use app\models\BaseModel;
use app\models\Video;

class VideoForm extends BaseModel
{
    public $model;
    public $page;
    public $page_size;

    public $id;
    public $title;
    public $type;
    public $url;
    public $pic_url;
    public $content;
    public $sort;

    public function rules()
    {
        return [
            // [['title', 'type', 'content', 'pic_url'], 'required'],
            [['type', 'sort', 'id', 'page'], 'integer'],
            [['content'], 'string'],
            [['title', 'pic_url'], 'string', 'max' => 255],
            [['url'], 'string', 'max' => 2048],
            [['sort'], 'default', 'value' => 0],
            [['page_size'], 'default', 'value' => 10],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => '标题',
            'type' => '视频来源 0--源地址 1--腾讯视频',
            'url' => '链接',
            'content' => '详情介绍',
            'pic_url' => '缩略图',
            'sort' => '排序',
        ];
    }

    /**
     * 获取列表数据
     * @return array
     */
    public function getList()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        };

        $list = Video::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0,
        ])
        ->page($pagination, $this->page_size)
        ->orderBy('sort ASC,id DESC')
        ->asArray()
        ->all();
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'list' => $list,
                'pagination' => $pagination
            ]
        ];
    }

    /**
     * 删除
     * @return array
     */
    public function destroy()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }
        
        $model = Video::findOne([
            'id' => $this->id,
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0
        ]);
        if (!$model) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => '数据不存在或已删除',
            ];
        }
        $model->is_delete = 1;
        $model->deleted_at = time();
        $model->save();
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '删除成功'
        ];
    }

    /**
     * 详情
     * @return array
     */
    public function getDetail()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        $list = Video::find()->where(['mall_id' => \Yii::$app->mall->id,'id' => $this->id])->one();
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'list' => $list,
            ]
        ];
    }

    /**
     * 保存
     * @return array
     */
    public function save()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        $model = Video::findOne([
            'mall_id' => \Yii::$app->mall->id,
            'id' => $this->id
        ]);
        if (!$model) {
            $model = new Video();
        }

        $model->attributes = $this->attributes;
        $model->mall_id = \Yii::$app->mall->id;
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

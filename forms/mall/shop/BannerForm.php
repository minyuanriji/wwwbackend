<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: zal
 * Date: 2020-04-11
 * Time: 15:16
 */

namespace app\forms\mall\shop;

use app\core\ApiCode;
use app\models\Banner;
use app\models\BaseModel;

class BannerForm extends BaseModel
{
    public $page;
    public $page_size;
    public $ids;

    public $id;
    public $mall_id;
    public $pic_url;
    public $title;
    public $page_url;
    public $sort;
    public $is_delete;
    public $params;
    public $open_type;

    public function rules()
    {
        return [
            [['mall_id', 'sort', 'id', 'is_delete'], 'integer'],
            [['title'], 'string', 'max' => 255],
            [['page_url', 'pic_url'], 'string', 'max' => 2048],
            [['title'],'default','value' => '暂无标题'],
            [['is_delete', 'sort'], 'default', 'value' => 0],
            [['sort'], 'integer', 'min' => 0, 'max' => 999999999],
            [['page'], 'default', 'value' => 1],
            [['ids'], 'safe'],
            [['page_size'], 'default', 'value' => 9],
            [['open_type'], 'default', 'value' => ''],
            [['open_type'], 'string', 'max' => 65],
            [['params'], 'trim'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'mall_id' => 'mall ID',
            'pic_url' => '图片',
            'title' => '标题',
            'page_url' => 'Page Url',
            'sort' => '排序',
            'is_delete' => '删除',
            'open_type' => '打开方式',
            'params' => '导航参数1',
        ];
    }

    /**
     * 列表数据
     * @return array
     */
    public function getList()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }
        $query = Banner::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0,
        ]);

        $list = $query->page($pagination, $this->page_size, $this->page)
            ->orderBy('created_at DESC, id DESC')
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

    //DELETE
    public function destroy()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        $ids = $this->ids;
        if (!$ids) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => '数据不存在或已删除',
            ];
        }

        Banner::updateAll(['is_delete' => 1,'deleted_at' => date('Y-m-d H:i:s')], [
            'id' => $ids,
        ]);
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => 'success'
        ];
    }

    //DETAIL
    public function getDetail()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }
        
        $banners = Banner::find()->where([
                'mall_id' => \Yii::$app->mall->id,
                'id' => $this->id
            ])->asArray()->one();
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => (object)$banners
        ];
    }

    //SAVE
    public function save()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }
        $model = Banner::findOne([
            'mall_id' => \Yii::$app->mall->id,
            'id' => $this->id,
        ]);
        if (!$model) {
            $model = new Banner();
        }

        $model->attributes = $this->attributes;
        $model->mall_id = \Yii::$app->mall->id;
        $model->params = \Yii::$app->serializer->encode($this->params);
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

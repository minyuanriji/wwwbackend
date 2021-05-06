<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 图片魔方表单
 * Author: zal
 * Date: 2020-04-13
 * Time: 15:00
 */

namespace app\forms\mall\shop;

use app\core\ApiCode;
use app\models\BaseModel;
use app\models\ImgMagic;

class ImgMagicForm extends BaseModel
{
    public $id;
    public $page;
    public $keyword;

    public function rules()
    {
        return [
            [['id',], 'integer'],
            [['page'], 'default', 'value' => 1],
            [['keyword'], 'string'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => '魔方ID',
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

        $query = ImgMagic::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0,
        ]);

        if ($this->keyword) {
            $query->andWhere(['like', 'name', $this->keyword]);
        }
        $list = $query->page($pagination)->orderBy(['created_at' => SORT_DESC])->asArray()->all();

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'list' => $list,
                'pagination' => $pagination
            ]
        ];
    }

    /**
     * 详情
     * @return array
     */
    public function getDetail()
    {
        $detail = ImgMagic::find()->where(['mall_id' => \Yii::$app->mall->id, 'id' => $this->id])->asArray()->one();

        if ($detail) {
            if ($detail['value']) {
                $detail['value'] = json_decode($detail['value'], true);
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '请求成功',
                'data' => [
                    'detail' => $detail,
                ]
            ];
        }

        return [
            'code' => ApiCode::CODE_FAIL,
            'msg' => '请求失败',
        ];
    }

    /**
     * 删除
     * @return array
     */
    public function destroy()
    {
        try {
            $homeBlock = ImgMagic::findOne(['mall_id' => \Yii::$app->mall->id, 'id' => $this->id]);

            if (!$homeBlock) {
                return [
                    'code' => ApiCode::CODE_FAIL,
                    'msg' => '数据异常,该条数据不存在'
                ];
            }

            $homeBlock->is_delete = 1;
            $res = $homeBlock->save();

            if ($res) {
                return [
                    'code' => ApiCode::CODE_SUCCESS,
                    'msg' => '删除成功',
                ];
            }

            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => '删除失败',
            ];

        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $e->getMessage(),
            ];
        }
    }
}

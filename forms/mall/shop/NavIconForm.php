<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 导航图标
 * Author: zal
 * Date: 2020-04-13
 * Time: 10:16
 */

namespace app\forms\mall\shop;

use app\core\ApiCode;
use app\models\BaseModel;
use app\models\NavIcon;

class NavIconForm extends BaseModel
{
    public $id;
    public $page;
    public $status;
    public $keyword;
    public $limit;

    public function rules()
    {
        return [
            [['id', 'status', 'limit'], 'integer'],
            [['page'], 'default', 'value' => 1],
            [['keyword'], 'string'],
            [['keyword'], 'trim'],
            [['limit'], 'default', 'value' => 20],
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
        }

        $query = NavIcon::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0,
        ])->keyword($this->keyword, ['like', 'name', $this->keyword]);


        $list = $query->page($pagination, $this->limit)
            ->orderBy('sort ASC')
            ->asArray()->all();

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
        /* @var NavIcon $detail */
        $detail = NavIcon::find()->where(['mall_id' => \Yii::$app->mall->id, 'id' => $this->id])->one();

        $detail->params = $detail->params ? \Yii::$app->serializer->decode($detail->params) : [];
        if ($detail) {
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
            $navIcon = NavIcon::findOne(['mall_id' => \Yii::$app->mall->id, 'id' => $this->id]);

            if (!$navIcon) {
                return [
                    'code' => ApiCode::CODE_FAIL,
                    'msg' => '数据异常,该条数据不存在'
                ];
            }

            $navIcon->is_delete = 1;
            $res = $navIcon->save();

            if (!$res) {
                return [
                    'code' => ApiCode::CODE_FAIL,
                    'msg' => $this->responseErrorInfo($navIcon),
                ];
            }
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '删除成功',
            ];

        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $e->getMessage(),
            ];
        }
    }

    /**
     * 状态更新
     * @return array
     */
    public function status()
    {
        try {
            $navIcon = NavIcon::findOne(['mall_id' => \Yii::$app->mall->id, 'id' => $this->id]);

            if (!$navIcon) {
                return [
                    'code' => ApiCode::CODE_FAIL,
                    'msg' => '数据异常,该条数据不存在'
                ];
            }

            $navIcon->status = $this->status;
            $res = $navIcon->save();

            if ($res) {
                return [
                    'code' => ApiCode::CODE_SUCCESS,
                    'msg' => '更新成功',
                ];
            }

            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => '更新失败',
            ];

        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $e->getMessage(),
            ];
        }
    }
}

<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-04-16
 * Time: 14:13
 */

namespace app\forms\mall\sensitive;

use app\core\ApiCode;
use app\models\BaseModel;
use app\models\Sensitive;

class SensitiveForm extends BaseModel
{
    public $id;
    public $page;
    public $sensitive;
    public $keyword;
    public $mch_id;


    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['page'], 'default', 'value' => 1],
            [['keyword'], 'string'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => '主键ID',
        ];
    }

    /**
     * @Note: 获取敏感词列表，可带查询
     * @return array
     */

    public function search()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }
        $query = Sensitive::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'mch_id' => \Yii::$app->admin->identity->mch_id,
            'is_delete' => 0,
        ]);

        if ($this->keyword) {
            $query->andWhere(['like', 'sensitive', $this->keyword]);
        }
        $list = $query->page($pagination)
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

    public function delete()
    {
        $services = Sensitive::find()->where([
            'id' => $this->id,
            'mall_id' => \Yii::$app->mall->id,
            'mch_id' => \Yii::$app->admin->identity->mch_id,
        ])->one();

        if (!$services) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => '数据异常,该条数据不存在',
            ];
        }

        try {
            $services->is_delete = 1;
            $res = $services->save();

            if (!$res) {
                return [
                    'code' => ApiCode::CODE_FAIL,
                    'msg' => $this->responseErrorMsg($services),
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
                'error' => [
                    'line' => $e->getLine()
                ]
            ];
        }
    }
}
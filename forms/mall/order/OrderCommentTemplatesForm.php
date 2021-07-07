<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 订单评价模板
 * Author: zal
 * Date: 2020-04-17
 * Time: 14:11
 */

namespace app\forms\mall\order;

use app\core\ApiCode;
use app\models\BaseModel;
use app\models\OrderCommentsTemplates;

class OrderCommentTemplatesForm extends BaseModel
{
    public $id;
    public $page_size;
    public $is_show;
    public $keyword;
    public $keyword_name;
    public $type;

    public function rules()
    {
        return [
            [['id', 'page_size', 'type',], 'integer'],
            [['keyword', 'keyword_name'], 'string'],
            [['keyword',], 'default', 'value' => ''],
            [['page_size'], 'default', 'value' => 10],
        ];
    }

    //GET
    public function search()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }
        $query = OrderCommentsTemplates::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'mch_id' => \Yii::$app->admin->identity->mch_id,
            'is_delete' => 0,
        ]);

        if ($this->keyword) {
            switch ($this->keyword_name) {
                case 'id':
                    $query->andWhere(['like', 'id', $this->keyword]);
                    break;
                case 'title':
                    $query->andWhere(['like', 'title', $this->keyword]);
                    break;
                default:
                    break;
            }
        }

        if ($this->type > 0) {
            $query->andWhere(['type' => $this->type]);
        }

        $list = $query->orderBy('created_at DESC')
            ->page($pagination, $this->page_size)
            ->all();

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'list' => $list,
                'pagination' => $pagination,
            ]
        ];
    }

    public function getDetail()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        $detail = OrderCommentsTemplates::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'mch_id' => \Yii::$app->admin->identity->mch_id,
            'is_delete' => 0,
            'id' => $this->id,
        ])->one();

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'detail' => $detail,
            ]
        ];
    }


    //DELETE
    public function destroy()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }
        try {
            $model = OrderCommentsTemplates::findOne([
                'id' => $this->id,
                'is_delete' => 0,
                'mall_id' => \Yii::$app->mall->id,
                'mch_id' => \Yii::$app->admin->identity->mch_id,
            ]);
            if (!$model) {
                throw new \Exception('数据不存在或已经删除');
            }

            $model->is_delete = 1;
            $res = $model->save();
            if (!$res) {
                throw new \Exception($this->responseErrorMsg($model));
            }
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '删除成功'
            ];
        }catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $exception->getMessage(),
            ];
        }
    }
}

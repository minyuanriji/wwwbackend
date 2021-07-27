<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/12
 * Time: 11:38
 */

namespace app\forms\mall\free_delivery_rules;


use app\core\ApiCode;
use app\models\BaseModel;
use app\models\FreeDeliveryRules;


class ListForm extends BaseModel
{
    public $limit;
    public $page;
    public $keyword;

    public function rules()
    {
        return [
            [['limit', 'page'], 'integer'],
            ['limit', 'default', 'value' => 20],
            ['page', 'default', 'value' => 1],
            [['keyword'], 'string'],
        ];
    }

    public function search()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        /* @var $pagination \app\core\BasePagination */
        $pagination = null;
        $query = FreeDeliveryRules::find()->where([
            'is_delete' => 0,
            'mall_id' => \Yii::$app->mall->id,
            'mch_id' => \Yii::$app->admin->identity->mch_id,
        ]);
        if ($this->keyword) {
            $query->andWhere(['like', 'name', $this->keyword]);
        }
        $list = $query->page($pagination, $this->limit, $this->page)->all();
        /* @var $list FreeDeliveryRules[] */
        foreach ($list as &$value) {
            $value->detail = \Yii::$app->serializer->decode($value->detail);
        }
        unset($value);
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => 'success',
            'data' => [
                'list' => $list,
                'pagination' => $pagination,
                'ofs' => $pagination->offset,
                'lim' => $pagination->limit
            ]
        ];
    }
}

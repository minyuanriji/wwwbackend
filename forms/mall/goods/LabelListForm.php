<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-04-29
 * Time: 17:44
 */

namespace app\forms\mall\goods;


use app\core\ApiCode;
use app\core\BasePagination;
use app\models\BaseModel;
use app\models\Label;

class LabelListForm extends BaseModel
{
    public $search;
    public $page;
    public $limit = 20;

    public function rules()
    {
        return [
            [['page'], 'integer'],
            [['search'], 'safe']
        ];
    }

    public function search()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }
        /**
         * @var BasePagination $pagination
         */
        $query = Label::find()->where(['is_delete' => 0, 'mall_id' => \Yii::$app->mall->id]);
        if ($this->search) {
            $query->andWhere(['or', ['like', 'title', $this->search], ['like', 'sub_title', $this->search]]);
        }
        $list = $query->page($pagination, $this->limit, $this->page)->orderBy('created_at DESC')->asArray()->all();;
        foreach ($list as &$item) {
            $item['updated_at'] = date('Y-m-d H:i:s', $item['updated_at']);
        }
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'pagination' => $pagination,
                'list' => $list,
            ]
        ];
    }


}
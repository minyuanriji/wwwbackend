<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-04-23
 * Time: 12:05
 */

namespace app\forms\mall\wechat;


use app\core\ApiCode;
use app\core\BasePagination;
use app\models\BaseModel;
use app\models\MaterialArticle;

class MaterialArticleListForm extends BaseModel
{


    public $id;
    public $page;

    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['page'], 'default', 'value' => 1],
        ];
    }

    public function search()
    {
        $query = MaterialArticle::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0,
        ]);
        /**
         * @var BasePagination $pagination
         */
        $list = $query->page($pagination)->orderBy(['created_at' => SORT_ASC])->asArray()->all();

        foreach ($list as &$item) {
            $item['updated_at'] = date('Y-m-d H:i:s', $item['updated_at']);
        }
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'list' => $list,
                'pagination' => $pagination
            ]
        ];
    }


}
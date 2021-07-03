<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-04-06
 * Time: 17:23
 */

namespace app\forms\common;


use app\models\FilePath;
use yii\base\Model;
use yii\data\Pagination;


/**
 * Class DemoListForm
 * @package app\forms\common
 * 一个标准的listForm的写法
 */
class DemoListForm extends Model
{
    public $mall_id;
    public $limit;
    public $page;

    public function rules()
    {
        return [
            [['page', 'limit'], 'integer'],
            [['page',], 'default', 'value' => 1],
            [['limit',], 'default', 'value' => 20],
        ]; // TODO: Change the autogenerated stub
    }


    public function search()
    {
        $query = FilePath::find()->where(['mall_id' => $this->mall_id, 'is_delete' => 0]);
        $count = $query->count();
        $pagination = new Pagination(['totalCount' => $count, 'page' => $this->page - 1, 'pageSize' => $this->limit,]);
        /* @var FilePath[] $list */
        $list = $query->orderBy('created_at DESC')->limit($pagination->limit)->offset($pagination->offset)->all();
        return [
            'code' => 0,
            'msg' => 'success',
            'data' => [
                'row_count' => $count,
                'page_count' => $pagination->pageCount,
                'list' => $list,
            ],
        ];

    }


}
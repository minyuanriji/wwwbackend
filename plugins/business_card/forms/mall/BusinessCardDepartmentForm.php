<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 部门
 * Author: zal
 * Date: 2020-07-09
 * Time: 14:10
 */

namespace app\plugins\business_card\forms\mall;

use app\models\BaseModel;
use app\models\User;
use app\plugins\business_card\models\BusinessCardDepartment;

class BusinessCardDepartmentForm extends BaseModel
{
    public $limit = 10;
    public $page = 1;
    public $sort;

    public function rules()
    {
        return [
            [['limit', 'page'], 'integer'],
            [['sort'], 'default', 'value' => ['d.created_at' => SORT_DESC]],
        ];
    }

    public function getList()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }
        $mall = \Yii::$app->mall;
        $list = BusinessCardDepartment::getData(['mall_id' => $mall->id,"limit" => $this->limit,"page" => $this->page,"sort_key" => "sort","sort_val" => " asc"]);
        $newList = [];
        /* @var Distribution[] $list */
        foreach ($list["list"] as $item) {
            /* @var User $user */
            $item["created_at"] = date("Y-m-d H:i:s",$item["created_at"]);
            $newList[] = $item;
        }
        return [
            'code' => 0,
            'msg' => '',
            'data' => [
                'list' => $newList,
                'pagination' => $list["pagination"],
            ]
        ];
    }
}
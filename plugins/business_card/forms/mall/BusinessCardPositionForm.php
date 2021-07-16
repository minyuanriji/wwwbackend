<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 职位
 * Author: zal
 * Date: 2020-07-09
 * Time: 14:10
 */

namespace app\plugins\business_card\forms\mall;

use app\models\BaseModel;
use app\models\User;
use app\plugins\business_card\models\BusinessCardPosition;

class BusinessCardPositionForm extends BaseModel
{
    public $limit = 10;
    public $page = 1;
    public $bcpid;

    public function rules()
    {
        return [
            [['limit', 'page','bcpid'], 'integer'],
        ];
    }

    public function getList()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }
        $mall = \Yii::$app->mall;
        $list = BusinessCardPosition::getData(['mall_id' => $mall->id,'bcpid' => $this->bcpid,'department' => 1,"limit" => $this->limit,"page" => $this->page]);
        $newList = [];
        foreach ($list["list"] as $item) {
            /* @var User $user */
            $item["created_at"] = date("Y-m-d H:i:s",$item["created_at"]);
            $item["department_name"] = isset($item["department"]["name"]) ? $item["department"]["name"] : "";
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
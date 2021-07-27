<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 用户操作日志
 * Author: zal
 * Date: 2020-04-14
 * Time: 17:19
 */

namespace app\forms\mall\setting;

use app\core\ApiCode;
use app\models\AdminUserVisitLog;
use app\models\BaseModel;

class UserVisitLogForm extends BaseModel
{
    public $page;
    public $id;

    public function rules()
    {
        return [
            [['page', 'id'], 'integer'],
            [['page'], 'default', 'value' => 1]
        ];
    }

    public function getList()
    {
        $query = AdminUserVisitLog::find();
        $list = $query->where(['mall_id' => \Yii::$app->mall->id])
                ->orderBy(['id' => SORT_DESC])
                ->page($pagination)
                ->all();
        foreach ($list as $item) {
            $item->created_at = date("Y-m-d H:i:s",$item->created_at);
            if ($item->type == 'applets') {
                $item->type = '小程序';
            } elseif ($item->type == 'official_account'){
                $item->type = '公众号';
            }
        }
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => "请求成功",
            'data' => [
                'list' => $list,
                'pagination' => $pagination
            ]
        ];
    }
}
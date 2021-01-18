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
use app\models\Admin;
use app\models\AdminOperateLog;
use app\models\BaseModel;
use app\models\ActionLog;
use yii\helpers\ArrayHelper;

class OperateLogForm extends BaseModel
{
    public $keyword;
    public $page;
    public $id;

    public function rules()
    {
        return [
            [['keyword'], 'trim'],
            [['page', 'id'], 'integer'],
            [['page'], 'default', 'value' => 1]
        ];
    }

    public function getList()
    {
        /** @var Admin $admins */
        $admins = \Yii::$app->admin->identity;
        $query = AdminOperateLog::find()->with("admin");
        if($admins->admin_type == Admin::ADMIN_TYPE_SUPER){
            $query->where(['is_delete' => 0]);
        }else{
            $query->where(['admin_id' => $admins->id,'is_delete' => 0]);
        }
        $list = $query->where(['mall_id' => \Yii::$app->mall->id])
                ->orderBy(['id' => SORT_DESC])
                ->page($pagination)
                ->all();

        $newList = [];
        /** @var AdminOperateLog $item */
        foreach ($list as $item) {
            $newItem = ArrayHelper::toArray($item);
            $newItem['admin'] = $item->admin;
            $newItem['name'] = $item->name."-".$item->remark;
            $newItem["created_at"] = date("Y-m-d H:i:s",$item["created_at"]);
            $newList[] = $newItem;
        }

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => "请求成功",
            'data' => [
                'list' => $newList,
                'pagination' => $pagination
            ]
        ];
    }

    public function getDetail()
    {
        /** @var ActionLog $detail */
        $detail = ActionLog::find()->where(['id' => $this->id])
            ->with('user')
            ->one();

        $detail->after_update = \Yii::$app->serializer->decode($detail->after_update);
        $detail->before_update = \Yii::$app->serializer->decode($detail->before_update);

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => "请求成功",
            'data' => [
                'detail' => $detail,
            ]
        ];
    }
}
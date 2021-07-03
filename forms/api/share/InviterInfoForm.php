<?php
namespace app\forms\api\share;

use app\core\ApiCode;
use app\models\BaseModel;
use app\models\User;

class InviterInfoForm extends BaseModel{

    public function getDetail(){

        try {
            $headers = \Yii::$app->request->headers;
            $parentId = !empty($headers['x-parent-id']) ? (int)$headers['x-parent-id'] : 0;
            $parent = User::findOne($parentId);
            if(!$parent || $parent->is_delete){
                throw new \Exception("无法获取推荐人信息");
            }

            $detail = [
                'id'         => $parent->id,
                'nickname'   => $parent->nickname,
                'avatar_url' => $parent->avatar_url,
                'role_type'  => $parent->role_type
            ];

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'detail' => $detail
                ]
            ];
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }

    }

}
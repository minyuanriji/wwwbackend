<?php
namespace app\forms\common;


use app\core\ApiCode;
use app\models\BaseModel;
use app\models\User;
use app\models\UserRoleTypeLog;

class UserRoleTypeEditForm extends BaseModel{

    const ACTION_UPGRADE = "upgrade";
    const ACTION_REDUCE  = "reduce";
    const ACTION_FORCE   = "force";

    public $id;
    public $role_type;
    public $action;
    public $source_type;
    public $source_id;
    public $content;


    public function rules(){
        return [
            [['id', 'role_type', 'action', 'source_type', 'source_id'], 'required'],
            [['content'], 'safe']
        ];
    }

    public function save(){
        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {

            $user = User::findOne($this->id);
            if(!$user || $user->is_delete){
                throw new \Exception("用户不存在");
            }

            $weights = ["user" => 1, "store" => 2, "partner" => 3, "branch_office" => 4];
            $targetWeight = isset($weights[$this->role_type]) ? $weights[$this->role_type] : 0;
            $currentType = $user->role_type;
            $currentWeight = isset($weights[$currentType]) ? $weights[$currentType] : 0;
            if($targetWeight <= 0){
                throw new \Exception("无效等级类型");
            }
            if($this->action == self::ACTION_UPGRADE){ //升级
                if($targetWeight > $currentWeight){
                    $user->role_type = $this->role_type;
                }
            }elseif($this->action == self::ACTION_REDUCE){ //降级
                if($currentWeight > $targetWeight){
                    $user->role_type = $this->role_type;
                }
            }else{ //强制改变
                $user->role_type = $this->role_type;
            }

            if($user->role_type != $currentType){
                $log = new UserRoleTypeLog([
                    'mall_id'     => \Yii::$app->mall->id,
                    'user_id'     => $user->id,
                    'origin_type' => $currentType,
                    'target_type' => $this->role_type,
                    'source_id'   => $this->source_id,
                    'source_type' => $this->source_type,
                    'content'     => $this->content,
                    'created_at'  => time()
                ]);
                if(!$log->save()){
                    throw new \Exception($this->responseErrorMsg($log));
                }
                if(!$user->save()){
                    throw new \Exception($this->responseErrorMsg($user));
                }
            }

        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }
    }
}
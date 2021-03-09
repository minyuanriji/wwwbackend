<?php
namespace app\models\user;
use yii\db\ActiveRecord;
class User extends ActiveRecord{
    public function updateUsers($data,$id){
        return $this -> updateAll($data,['id' => $id]);
    }
    
     public function getOneUserInfo($id){
        return $this -> find() -> select(['id','static_integral','dynamic_integral','level','parent_id']) -> where(['=','id',$id]) -> asArray() -> one();
    }

    public function getOneUserMobile($id){
        return $this -> find() -> select(['mobile']) -> where(['id' => $id]) -> asArray() -> one();
    }

    public function getOneUserParent($token){
        return $this -> find() -> select('id,parent_id') -> where(['access_token' => $token]) -> asArray() -> one();
    }


}
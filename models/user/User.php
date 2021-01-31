<?php
namespace app\models\user;
use yii\db\ActiveRecord;
class User extends ActiveRecord{
    public function updateUsers($data,$id){
        return $this -> updateAll($data,['id' => $id]);
    }
}
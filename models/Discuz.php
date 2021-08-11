<?php
/**
 * Created by PhpStorm.
 * User: 阿源
 * Date: 2020/10/29
 * Time: 15:30
 */

namespace app\models;
use Yii;
use yii\db\ActiveRecord;

class Discuz extends ActiveRecord {


    public static function tableName(){
        return '{{%forum_post}}';
    }



    public static function getDb(){
        return Yii::$app->get('db2');

    }


}
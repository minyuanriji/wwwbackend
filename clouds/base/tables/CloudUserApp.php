<?php
/**
 * @link:http://www.######.com/
 * @copyright: Copyright (c) #### ########
 * 用户云应用
 * Author: Mr.Lin
 * Email: 746027209@qq.com
 * Date: 2021-07-05 14:13
 */
namespace app\clouds\base\tables;


/**
 * Class CloudUserApp
 * @package app\clouds\models
 * @property integer $user_id
 * @property string  $name
 * @property string  $host
 * @property integer $is_deleted
 * @property integer $deleted_at
 */
class CloudUserApp extends BaseActiveRecord
{
    public static function tableName()
    {
        return "{{%cloud_user_app}}";
    }

    public function rules()
    {
        return [
            [['user_id', 'name', 'host'], 'required'],
            [['is_deleted', 'user_id', 'deleted_at'], 'integer']
        ];
    }

}
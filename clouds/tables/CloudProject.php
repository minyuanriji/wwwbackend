<?php
/**
 * @link:http://www.######.com/
 * @copyright: Copyright (c) #### ########
 * 云项目
 * Author: Mr.Lin
 * Email: 746027209@qq.com
 * Date: 2021-07-06 16:19
 */

namespace app\clouds\tables;

/**
 * Class CloudProject
 * @package app\clouds\tables
 * @property string  $name
 * @property string  $class_dir
 * @property integer $is_deleted
 * @property integer $deleted_at
 *
 */
class CloudProject extends BaseActiveRecord
{
    public static function tableName()
    {
        return "{{%cloud_projects}}";
    }

    public function rules()
    {
        return [
            [['name', 'class_dir'], 'required'],
            [['is_deleted', 'deleted_at'], 'safe']
        ];
    }

}
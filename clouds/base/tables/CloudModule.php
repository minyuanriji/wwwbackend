<?php
/**
 * @link:http://www.######.com/
 * @copyright: Copyright (c) #### ########
 * 云模块
 * Author: Mr.Lin
 * Email: 746027209@qq.com
 * Date: 2021-07-06 16:25
 */

namespace app\clouds\base\tables;

/**
 * Class CloudModule
 * @package app\clouds\tables
 * @property integer $author_id
 * @property integer $project_id
 * @property string  $name
 * @property string  $class_dir
 * @property integer $is_deleted
 * @property integer $deleted_at
 */
class CloudModule extends BaseActiveRecord
{
    public static function tableName()
    {
        return "{{%cloud_modules}}";
    }

    public function rules()
    {
        return [
            [['author_id', 'project_id', 'name', 'class_dir'], 'required'],
            [['is_deleted', 'deleted_at'], 'safe']
        ];
    }

}



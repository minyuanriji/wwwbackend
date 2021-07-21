<?php
/**
 * @link:http://www.######.com/
 * @copyright: Copyright (c) #### ########
 * 云项目
 * Author: Mr.Lin
 * Email: 746027209@qq.com
 * Date: 2021-07-06 16:19
 */

namespace app\clouds\base\tables;

/**
 * Class CloudProject
 * @package app\clouds\tables
 * @property integer $author_id
 * @property string  $name
 * @property string  $security
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
            [['author_id', 'name', 'security', 'class_dir'], 'required'],
            [['is_deleted', 'deleted_at'], 'safe']
        ];
    }

}
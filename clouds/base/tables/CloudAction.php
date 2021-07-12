<?php
/**
 * @link:http://www.######.com/
 * @copyright: Copyright (c) #### ########
 * 云操作
 * Author: Mr.Lin
 * Email: 746027209@qq.com
 * Date: 2021-07-06 14:13
 */
namespace app\clouds\base\tables;

/**
 * Class CloudAction
 * @package app\clouds\tables
 * @property integer $author_id
 * @property integer $project_id
 * @property integer $module_id
 * @property string  $name
 * @property string  $controllerID
 * @property string  $actionID
 * @property string  $security
 * @property string  $class_dir
 * @property integer $is_deleted
 * @property integer $deleted_at
 */
class CloudAction extends BaseActiveRecord
{
    public static function tableName()
    {
        return "{{%cloud_actions}}";
    }

    public function rules()
    {
        return [
            [['author_id', 'class_dir', 'project_id', 'security', 'module_id', 'name', 'controllerID', 'actionID'], 'required'],
            [['is_deleted', 'deleted_at'], 'safe']
        ];
    }
}





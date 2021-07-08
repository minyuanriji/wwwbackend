<?php
/**
 * @link:http://www.######.com/
 * @copyright: Copyright (c) #### ########
 * 云操作
 * Author: Mr.Lin
 * Email: 746027209@qq.com
 * Date: 2021-07-06 14:13
 */
namespace app\clouds\tables;

/**
 * Class CloudAction
 * @package app\clouds\tables
 * @property integer $project_id
 * @property integer $module_id
 * @property string  $name
 * @property string  $controllerID
 * @property string  $actionID
 * @property string  $class_dir
 *
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
            [['project_id', 'security', 'module_id', 'name', 'controllerID', 'actionID'], 'required'],
            [['class_dir'], 'safe']
        ];
    }
}





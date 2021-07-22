<?php
/**
 * @link:http://www.######.com/
 * @copyright: Copyright (c) #### ########
 * 用户路由表
 * Author: Mr.Lin
 * Email: 746027209@qq.com
 * Date: 2021-07-06 15:40
 */

namespace app\clouds\base\tables;

/**
 * Class CloudUserAppRouteList
 * @package app\clouds\tables
 * @property integer $action_id
 * @property string  $host_name
 * @property string  $path_uri
 */
class CloudActionRoute extends BaseActiveRecord
{
    public static function tableName()
    {
        return "{{%cloud_action_route}}";
    }

    public function rules()
    {
        return [
            [['action_id', 'host_name', 'path_uri'], 'required'],
            [[], 'integer']
        ];
    }
}




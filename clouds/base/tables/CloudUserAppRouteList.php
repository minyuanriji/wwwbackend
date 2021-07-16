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
 * @property integer $user_id
 * @property integer $app_id
 * @property integer $action_id
 * @property string  $path_uri
 */
class CloudUserAppRouteList extends BaseActiveRecord
{
    public static function tableName()
    {
        return "{{%cloud_user_app_route_list}}";
    }

    public function rules()
    {
        return [
            [['user_id', 'app_id', 'action_id', 'path_uri'], 'required'],
            [[], 'integer']
        ];
    }
}




<?php
/**
 * @link:http://www.######.com/
 * @copyright: Copyright (c) #### ########
 * Author: Mr.Lin
 * Email: 746027209@qq.com
 * Date: 2021-07-12 14:35
 */
namespace app\clouds\base\tables;


class CloudAuthProjectList extends BaseActiveRecord
{
    public static function tableName()
    {
        return "{{%cloud_auth_project_list}}";
    }

    public function rules()
    {
        return [
            [['auth_id', 'target_id', 'user_id'], 'required'],
            [[], 'safe']
        ];
    }

}
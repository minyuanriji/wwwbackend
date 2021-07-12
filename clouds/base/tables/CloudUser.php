<?php
/**
 * @link:http://www.######.com/
 * @copyright: Copyright (c) #### ########
 * 云用户
 * Author: Mr.Lin
 * Email: 746027209@qq.com
 * Date: 2021-07-07 18:07
 */
namespace app\clouds\base\tables;



/**
 * @property string $mobile
 * @property string $nickname
 */
class CloudUser extends BaseActiveRecord
{
    public static function tableName()
    {
        return "{{%cloud_users}}";
    }

    public function rules()
    {
        return [
            [['mobile', 'nickname'], 'required']
        ];
    }

}
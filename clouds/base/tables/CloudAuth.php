<?php
/**
 * @link:http://www.######.com/
 * @copyright: Copyright (c) #### ########
 * Author: Mr.Lin
 * Email: 746027209@qq.com
 * Date: 2021-07-12 14:31
 */
namespace app\clouds\base\tables;

/**
 * @property string $target
 */
class CloudAuth extends BaseActiveRecord
{
    public static function tableName()
    {
        return "{{%cloud_auth}}";
    }

    public function rules()
    {
        return [
            [['target'], 'required'],
            [[], 'safe']
        ];
    }
}
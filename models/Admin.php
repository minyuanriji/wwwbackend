<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 基础model
 * Author: zal
 * Date: 2020-04-08
 * Time: 15:12
 */

namespace app\models;

use Yii;
use yii\web\IdentityInterface;
use yii\base\Model;

/**
 * This is the model class for table "{{%admin}}".
 *
 * @property int $id ID
 * @property string $username 用户名
 * @property string $password 密码
 * @property string $auth_key
 * @property int $mall_id
 * @property int $mch_id
 * @property string $access_token
 * @property int $admin_type 管理员类型1超级管理员2管理员3操作员
 * @property int $mall_num
 * @property int $is_delete 是否删除
 * @property int $expired_at 过期时间
 * @property int $created_at 创建时间
 * @property int $updated_at 更新时间
 *
 */
class Admin extends BaseActiveRecord implements IdentityInterface
{

    /**
     * 超级管理员
     */
    const ADMIN_TYPE_SUPER = 1;
    /**
     * 管理员
     */
    const ADMIN_TYPE_ADMIN = 2;
    /**
     * 操作员
     */
    const ADMIN_TYPE_OPERATE = 3;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%admin}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['username', 'password', 'admin_type'], 'required'],
            [['admin_type','mall_id','mch_id', 'is_delete', 'mall_num', 'expired_at', 'created_at', 'updated_at'], 'integer'],
            [['password', 'auth_key','access_token'], 'string'],
            [['username'], 'string', 'max' => 15],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'username' => '用户名',
            'password' => '密码',
            'mall_id' => '商城id',
            'mch_id' => '商户id',
            'auth_key' => '',
            'access_token' => '',
            'admin_type' => '管理员类型',
            'mall_num' => '商城数量',
            'is_delete' => '是否删除',
            'expired_at' => '过期时间',
            'created_at' => '创建时间',
            'updated_at' => '更新时间',
        ];
    }

    /**
     * 根据给到的ID查询身份。
     * @Author: 广东七件事 zal
     * @Date: 2020-04-08
     * @Time: 19:49
     * @param string|integer $id 被查询的ID
     * @return IdentityInterface|null 通过ID匹配到的身份对象
     */
    public static function findIdentity($id)
    {
        return static::findOne($id);
    }

    /**
     * 根据 token 查询身份。
     * @Author: 广东七件事 zal
     * @Date: 2020-04-08
     * @Time: 19:49
     * @param string $token 被查询的 token
     * @return IdentityInterface|null 通过 token 得到的身份对象
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::findOne(['access_token' => $token]);
    }

    /**
     * @Author: 广东七件事 zal
     * @Date: 2020-04-08
     * @Time: 19:49
     * @return int|string 当前用户ID
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @Author: 广东七件事 zal
     * @Date: 2020-04-08
     * @Time: 19:49
     * @return string 当前用户的（cookie）认证密钥
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * @Author: 广东七件事 zal
     * @Date: 2020-04-08
     * @Time: 19:49
     * @param string $authKey
     * @return boolean if auth key is valid for current user
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    public function getAdminInfo()
    {
        return $this->hasOne(AdminInfo::className(), ['admin_id' => 'id']);
    }

    public function getRole()
    {
        return $this->hasMany(Role::className(), ['id' => 'role_id'])
            ->viaTable(RoleUser::tableName(), ['admin_id' => 'id', 'is_delete' => 'is_delete']);
    }

    public function getMall()
    {
        return $this->hasMany(Mall::className(), ['admin_id' => 'id']);
    }

    /**
     * 添加管理员
     * @Author: 广东七件事 zal
     * @Date: 2020-04-08
     * @Time: 19:49
     * @param $data
     * @return bool
     * @throws \Exception
     */
    public static function createAdminUser($data)
    {
        $model = new BaseModel();
        // $data 需传参数
        $arr = [
            'username' => '用户名',
            'password' => '密码',
            'mall_num' => '最大商城数：无限制则传整数：0',
            'expired_at' => '账户过期日期 默认请传：0',
        ];

        self::checkData($arr, $data);

        // 判断账号是否重复
        $admin = Admin::find()->alias('u')
            ->where(['u.username' => [$data['username']], 'u.is_delete' => 0])
            ->one();
        if ($admin) {
            throw new \Exception('账号已存在');
        }

        $admin = new Admin();
        $admin->admin_type = $data["admin_type"];
        $admin->username = $data['username'];
        $admin->password = $hash = \Yii::$app->getSecurity()->generatePasswordHash($data['password']);
        $admin->auth_key = \Yii::$app->security->generateRandomString();
        $admin->access_token = \Yii::$app->security->generateRandomString();
        $admin->mall_num = $data['mall_num'];
        $admin->expired_at = $data['expired_at'] != 0 ? strtotime($data['expired_at']) : $data['expired_at'];
        $res = $admin->save();
        if (!$res) {
            throw new \Exception($model->responseErrorMsg($admin));
        }

        // 管理员信息
        $adminInfo = new AdminInfo();
        $adminInfo->admin_id = $admin->id;
        $adminInfo->remark = $data['remark'] ? $data['remark'] : '';
        if (!is_array($data['permissions'])) {
            throw new \Exception('权限列表必须为数组');
        }
        $adminInfo->permissions = \Yii::$app->serializer->encode($data['permissions']);
        $adminInfo->secondary_permissions = \Yii::$app->serializer->encode($data['secondary_permissions']);
        $res = $adminInfo->save();
        if (!$res) {
            throw new \Exception($model->responseErrorMsg($adminInfo));
        }

        return $admin;
    }

    /**
     * 更新管理员
     * @Author: 广东七件事 zal
     * @Date: 2020-04-08
     * @Time: 19:49
     * @param $data
     * @return Admin|null
     * @throws \Exception
     */
    public static function updateAdminUser($data)
    {
        $model = new BaseModel();
        // $data 需传参数
        $arr = [
            'id' => '管理员id',
            'mall_num' => '最多商城限制：无限制则传整数：0',
            'expired_at' => '账户过期日期 默认请传：0',
            'permissions' => '账户插件权限: 默认请传空数组'
        ];
        self::checkData($arr, $data);
        $admin = Admin::findOne($data['id']);
        if (!$admin) {
            throw new \Exception('数据不存在');
        }
        /** @var AdminInfo $adminInfo */
        $adminInfo = AdminInfo::find()->where(['admin_id' => $data['id'], 'is_delete' => 0])->one();
        if (!$adminInfo) {
            throw new \Exception('信息不存在');
        }
        $admin->mall_num = $data['mall_num'];
        $admin->expired_at = $data['expired_at'] != 0 ? strtotime($data['expired_at']) : $data['expired_at'];
        $res = $admin->save();
        if (!$res) {
            throw new \Exception($model->responseErrorMsg($admin));
        }

        // 管理员信息
        $adminInfo->remark = $data['remark'] ? $data['remark'] : '';
        if (!is_array($data['permissions'])) {
            throw new \Exception('权限列表必须为数组');
        }
        $adminInfo->permissions = \Yii::$app->serializer->encode($data['permissions']);
        $adminInfo->secondary_permissions = \Yii::$app->serializer->encode($data['secondary_permissions']);
        $res = $adminInfo->save();
        if (!$res) {
            throw new \Exception($model->responseErrorMsg($adminInfo));
        }

        return $admin;
    }

    /**
     * 检测参数数据
     * @Author: 广东七件事 zal
     * @Date: 2020-04-08
     * @Time: 19:49
     * @param $arr
     * @param $data
     * @throws \Exception
     */
    private static function checkData($arr, $data)
    {
        foreach ($arr as $key => $item) {
            if (!isset($data[$key])) {
                throw new \Exception('请传参数' . $key . '->' . $item);
            }
        }
    }
}

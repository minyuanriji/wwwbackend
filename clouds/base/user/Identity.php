<?php
/**
 * @link:http://www.######.com/
 * @copyright: Copyright (c) #### ########
 * 客户端凭证
 * Author: Mr.Lin
 * Email: 746027209@qq.com
 * Date: 2021-07-07 18:37
 */
namespace app\clouds\base\user;


use app\clouds\base\tables\CloudUser;
use yii\base\BaseObject;
use yii\web\IdentityInterface;

class Identity extends BaseObject implements IdentityInterface
{
    private $cloudUserModel;

    private static $identitys = [];

    public function __construct(CloudUser $cloudUserModel, $config = [])
    {
        parent::__construct($config);
        $this->cloudUserModel = $cloudUserModel;
        static::$identitys[$cloudUserModel->id] = $this;
    }

    /**
     * Finds an identity by the given ID.
     * @param string|int $id the ID to be looked for
     * @return IdentityInterface|null the identity object that matches the given ID.
     * Null should be returned if such an identity cannot be found
     * or the identity is not in an active state (disabled, deleted, etc.)
     */
    public static function findIdentity($id)
    {
        if(!isset(static::$identitys[$id]))
        {
            $cloudUserModel = CloudUser::findOne($id);
            if($cloudUserModel)
            {
                static::$identitys[$id] = new static($cloudUserModel);
            }
        }
        return isset(static::$identitys[$id]) ? static::$identitys[$id] : null;
    }

    /**
     * Finds an identity by the given token.
     * @param mixed $token the token to be looked for
     * @param mixed $type the type of the token. The value of this parameter depends on the implementation.
     * For example, [[\yii\filters\auth\HttpBearerAuth]] will set this parameter to be `yii\filters\auth\HttpBearerAuth`.
     * @return IdentityInterface|null the identity object that matches the given token.
     * Null should be returned if such an identity cannot be found
     * or the identity is not in an active state (disabled, deleted, etc.)
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        // TODO: Implement findIdentityByAccessToken() method.
    }

    /**
     * Returns an ID that can uniquely identify a user identity.
     * @return string|int an ID that uniquely identifies a user identity.
     */
    public function getId()
    {
        return $this->cloudUserModel->id;
    }

    /**
     * Returns a key that can be used to check the validity of a given identity ID.
     *
     * The key should be unique for each individual user, and should be persistent
     * so that it can be used to check the validity of the user identity.
     *
     * The space of such keys should be big enough to defeat potential identity attacks.
     *
     * The returned key is used to validate session and auto-login (if [[User::enableAutoLogin]] is enabled).
     *
     * Make sure to invalidate earlier issued authKeys when you implement force user logout, password change and
     * other scenarios, that require forceful access revocation for old sessions.
     *
     * @return string|null a key that is used to check the validity of a given identity ID.
     * @see validateAuthKey()
     */
    public function getAuthKey()
    {
        // TODO: Implement getAuthKey() method.
    }

    /**
     * Validates the given auth key.
     *
     * @param string $authKey the given auth key
     * @return bool|null whether the given auth key is valid.
     * @see getAuthKey()
     */
    public function validateAuthKey($authKey)
    {
        // TODO: Implement validateAuthKey() method.
    }
}
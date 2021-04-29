<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 后台用户业务处理类
 * Author: zal
 * Date: 2020-04-09
 * Time: 14:36
 */

namespace app\logic;

use app\models\Admin;
use app\models\AdminInfo;

class AdminLogic extends BaseLogic
{

    /**
     * 搜索用户
     * @Author: 广东七件事 zal
     * @Date: 2020-04-09
     * @Time: 14:49
     * @param  [type] $keyword [description]
     * @return [type]          [description]
     */
    public static function selectAdminList($keyword)
    {
        $keyword = trim($keyword);

        $query = Admin::find()->alias('u')->select('u.id,u.username')->where(['LIKE', 'u.username', $keyword])
                    ->andWhere(['u.mall_id' => \Yii::$app->mall->id]);
        $list = $query->orderBy('u.id')->limit(30)->asArray()->all();

        return [
            'list' => $list,
        ];
    }

    /**
     * 获取单个管理员信息
     * @Author: 广东七件事 zal
     * @Date: 2020-04-09
     * @Time: 14:49
     * @param string $columns
     * @return array
     */
    public static function getAdmin($columns = '*')
    {
        $adminInfo = Admin::find()->where([
            'id' => \Yii::$app->admin->id,
            'is_delete' => 0
        ])->select($columns)->one();
        return $adminInfo;
    }

    /**
     * 获取单个管理员基础信息
     * @Author: 广东七件事 zal
     * @Date: 2020-04-09
     * @Time: 14:49
     * @param string $columns
     * @return array|\yii\db\ActiveRecord|null|AdminInfo
     */
    public static function getAdminInfo($columns = '*')
    {
        $adminInfo = AdminInfo::find()->where([
            'admin_id' => \Yii::$app->admin->id,
            'is_delete' => 0
        ])->select($columns)->one();
        return $adminInfo;
    }


    /**
     * 获取员工的所有权限路由数组
     * @Author: 广东七件事 zal
     * @Date: 2020-04-09
     * @Time: 14:49
     * @return array
     */
    public static function getAdminAllPermissions()
    {
        $user = Admin::find()->where([
            'id' => \Yii::$app->admin->id
        ])->with(['role.permissions'])->asArray()->one();

        $newPermissions = ['mall/overview/index'];
        foreach ($user['role'] as $item) {
            $newPermissions = array_merge($newPermissions, json_decode($item['permissions']['permissions']));
        }
        $newPermissions = array_values(array_unique($newPermissions));

        //如果是插件路由，权限路由需加上插件路由
        foreach ($newPermissions as $item) {
            if (strpos($item, 'plugin/')  !== false) {
                $newPermissions[] = 'mall/plugin/index';
                break;
            }
        }

        return $newPermissions;
    }

    /**
     * 多商户权限路由
     * @Author: 广东七件事 zal
     * @Date: 2020-04-09
     * @Time: 15:11
     * @return array
     */
    public static function getMchPermissions()
    {
        return [
            'mall/overview/index',
            'mall/mch/setting',
            'mall/mch/manage',
            'mall/setting/sms',
            'mall/setting/mail',
            'mall/setting/notice',
            'mall/postage-rules/index',
            'mall/postage-rules/index',
            'mall/free-delivery-rules/index',
            'mall/free-delivery-rules/edit',
            'mall/express/index',
            'mall/express/edit',
            'mall/printer/index',
            'mall/printer/edit',
            'mall/printer/setting',
            'mall/area-limit/index',
            'mall/offer-price/index',
            'mall/refund-setting/index',
            'mall/refund-setting/edit',
            'mall/goods/index',
            'mall/goods/edit',
            'mall/cat/index',
            'mall/cat/edit',
            'mall/cat/style',
            'mall/service/index',
            'mall/service/edit',
            'mch/goods/taobao-copy',
            'mall/order/index',
            'mall/order/detail',
            'mall/order/offline',
            'mall/order/refund',
            'mall/order/batch-send-model',
            'mall/order-comments/index',
            'mall/order-comments/edit',
            'mall/order-comments/reply',
            'mall/order/batch-send',
            'mch/store/order-message',
            'mall/user/clerk',
            'mall/mch/account-log',
            'mall/mch/cash-log',
            'mall/mch/order-close-log',
            'mall/order-comment-templates/index',
            'mall/order/refund-detail',
            'mall/setting/rule',
            'mall/goods/import-goods',
            'mall/goods/import-goods-log',
            'mall/goods/export-goods-list',
        ];
    }
}

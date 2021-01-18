<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 跳转链接表单
 * Author: zal
 * Date: 2020-04-13
 * Time: 10:12
 */

namespace app\forms\common;

use app\core\ApiCode;
use app\models\BaseModel;


class OperateModelForm extends BaseModel
{
    public static $modelActions = [
        "goods" => [
            "Goods" => "商品信息",
            "GoodsAttr" => "商品规格",
            "GoodsCatRelation" => "商品分类",
            "GoodsMemberPrice" => "商品规格价",
            "GoodsWarehouse" => "商品信息",
            "GoodsService" => "商品服务",
            "GoodsCats" => "分类",
            "Coupon" => "优惠券",
            "CouponAutoSend" => "优惠券发送配置",
            "CouponCatRelation" => "分类下优惠券",
            "CouponCenter" => "领券中心",
            "CouponGoodsRelation" => "指定商品优惠券",
            "CouponMemberRelation" => "会员等级优惠券",
        ],
        "user" => [
            "User" => "用户",
            "UserInfo" => "用户信息",
        ],
        "setting" => [
            "CosSetting" => "腾讯云文件存储配置",
            "MallSetting" => "商城配置",
            "MailSetting" => "邮件配置",
            "PrinterSetting" => "打印配置",
            "OssSetting" => "阿里云文件存储配置",
            "QiniuSetting" => "七牛云文件存储配置",
            "UploadSetting" => "上传配置",
        ],
        "order" => [
          "Order" => "订单",
          "OrderDetailExpress" => "订单物流",
          "OrderRefund" => "订单退款",
        ],
        "agent" => [
            "Agent" => "经销商",
            "AgentLevel" => "经销商等级",
            "AgentSetting" => "基础配置",
        ],
        "area" => [
            "AreaAgent" => "区域代理",
            "AreaApply" => "区域代理申请",
            "AreaGoods" => "区域商品",
            "AreaSetting" => "基础配置",
        ],
        "boss" => [
            "Boss" => "股东",
            "BossLevel" => "股东等级",
            "BossSetting" => "基础配置",
        ],
        "business_card" => [
            "BusinessCard" => "名片",
            "BusinessCardCustomer" => "客户资料",
            "BusinessCardDepartment" => "部门",
            "BusinessCardPosition" => "职位",
            "BusinessCardSetting" => "基础配置",
        ],
        "distribution" => [
            "Distribution" => "分销商",
            "DistributionSetting" => "基础配置",
            "DistributionApply" => "分销商申请",
            "DistributionLevel" => "分销商等级",
            "DistributionTeam" => "分销商团队奖励配置",
        ],
        "stock" => [
            "StockAgent" => "代理商",
            "StockAgentGoods" => "库存商品",
            "StockUpgradeBag" => "升级礼包",
            "StockLevel" => "代理商等级",
            "StockSetting" => "基础配置",
        ],
        "group_buy" => [
            "GroupBuyActive" => "拼团设置",
            "GroupBuyGoods" => "拼团商品",
            "GroupBuyGoodsAttr" => "拼团商品规格",
        ],
        "video" => [
            "Video" => "短视频",
            "VideoConfig" => "短视频配置",
            "VideoLabel" => "短视频标签",
            "VideoLookAward" => "视频观看奖励",
        ],
        "sign_in" => [
            "SignIn" => "签到",
            "SignInAwardConfig" => "签到奖励配置",
            "SignInConfig" => "签到设置",
        ],
    ];

    /**
     * 获取模型名称
     * @param $moduleName
     * @param $class
     * @return string
     */
    public static function getModelName($moduleName,$class){
        $actionName = "其他";
        if(isset(self::$modelActions[$moduleName][$class])){
            $actionName = self::$modelActions[$moduleName][$class];
        }
        return $actionName;
    }

    /**
     * 获取模块名称
     * @param $modelName
     * @param $class
     * @return array
     */
    public static function getModuleName($modelName,$class){
        $moduleName = "其他";
        $module = "";
        if(strpos($modelName,"Goods") === 0 || strpos($modelName,"Coupon") === 0){
            $moduleName = "商品";
            $module = "goods";
        }else if(strpos($modelName,"Order") !== false){
            $moduleName = "订单";
            $module = "order";
        }else if(strpos($modelName,"Mall") !== false || strpos($modelName,"Option") !== false ||  strpos($modelName,"Setting") !== false
            ||  strpos($modelName,"Role") !== false){
            $moduleName = "系统";
            $module = "setting";
        }else if(strpos($modelName,"BusinessCard") !== false){
            $moduleName = "插件-名片";
            $module = "business_card";
        }else if(strpos($modelName,"Agent") === 0){
            $moduleName = "插件-经销商";
            $module = "agent";
        }else if(strpos($modelName,"Appointing") === 0){
            $moduleName = "插件-预约";
            $module = "appointing";
        }else if(strpos($modelName,"Area") === 0){
            $moduleName = "插件-区域";
            $module = "area";
        }else if(strpos($modelName,"Boss") === 0){
            $moduleName = "插件-股东分红";
            $module = "boss";
        }else if(strpos($modelName,"distribution") === 0){
            $moduleName = "插件-分销商";
            $module = "distribution";
        }else if(strpos($modelName,"SignIn") === 0){
            $moduleName = "插件-签到";
            $module = "sign_in";
        }else if(strpos($modelName,"GroupBuy") === 0){
            $moduleName = "插件-拼团";
            $module = "group_buy";
        }else if(strpos($modelName,"Stock") === 0){
            $moduleName = "插件-云库存";
            $module = "stock";
        }else if(strpos($modelName,"ShortVideo") === 0){
            $moduleName = "插件-短视频";
            $module = "short_video";
        }else if(strpos($modelName,"User") === 0){
            $moduleName = "用户";
            $module = "user";
        }
        $model = self::getModelName($module,$class);
        return ["module" => $moduleName,"model" => $model];
    }

    public static function analysisUpdateContent($newBeforeUpdate,$newAfterUpdate){

        foreach ($newBeforeUpdate as $key => $value){

        }
    }

}
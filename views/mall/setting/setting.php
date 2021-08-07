<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: zal
 * Date: 2020-04-10
 * Time: 12:36
 */
?>
<style>
    .el-tabs__header {
        padding: 0 20px;
        height: 56px;
        line-height: 56px;
        background-color: #fff;
        margin-bottom: 0;
    }

    .title {
        margin-top: 10px;
        padding: 18px 20px;
        border-top: 1px solid #F3F3F3;
        border-bottom: 1px solid #F3F3F3;
        background-color: #fff;
    }

    .form-body {
        background-color: #fff;
        padding: 20px 50% 20px 0;
    }

    .button-item {
        padding: 9px 25px;
    }

    .form-body .item {
        width: 300px;
        margin-bottom: 50px;
        margin-right: 25px;
    }

    .item-img {
        height: 550px;
        padding: 25px 10px;
        border-radius: 30px;
        border: 1px solid #CCCCCC;
        background-color: #fff;
    }

    .item .el-form-item {
        width: 300px;
        margin: 20px auto;
    }

    .left-setting-menu {
        width: 260px;
    }

    .left-setting-menu .el-form-item {
        height: 60px;
        padding-left: 20px;
        display: flex;
        align-items: center;
        margin-bottom: 0;
        cursor: pointer;
    }

    .left-setting-menu .el-form-item .el-form-item__label {
        cursor: pointer;
    }

    .left-setting-menu .el-form-item.active {
        background-color: #F3F5F6;
        border-top-left-radius: 10px;
        width: 105%;
        border-bottom-left-radius: 10px;
    }

    .left-setting-menu .el-form-item .el-form-item__content {
        margin-left: 0 !important
    }

    .no-radius {
        border-top-left-radius: 0 !important;
    }

    .del-btn {
        position: absolute;
        right: -8px;
        top: -8px;
        padding: 4px 4px;
    }

    .reset {
        position: absolute;
        top: 3px;
        left: 90px;
    }

    .app-tip {
        position: absolute;
        right: 24px;
        top: 16px;
        height: 72px;
        line-height: 72px;
        max-width: calc(100% - 78px);
    }

    .app-tip:before {
        content: ' ';
        width: 0;
        height: 0;
        border-color: inherit;
        position: absolute;
        top: -32px;
        right: 100px;
        border-width: 16px;
        border-style: solid;
    }

    .tip-content {
        display: block;
        white-space: nowrap;
        width: 100%;
        overflow: hidden;
        text-overflow: ellipsis;
        margin: 0 28px;
        font-size: 28px;
    }
    .btn-abs{
        position: absolute;
        top: 12px;
        right: 18px;
    }

    .form-body .app-share {
        padding-top: 12px;
        border-top: 1px solid #e2e2e2;
        margin-top: -20px;
    }

    .form-body .app-share .app-share-bg {
        position: relative;
        width: 310px;
        height: 360px;
        background-repeat: no-repeat;
        background-size: contain;
        background-position: center
    }

    .form-body .app-share .app-share-bg .title {
        width: 160px;
        height: 29px;
        line-height: 1;
        word-break: break-all;
        text-overflow: ellipsis;
        display: -webkit-box;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 2;
        overflow: hidden;
    }

    .form-body .app-share .app-share-bg .pic-image {
        background-repeat: no-repeat;
        background-position: 0 0;
        background-size: cover;
        width: 160px;
        height: 130px;
    }

</style>

<div id="app" v-cloak>
    <el-card v-loading="loading" style="border:0" shadow="never" body-style="background-color: #f3f3f3;padding: 0 0;">
        <el-form :model="ruleForm"
                 :rules="rules"
                 ref="ruleForm"
                 label-width="172px"
                 style="position: relative"
                 size="small">
            <el-tabs v-model="activeName" @tab-click="handleClick">
                <el-tab-pane label="基本信息" name="first">

                    <el-row>
                        <el-col :span="24">
                            <div class="title">
                                <span>基本设置</span>
                            </div>
                            <div class="form-body">
                                <el-form-item label="商城名称" prop="name">
                                    <el-input v-model="ruleForm.name"></el-input>
                                </el-form-item>

                                <el-form-item label="联系号码" prop="contact_tel">
                                    <el-input type="number" v-model="ruleForm.setting.contact_tel"></el-input>
                                </el-form-item>

                                <el-form-item label="外链客服链接"
                                              prop="web_service_url">
                                    <el-input v-model="ruleForm.setting.web_service_url"></el-input>
                                </el-form-item>
                                <el-form-item label="网站url"
                                              prop="web_url">
                                    <el-input v-model="ruleForm.setting.web_url" placeholder="请输入网站url,如http://www.baidu.com"></el-input>
                                </el-form-item>
                                <el-form-item prop="logo">
                                    <label slot="label">
                                        <span>商城logo</span>
                                        <el-tooltip effect="dark" content="在授权登录页显示"
                                                    placement="top">
                                            <i class="el-icon-info"></i>
                                        </el-tooltip>
                                    </label>
                                    <com-attachment v-model="ruleForm.setting.logo" :multiple="false" :max="1">
                                        <el-tooltip class="item" effect="dark" content="建议尺寸:100 * 100"
                                                    placement="top">
                                            <el-button size="mini">选择图片</el-button>
                                        </el-tooltip>
                                    </com-attachment>
                                    <div class="customize-share-title">
                                        <com-image mode="aspectFill" width='80px' height='80px'
                                                   :src="ruleForm.setting.logo ? ruleForm.setting.logo : ''"></com-image>
                                        <el-button v-if="ruleForm.setting.logo" class="del-btn" size="mini"
                                                   type="danger" icon="el-icon-close" circle
                                                   @click="ruleForm.setting.logo = ''"></el-button>
                                    </div>
                                    <el-button @click="app_share.dialog = true;app_share.type = 'pic_bg'"
                                               type="text">查看图例
                                    </el-button>
                                </el-form-item>
                                <el-form-item label="详细地址">
                                    <el-input v-model="ruleForm.setting.quick_map_address"
                                              placeholder="请输入详细地址">
                                    </el-input>
                                </el-form-item>

                                <el-form-item>
                                    <template slot='label'>
                                        <span>经纬度</span>
                                        <el-tooltip effect="dark" content="点击地图,可获取经纬度"
                                                    placement="top">
                                            <i class="el-icon-info"></i>
                                        </el-tooltip>
                                    </template>
                                    <div flex="dir:left">
                                        <el-input v-model="ruleForm.setting.latitude_longitude"
                                                  placeholder="请输入经纬度,用英文逗号分离">
                                        </el-input>
                                    </div>
                                </el-form-item>
                                <el-form-item label="地图">
                                    <div flex="dir:left">
                                        <com-map @map-submit="mapEvent"
                                                 :address="ruleForm.setting.quick_map_address"
                                                 :lat="ruleForm.setting.latitude"
                                                 :long="ruleForm.setting.longitude">
                                            <el-button size="small">展开地图</el-button>
                                        </com-map>
                                    </div>
                                </el-form-item>

                                <el-form-item prop="app_share_title">
                                    <label slot="label">
                                        <span>自定义分享标题</span>
                                        <el-tooltip effect="dark" content="分享给好友时，显示的标题"
                                                    placement="top">
                                            <i class="el-icon-info"></i>
                                        </el-tooltip>
                                    </label>
                                    <el-input placeholder="请输入分享标题"
                                              v-model="ruleForm.setting.app_share_title"></el-input>
                                    <el-button @click="app_share.dialog = true;app_share.type = 'name_bg'"
                                               type="text">查看图例
                                    </el-button>
                                </el-form-item>
                                <el-form-item prop="app_share_desc">
                                    <label slot="label">
                                        <span>自定义分享描述</span>
                                        <el-tooltip effect="dark" content="分享给好友时，显示的描述"
                                                    placement="top">
                                            <i class="el-icon-info"></i>
                                        </el-tooltip>
                                    </label>
                                    <el-input type="textarea" placeholder="请输入分享描述"
                                              v-model="ruleForm.setting.app_share_desc"></el-input>
                                </el-form-item>
                                <el-form-item prop="app_share_pic">
                                    <label slot="label">
                                        <span>自定义分享图片</span>
                                        <el-tooltip effect="dark" content="分享给好友时，作为分享图片"
                                                    placement="top">
                                            <i class="el-icon-info"></i>
                                        </el-tooltip>
                                    </label>
                                    <com-attachment v-model="ruleForm.setting.app_share_pic" :multiple="false" :max="1">
                                        <el-tooltip class="item" effect="dark" content="建议尺寸:420 * 336"
                                                    placement="top">
                                            <el-button size="mini">选择图片</el-button>
                                        </el-tooltip>
                                    </com-attachment>
                                    <div class="customize-share-title">
                                        <com-image mode="aspectFill" width='80px' height='80px'
                                                   :src="ruleForm.setting.app_share_pic ? ruleForm.setting.app_share_pic : ''"></com-image>
                                        <el-button v-if="ruleForm.setting.app_share_pic" class="del-btn" size="mini"
                                                   type="danger" icon="el-icon-close" circle
                                                   @click="ruleForm.setting.app_share_pic = ''"></el-button>
                                    </div>
                                    <el-button @click="app_share.dialog = true;app_share.type = 'pic_bg'"
                                               type="text">查看图例
                                    </el-button>
                                </el-form-item>

                                <!-- 自定义 -->
                                <el-dialog :title="app_share['type'] == 'pic_bg' ? `查看自定义分享图片图例`:`查看自定义分享标题图例`"
                                           :visible.sync="app_share.dialog" width="30%">
                                    <div flex="dir:left main:center" class="app-share">
                                        <div class="app-share-bg"
                                             :style="{backgroundImage: 'url('+app_share[app_share.type]+')'}"></div>
                                    </div>
                                    <div slot="footer" class="dialog-footer">
                                        <el-button @click="app_share.dialog = false" type="primary">我知道了</el-button>
                                    </div>
                                </el-dialog>
                                <el-form-item label="关闭小程序手机授权绑定" prop="balance_status">
                                    <el-switch v-model="ruleForm.setting.close_auth_bind" active-value="1"
                                               inactive-value="0"></el-switch> <b>为了小程序过审，加了这个功能</b>
                                </el-form-item>

                                <el-form-item label="是否需要启用手机号绑定" prop="balance_status">
                                    <el-tooltip effect="dark" content="如果启用，授权登录和下单必须要绑定手机号，同时在app,h5和微信等多端平台共用一个账号，
                                                                        如果不启用，h5和app端无法共用一个账号"
                                                placement="top">
                                        <i class="el-icon-info"></i>
                                    </el-tooltip>
                                    <el-switch v-model="ruleForm.setting.all_network_enable" active-value="1"
                                               inactive-value="0"></el-switch> <b></b>
                                </el-form-item>
<!--                                <el-form-item label="开启手机绑定">-->
<!--                                    <el-switch v-model="ruleForm.setting.bind_phone_enable" active-value="1"-->
<!--                                               inactive-value="0"></el-switch><b>如果开启了全网通，此设置无效</b>-->
<!--                                </el-form-item>-->
                            </div>
                            <div class="title">
                                <span>交易设置</span>
                            </div>
                            <div class="form-body">
                                <el-form-item label="收货地址是否精确到镇">
                                    <template slot='label'>
                                        <span>收货地址是否精确到镇</span>
                                        <el-tooltip effect="dark" content="收货地址精确到镇，用户收货地址需要选择镇"
                                                    placement="top">
                                            <i class="el-icon-info"></i>
                                        </el-tooltip>
                                    </template>
                                        <el-switch v-model="ruleForm.setting.is_town" active-value="1"
                                                   inactive-value="0"></el-switch>
                                </el-form-item>
                                <el-form-item prop="over_time">
                                    <template slot='label'>
                                        <span>未支付订单超时时间</span>
                                        <el-tooltip effect="dark" content="注意：如设置为0分，则未支付订单将不会被取消"
                                                    placement="top">
                                            <i class="el-icon-info"></i>
                                        </el-tooltip>
                                    </template>
                                    <el-input v-model="ruleForm.setting.over_time" type="number">
                                        <template slot="append">分</template>
                                    </el-input>
                                </el-form-item>
                                <el-form-item prop="delivery_time">
                                    <template slot='label'>
                                        <span>自动确认收货时间</span>
                                        <el-tooltip effect="dark" content="从发货到自动确认收货的时间"
                                                    placement="top">
                                            <i class="el-icon-info"></i>
                                        </el-tooltip>
                                    </template>
                                    <el-input v-model="ruleForm.setting.delivery_time"
                                              type="number">
                                        <template slot="append">天</template>
                                    </el-input>
                                </el-form-item>
                                <el-form-item prop="after_sale_time">
                                    <template slot='label'>
                                        <span>售后时间</span>
                                        <el-tooltip effect="dark" placement="top">
                                            <div slot="content">可以申请售后的时间<br/>
                                                注意：分销订单中的已完成订单，只有订单已确认收货，并且时间超过设置的售后天数之后才计入其中！
                                            </div>
                                            <i class="el-icon-info"></i>
                                        </el-tooltip>
                                    </template>
                                    <el-input v-model="ruleForm.setting.after_sale_time"
                                              type="number">
                                        <template slot="append">天</template>
                                    </el-input>
                                </el-form-item>
                                <el-form-item prop="send_type">
                                    <template slot='label'>
                                        <span>发货方式</span>
                                        <el-tooltip effect="dark" content="需添加门店，到店自提方可生效"
                                                    placement="top">
                                            <i class="el-icon-info"></i>
                                        </el-tooltip>
                                    </template>
                                    <div>
                                        <el-checkbox-group v-model="ruleForm.setting.send_type">
                                            <el-checkbox label="express">快递配送</el-checkbox>
                                            <el-checkbox label="offline">到店自提</el-checkbox>
                                            <el-checkbox label="city">同城配送</el-checkbox>
                                        </el-checkbox-group>
                                    </div>
                                </el-form-item>
                            </div>
                        </el-col>
                    </el-row>
                </el-tab-pane>
                <el-tab-pane label="显示设置" name="second">


                    <el-tabs v-model="secondActiveName" @tab-click="secondHandleClick" style="margin-top: 10px;">
                        <el-tab-pane label="商品列表显示" name="first">
                            <div class="form-body" style="padding: 40px;display: flex;">
                                <div class='left-setting-menu'>
                                    <el-form-item :class='active_setting == "is_show_cart" ? "active":""'
                                                  @click.native='chooseSetting("is_show_cart")' label="购物车"
                                                  prop="is_show_cart">
                                        <el-switch
                                                v-model="ruleForm.setting.is_show_cart"
                                                active-value="1"
                                                inactive-value="0">
                                        </el-switch>
                                    </el-form-item>
                                    <el-form-item :class='active_setting == "is_show_sales_num" ? "active":""'
                                                  @click.native='chooseSetting("is_show_sales_num")' label="已售量"
                                                  prop="is_show_sales_num">
                                        <el-switch
                                                v-model="ruleForm.setting.is_show_sales_num"
                                                active-value="1"
                                                inactive-value="0">
                                        </el-switch>
                                    </el-form-item>
                                    <el-form-item :class='active_setting == "is_show_goods_name" ? "active":""'
                                                  @click.native='chooseSetting("is_show_goods_name")' label="商品名称"
                                                  prop="is_show_goods_name">
                                        <el-switch
                                                v-model="ruleForm.setting.is_show_goods_name"
                                                active-value="1"
                                                inactive-value="0">
                                        </el-switch>
                                    </el-form-item>
                                    <el-form-item :class='active_setting == "is_show_goods_name_diy" ? "active":""'
                                                  @click.native='chooseSetting("is_show_goods_name_diy")' label="自定义名称"
                                                  prop="is_show_goods_name_diy">
                                        <el-switch
                                                v-model="is_goods_name_diy"
                                                active-value="1"
                                                inactive-value="0"
                                                @change='change'
                                                >
                                        </el-switch>
                                    </el-form-item>
                                </div>
                                <div style='background-color: #F3F5F6;padding: 30px;border-radius: 10px;'
                                     :class='active_setting == "is_purchase_frame" ? "no-radius":""'>
                                    <div class="item-img">
                                        <com-image v-if='active_setting == "is_show_cart"' mode="aspectFill"
                                                   src="statics/img/mall/is_show_cart.png"
                                                   style="margin-bottom: 20px" height="500" width="280"></com-image>
                                        <com-image v-if='active_setting == "is_show_sales_num"' mode="aspectFill"
                                                   src="statics/img/mall/is_show_sales_num.png"
                                                   style="margin-bottom: 20px" height="500" width="280"></com-image>
                                        <com-image v-if='active_setting == "is_show_goods_name"' mode="aspectFill"
                                                   src="statics/img/mall/is_show_goods_name.png"
                                                   style="margin-bottom: 20px" height="500" width="280"></com-image>
                                        <com-image v-if='active_setting == "is_show_goods_name_diy"' mode="aspectFill"
                                                   src="statics/img/mall/is_show_cart.png"
                                                   style="margin-bottom: 20px" height="500" width="280"></com-image>
                                    </div>
                                </div>
                            </div>
                        </el-tab-pane>
                        <el-tab-pane label="商品详情显示" name="second">
                            <div class="form-body" style="padding: 40px;display: flex;">
                                <div class='left-setting-menu'>
                                    <el-form-item :class='active_setting == "is_comment" ? "active":""'
                                                  @click.native='chooseSetting("is_comment")' label="商城评论"
                                                  prop="is_comment">
                                        <el-switch
                                                v-model="ruleForm.setting.is_comment"
                                                active-value="1"
                                                inactive-value="0">
                                        </el-switch>
                                    </el-form-item>
                                    <el-form-item :class='active_setting == "is_underline_price" ? "active":""'
                                                  @click.native='chooseSetting("is_underline_price")' label="划线价"
                                                  prop="is_underline_price">
                                        <el-switch
                                                v-model="ruleForm.setting.is_underline_price"
                                                active-value="1"
                                                inactive-value="0">
                                        </el-switch>
                                    </el-form-item>
                                    <el-form-item :class='active_setting == "is_common_user_member_price" ? "active":""'
                                                  @click.native='chooseSetting("is_common_user_member_price")'
                                                  label="普通用户会员价"
                                                  prop="is_common_user_member_price">
                                        <el-switch
                                                v-model="ruleForm.setting.is_common_user_member_price"
                                                active-value="1"
                                                inactive-value="0">
                                        </el-switch>
                                    </el-form-item>
                                    <el-form-item :class='active_setting == "is_member_user_member_price" ? "active":""'
                                                  @click.native='chooseSetting("is_member_user_member_price")'
                                                  label="会员用户会员价"
                                                  prop="is_member_user_member_price">
                                        <el-switch
                                                v-model="ruleForm.setting.is_member_user_member_price"
                                                active-value="1"
                                                inactive-value="0">
                                        </el-switch>
                                    </el-form-item>
                                    <el-form-item v-if="is_svip"
                                                  :class='active_setting == "is_show_normal_vip" ? "active":""'
                                                  @click.native='chooseSetting("is_show_normal_vip")'
                                                  prop="is_show_normal_vip">
                                        <template slot='label'>
                                            <span>非SVIP用户超级会员价</span>
                                        </template>
                                        <el-switch
                                                v-model="ruleForm.setting.is_show_normal_vip"
                                                active-value="1"
                                                inactive-value="0">
                                        </el-switch>
                                    </el-form-item>
                                    <el-form-item v-if="is_svip"
                                                  :class='active_setting == "is_show_super_vip" ? "active":""'
                                                  @click.native='chooseSetting("is_show_super_vip")'
                                                  prop="is_show_super_vip">
                                        <template slot='label'>
                                            <span>SVIP用户超级会员价</span>
                                        </template>
                                        <el-switch
                                                v-model="ruleForm.setting.is_show_super_vip"
                                                active-value="1"
                                                inactive-value="0">
                                        </el-switch>
                                    </el-form-item>
                                    <el-form-item :class='active_setting == "is_express" ? "active":""'
                                                  @click.native='chooseSetting("is_express")' label="快递"
                                                  prop="is_express">
                                        <el-switch
                                                v-model="ruleForm.setting.is_express"
                                                active-value="1"
                                                inactive-value="0">
                                        </el-switch>
                                    </el-form-item>
                                    <el-form-item :class='active_setting == "is_sales" ? "active":""'
                                                  @click.native='chooseSetting("is_sales")' label="已售量" prop="is_sales">
                                        <el-switch
                                                v-model="ruleForm.setting.is_sales"
                                                active-value="1"
                                                inactive-value="0">
                                        </el-switch>
                                    </el-form-item>


                                </div>
                                <div style='background-color: #F3F5F6;padding: 30px;border-radius: 10px;'
                                     :class='active_setting == "is_purchase_frame" ? "no-radius":""'>
                                    <div class="item-img">
                                        <com-image v-if='active_setting == "is_purchase_frame"' mode="aspectFill"
                                                   src="statics/img/mall/buy_log.png"
                                                   style="margin-bottom: 20px" height="500" width="280"></com-image>
                                        <com-image v-if='active_setting == "is_underline_price"' mode="aspectFill"
                                                   src="statics/img/mall/is_underline_price.png"
                                                   style="margin-bottom: 20px" height="500" width="280"></com-image>
                                        <com-image mode="aspectFill" v-if='active_setting == "is_comment"'
                                                   src="statics/img/mall/comment_show.png"
                                                   style="margin-bottom: 20px" height="500" width="280"></com-image>
                                        <com-image v-if='active_setting == "is_express"' mode="aspectFill"
                                                   src="statics/img/mall/is_express.png"
                                                   style="margin-bottom: 20px" height="500" width="280"></com-image>
                                        <com-image v-if='active_setting == "is_sales"' mode="aspectFill"
                                                   src="statics/img/mall/sales_show.png"
                                                   style="margin-bottom: 20px" height="500" width="280"></com-image>
                                        <com-image v-if='active_setting == "is_common_user_member_price"'
                                                   mode="aspectFill"
                                                   src="statics/img/mall/price_show_2.png"
                                                   style="margin-bottom: 20px" height="500" width="280"></com-image>
                                        <com-image v-if='active_setting == "is_member_user_member_price"'
                                                   mode="aspectFill"
                                                   src="statics/img/mall/price_show.png"
                                                   style="margin-bottom: 20px" height="500" width="280"></com-image>
                                        <com-image v-if='active_setting == "is_icon_members_grade"' mode="aspectFill"
                                                   src="statics/img/mall/icon_members_grade.png"
                                                   style="margin-bottom: 20px" height="500" width="280"></com-image>
                                    </div>
                                </div>
                            </div>
                        </el-tab-pane>
                    </el-tabs>
                </el-tab-pane>

                <el-tab-pane label="悬浮按钮设置" name="third">
                    <el-row>
                        <el-col :span="24">
                            <div class="title">
                                <span>悬浮窗设置</span>
                            </div>
                            <div class="form-body">
                                <el-form-item label="启用悬浮按钮">
                                    <el-switch
                                            v-model="ruleForm.setting.is_quick_navigation"
                                            active-value="1"
                                            inactive-value="0"
                                            active-color="#409EFF">
                                    </el-switch>
                                </el-form-item>
                                <el-form-item v-if="ruleForm.setting.is_quick_navigation == '1'" label="悬浮按钮样式"
                                              prop="quick_navigation_style">
                                    <el-radio v-model="ruleForm.setting.quick_navigation_style" label="1">展开收起
                                    </el-radio>
                                    <el-radio v-model="ruleForm.setting.quick_navigation_style" label="2">固定展开
                                    </el-radio>
                                </el-form-item>
                                <el-form-item v-if="ruleForm.setting.is_quick_navigation == '1'" label="展开图标">
                                    <com-attachment :multiple="false" :max="1" @selected="quickNavigationOpenedPic">
                                        <el-tooltip effect="dark"
                                                    content="建议尺寸:100 * 100"
                                                    placement="top">
                                            <el-button size="mini">选择图标</el-button>
                                        </el-tooltip>
                                    </com-attachment>
                                    <el-button size="mini" @click="resetImg('quick_navigation_opened_pic')"
                                               class="reset" type="primary">恢复默认
                                    </el-button>
                                    <com-image style="width: 80px; height: 80px;"
                                               :src="ruleForm.setting.quick_navigation_opened_pic">
                                    </com-image>
                                </el-form-item>
                                <el-form-item v-if="ruleForm.setting.is_quick_navigation == '1'" label="收起图标">
                                    <com-attachment :multiple="false" :max="1" @selected="quickNavigationClosedPic">
                                        <el-tooltip effect="dark"
                                                    content="建议尺寸:100 * 100"
                                                    placement="top">
                                            <el-button size="mini">选择图标</el-button>
                                        </el-tooltip>
                                    </com-attachment>
                                    <el-button size="mini" @click="resetImg('quick_navigation_closed_pic')"
                                               class="reset" type="primary">恢复默认
                                    </el-button>
                                    <com-image style="width: 80px; height: 80px;"
                                               :src="ruleForm.setting.quick_navigation_closed_pic">
                                    </com-image>
                                </el-form-item>
                            </div>

                            <div class="form-body" v-if="ruleForm.setting.is_quick_navigation == '1'"
                                 style="background-color: #ffffff;padding: 0 0 20px 0;">
                                <el-form-item label="在线客服开关" style="padding-top: 20px;border-top: 1px solid #F3F3F3;">
                                    <el-switch
                                            v-model="ruleForm.setting.is_customer_services"
                                            active-value="1"
                                            inactive-value="0"
                                            active-color="#409EFF">
                                    </el-switch>
                                </el-form-item>
                                <el-form-item>
                                    <com-attachment :multiple="false" :max="1" @selected="customerServicesPic">
                                        <el-tooltip effect="dark"
                                                    content="建议尺寸:100 * 100"
                                                    placement="top">
                                            <el-button size="mini">选择图标</el-button>
                                        </el-tooltip>
                                    </com-attachment>
                                    <el-button size="mini" @click="resetImg('customer_services_pic')" class="reset"
                                               type="primary">恢复默认
                                    </el-button>
                                    <com-image style="width: 80px; height: 80px;"
                                               :src="ruleForm.setting.customer_services_pic">
                                    </com-image>
                                </el-form-item>
                            </div>

                            <div class="form-body" v-if="ruleForm.setting.is_quick_navigation == '1'">
                                <el-form-item label="返回首页导航开关" style="padding-top: 20px;border-top: 1px solid #F3F3F3;">
                                    <el-switch
                                            v-model="ruleForm.setting.is_quick_home"
                                            active-value="1"
                                            inactive-value="0"
                                            active-color="#409EFF">
                                    </el-switch>
                                </el-form-item>
                                <el-form-item>
                                    <com-attachment :multiple="false" :max="1" @selected="quickHomePic">
                                        <el-tooltip effect="dark"
                                                    content="建议尺寸:100 * 100"
                                                    placement="top">
                                            <el-button size="mini">选择图标</el-button>
                                        </el-tooltip>
                                    </com-attachment>
                                    <el-button size="mini" @click="resetImg('quick_home_pic')" class="reset"
                                               type="primary">恢复默认
                                    </el-button>
                                    <com-image style="width: 80px; height: 80px;"
                                               :src="ruleForm.setting.quick_home_pic">
                                    </com-image>
                                </el-form-item>
                            </div>
                            <div class="form-body" v-if="ruleForm.setting.is_quick_navigation == '1'">
                                <el-form-item label="客服外链开关" style="padding-top: 20px;border-top: 1px solid #F3F3F3;">
                                    <el-switch
                                            v-model="ruleForm.setting.is_web_service"
                                            active-value="1"
                                            inactive-value="0"
                                            active-color="#409EFF">
                                    </el-switch>
                                </el-form-item>
                                <el-form-item>
                                    <com-attachment :multiple="false" :max="1" @selected="webServicePic">
                                        <el-tooltip effect="dark"
                                                    content="建议尺寸:100 * 100"
                                                    placement="top">
                                            <el-button size="mini">选择图标</el-button>
                                        </el-tooltip>
                                    </com-attachment>
                                    <el-button size="mini" @click="resetImg('web_service_pic')" class="reset"
                                               type="primary">恢复默认
                                    </el-button>
                                    <com-image style="width: 80px; height: 80px;"
                                               :src="ruleForm.setting.web_service_pic"></com-image>
                                </el-form-item>
                            </div>

                            <div class="form-body" v-if="ruleForm.setting.is_quick_navigation == '1'">
                                <el-form-item label="快捷导航地图开关" prop="is_quick_map"
                                              style="padding-top: 20px;border-top: 1px solid #F3F3F3;">
                                    <el-switch
                                            v-model="ruleForm.setting.is_quick_map"
                                            active-value="1"
                                            inactive-value="0"
                                            active-color="#409EFF">
                                    </el-switch>
                                </el-form-item>
                                <el-form-item>
                                    <com-attachment :multiple="false" :max="1" @selected="quickMapPic">
                                        <el-tooltip effect="dark"
                                                    content="建议尺寸:100 * 100"
                                                    placement="top">
                                            <el-button size="mini">选择图标</el-button>
                                        </el-tooltip>
                                    </com-attachment>
                                    <el-button size="mini" @click="resetImg('quick_map_pic')" class="reset"
                                               type="primary">恢复默认
                                    </el-button>
                                    <com-image style="width: 80px; height: 80px;"
                                               :src="ruleForm.setting.quick_map_pic"></com-image>
                                </el-form-item>
                            </div>
                            <div class="title">
                                <span>悬浮按钮设置</span>
                                <el-tooltip effect="dark" content="用于商品列表页"
                                            placement="top">
                                    <i class="el-icon-info"></i>
                                </el-tooltip>
                            </div>
                            <div class="form-body">
                                <el-form-item label="启用回到顶部悬浮按钮" prop="is_show_scroll_top">
                                    <el-switch
                                            v-model="ruleForm.setting.is_show_scroll_top"
                                            active-value="1"
                                            inactive-value="0"
                                            active-color="#409EFF">
                                    </el-switch>
                                </el-form-item>
                                <el-form-item label="启用购物车悬浮按钮" prop="is_show_cart_hover">
                                    <el-switch
                                            v-model="ruleForm.setting.is_show_cart_hover"
                                            active-value="1"
                                            inactive-value="0"
                                            active-color="#409EFF">
                                    </el-switch>
                                </el-form-item>
                            </div>
                            <div class="title">
                                <span>商品售罄图标设置</span>
                            </div>
                            <div class="form-body">
                                <el-form-item label="售罄图标显示开关">
                                    <el-switch
                                            v-model="ruleForm.setting.is_show_sale_out"
                                            active-value="1"
                                            inactive-value="0"
                                            active-color="#409EFF">
                                    </el-switch>
                                </el-form-item>
                                <el-form-item label="是否使用默认图标" v-if="ruleForm.setting.is_show_sale_out == '1'">
                                    <el-switch
                                            v-model="ruleForm.setting.is_use_sale_out"
                                            active-value="1"
                                            inactive-value="0"
                                            active-color="#409EFF">
                                    </el-switch>
                                </el-form-item>
                                <el-form-item label="商品图正常尺寸" v-if="ruleForm.setting.is_show_sale_out == '1'">
                                    <com-attachment :multiple="false" :max="1" @selected="sellOutPic">
                                        <el-tooltip effect="dark"
                                                    content="建议尺寸:702 * 702"
                                                    placement="top">
                                            <el-button size="mini">选择图标</el-button>
                                        </el-tooltip>
                                    </com-attachment>
                                    <com-image style="width: 80px; height: 80px;background-color: rgba(0,0,0,.5)"
                                               :src="ruleForm.setting.is_use_sale_out == '1' ? 'statics/img/app/mall/plugins-out.png' : ruleForm.setting.sale_out_pic">
                                    </com-image>
                                </el-form-item>
                                <el-form-item label="商品图4:3尺寸" v-if="ruleForm.setting.is_show_sale_out == '1'">
                                    <com-attachment :multiple="false" :max="1" @selected="sellOutOtherPic">
                                        <el-tooltip effect="dark"
                                                    content="建议尺寸:702 * 468"
                                                    placement="top">
                                            <el-button size="mini">选择图标</el-button>
                                        </el-tooltip>
                                    </com-attachment>
                                    <com-image style="width: 80px; height: 80px;background-color: rgba(0,0,0,.5)"
                                               :src="ruleForm.setting.is_use_sale_out == '1' ? 'statics/img/app/mall/rate-out.png' : ruleForm.setting.sale_out_other_pic">
                                    </com-image>
                                </el-form-item>
                            </div>
                        </el-col>
                    </el-row>
                </el-tab-pane>
            </el-tabs>
            <el-button :loading="submitLoading" class="button-item" size="small" type="primary"
                       @click="submit('ruleForm')">保存
            </el-button>
        </el-form>
    </el-card>
</div>

<script>
    const app = new Vue({
        el: '#app',
        data() {
            let noPay = (rule, value, callback) => {
                let reg = /^[1-9]\d*$/;
                if (!reg.test(this.ruleForm.setting.over_time) && this.ruleForm.setting.over_time != 0) {
                    callback(new Error('未支付订单超时时间必须为整数'))
                } else {
                    callback()
                }
            };
            let after_sale = (rule, value, callback) => {
                let reg = /^[1-9]\d*$/;
                if (!reg.test(this.ruleForm.setting.after_sale_time) && this.ruleForm.setting.after_sale_time != 0) {
                    callback(new Error('售后时间时间必须为整数'))
                } else {
                    callback()
                }
            };
            let delivery = (rule, value, callback) => {
                let reg = /^[1-9]\d*$/;
                if (!reg.test(this.ruleForm.setting.delivery_time) && this.ruleForm.setting.delivery_time != 0) {
                    callback(new Error('收货时间必须为整数'))
                } else {
                    callback()
                }
            };
            let integral = (rule, value, callback) => {
                let reg = /^[1-9]\d*$/;
                if (!reg.test(this.ruleForm.setting.score) && this.ruleForm.setting.score != 0) {
                    callback(new Error('用户积分必须为整数'))
                } else {
                    callback()
                }
            };
            let purchase = (rule, value, callback) => {
                if (this.ruleForm.setting.purchase_num > 50 || this.ruleForm.setting.purchase_num < 0) {
                    callback(new Error('轮播订单数范围为0-50'))
                } else {
                    callback()
                }
            };
            return {
                loading: false,
                submitLoading: false,
                mall: null,
                is_svip: false,
                active_setting: 'is_show_cart',
                activeName: 'first',
                secondActiveName: 'first',
                checkList: [],
                is_goods_name_diy:'',

                ruleForm: {
                    name: '',
                    setting: {
                        payment_type: [],
                        send_type: [],
                        app_share_title: '', //自定义分享标题,
                        app_share_pic: '', //自定义分享图片
                        close_auth_bind:0,
                        logo:'',
                    },
                },
                rules: {
                    name: [
                        {required: true, message: '请填写商城名称。'},
                        {max: 64, message: '最多64个字。'},
                    ],
                    over_time: [
                        {validator: noPay, trigger: 'blur'}
                    ],
                    delivery_time: [
                        {validator: delivery, trigger: 'blur'}
                    ],
                    after_sale_time: [
                        {validator: after_sale, trigger: 'blur'}
                    ],
                    score: [
                        {validator: integral, trigger: 'blur'}
                    ],
                    purchase_num: [
                        {validator: purchase, trigger: 'blur'}
                    ]
                },
                catGoodsCols: [
                    {
                        label: '1',
                        value: 1
                    },
                    {
                        label: '2',
                        value: 2
                    },
                    {
                        label: '3',
                        value: 3
                    },
                ],
                predefineColors: [
                    '#000',
                    '#fff',
                    '#888',
                    '#ff4544'
                ],
                app_share: {
                    dialog: false,
                    type: '',
                    bg: "<?= \Yii::$app->request->baseUrl?>/statics/img/mall/app-share.png",
                    name_bg: "<?= \Yii::$app->request->baseUrl?>/statics/img/mall/app-share-name.png",
                    pic_bg: "<?= \Yii::$app->request->baseUrl?>/statics/img/mall/app-share-pic.png",
                },
            };
        },
        created() {
            this.loadData();
            this.getSvip();
            this.getSetData();
        },
        methods: {
            change(){
                request({
                    params: {
                        r: 'mall/setting/mall',
                    },
                    method:'post',
                    data:{
                        key:'integral_price_display',
                        value:{
                            is_display:this.is_goods_name_diy
                        }
                    }
                }).then(e => {
                    if (e.data.code === 0) {
                        console.log(e,'eeeeeee');
                    } else {
                        this.$message.error(e.data.msg);
                    }
                })
            },
            getSetData(){
                request({
                    params: {
                        r: 'mall/setting/mall',
                        key:'integral_price_display',
                    },
                }).then(e => {
                    if (e.data.code === 0) {
                        this.is_goods_name_diy = e.data.data.is_display;
                    } else {
                        this.$message.error(e.data.msg);
                    }
                })
            },
            chooseSetting(setting) {
                console.log(setting,'setting');
                this.active_setting = setting;
            },

            loadData() {
                this.loading = true;
                request({
                    params: {
                        r: 'mall/setting/setting',
                    },
                }).then(e => {
                    this.loading = false;
                    if (e.data.code === 0) {
                        this.ruleForm = e.data.data.detail;
                        let setting = this.ruleForm.setting;
                        this.ruleForm.setting.latitude_longitude = setting.latitude + ',' + setting.longitude;
                        this.initMap();
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {
                });
            },
            getSvip() {
                request({
                    params: {
                        r: 'mall/member-level/vip-card-permission',
                    },
                }).then(e => {
                    if (e.data.code === 0) {
                        this.is_svip = true;
                    } else {
                        this.is_svip = false;
                    }
                })
            },
            submit(formName) {
                this.$refs[formName].validate(valid => {
                    if (valid) {
                        this.submitLoading = true;
                        request({
                            params: {
                                r: 'mall/setting/setting',
                            },
                            method: 'post',
                            data: {
                                ruleForm: JSON.stringify(this.ruleForm)
                            },
                        }).then(e => {
                            this.submitLoading = false;
                            if (e.data.code === 0) {
                                this.$message.success(e.data.msg);
                            } else {
                                this.$message.error(e.data.msg);
                            }
                        }).catch(e => {
                        });
                    } else {
                        this.$message.error('部分参数验证不通过');
                    }
                });
            },
            handleClick(tab, event) {
                console.log(tab, event);
            },
            secondHandleClick(tab, event) {
                if (tab.name == 'first') {
                    this.active_setting = 'is_show_cart';
                } else if (tab.name == 'second') {
                    this.active_setting = 'is_comment';
                } else if (tab.name == 'third') {
                    this.active_setting = 'is_not_share_show';
                }
            },
            //
            sharePic(e) {
                if (e.length) {
                    this.ruleForm.setting.share_pic = e[0].url;
                }
            },
            removeSharePic() {
                this.ruleForm.setting.share_pic = '';
            },
            //客服图标
            customerServicesPic(e) {
                if (e.length) {
                    this.ruleForm.setting.customer_services_pic = e[0].url;
                }
            },
            //拨号图标
            dialPic(e) {
                if (e.length) {
                    this.ruleForm.setting.dial_pic = e[0].url;
                }
            },
            //客服外链图标
            webServicePic(e) {
                if (e.length) {
                    this.ruleForm.setting.web_service_pic = e[0].url;
                }
            },
            //快捷导航图标(展开)
            quickNavigationOpenedPic(e) {
                if (e.length) {
                    this.ruleForm.setting.quick_navigation_opened_pic = e[0].url;
                }
            },
            //快捷导航图标(收起)
            quickNavigationClosedPic(e) {
                if (e.length) {
                    this.ruleForm.setting.quick_navigation_closed_pic = e[0].url;
                }
            },
            //客服外链图标
            smallAppPic(e) {
                if (e.length) {
                    this.ruleForm.setting.small_app_pic = e[0].url;
                }
            },
            //一键导航图标
            quickMapPic(e) {
                if (e.length) {
                    this.ruleForm.setting.quick_map_pic = e[0].url;
                }
            },
            quickHomePic(e) {
                if (e.length) {
                    this.ruleForm.setting.quick_home_pic = e[0].url;
                }
            },
            sellOutPic(e) {
                if (e.length) {
                    this.ruleForm.setting.sale_out_pic = e[0].url;
                    this.ruleForm.setting.is_use_sale_out = '0'
                }
            },
            sellOutOtherPic(e) {
                if (e.length) {
                    this.ruleForm.setting.sale_out_other_pic = e[0].url;
                    this.ruleForm.setting.is_use_sale_out = '0'
                }
            },
            //地图确定事件
            mapEvent(e) {
                let self = this;
                self.ruleForm.setting.latitude_longitude = e.lat + ',' + e.long;
                self.ruleForm.setting.longitude = e.long;
                self.ruleForm.setting.latitude = e.lat;
                self.ruleForm.setting.quick_map_address = e.address;
            },

            resetImg(type) {
                const host = "<?php echo \Yii::$app->request->hostInfo . \Yii::$app->request->baseUrl . "/" ?>";
                if (type == 'quick_navigation_opened_pic') {
                    this.ruleForm.setting.quick_navigation_opened_pic = host + 'statics/img/mall/quick_navigation_opened_pic.png';
                } else if (type == 'quick_navigation_closed_pic') {
                    this.ruleForm.setting.quick_navigation_closed_pic = host + 'statics/img/mall/quick_navigation_closed_pic.png';
                } else if (type == 'small_app_pic') {
                    this.ruleForm.setting.small_app_pic = host + 'statics/img/mall/small_app_pic.png';
                } else if (type == 'quick_map_pic') {
                    this.ruleForm.setting.quick_map_pic = host + 'statics/img/mall/quick_map_pic.png';
                } else if (type == 'web_service_pic') {
                    this.ruleForm.setting.web_service_pic = host + 'statics/img/mall/web_service_pic.png';
                } else if (type == 'dial_pic') {
                    this.ruleForm.setting.dial_pic = host + 'statics/img/mall/dial_pic.png';
                } else if (type == 'quick_home_pic') {
                    this.ruleForm.setting.quick_home_pic = host + 'statics/img/mall/quick_home_pic.png';
                } else if (type == 'customer_services_pic') {
                    this.ruleForm.setting.customer_services_pic = host + 'statics/img/mall/customer_services_pic.png';
                }
            },
        },
        computed: {
            appTip() {
                let setting = this.ruleForm.setting;
                return `background-color:${setting.add_app_bg_color};` +
                    `opacity:${setting.add_app_bg_transparency / 100};` +
                    `border-radius:${setting.add_app_bg_radius}px;` +
                    `border-color: transparent transparent ${setting.add_app_bg_color} transparent;` +
                    `color:${setting.add_app_text_color}`;
            }
        }
    });
</script>
<style>
    .el-tabs__header {
        padding: 0 20px;
        height: 56px;
        line-height: 56px;
        background-color: #fff;
        margin-bottom: 0;
    }

    .title {
        margin-top: 10px;
        padding: 18px 20px;
        border-top: 1px solid #F3F3F3;
        border-bottom: 1px solid #F3F3F3;
        background-color: #fff;
    }

    .form-body {
        background-color: #fff;
        padding: 20px 50% 20px 0;
    }

    .button-item {
        margin-top: 12px;
        padding: 9px 25px;
    }

    .form-body .item {
        width: 300px;
        margin-bottom: 50px;
        margin-right: 25px;
    }

    .item-img {
        height: 550px;
        padding: 25px 10px;
        border-radius: 30px;
        border: 1px solid #CCCCCC;
        background-color: #fff;
    }

    .item .el-form-item {
        width: 300px;
        margin: 20px auto;
    }

    .left-setting-menu {
        width: 260px;
    }

    .left-setting-menu .el-form-item {
        height: 60px;
        padding-left: 20px;
        display: flex;
        align-items: center;
        margin-bottom: 0;
        cursor: pointer;
    }

    .left-setting-menu .el-form-item .el-form-item__label {
        cursor: pointer;
    }

    .left-setting-menu .el-form-item.active {
        background-color: #F3F5F6;
        border-top-left-radius: 10px;
        width: 105%;
        border-bottom-left-radius: 10px;
    }

    .left-setting-menu .el-form-item .el-form-item__content {
        margin-left: 0 !important
    }

    .no-radius {
        border-top-left-radius: 0 !important;
    }

    .del-btn {
        position: absolute;
        right: -8px;
        top: -8px;
        padding: 4px 4px;
    }

    .reset {
        position: absolute;
        top: 3px;
        left: 90px;
    }

    .app-tip {
        position: absolute;
        right: 24px;
        top: 16px;
        height: 72px;
        line-height: 72px;
        max-width: calc(100% - 78px);
    }

    .app-tip:before {
        content: ' ';
        width: 0;
        height: 0;
        border-color: inherit;
        position: absolute;
        top: -32px;
        right: 100px;
        border-width: 16px;
        border-style: solid;
    }

    .tip-content {
        display: block;
        white-space: nowrap;
        width: 100%;
        overflow: hidden;
        text-overflow: ellipsis;
        margin: 0 28px;
        font-size: 28px;
    }
</style>
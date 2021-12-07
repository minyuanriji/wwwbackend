<?php
Yii::$app->loadComponentView('com-rich-text');
Yii::$app->loadComponentView('com-dialog-select');
Yii::$app->loadComponentView('goods/com-attr');
Yii::$app->loadComponentView('goods/com-attr-select');
Yii::$app->loadComponentView('goods/com-add-cat');
Yii::$app->loadComponentView('goods/com-select-goods');
Yii::$app->loadComponentView('goods/com-area-limit');
Yii::$app->loadComponentView('goods/com-preview');
Yii::$app->loadComponentView('goods/com-attr-group');
Yii::$app->loadComponentView('com-goods-form', __DIR__ . '/goods');
Yii::$app->loadComponentView('com-goods-distribution-new', __DIR__ . '/goods');
Yii::$app->loadComponentView('goods/com-goods-area');
Yii::$app->loadComponentView('goods/com-goods-agent');
?>
<style>
    .mt-24 {
        margin-bottom: 24px;
    }

    .com-goods .el-form-item__label {
        padding: 0 20px 0 0;
    }

    .com-goods .el-dialog__body h3 {
        font-weight: normal;
        color: #999999;
    }

    .com-goods .form-body {
        padding: 10px 20px 20px;
        background-color: #fff;
        margin-bottom: 30px;
    }

    .com-goods .button-item {
        padding: 9px 25px;
        margin-bottom: 10px;
    }

    .com-goods .sortable-chosen {
        /* border: 2px solid #3399ff; */
    }

    .del-btn {
        position: absolute;
        right: -8px;
        top: -8px;
        padding: 4px 4px;
    }

    .com-goods .app-share {
        padding-top: 12px;
        border-top: 1px solid #e2e2e2;
        margin-top: -20px;
    }

    .com-goods .app-share .app-share-bg {
        position: relative;
        width: 310px;
        height: 360px;
        background-repeat: no-repeat;
        background-size: contain;
        background-position: center
    }

    .com-goods .app-share .app-share-bg .title {
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

    .com-goods .app-share .app-share-bg .pic-image {
        background-repeat: no-repeat;
        background-position: 0 0;
        background-size: cover;
        width: 160px;
        height: 130px;
    }

    .bottom-div {
        border-top: 1px solid #E3E3E3;
        position: fixed;
        bottom: 0;
        background-color: #ffffff;
        z-index: 999;
        padding: 10px;
        width: 80%;
    }

    .com-goods .add-image-btn {
        width: 100px;
        height: 100px;
        color: #419EFB;
        border: 1px solid #e2e2e2;
        cursor: pointer;
    }

    .com-goods .pic-url-remark {
        font-size: 13px;
        color: #c9c9c9;
        margin-bottom: 12px;
    }

    .com-goods .customize-share-title {
        margin-top: 10px;
        width: 80px;
        height: 80px;
        position: relative;
        cursor: move;
    }

    .com-goods .share-title {
        font-size: 16px;
        color: #303133;
        padding-bottom: 22px;
        border-bottom: 1px solid #e2e2e2;
    }

    .box-grow-0 {
        /* flex 子元素固定宽度*/
        min-width: 0;
        -webkit-box-flex: 0;
        -webkit-flex-grow: 0;
        -ms-flex-positive: 0;
        flex-grow: 0;
        -webkit-flex-shrink: 0;
        -ms-flex-negative: 0;
        flex-shrink: 0;
    }

    /* .agent-setting-item{
		width: 480px;
	} */
    .member-money {
        margin-top: 20px;
    }

   .diy_box {
        position: relative;
    }

    .bbb {
        position: absolute;
        top: 0;
        left: -20px;
        transform: translateX(-100%);
        z-index: 9;
        width: 130px;
    }

    #goods_aera :before {
        content: none;
    }
</style>
<template id="com-goods">
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;" class="com-goods" v-loading="cardLoading">
        <div class='form-body'>
            <el-form :model="cForm" :rules="cRule" ref="ruleForm" label-width="180px" size="small" class="demo-ruleForm">
                <el-tabs v-model="activeName" @tab-click="handleClick">
                    <el-tab-pane label="基础设置" name="first" v-if="is_basic == 1">

                        <!-- 选择分类 -->
                        <slot name="before_cats"></slot>
                        <el-card v-if="is_cats == 1" shadow="never" class="mt-24">
                            <div slot="header">
                                <span>选择分类</span>
                            </div>
                            <el-row>
                                <el-col :xl="12" :lg="16">
                                    <el-form-item label="商品分类" prop="cats">
                                        <el-tag style="margin-right: 5px;margin-bottom:5px" v-for="(item,index) in cats" :key="index" type="warning" closable disable-transitions @close="destroyCat(index)">{{item.label}}
                                        </el-tag>
                                        <el-button type="primary" @click="$refs.cats.openDialog()">选择分类</el-button>
                                        <el-button type="text" @click="$navigate({r:'mall/cat/edit'}, true)">添加分类
                                        </el-button>
                                        <com-add-cat ref="cats" :new-cats="ruleForm.cats" @select="selectCat"></com-add-cat>
                                    </el-form-item>
                                    <!-- mch -->
                                    <el-form-item v-if="is_mch" label="多商户分类" prop="mchCats">
                                        <el-tag style="margin-right: 5px" v-for="(item,index) in mchCats" :key="item.value" v-model="ruleForm.mchCats" type="warning" closable disable-transitions @close="destroyCat_2(item.value,index)">{{item.label}}
                                        </el-tag>
                                        <el-button type="primary" @click="$refs.mchCats.openDialog()">选择分类</el-button>
                                        <el-button type="text" @click="$navigate({r:'mall/cat/edit'}, true)">添加分类
                                        </el-button>
                                        <com-add-cat ref="mchCats" :new-cats="ruleForm.mchCats" :mch_id="mch_id" @select="selectMchCat"></com-add-cat>
                                    </el-form-item>
                                </el-col>
                            </el-row>
                        </el-card>

                        <!-- 联创合伙人 -->
                        <el-card shadow="never" class="mt-24">
                            <div slot="header">
                                <span>联创合伙人</span>
                            </div>
                            <el-row>
                                <el-col :xl="12" :lg="16">
                                    <el-form-item label="品牌商归属">
                                        <el-tag @close="clearLiancUser" type="warning" closable disable-transitions v-if="forLiancDlgSelect.selection.id > 0">{{forLiancDlgSelect.selection.nickname}}[UID:{{forLiancDlgSelect.selection.id}}]</el-tag>
                                        <el-button @click="openLiancDlgSelect" type="primary">选择用户</el-button>
                                    </el-form-item>
                                    <el-form-item label="分佣" v-if="forLiancDlgSelect.selection.id > 0">
                                        <el-input type="number" placeholder="请输入内容" v-model="ruleForm.lianc_commisson_value" style="width:350px">
                                            <el-select v-model="ruleForm.lianc_commission_type" slot="prepend" style="width:110px;">
                                                <el-option label="按百分比" value="1"></el-option>
                                                <el-option label="按固定值" value="2"></el-option>
                                            </el-select>
                                            <template slot="append">{{ruleForm.lianc_commission_type == 1 ? '%' : '元'}}</template>
                                        </el-input>
                                    </el-form-item>
                                </el-col>
                            </el-row>

                        </el-card>

                        <!-- 基本信息 -->
                        <slot name="before_info"></slot>
                        <el-card shadow="never" class="mt-24">
                            <div slot="header">
                                <span>基本信息</span>
                            </div>
                            <el-row>
                                <el-col :xl="12" :lg="16">
                                    <template v-if="is_info == 1">
                                        <el-form-item label="淘宝采集" hidden>
                                            <el-input v-model="copyUrl">
                                                <template slot="append">
                                                    <el-button @click="copyGoods" :loading="copyLoading">获取
                                                    </el-button>
                                                </template>
                                            </el-input>
                                        </el-form-item>
                                        <el-form-item>
                                            <template slot="label">
                                                <span>品牌名称</span>
                                            </template>
                                            <el-input v-model="ruleForm.goods_brand" type="text" placeholder="请输入品牌名称"></el-input>
                                        </el-form-item>
                                        <el-form-item>
                                            <template slot="label">
                                                <span>供应商</span>
                                            </template>
                                            <el-input v-model="ruleForm.goods_supplier" type="text" placeholder="请输入供应商名称"></el-input>
                                        </el-form-item>
                                        <el-form-item prop="number">
                                            <template slot="label">
                                                <span>商城商品编码</span>
                                                <el-tooltip effect="dark" placement="top" content="只能从商城中获取商品信息，且基本信息与商城商品保持一致">
                                                    <i class="el-icon-info"></i>
                                                </el-tooltip>
                                            </template>
                                            <el-input v-model="copyId" type="number" min="0" oninput="this.value = this.value.replace(/[^0-9]/g, '')" placeholder="请输入商城商品id">
                                                <template slot="append">
                                                    <el-button @click="getDetail(copyId)" :loading="copyLoading">获取
                                                    </el-button>
                                                </template>
                                            </el-input>
                                        </el-form-item>
                                        <el-form-item label="商品名称" prop="name">
                                            <el-input v-model="ruleForm.name" maxlength="100" show-word-limit></el-input>
                                        </el-form-item>

                                        <el-form-item label="商品描述" prop="product">
                                            <el-input v-model="ruleForm.product" maxlength="100" show-word-limit></el-input>
                                        </el-form-item>

                                        <el-form-item label="商品标签">
                                            <el-select v-model="ruleForm.labels" multiple placeholder="请选择标签">
                                                <el-option v-for="item in label_list" :key="item.title" :label="item.title" :value="item.title">
                                                </el-option>
                                            </el-select>
                                        </el-form-item>
                                        <el-form-item prop="pic_url">
                                            <template slot="label">
                                                <span>商品轮播图(多张)</span>
                                                <el-tooltip effect="dark" placement="top" content="第一张图片为封面图">
                                                    <i class="el-icon-info"></i>
                                                </el-tooltip>
                                            </template>
                                            <div class="pic-url-remark">
                                                第一张图片为缩略图,其它图片为轮播图,建议像素750*750,可拖拽使其改变顺序，最多支持上传9张
                                            </div>
                                            <div flex="dir:left">
                                                <template v-if="ruleForm.pic_url.length">
                                                    <draggable v-model="ruleForm.pic_url" flex="dif:left">
                                                        <div v-for="(item,index) in ruleForm.pic_url" :key="index" style="margin-right: 20px;position: relative;cursor: move;">
                                                            <com-attachment @selected="updatePicUrl" :params="{'currentIndex': index}">
                                                                <com-image mode="aspectFill" width="100px" height='100px' :src="item.pic_url">
                                                                </com-image>
                                                            </com-attachment>
                                                            <el-button class="del-btn" size="mini" type="danger" icon="el-icon-close" circle @click="delPic(index)"></el-button>
                                                        </div>
                                                    </draggable>
                                                </template>
                                                <template v-if="ruleForm.pic_url.length < 9">
                                                    <com-attachment style="margin-bottom: 10px;" :multiple="true" :max="9" @selected="picUrl">
                                                        <el-tooltip class="item" effect="dark" content="建议尺寸:750 * 750" placement="top">
                                                            <div flex="main:center cross:center" class="add-image-btn">
                                                                + 添加图片
                                                            </div>
                                                        </el-tooltip>
                                                    </com-attachment>
                                                </template>
                                            </div>
                                        </el-form-item>

                                        <el-form-item label="商品视频" prop="video_url">
                                            <el-input v-model="ruleForm.video_url" placeholder="请输入视频原地址或选择上传视频">
                                                <template slot="append">
                                                    <com-attachment :multiple="false" :max="1" @selected="videoUrl" type="video">
                                                        <el-tooltip class="item" effect="dark" content="支持格式mp4;支持编码H.264;视频大小不能超过50 MB" placement="top">
                                                            <el-button size="mini">添加视频</el-button>
                                                        </el-tooltip>
                                                    </com-attachment>
                                                </template>
                                            </el-input>
                                            <el-link class="box-grow-0" type="primary" style="font-size:12px" v-if='ruleForm.video_url' :underline="false" target="_blank" :href="ruleForm.video_url">视频链接
                                            </el-link>
                                        </el-form-item>
                                    </template>
                                    <template v-else>
                                        <!-- plugins -->
                                        <el-form-item label="商品信息获取" width="120">
                                            <label slot="label">
                                                商品信息获取
                                                <el-tooltip class="item" effect="dark" content="只能从商城中获取商品信息，且基本信息与商城商品保持一致" placement="top">
                                                    <i class="el-icon-info"></i>
                                                </el-tooltip>
                                            </label>
                                            <div>
                                                <el-row type="flex">
                                                    <el-button type="text" size="medium" style="max-width: 100%;" @click="$navigate({r:'mall/goods/edit', id: goods_warehouse.goods_id}, true)" v-if="goods_warehouse.goods_id">
                                                        <com-ellipsis :line="1">
                                                            ({{goods_warehouse.goods_id}}){{goods_warehouse.name}}
                                                        </com-ellipsis>
                                                    </el-button>
                                                    <com-select-goods :multiple="false" @selected="selectGoodsWarehouse">
                                                        <el-button>选择商品</el-button>
                                                    </com-select-goods>
                                                </el-row>
                                                <el-button type="text" @click="$navigate({r:'mall/goods/edit'}, true)">
                                                    商城还未添加商品？点击前往
                                                </el-button>
                                            </div>
                                        </el-form-item>
                                        <el-form-item label="商品名称">
                                            <el-input :value="goods_warehouse.name" :disabled="true"></el-input>
                                        </el-form-item>
                                        <el-form-item>
                                            <template slot="label">
                                                <span>原价</span>
                                                <el-tooltip effect="dark" content="以划线形式显示" placement="top">
                                                    <i class="el-icon-info"></i>
                                                </el-tooltip>
                                            </template>
                                            <el-input :value="goods_warehouse.original_price" :disabled="true"></el-input>
                                        </el-form-item>
                                        <el-form-item label="规格" v-if="is_attr == 0">
                                            <el-row type="flex">
                                                <com-ellipsis :line="1">
                                                    <template v-for="item in ruleForm.select_attr_groups">
                                                        <span style="margin: 0 5px;">
                                                            {{item.attr_group_name}}:{{item.attr_name}}
                                                        </span>
                                                    </template>
                                                </com-ellipsis>
                                                <com-attr-select :attr-groups="goods_warehouse.attr_groups" v-model="ruleForm.select_attr_groups">
                                                    <el-button>选择</el-button>
                                                </com-attr-select>
                                            </el-row>
                                        </el-form-item>
                                    </template>
                                    <el-form-item v-if="is_goods == 1" prop="app_share_title">
                                        <label slot="label">
                                            <span>自定义分享标题</span>
                                            <el-tooltip class="item" effect="dark" content="分享给好友时，作为商品名称" placement="top">
                                                <i class="el-icon-info"></i>
                                            </el-tooltip>
                                        </label>
                                        <el-input placeholder="请输入分享标题" v-model="ruleForm.app_share_title"></el-input>
                                        <el-button @click="app_share.dialog = true;app_share.type = 'name_bg'" type="text">查看图例
                                        </el-button>
                                    </el-form-item>
                                    <el-form-item v-if="is_goods == 1" prop="app_share_pic">
                                        <label slot="label">
                                            <span>自定义分享图片</span>
                                            <el-tooltip class="item" effect="dark" content="分享给好友时，作为分享图片" placement="top">
                                                <i class="el-icon-info"></i>
                                            </el-tooltip>
                                        </label>
                                        <com-attachment v-model="ruleForm.app_share_pic" :multiple="false" :max="1">
                                            <el-tooltip class="item" effect="dark" content="建议尺寸:420 * 336" placement="top">
                                                <el-button size="mini">选择图片</el-button>
                                            </el-tooltip>
                                        </com-attachment>
                                        <div class="customize-share-title">
                                            <com-image mode="aspectFill" width='80px' height='80px' :src="ruleForm.app_share_pic ? ruleForm.app_share_pic : ''"></com-image>
                                            <el-button v-if="ruleForm.app_share_pic" class="del-btn" size="mini" type="danger" icon="el-icon-close" circle @click="ruleForm.app_share_pic = ''"></el-button>
                                        </div>
                                        <el-button @click="app_share.dialog = true;app_share.type = 'pic_bg'" type="text">查看图例
                                        </el-button>
                                    </el-form-item>
                                    <el-form-item v-if="!is_mch && is_goods == 1" label="上架状态" prop="status">
                                        <el-switch @change="statusChange" :active-value="1" :inactive-value="0" v-model="ruleForm.status">
                                        </el-switch>
                                    </el-form-item>
                                    <el-form-item label="是否到店消费" prop="status">
                                        <el-switch :active-value="1" :inactive-value="0" v-model="ruleForm.is_on_site_consumption">
                                        </el-switch>
                                    </el-form-item>

                                    <el-form-item label="购买权限" prop="purchase_permission">
                                        <el-checkbox-group v-model="ruleForm.purchase_permission">
                                            <el-checkbox label="user">普通用户</el-checkbox>
                                            <el-checkbox label="store">VIP会员</el-checkbox>
                                            <el-checkbox label="partner">合伙人</el-checkbox>
                                            <el-checkbox label="branch_office">分公司</el-checkbox>
                                        </el-checkbox-group>
                                    </el-form-item>

                                    <!-- 自定义 -->
                                    <el-dialog :title="app_share['type'] == 'pic_bg' ? `查看自定义分享图片图例`:`查看自定义分享标题图例`" :visible.sync="app_share.dialog" width="30%">
                                        <div flex="dir:left main:center" class="app-share">
                                            <div class="app-share-bg" :style="{backgroundImage: 'url('+app_share[app_share.type]+')'}"></div>
                                        </div>
                                        <div slot="footer" class="dialog-footer">
                                            <el-button @click="app_share.dialog = false" type="primary">我知道了</el-button>
                                        </div>
                                    </el-dialog>
                                </el-col>
                            </el-row>
                        </el-card>

                        <!-- 价格库存 -->
                        <slot name="before_attr"></slot>
                        <el-card shadow="never" class="mt-24">
                            <div slot="header">
                                <span>价格库存</span>
                            </div>
                            <el-row>
                                <el-col :xl="12" :lg="16">
                                    <template v-if="is_attr == 1">

                                        <el-form-item label="商品利润">
                                            <el-input type="number" oninput="this.value = this.value.replace(/[^0-9\.]/, '');" min="0" v-model="ruleForm.profit_price">
                                                <template slot="append">元</template>
                                            </el-input>
                                        </el-form-item>

                                        <el-form-item label="商品总库存" prop="goods_num">
                                            <el-input type="number" min="0" oninput="this.value = this.value.replace(/[^0-9]/g, '');" :disabled="ruleForm.use_attr == 1 ? true : false" v-model="ruleForm.goods_num">
                                            </el-input>
                                        </el-form-item>
                                        <el-form-item label="默认规格名" prop="attr_default_name">
                                            <el-input :disabled="ruleForm.use_attr == 1" v-model="ruleForm.attr_default_name">
                                            </el-input>
                                        </el-form-item>
                                        <el-form-item prop="goods_num" id="goods_aera">
                                            <label slot="label" >
                                                <span>商品规格</span>
                                                <el-tooltip class="item" effect="dark" content="如有颜色、尺码等多种规格，请添加商品规格" placement="top">
                                                    <i class="el-icon-info"></i>
                                                </el-tooltip>
                                            </label>
                                            <div style="width:130%">
                                                <com-attr-group @select="makeAttrGroup" v-model="attrGroups"></com-attr-group>
                                            </div>
                                            <div v-if="ruleForm.use_attr" style="width:130%;margin-top: 24px;">
                                                <com-attr v-model="ruleForm.attr" :attr-groups="attrGroups" :extra="cForm.extra ? cForm.extra : {}"></com-attr>
                                            </div>
                                        </el-form-item>
                                        <el-form-item prop="sort">
                                            <template slot="label">
                                                <span>排序</span>
                                                <el-tooltip effect="dark" content="排序值越小排序越靠前" placement="top">
                                                    <i class="el-icon-info"></i>
                                                </el-tooltip>
                                            </template>
                                            <el-input type="number" placeholder="请输入排序" min="0" oninput="this.value = this.value.replace(/[^0-9]/g, '');" v-model.number="ruleForm.sort">
                                            </el-input>
                                        </el-form-item>
                                        <!-- qwe -->
                                        <el-form-item prop="price" label="售价" v-if="is_price !=3" class="diy_box">
                                            <el-select v-loadmore="loadmore" class="bbb" v-model="pic_value" v-if="pic_options.length != 0" placeholder="请选择">
                                                <el-option v-for="(item,index) in pic_options" :key="item.value" :label="item.label" :value="item.value">
                                                    <span style="float: left">{{ item.label }}</span>
                                                    <i class="el-icon-circle-close" @click.stop='deletePrice(index)' style="float: right; color: #8492a6; font-size: 13px;line-height:34px"></i>
                                                    <!-- <span  >{{ item.value }}</span> -->
                                                </el-option>
                                            </el-select>

                                            <div style="display:flex;justify-content: space-between;width:600px">
                                                <el-input type="number" oninput="this.value = this.value.replace(/[^0-9\.]/, '');" min="0" v-model="ruleForm.price" style="width: 550px;">
                                                    <template slot="append">元</template>
                                                </el-input>

                                                <el-button type="primary" :loading="is_loading" @click='addPriceName' style="margin-left: 10px;">新增</el-button>
                                            </div>
                                            <div style="font-size: 12px;color:#bcbcbc;">这是售价字段</div>
                                        </el-form-item>

                                        <el-form-item v-if="cForm.extra" v-for="(item, key, index) in cForm.extra" :key="item.id">
                                            <label slot="label">{{item}}</label>
                                            <el-input v-model="ruleForm[key]"></el-input>
                                        </el-form-item>
                                        <el-form-item v-if="is_show == 1" prop="original_price">
                                            <template slot="label">
                                                <span>原价</span>
                                                <el-tooltip effect="dark" content="以划线形式显示" placement="top">
                                                    <i class="el-icon-info"></i>
                                                </el-tooltip>
                                            </template>

                                            <el-input type="number" min="0" oninput="this.value = this.value.replace(/[^0-9\.]/, '');" v-model="ruleForm.original_price">
                                                <template slot="append">元</template>
                                            </el-input>
                                        </el-form-item>
                                        <el-form-item v-if="is_show == 1" label="单位" prop="unit">
                                            <el-input v-model="ruleForm.unit"></el-input>
                                        </el-form-item>
                                        <el-form-item v-if="is_show == 1" label="成本价" prop="cost_price">
                                            <el-input type="number" oninput="this.value = this.value.replace(/[^0-9\.]/, '');" min="0" v-model="ruleForm.cost_price">
                                                <template slot="append">元</template>
                                            </el-input>
                                        </el-form-item>
                                        <!--                                        <el-form-item v-if="is_show == 1 && !is_mch" prop="is_negotiable">-->
                                        <!--                                            <template slot='label'>-->
                                        <!--                                                <span>商品面议111</span>-->
                                        <!--                                                <el-tooltip effect="dark" content="如果开启面议，则商品无法在线支付" placement="top">-->
                                        <!--                                                    <i class="el-icon-info"></i>-->
                                        <!--                                                </el-tooltip>-->
                                        <!--                                            </template>-->
                                        <!--                                            <el-switch :active-value="1"-->
                                        <!--                                                       :inactive-value="0"-->
                                        <!--                                                       v-model="ruleForm.is_negotiable">-->
                                        <!--                                            </el-switch>-->
                                        <!--                                        </el-form-item>-->


                                        <el-form-item label="是否显示销量" prop="is_show_sales">
                                            <el-switch v-model="ruleForm.is_show_sales" :active-value="1" :inactive-value="0">
                                            </el-switch>
                                        </el-form-item>

                                        <el-form-item label="是否启用虚拟销量" prop="use_virtual_sales">
                                            <el-switch v-model="ruleForm.use_virtual_sales" :active-value="1" :inactive-value="0">
                                            </el-switch>
                                        </el-form-item>

                                        <el-form-item prop="virtual_sales">

                                            <template slot='label'>
                                                <span>已出售量</span>
                                                <el-tooltip effect="dark" content="前端展示的销量=实际销量+已出售量" placement="top">
                                                    <i class="el-icon-info"></i>
                                                </el-tooltip>
                                            </template>
                                            <el-input type="number" oninput="this.value = this.value.replace(/[^0-9]/, '')" min="0" v-model="ruleForm.virtual_sales">
                                                <template slot="append">{{ruleForm.unit}}</template>
                                            </el-input>
                                        </el-form-item>

                                        <el-form-item label="真实销量">
                                            <el-input v-model="ruleForm.real_sales" disabled>
                                            </el-input>
                                        </el-form-item>

                                        <el-form-item label="商品货号">
                                            <el-input :disabled="ruleForm.use_attr == 1 ? true : false" v-model="ruleForm.goods_no">
                                            </el-input>
                                        </el-form-item>
<!--                                        <el-form-item label="商品重量">-->
<!--                                            <el-input oninput="this.value = this.value.replace(/[^0-9]/g, '');" :disabled="ruleForm.use_attr == 1 ? true : false" v-model="ruleForm.goods_weight">-->
<!--                                                <template slot="append">克</template>-->
<!--                                            </el-input>-->
<!--                                        </el-form-item>-->
                                    </template>
                                    <template v-else-if="is_price == 1">
                                        <el-form-item label="售价" prop="price">
                                            <el-input type="number" oninput="this.value = this.value.replace(/[^0-9\.]/, '');" min="0" v-model="ruleForm.price">
                                                <template slot="append">元</template>
                                            </el-input>
                                        </el-form-item>
                                    </template>
                                    <template v-else>
                                        <el-form-item label="售价" prop="price">
                                            <el-input type="number" oninput="this.value = this.value.replace(/[^0-9\.]/, '');" min="0" v-model="ruleForm.price">
                                                <template slot="append">元</template>
                                            </el-input>
                                        </el-form-item>
                                        <el-form-item prop="sort">
                                            <template slot="label">
                                                <span>排序</span>
                                                <el-tooltip effect="dark" content="排序值越小排序越靠前" placement="top">
                                                    <i class="el-icon-info"></i>
                                                </el-tooltip>
                                            </template>
                                            <el-input type="number" oninput="this.value = this.value.replace(/[^0-9]/, '');" min="0" placeholder="请输入排序" v-model.number="ruleForm.sort">
                                            </el-input>
                                        </el-form-item>
                                        <el-form-item prop="virtual_sales">
                                            <template slot='label'>
                                                <span>已出售量</span>
                                                <el-tooltip effect="dark" content="前端展示的销量=实际销量+已出售量" placement="top">
                                                    <i class="el-icon-info"></i>
                                                </el-tooltip>
                                            </template>
                                            <el-input type="number" oninput="this.value = this.value.replace(/[^0-9]/, '');" min="0" v-model="ruleForm.virtual_sales">
                                                <template slot="append">{{ruleForm.unit}}</template>
                                            </el-input>
                                        </el-form-item>
                                    </template>
                                </el-col>
                            </el-row>
                        </el-card>

                        <!-- 商品服务 -->
                        <slot name="before_goods"></slot>
                        <el-card shadow="never" class="mt-24" v-if="is_goods == 1">
                            <div slot="header">
                                <span>商品服务</span>
                            </div>
                            <el-row>
                                <el-col :xl="12" :lg="16">
                                    <el-form-item label="商品服务">
                                        <template v-if="!defaultServiceChecked">
                                            <el-tag v-for="(service, index) in ruleForm.services" @close="serviceDelete(index)" :key="service.id" :disable-transitions="true" style="margin-right: 10px;" closable>
                                                {{service.name}}
                                            </el-tag>
                                            <el-button type="button" size="mini" @click="serviceOpen">新增服务
                                            </el-button>
                                            <el-dialog title="选择商品服务" :visible.sync="service.dialog" width="30%">
                                                <el-card shadow="never" flex="dir:left" style="flex-wrap: wrap" v-loading="service.loading">
                                                    <el-checkbox-group v-model="service.list">
                                                        <el-checkbox v-for="item in service.services" :label="item" :key="item.id">{{item.name}}
                                                        </el-checkbox>
                                                    </el-checkbox-group>
                                                </el-card>
                                                <div slot="footer" class="dialog-footer">
                                                    <el-button @click="serviceCancel">取 消</el-button>
                                                    <el-button type="primary" @click="serviceConfirm">确 定</el-button>
                                                </div>
                                            </el-dialog>
                                        </template>
                                        <el-checkbox v-model="defaultServiceChecked" @change="defaultService()">默认服务
                                        </el-checkbox>
                                    </el-form-item>

                                    <el-form-item prop="freight_id">
                                        <template slot='label'>
                                            <span>运费设置</span>
                                            <el-tooltip effect="dark" content="选择第一项（默认运费）将会根据运费管理的（默认运费）变化而变化" placement="top">
                                                <i class="el-icon-info"></i>
                                            </el-tooltip>
                                        </template>
                                        <el-tag @close="freightDelete()" v-if="ruleForm.freight" :key="ruleForm.freight.name" :disable-transitions="true" style="margin-right: 10px;" closable>
                                            {{ruleForm.freight.name}}
                                        </el-tag>
                                        <el-button type="button" size="mini" @click="freightOpen">选择运费
                                        </el-button>
                                        <el-dialog title="选择运费" :visible.sync="freight.dialog" width="30%">
                                            <el-card shadow="never" flex="dir:left" style="flex-wrap: wrap" v-loading="freight.loading">
                                                <el-radio-group v-model="freight.checked">
                                                    <el-radio @change="open_show(item.id)" style="padding: 10px;" v-for="item in freight.list" :label="item" :key="item.id">{{item.name}}
                                                    </el-radio>
                                                </el-radio-group>
                                            </el-card>
                                            <el-button type="primary" style="margin-top: 20px;" @click="open_option" v-if="ExpressFlag">{{CustomExpressText}}</el-button>
                                            <div slot="footer" class="dialog-footer">
                                                <el-button @click="freightCancel">取 消</el-button>
                                                <el-button type="primary" @click="freightConfirm">确 定</el-button>
                                            </div>
                                        </el-dialog>
                                    </el-form-item>

                                    <el-drawer
                                            title="追加快递费"
                                            :visible.sync="drawer"
                                            size="25%" style="overflow: scroll">
                                        <div>
                                            <el-form ref="form" label-width="120px">
                                            <el-form-item v-for="(el,i) in ExpressDataList" :label="el.name">
                                                <el-input v-model="ExpressForm[i]" :name="el.name" value="" size="small" style="width: 200px"></el-input>
                                            </el-form-item>
                                            </el-form>
                                        </div>
                                    </el-drawer>

                                    <el-form-item prop="freight_id" v-if="is_form == 1">
                                        <template slot='label'>
                                            <span>自定义表单</span>
                                            <el-tooltip effect="dark" content="选择第一项（默认表单）将会根据表单列表的（默认表单）变化而变化" placement="top">
                                                <i class="el-icon-info"></i>
                                            </el-tooltip>
                                        </template>
                                        <com-goods-form v-model="ruleForm.form" @selected="selectForm" title="选择表单" url="mall/order-form/all-list"></com-goods-form>
                                    </el-form-item>
                                    <el-form-item label="限购数量" prop="confine_count">
                                        <div flex="dir:left">
                                            <span class="box-grow-0" style="color:#606266">商品</span>
                                            <div style="width: 100%;margin:0 10px">
                                                <el-input oninput="this.value = this.value.replace(/[^0-9]/g, '');" :disabled="ruleForm.confine_count <= -1" placeholder="请输入限购数量" v-model="ruleForm.confine_count">
                                                    <template slot="append">件</template>
                                                </el-input>
                                            </div>
                                            <el-checkbox style="margin-left: 5px;" @change="itemChecked" v-model="ruleForm.confine_count <= -1">无限制
                                            </el-checkbox>
                                        </div>
                                        <div flex="dir:left" style="margin-top: 10px;">
                                            <span class="box-grow-0" style="color:#606266">订单</span>
                                            <div style="width: 100%;margin:0 10px">
                                                <el-input oninput="this.value = this.value.replace(/[^0-9]/g, '');" :disabled="ruleForm.confine_order_count <= -1" placeholder="请输入限购数量" v-model="ruleForm.confine_order_count">
                                                    <template slot="append">单</template>
                                                </el-input>
                                            </div>
                                            <el-checkbox style="margin-left: 5px;" @change="itemOrderChecked" v-model="ruleForm.confine_order_count <= -1">无限制
                                            </el-checkbox>
                                        </div>
                                    </el-form-item>

                                    <el-form-item label="" prop="pieces">
                                        <template slot='label'>
                                            <span>单品满件包邮</span>
                                            <el-tooltip effect="dark" content="如果设置0或空，则不支持满件包邮" placement="top">
                                                <i class="el-icon-info"></i>
                                            </el-tooltip>
                                        </template>
                                        <el-input type="number" min="0" oninput="" placeholder="请输入数量" v-model="ruleForm.pieces">
                                            <template slot="append">件</template>
                                        </el-input>
                                    </el-form-item>

                                    <el-form-item prop="forehead">
                                        <template slot='label'>
                                            <span>单品满额包邮</span>
                                            <el-tooltip effect="dark" content="如果设置0或空，则不支持满额包邮" placement="top">
                                                <i class="el-icon-info"></i>
                                            </el-tooltip>
                                        </template>

                                        <el-input type="number" oninput="this.value = this.value.replace(/[^0-9\.]/, '');" min="0" placeholder="请输入金额" v-model="ruleForm.forehead">
                                            <template slot="append">元</template>
                                        </el-input>
                                    </el-form-item>

                                    <el-form-item label="区域购买" prop="is_area_limit">
                                        <el-switch v-model="ruleForm.is_area_limit" :active-value="1" :inactive-value="0">
                                        </el-switch>
                                    </el-form-item>
                                    <el-form-item v-if="ruleForm.is_area_limit" label="允许购买区域" prop="area_limit">
                                        <com-area-limit v-model="ruleForm.area_limit"></com-area-limit>
                                    </el-form-item>
                                </el-col>
                            </el-row>
                        </el-card>

                        <!-- 单品满额减免 begin -->
                        <el-card shadow="never" class="mt-24" v-if="is_goods == 1">
                            <div slot="header">
                                <span>单品满额减免</span>
                            </div>
                            <el-row>
                                <el-col :xl="12" :lg="16">

                                    <el-form-item prop="fulfil_price">
                                        <template slot='label'>
                                            <span>单品满额金额</span>
                                            <el-tooltip effect="dark" content="单品达到该金额可减免一定金额，如果设置0或空，则不支持单品满额减免" placement="top">
                                                <i class="el-icon-info"></i>
                                            </el-tooltip>
                                        </template>

                                        <el-input type="number" oninput="this.value = this.value.replace(/[^0-9\.]/, '');" min="0" placeholder="请输入金额" v-model="ruleForm.fulfil_price" @input="change($event)">
                                            <template slot="append">元</template>
                                        </el-input>
                                    </el-form-item>

                                    <el-form-item prop="full_relief_price">
                                        <template slot='label'>
                                            <span>单品满额减免金额</span>
                                            <el-tooltip effect="dark" content="单品达到该金额可减免一定金额,如果设置0或空，则不支持单品满额减免" placement="top">
                                                <i class="el-icon-info"></i>
                                            </el-tooltip>
                                        </template>

                                        <el-input type="number" @input="change($event)" oninput="this.value = this.value.replace(/[^0-9\.]/, '');" min="0" placeholder="请输入金额" v-model="ruleForm.full_relief_price">
                                            <template slot="append">元</template>
                                        </el-input>
                                    </el-form-item>

                                </el-col>
                            </el-row>
                        </el-card>
                        <!-- 单品满额减免 end -->

                        <!-- 显示设置 todo -->
                        <el-card shadow="never" class="mt-24" v-if="false && is_show == 1">
                            <div slot="header">
                                <span>显示设置</span>
                            </div>
                        </el-card>

                        <!-- 营销设置 -->
                        <slot name="before_marketing"></slot>
                        <el-card shadow="never" class="mt-24" v-if="is_marketing == 1 && !is_mch">
                            <div slot="header">
                                <span>营销设置</span>
                            </div>
                            <el-row>
                                <el-col :xl="12" :lg="16">
                                    <!-- 积分券赠送 -->
                                    <el-form-item  label="" prop="status">
                                        <template slot='label'>
                                            <span>积分券赠送</span>
                                            <el-tooltip effect="dark" placement="top">
                                                <div slot="content">积分券赠送开关，积分券、积分二选一<br />
                                                    关闭， 则以积分（累加）形式赠送</br>
                                                    开启， 则以积分券（充值）形式赠送 
                                                </div>
                                                <i class="el-icon-info"></i>
                                            </el-tooltip>
                                        </template>
                                        <el-switch v-model="info.enable_score" :active-value="1" :inactive-value="0" active-text="开启" inactive-text="关闭">
                                        </el-switch>
                                        <div v-if="info.enable_score==1">
                                            <el-switch v-model="isPermanentScore" :active-value="1" :inactive-value="0" active-text="限时有效" inactive-text="永久有效" @change="isPermanentScoreChange">
                                            </el-switch>
                                        </div>
                                    
                                        <div v-if="info.enable_score==1" class="demo-input-suffix agent-setting-item">
                                    
                                            <el-input type="number" :min="0" class="member-money" v-model="score_setting.integral_num" placeholder="">
                                                <template slot="append">积分券</template>
                                            </el-input>
                                            <el-input type="number" :min="0" class="member-money" v-model="score_setting.period" placeholder="">
                                                <template slot="append">月</template>
                                            </el-input>
                                            <el-input v-if="isPermanentScore==1" type="number" class="member-money" style="width: 180px;" v-model="score_setting.expire" placeholder="">
                                                <template slot="append">有效期(天)</template>
                                            </el-input>
                                    
                                        </div>
                                    </el-form-item>
                                    <el-form-item  v-if="info.enable_score==0">
                                        <template slot='label'>
                                            <span>积分赠送</span>
                                            <el-tooltip effect="dark" placement="top">
                                                <div slot="content">用户购物赠送的积分, 如果不填写或填写0，则默认为不赠送积分，
                                                    如果为百分比则为按成交价格的比例计算积分"<br />
                                                    如: 购买2件，设置10 积分, 不管成交价格是多少， 则购买后获得20积分</br>
                                                    如: 购买2件，设置10%积分, 成交价格2 * 200= 400， 则购买后获得 40 积分（400*10%）
                                                </div>
                                                <i class="el-icon-info"></i>
                                            </el-tooltip>
                                        </template>
                                        <el-input type="number" min="0" oninput="this.value = this.value.replace(/[^0-9]/g, '');" placeholder="请输入赠送积分数量" v-model="ruleForm.give_score">
                                            <template slot="append">
                                                分
                                                <el-radio v-model="ruleForm.give_score_type" :label="1">固定值
                                                </el-radio>
                                                <el-radio v-model="ruleForm.give_score_type" :label="2">百分比
                                                </el-radio>
                                            </template>
                                        </el-input>
                                    </el-form-item>
                                    <el-form-item>
                                        <template slot='label'>
                                            <span>积分抵扣</span>
                                            <el-tooltip effect="dark" content="如果设置0，则不支持积分抵扣" placement="top">
                                                <i class="el-icon-info"></i>
                                            </el-tooltip>
                                        </template>
                                        <el-input :disabled="ruleForm.full_forehead_score==1" type="number" min="0" oninput="this.value = this.value.replace(/[^0-9\.]/g, '');" placeholder="请输最高抵扣金额" v-model="ruleForm.forehead_score">
                                            <template slot="prepend">最多抵扣</template>
                                            <template slot="append">
                                                元
                                                <el-radio v-model="ruleForm.forehead_score_type" :label="1" :disabled="ruleForm.full_forehead_score==1">固定值
                                                </el-radio>
                                                <el-radio style="display: none" v-model="ruleForm.forehead_score_type" :label="2">百分比
                                                </el-radio>
                                            </template>
                                        </el-input>
                                        <el-checkbox :true-label="1" :false-label="0" :disabled="ruleForm.full_forehead_score==1" v-model="ruleForm.accumulative">
                                            允许多件累计抵扣
                                        </el-checkbox>

                                        <el-checkbox :true-label="1" :false-label="0" @change="fullForeheadScore" v-model="ruleForm.full_forehead_score">
                                            允许全额抵扣
                                        </el-checkbox>
                                    </el-form-item>
                                <!-- </el-col>
                                <el-col :xl="12" :lg="16"> -->
									<!-- 红包券赠送 -->
									<el-form-item  label="红包券赠送" prop="status">
									    <el-switch v-model="info.enable_integral" :active-value="1" :inactive-value="0" active-text="开启" inactive-text="关闭">
									    </el-switch>
                                        <!--
									    <div v-if="info.enable_integral==1">
									        <el-switch v-model="isPermanent" :active-value="1" :inactive-value="0" active-text="限时有效" inactive-text="永久有效" @change="isPermanentChange">
									        </el-switch>
									    </div>-->
									
									    <div v-if="info.enable_integral==1" class="demo-input-suffix agent-setting-item">
									
									        <el-input type="number" :min="0" class="member-money" v-model="integral_setting.integral_num" placeholder="">
									            <template slot="append">红包券</template>
									        </el-input>
                                            <!--
									        <el-input type="number" :min="0" class="member-money" v-model="integral_setting.period" placeholder="">
									            <template slot="append">月</template>
									        </el-input>
									        <el-input v-if="isPermanent==1" type="number" class="member-money" style="width: 180px;" v-model="integral_setting.expire" placeholder="">
									            <template slot="append">有效期(天)</template>
									        </el-input>
									        -->
									    </div>
									
									    <!-- <el-input type="number" min="0" class="member-money" oninput="this.value = this.value.replace(/[^0-9\.]/g, '');" placeholder="请输最高抵扣金额" v-model="info.max_deduct_integral">
									        <template slot="prepend">最多抵扣</template>
									        <template slot="append">元</template>
									    </el-input> -->
									</el-form-item>
									<!-- 红包券抵扣 -->
									<el-form-item >
									    <template slot='label'>
									        <span>红包券抵扣</span>
									        <!-- <el-tooltip effect="dark" content="如果设置0，则不支持积分抵扣"
									                    placement="top">
									            <i class="el-icon-info"></i>
									        </el-tooltip> -->
									    </template>
									    <el-input type="number" min="0" 
											oninput="this.value = this.value.replace(/[^0-9\.]/g, '');" 
											placeholder="请输最高抵扣金额" v-model="info.max_deduct_integral">
									        <template slot="prepend">最多抵扣</template>
									        <template slot="append">元</template>
									    </el-input>

									</el-form-item>

                                    <el-form-item >
                                        <template slot='label'>
                                            <span>红包券抵扣服务费比例</span>
                                            <el-tooltip effect="dark" content="使用红包券抵扣支付时，需要额外收取的红包券"
                                                        placement="top">
                                                <i class="el-icon-info"></i>
                                            </el-tooltip>
                                        </template>
                                        <el-input type="number" min="0" max="100"
                                                  oninput="this.value = this.value.replace(/[^0-9\.]/g, '');"
                                                  placeholder="请输入0-100的数值" v-model="info.integral_fee_rate">
                                            <template slot="append">元</template>
                                        </el-input>
                                    </el-form-item>

                                    <el-form-item label="下单升级会员" prop="enable_upgrade_user_role">
                                        <el-switch v-model="ruleForm.enable_upgrade_user_role" :active-value="1" :inactive-value="0" active-text="开启" inactive-text="关闭">
                                        </el-switch>
                                        <div v-if="ruleForm.enable_upgrade_user_role == 1">
                                            <el-select v-model="ruleForm.upgrade_user_role_type">
                                                <el-option :label="'VIP会员'" :value="'store'"></el-option>
                                                <el-option :label="'合伙人'" :value="'partner'"></el-option>
                                                <el-option :label="'分公司'" :value="'branch_office'"></el-option>
                                            </el-select>
                                        </div>
                                    </el-form-item>

                                    <div style="border-style: solid;border-width: 1px;border-color: RGB(123,125,128);padding-top: 20px;padding-right: 20px">
                                        <el-form-item >
                                            <template slot='label'>
                                                <span>首次购买该商品</span>
                                            </template>
                                            <el-input type="number" v-model="ruleForm.first_buy_setting.buy_num">
                                                <template slot="append">件</template>
                                            </el-input>
                                        </el-form-item>

                                        <el-form-item>
                                            <template slot='label'>
                                                <span>首次购买返红包数</span>
                                            </template>
                                            <el-input type="number" v-model="ruleForm.first_buy_setting.return_red_envelopes">
                                            </el-input>
                                        </el-form-item>

                                        <el-form-item >
                                            <template slot='label'>
                                                <span>首次购买商品利润</span>
                                            </template>
                                            <el-input type="number" v-model="ruleForm.first_buy_setting.return_commission">
                                                <template slot="append">元</template>
                                            </el-input>
                                        </el-form-item>
                                    </div>
								</el-col>
                            </el-row>
                        </el-card>



                        <!-- 商品详情 -->
                        <slot name="before_detail"></slot>
                        <el-card shadow="never" class="mt-24" v-if="is_detail == 1">
                            <div slot="header">
                                <span>商品详情</span>
                            </div>
                            <el-row>
                                <el-col :xl="12" :lg="16">
                                    <el-form-item label="商品详情">
                                        <com-rich-text style="width: 750px" v-model="ruleForm.detail"></com-rich-text>
                                    </el-form-item>
                                </el-col>
                            </el-row>
                        </el-card>
                        <slot name="after_detail"></slot>
                    </el-tab-pane>

                    <el-tab-pane label="订单设置" name="order" v-if="is_order == 1">
                        <el-form-item prop="is_order_paid">
                            <template slot='label'>
                                <span>订单支付后执行</span>
                                <el-tooltip effect="dark" content="订单在支付后执行发放积分、积分券或红包券，否则默认订单完成后发放"
                                            placement="top">
                                    <i class="el-icon-info"></i>
                                </el-tooltip>
                            </template>
                            <el-switch :active-value="1" :inactive-value="0" v-model="info.is_order_paid">
                            </el-switch>
                        </el-form-item>
                        <template v-if="info.is_order_paid == 1">
                            
                            <el-form-item label="积分"  prop="paid_is_score">
                                <el-radio-group v-model="order_paid.is_score" @change="isOrderSetting($event,'paid','is_score')">
                                    <el-radio :label="1" >开启</el-radio>
                                    <el-radio :label="0" >关闭</el-radio>
                                </el-radio-group>
                            </el-form-item>
                            <el-form-item label="积分券"  prop="paid_is_score_card">
                                <el-radio-group v-model="order_paid.is_score_card" @change="isOrderSetting($event,'paid','is_score_card')">
                                    <el-radio :label="1" >开启</el-radio>
                                    <el-radio :label="0" >关闭</el-radio>
                                </el-radio-group>
                            </el-form-item>
                            <el-form-item label="红包券"  prop="paid_is_integral_card">
                                <el-radio-group v-model="order_paid.is_integral_card" @change="isOrderSetting($event,'paid','is_integral_card')">
                                    <el-radio :label="1" >开启</el-radio>
                                    <el-radio :label="0" >关闭</el-radio>
                                </el-radio-group>
                            </el-form-item>
                           
                        </template>
                        <el-form-item prop="cannotrefund">
                            <template slot='label'>
                                <span>是否支持退换货</span>
                                <el-tooltip effect="dark" content="订单中有多个不同售后选项的商品时，整单发起售后只能满足相同选项条件进行售后"
                                            placement="top">
                                    <i class="el-icon-info"></i>
                                </el-tooltip>
                            </template>
                            <div>
                                <el-checkbox-group v-model="ruleForm.cannotrefund">
                                    <el-checkbox label="1">退款</el-checkbox>
                                    <el-checkbox label="2">退货退款</el-checkbox>
                                    <el-checkbox label="3">换货</el-checkbox>
                                </el-checkbox-group>
                            </div>
                        </el-form-item>
                        <!-- <el-form-item label="订单结算后执行" prop="is_order_sales">
                            <el-switch :active-value="1" :inactive-value="0" v-model="info.is_order_sales">
                            </el-switch>
                        </el-form-item>
                        <template v-if="info.is_order_sales == 1">
                            <el-form-item label="积分"  prop="sales_is_score">
                                <el-radio-group v-model="order_sales.is_score" @change="isOrderSetting($event,'sales','is_score')">
                                    <el-radio :label="1" >开启</el-radio>
                                    <el-radio :label="0" >关闭</el-radio>
                                </el-radio-group>
                            </el-form-item>
                            <el-form-item label="积分券"  prop="sales_is_score_card">
                                <el-radio-group v-model="order_sales.is_score_card" @change="isOrderSetting($event,'sales','is_score_card')">
                                    <el-radio :label="1" >开启</el-radio>
                                    <el-radio :label="0" >关闭</el-radio>
                                </el-radio-group>
                            </el-form-item>
                            <el-form-item label="红包券"  prop="sales_is_integral_card">
                                <el-radio-group v-model="order_sales.is_integral_card" @change="isOrderSetting($event,'sales','is_integral_card')">
                                    <el-radio :label="1" >开启</el-radio>
                                    <el-radio :label="0" >关闭</el-radio>
                                </el-radio-group>
                            </el-form-item>
                        </template> -->
                    </el-tab-pane>

                    <el-tab-pane label="会员价设置" name="second" v-if="is_member == 1">
                        <el-form-item label="是否享受会员功能" prop="is_level">
                            <el-switch :active-value="1" :inactive-value="0" v-model="ruleForm.is_level">
                            </el-switch>
                        </el-form-item>
                        <template v-if="ruleForm.is_level == 1">
                            <el-form-item label="是否单独设置会员价" prop="is_level_alone">
                                <el-switch :active-value="1" :inactive-value="0" v-model="ruleForm.is_level_alone">
                                </el-switch>
                            </el-form-item>
                            <template v-if="ruleForm.is_level_alone == 1">
                                <template v-if="ruleForm.use_attr == 1 && memberLevel.length > 0">
                                    <!--多规格会员价设置-->
                                    <el-form-item label="会员价设置">
                                        <com-attr v-model="ruleForm.attr" :attr-groups="attrGroups" :members="memberLevel" :is-level="true"></com-attr>
                                    </el-form-item>
                                </template>
                                <!-- 无规格默认会员价 -->
                                <template v-if="ruleForm.use_attr == 0 && memberLevel.length > 0">
                                    <el-form-item label="默认规格会员价设置">
                                        <el-col :xl="12" :lg="16">
                                            <el-input v-for="item in defaultMemberPrice" :key="item.id" type="number" v-model="ruleForm.member_price[item.level]">
                                                <span slot="prepend">{{item.name}}</span>
                                                <span slot="append">元</span>
                                            </el-input>
                                        </el-col>
                                    </el-form-item>
                                    <el-form-item>
                                        <el-tag type="danger">如需设置多规格会员价,请先添加商品规格</el-tag>
                                    </el-form-item>
                                </template>
                                <el-form-item v-if="memberLevel.length == 0" label="会员价设置">
                                    <el-button type="danger" @click="$navigate({r: 'mall/member-level/edit'})">
                                        如需设置,请先添加会员
                                    </el-button>
                                </el-form-item>
                            </template>
                        </template>
                        <el-form-item v-if="is_svip" label="是否享受超级会员功能" prop="is_vip_card_goods">
                            <el-switch v-model="is_vip_card_goods" :active-value="1" :inactive-value="0"></el-switch>
                        </el-form-item>
                    </el-tab-pane>

                    <el-tab-pane label="分销设置" name="third" v-if="is_show_distribution">
                        <com-goods-distribution-new v-model="ruleForm" :is_mch="is_mch" :goods_type="goods_type" :goods_id="goods_id" v-if="activeName == 'third'">
                        </com-goods-distribution-new>
                    </el-tab-pane>

                    <el-tab-pane label="经销设置" name="fourth" v-if="is_show_agent">
                        <com-goods-agent v-model="ruleForm" :is_mch="is_mch" :goods_type="goods_type" :goods_id="goods_id" v-if="activeName == 'fourth'">
                        </com-goods-agent>
                    </el-tab-pane>

                    <el-tab-pane label="区域设置" name="fifth" v-if="is_show_area">
                        <com-goods-area v-model="ruleForm" :is_mch="is_mch" :goods_type="goods_type" :goods_id="goods_id" v-if="activeName == 'fifth'">
                        </com-goods-area>
                    </el-tab-pane>

                    <el-tab-pane label="购物券设置" name="shopping_setting" v-if="goods_id > 0">
                        <el-form ref="shoppingFormData" :rules="shoppingFormRule" label-width="30%" :model="shoppingFormData" size="small">
                            <el-form-item label="赠送比例" prop="give_value">
                                <el-input :disabled="formProgressData.loading" type="number" min="0" max="100" placeholder="请输入内容" v-model="shoppingFormData.give_value" style="width:260px;">
                                    <template slot="append">%</template>
                                </el-input>
                            </el-form-item>
                            <el-form-item label="启动日期" prop="start_at">
                                <el-date-picker :disabled="formProgressData.loading" v-model="shoppingFormData.start_at" type="date" placeholder="选择日期"></el-date-picker>
                            </el-form-item>
                            <el-form-item label="运费（运营费）" prop="enable_express">
                                <el-switch
                                        v-model="shoppingFormData.enable_express"
                                        active-text="赠送购物券"
                                        inactive-text="不赠送"
                                        active-value="1"
                                        inactive-value="0">
                                </el-switch>
                            </el-form-item>
                        </el-form>
                        <div style="margin-left: 500px">
                            <el-button type="primary" @click="shoppingSave">确 定</el-button>
                        </div>
                    </el-tab-pane>

                    <slot name="tab_pane"></slot>
                </el-tabs>
            </el-form>
            <div class="bottom-div" flex="cross:center" v-if="is_save_btn == 1 && activeName != 'shopping_setting'">
                <el-button class="button-item" :loading="btnLoading" type="primary" size="small" @click="store('ruleForm')">保存
                </el-button>
                <el-button class="button-item" size="small" @click="showPreview">预览</el-button>
            </div>
        </div>

        <com-preview ref="preview" :rule-form="ruleForm" @submit="store('ruleForm')" :preview-info="previewInfo">
            <template slot="preview">
                <slot name="preview"></slot>
            </template>
            <template slot="preview_end">
                <slot name="preview_end"></slot>
            </template>
        </com-preview>

        <com-dialog-select
                @close="closeLiancDlgSelect"
                @selected="confirmLiancDlgSelect"
                :url="forLiancDlgSelect.url"
                :multiple="forLiancDlgSelect.multiple"
                :title="forLiancDlgSelect.title"
                :list-key="forLiancDlgSelect.listKey"
                :params="forLiancDlgSelect.params"
                :columns="forLiancDlgSelect.columns"
                :extra-search="forLiancDlgSelect.extraSearch"
                :visible="forLiancDlgSelect.visible"></com-dialog-select>
    </el-card>

</template>
<script src="<?= Yii::$app->request->baseUrl ?>/statics/unpkg/vuedraggable@2.18.1/dist/vuedraggable.umd.min.js"></script>
<script>
    Vue.component('com-goods', {
        template: '#com-goods',
        props: {
            // 选择分类  0--不显示 1--显示可编辑
            is_cats: {
                type: Number,
                default: 0
            },
            // 基本信息
            is_basic: {
                type: Number,
                default: 1
            },
            is_info: {
                type: Number,
                default: 0
            },
            // 规格库存
            is_attr: {
                type: Number,
                default: 1
            },
            // 商品设置
            is_goods: {
                type: Number,
                default: 1
            },
            // 显示设置
            is_show: {
                type: Number,
                default: 1
            },
            // 营销设置
            is_marketing: {
                type: Number,
                default: 1
            },
            // 商品详情
            is_detail: {
                type: Number,
                default: 0
            },
            // 分销设置
            is_distribution: {
                type: Number,
                default: 1
            },
            // 会员设置
            is_member: {
                type: Number,
                default: 1
            },

            // 订单设置
            is_order: {
                type: Number,
                default: 1
            },

            //todo 仅显示售价（抽奖） 秒杀3显示
            is_price: {
                type: Number,
                default: 0
            },

            // 请求数据地址
            url: {
                type: String,
                default: 'mall/goods/edit'
            },
            // 请求数据地址
            get_goods_url: {
                type: String,
                default: 'mall/goods/edit'
            },
            // 保存之后返回地址
            referrer: {
                default: 'mall/goods/index'
            },
            is_mch: {
                type: Number,
                default: 0
            },
            mch_id: {
                type: Number,
                default: 0
            },
            // 页面上数据
            form: Object,
            // 数据验证方式
            rule: Object,
            status_change_text: {
                type: String,
                default: '',
            },
            // 是否使用表单
            is_form: {
                type: Number,
                default: 1
            },
            sign: String,

            is_save_btn: {
                type: Number,
                default: 1
            },
            previewInfo: {
                type: Object,
                default: function() {
                    return {
                        is_head: true,
                        is_cart: true,
                        is_mch: this.is_mch == 1
                    }
                }
            },
        },
        data() {
            let ruleForm = {
                labels: [],
                attr: [],
                cats: [],
                mchCats: [], //多商户系统分类
                cards: [],
                services: [],
                pic_url: [],
                use_attr: 0,
                goods_num: 0,
                status: 0,
                unit: '件',
                virtual_sales: 0,
                cover_pic: '',
                sort: 100,
                accumulative: 0,
                confine_count: -1,
                confine_order_count: -1,
                forehead: 0,
                forehead_score: 0,
                forehead_score_type: 1,
                freight_id: 0,
                freight: null,
                give_score: 0,
                give_score_type: 1,
                individual_share: 0,
                is_level: 1,
                is_level_alone: 0,
                goods_brand:'',
                goods_supplier:'',
                pieces: 0,
                share_type: 0,
                attr_setting_type: 0,
                video_url: '',
                is_sell_well: 0,
                is_negotiable: 0,
                name: '',
                price: 0,
                original_price: 0,
                cost_price: 0,
                detail: '',
                extra: '',
                app_share_title: '', //自定义分享标题,
                app_share_pic: '', //自定义分享图片
                is_default_services: 1,
                member_price: {},
                goods_no: '',
                goods_weight: '',
                select_attr_groups: [], // 已选择的规格
                goodsWarehouse_attrGroups: [], // 商品库商品所有的规格
                is_on_site_consumption:0,//到店消费类商品
                share_level_type: 0,
                distributionLevelList: [],
                form: null,
                is_show_sales: 0,
                use_virtual_sales: 1,
                form_id: 0,
                attr_default_name: '',
                is_area_limit: 0,
                use_score: 0,
                area_limit: [{
                    list: []
                }],
                full_relief_price: 0,
                fulfil_price: 0,
                cannotrefund:["1","2","3"],
                profit_price: 0,  //商品利润
                enable_upgrade_user_role:0, //下单后升级会员VIP会员、合伙人或分公司
                upgrade_user_role_type: '',
                product: '',
                purchase_permission: [],
                first_buy_setting:{
                    buy_num : 0,
                    return_red_envelopes : 0,
                    return_commission : 0,
                },
                lianc_user_id: 0,
                lianc_commission_type: 1,
                lianc_commisson_value: 0
            };
            let rules = {
                cats: [{
                    required: true,
                    type: 'array',
                    validator: (rule, value, callback) => {
                        if (this.ruleForm.cats instanceof Array && this.ruleForm.cats.length > 0) {
                            callback();
                        }
                        callback('请选择分类');
                    }
                }],
                mchCats: [{
                    required: true,
                    type: 'array',
                    validator: (rule, value, callback) => {
                        if (this.ruleForm.mchCats instanceof Array && this.ruleForm.mchCats.length > 0) {
                            callback();
                        }
                        callback('请选择系统分类');
                    }
                }],
                name: [{
                    required: true,
                    message: '请输入商品名称',
                    trigger: 'change'
                }, ],
                price: [{
                    required: true,
                    message: '请输入商品价格',
                    trigger: 'change'
                }],
                original_price: [{
                    required: true,
                    message: '请输入商品原价',
                    trigger: 'change'
                }],
                cost_price: [{
                    required: false,
                    message: '请输入商品成本价',
                    trigger: 'change'
                }],
                unit: [{
                        required: true,
                        message: '请输入商品单位',
                        trigger: 'change'
                    },
                    {
                        max: 5,
                        message: '最大为5个字符',
                        trigger: 'change'
                    },
                ],
                goods_num: [{
                    required: true,
                    message: '请输入商品总库存',
                    trigger: 'change'
                }, ],
                is_area_limit: [{
                    required: false,
                    type: 'integer',
                    message: '请选择是否开启',
                    trigger: 'blur'
                }],
                area_limit: [{
                    required: true,
                    type: 'array',
                    validator: (rule, value, callback) => {
                        if (value instanceof Array && value[0]['list'].length === 0) {
                            callback('允许购买区域不能为空');
                        }
                        callback();
                    }
                }],
                pic_url: [{
                    required: true,
                    message: '请上传商品轮播图',
                    trigger: 'change'
                }, ],
            };
            return {
                forLiancDlgSelect:{
                    visible: false,
                    multiple: false,
                    title: "选择用户",
                    params: {},
                    columns: [
                        {label:"手机号", key:"mobile"},
                        {label:"等级", key:"role_type_text"}
                    ],
                    listKey: 'nickname',
                    extraSearch:{
                        is_lianc: 1
                    },
                    url: "mall/user/index",
                    selection: {nickname: '', id: 0}
                },
                pic_options: [],
                pic_value: '',
                is_loading: false,
                priceName_page: 1,
                priceName_page_count: 0,
                detail_data: '',
                goods_id: 0,
                keyword: '',
                goods_type: 'MALL_GOODS',
                label_list: [],
                cardLoading: false,
                btnLoading: false,
                dialogLoading: false,
                activeName: 'first',
                ruleForm: ruleForm,
                // 分销层级
                shareLevel: [],
                // 会员等级
                memberLevel: [],
                rules: rules,
                options: [], // 商品分类列表
                mchOptions: [], //多商户商品编辑时使用
                newOptions: [],
                cats: [], //用于前端已选的分类展示
                mchCats: [], //用于前端已选的分类展示 多商户
                cards: [], // 优惠券
                attrGroups: [], //规格组
                CustomExpress:'',
                ExpressFlag:false,
                CustomExpressText:'开启自定义',
                ExpressForm:[],
                ExpressName:[],
                ExpressDataList:[],
                drawer: false,
                innerDrawer: false,
                attrGroupName: '',
                attrName: [],
                // 批量设置
                batch: {},
                dialogVisible: false, //分类选择弹框
                mchDialogVisible: false,
                is_vip_card_goods: 0,
                is_svip: false,
                goods_warehouse: {},
                copyUrl: '',
                copyLoading: false,
                defaultServiceChecked: false,
                service: {
                    dialog: false,
                    list: [],
                    services: [], // 商品服务列表
                    loading: false
                },
                freight: {
                    dialog: false,
                    list: [],
                    checked: {},
                    loading: false
                },
                copyId: '',
                app_share: {
                    dialog: false,
                    type: '',
                    bg: "<?= \Yii::$app->request->baseUrl ?>/statics/img/mall/app-share.png",
                    name_bg: "<?= \Yii::$app->request->baseUrl ?>/statics/img/mall/app-share-name.png",
                    pic_bg: "<?= \Yii::$app->request->baseUrl ?>/statics/img/mall/app-share-pic.png",
                },
                is_show_distribution: 1,
                is_show_agent: 1,
                is_show_area: 1,
                use_score: 0,
                video_type: 1,
                cardDialogVisible: false,
                // 积分券赠送数据
                score_setting: {
                    "integral_num": 0, //积分数量
                    "period": 12, //周期
                    "period_unit": "month", //单位
                    "expire": 30 //有效天数
                },
                isPermanentScore: 0, //默认永久
                // 红包券赠送数据
                integral_setting: {
                    "integral_num": 0, //积分数量
                    "period": 1, //周期
                    "period_unit": "month", //单位
                    "expire": 30 //有效天数
                },
                isPermanent: 1, //默认永久
                // 红包券赠送数据
                order_paid: {
                    is_score:0,
                    is_score_card:0,
                    is_integral_card:0,
                    is_member_upgrade:0,
                },
                // 红包券赠送数据
                order_sales: {
                    is_score:0,
                    is_score_card:0,
                    is_integral_card:0,
                    is_member_upgrade:0,
                },
                info: {
                    id: "1",
                    mall_id: "5",
                    goods_id: "45",
                    enable_buy_reward: "0", //会员购物奖励
                    enable_repurchase: "0", //复购设置开关
                    is_percent: "0",
                    // 这里是是否开启积分券赠送
                    enable_score: "0", //是否开启赠送积分券
                    // 这里是是否开启红包券赠送
                    enable_integral: "0", //是否开启赠送红包券
                    max_deduct_integral: 0, //红包券抵扣金额
                    integral_fee_rate: 0,
                    is_order_paid:"0",
                    is_order_sales:"0",
                    
                },
                //购物券设置
                shoppingFormData: {
                    give_type: 1,
                    give_value: 0,
                    start_at: '',
                    enable_express: "0",
                },
                shoppingFormRule:{
                    give_value: [
                        {required: true, message: '赠送比例不能为空', trigger: 'change'},
                    ],
                    start_at:[
                        {required: true, message: '启动日期不能为空', trigger: 'change'},
                    ]
                },
                formProgressData:{
                    loading: false,
                }
            };
        },
        created() {
            if (getQuery('id')) {
                this.getDetail(getQuery('id'));
                this.goods_id = getQuery('id');
            }
            if (this.is_distribution == 1) {
                this.getPermissions();
            } else {
                this.is_show_distribution = 0
            }
            //this.getSvip();
            this.getLabels();
        },
        watch: {
            'ruleForm.detail'(newVal, oldVal) {
                this.cForm.detail = newVal
            },
            'attrGroups'(newVal, oldVal) {
                this.ruleForm.use_attr = newVal.length === 0 ? 0 : 1;
            },
            'ruleForm.is_level'(newVal, oldVal) {
                if (newVal === 0) {
                    this.ruleForm.is_level_alone = 0;
                }
            }
        },
        computed: {
            cForm() {
                let form = {};
                let ruleForm = JSON.parse(JSON.stringify(this.ruleForm));
                if (this.form) {
                    form = Object.assign(ruleForm, JSON.parse(JSON.stringify(this.form)));
                } else {
                    form = ruleForm;
                }
                if (getQuery('id')) {
                    form.id = getQuery('id')
                }
                return form;
            },
            cRule() {
                return this.rule ? Object.assign({}, this.rules, this.rule) : this.rules;
            },
            isConfineCount() {
                return this.ruleForm.confine_count === -1;
            },

        },
        directives: {
            loadmore: {
                // 指令的定义
                inserted(el, binding) {
                    // 获取element-ui定义好的scroll盒子
                    const SELECTDOWN_DOM = el.querySelector('.el-select-dropdown .el-select-dropdown__wrap')
                    SELECTDOWN_DOM.addEventListener('scroll', function() {
                        const CONDITION = this.scrollHeight - this.scrollTop <= this.clientHeight
                        if (CONDITION) {
                            binding.value()
                        }
                    })
                }
            }
        },
        methods: {
            //购物券设置保存
            shoppingSave(){
                let that = this;
                this.shoppingFormData.goods_id = getQuery('id');
                let do_request = function(){
                    that.formProgressData.loading = true;
                    request({
                        params: {
                            r: "mall/goods/shopping-save"
                        },
                        method: "post",
                        data: that.shoppingFormData
                    }).then(e => {
                        that.formProgressData.loading = false;
                        if (e.data.code == 0) {
                            that.$message.success(e.data.msg);
                            if (that.referrer.page > 1) {
                                url = Qs.stringify(that.referrer);
                            } else {
                                url = 'r=mall/goods/index';
                            }
                            console.log(url);
                            window.location.href = _baseUrl + '/index.php?' + url;
                        } else {
                            that.$message.error(e.data.msg);
                        }
                    }).catch(e => {
                        that.$message.error(e.data.msg);
                        that.formProgressData.loading = true;
                    });
                };
                this.$refs['shoppingFormData'].validate((valid) => {
                    if (valid) {
                        do_request();
                    }
                });
            },

            clearLiancUser(){
                this.forLiancDlgSelect.selection = {nickname: '', id: 0}
            },
            openLiancDlgSelect(){
                this.forLiancDlgSelect.visible = true;
            },
            closeLiancDlgSelect(){
                this.forLiancDlgSelect.visible = false;
            },
            confirmLiancDlgSelect(selection){
                this.forLiancDlgSelect.selection = selection;
            },
            open_show(id){
                this.ExpressFlag = true;
                this.CustomExpress = id;
            },
            open_option(){
                this.drawer = true;
                request({
                    params: {
                        r: 'mall/postage-rules/express-list',
                        'id': this.CustomExpress
                    },
                }).then(e => {
                    this.ExpressDataList = e.data.data.list;

                    e.data.data.list.forEach((el,i) => {
                        var val = el.name;
                        this.ExpressForm[i] = '';
                        this.ExpressName[i] = el.name;
                    })
                }).catch(e => {

                });
            },
            handleClose(done) {
                this.$confirm('还有未保存的工作哦确定关闭吗？')
                    .then(_ => {
                        done();
                    })
                    .catch(_ => {});
            },
            // 如果是效时有效、红包券
            isPermanentChange() {
                if (this.isPermanent) {
                    this.integral_setting.expire = 1;
                }
            },
            // 如果是效时有效 、积分券
            isPermanentScoreChange() {
                if (this.isPermanentScore) {
                    this.score_setting.expire = 1;
                }
            },
            isOrderSetting(e,o,type){
                let self = this;
                console.log(e,o,type); 
                if(self.info.is_order_paid == self.info.is_order_sales){
                    if(type=='is_score'){
                        console.log('is_score');
                        o!='paid'?self.order_paid.is_score = 0:'';
                        o!='sales'?self.order_sales.is_score = 0:'';
                    }
                    if(type=='is_score_card'){
                        console.log('is_score_card');
                        o!='paid'?self.order_paid.is_score_card = 0:'';
                        o!='sales'?self.order_sales.is_score_card = 0:'';
                    }
                    if(type=='is_integral_card'){
                        console.log('is_integral_card');
                        o!='paid'?self.order_paid.is_integral_card = 0:'';
                        o!='sales'?self.order_sales.is_integral_card = 0:'';
                    }
                }
            },
            loadmore() {
                if (this.page <= this.priceName_page_count) {
                    this.getGoodsNameDiy();
                }
            },
            deletePrice(index) { //删除自定义价格名
                request({
                    params: {
                        r: 'mall/goods-price-display/destroy',
                        id: this.pic_options[index].value
                    },
                }).then(e => {
                    if (e.data.code == 0) {
                        var obj = this.pic_options[index];
                        console.log(obj, 'this.pic_options');
                        if (obj.value == this.pic_value) {
                            this.pic_value = '';
                        }
                        this.pic_options.splice(index, 1);
                        this.$message({
                            type: 'success',
                            message: '删除成功！'
                        });
                    } else {
                        console.log(e.data.msg);
                    }
                })
            },
            getGoodsNameDiy() { //获取商品名列表
                request({
                    params: {
                        r: 'mall/goods-price-display/list',
                        limit: 10000
                    },
                }).then(e => {
                    this.priceName_page_count = e.data.data.pagination.page_count;
                    e.data.data.list.forEach(item => {
                        this.pic_options.push({
                            value: item.id,
                            label: item.name
                        })
                    });

                    this.pic_options.forEach(item => {
                        if (this.detail_data.price_display.length > 0 && item.value == this.detail_data.price_display[0].display_id) {
                            this.pic_value = this.detail_data.price_display[0].display_id.toString();
                        }
                    })
                    console.log(this.pic_value, 'this.pic_value');
                })
            },
            addPriceName() { //新增自定义商品名
                this.$prompt('请输入自定义商品售价名', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    inputPattern: /^.{1,4}$/,
                    inputErrorMessage: '不能为空或大于四个字'
                }).then(({
                    value
                }) => {
                    this.is_loading = true;
                    request({
                        params: {
                            r: 'mall/goods-price-display/store',
                        },
                        method: 'post',
                        data: {
                            name: value
                        }
                    }).then(e => {
                        this.is_loading = false;
                        if (e.data.code == 0) {
                            this.pic_options.push({
                                value: e.data.data.id.toString(),
                                label: e.data.data.name
                            })
                            if(this.pic_options.length == 1){
                                this.pic_value = e.data.data.id.toString();
                                console.log(this.pic_value,'this.pic_valueqqqqq');
                                console.log(this.pic_options,'this.pic_options');
                            }

                            this.$message({
                                type: 'success',
                                message: '添加成功！'
                            });
                        }
                    })
                })
            },
            loadMore() {
                console.log(1212313);
            },
            getLabels() {
                request({
                    params: {
                        r: 'mall/goods/label',
                    },
                }).then(e => {
                    if (e.data.code === 0) {
                        this.label_list = e.data.data.list;
                    } else {

                    }
                })
            },
            showPreview() {
                this.$refs.preview.previewGoods();
                this.$emit('handle-preview', this.ruleForm);
            },
            selectCat(cats) {
                this.cats = cats;
                let arr = [];
                cats.map(v => {
                    arr.push(v.value);
                })
                this.ruleForm.cats = arr;
                this.$refs.ruleForm.validateField('cats');
            },
            selectMchCat(cats) {
                this.mchCats = cats;
                let arr = [];
                cats.map(v => {
                    arr.push(v.value);
                })
                this.ruleForm.mchCats = arr;
                this.$refs.ruleForm.validateField('mchCats');
            },
            getSvip() {
                request({
                    params: {
                        r: 'mall/member-level/vip-card-permission',
                        plugin: this.sign
                    },
                }).then(e => {
                    if (e.data.code === 0) {
                        this.is_svip = true;
                    } else {
                        this.is_svip = false;
                    }
                })
            },
            delPic(index) {
                this.ruleForm.pic_url.splice(index, 1)
            },
            catDialogCancel() {
                let that = this;
                that.mchDialogVisible = false;
                that.dialogVisible = false;
                that.ruleForm.cats = [];
                that.ruleForm.mchCats = [];
                if (that.cats.length > 0) {
                    that.cats.forEach(function(row) {
                        that.ruleForm.cats.push(row.value.toString());
                    })
                }
                if (that.mchCats.length > 0) {
                    that.mchCats.forEach(function(row) {
                        that.ruleForm.mchCats.push(row.value.toString());
                    })
                }
            },
            getPermissions() {
                let self = this;
                request({
                    params: {
                        r: 'mall/index/mall-permissions'
                    },
                    method: 'get',
                }).then(e => {
                    if (e.data.code === 0) {
                        self.is_show_distribution = 0;
                        e.data.data.permissions.forEach(function(item) {
                            if (item === 'distribution') {
                                self.is_show_distribution = 1;
                            }
                        })
                    } else {
                        self.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    console.log(e);
                });
            },
            store(formName) {
                let self = this;
                let url = null;
                try {
                    self.cForm.attr.map(item => {
                        if (item.price < 0 || item.price === '') {
                            throw new Error('规格价格不能为空');
                        }
                        if (item.stock < 0 || item.stock === '') {
                            throw new Error('库存不能为空');
                        }
                    })
                } catch (error) {
                    self.$message.error(error.message);
                    return;
                }
                self.$refs[formName].validate((valid) => {
                    if (valid) {
                        self.btnLoading = true;
                        if (self.is_svip) {
                            self.cForm.is_vip_card_goods = self.is_vip_card_goods
                        } else {
                            delete self.cForm['is_vip_card_goods']
                        }
                        let postData = JSON.parse(JSON.stringify(self.cForm));
                        postData['lianc_user_id'] = self.forLiancDlgSelect.selection.id;
                        // 这里是追加红包券赠送的字段
                        // max_deduct_integral 最大抵扣红包券
                        // enable_integral 是否启用积分赠送
                        // integral_setting 红包券赠送设置 
                        // 0.2 是否开启积分赠送
                        self.isPermanentScore == 0 ? self.score_setting.expire = -1 : ''; // 是否开启永久有效
                        postData['enable_score'] = self.info.enable_score;
                        self.info.enable_score == 1 ? postData['score_setting'] = self.score_setting : '';
                        // 0.3 是否开启购物赠送
                        self.isPermanent == 0 ? self.integral_setting.expire = -1 : ''; // 是否开启永久有效
                        postData['enable_integral'] = self.info.enable_integral;
                        self.info.enable_integral == 1 ? postData['integral_setting'] = self.integral_setting : '';
                        // 0.4 红包券优惠
                        postData['max_deduct_integral'] = self.info.max_deduct_integral;
                        postData['integral_fee_rate'] = self.info.integral_fee_rate;

                        // 0.5 是否开启订单支付后
                        postData['is_order_paid'] = self.info.is_order_paid;
                        self.info.is_order_paid == 1 ? postData['order_paid'] = self.order_paid : '';
                        // 0.6 是否开启订单完结后
                        postData['is_order_sales'] = self.info.is_order_sales;
                        self.info.is_order_sales == 1 ? postData['order_sales'] = self.order_sales : '';

                        var priceName_obj = [{
                            "key": "price",
                            "display_id": this.pic_value ? this.pic_value : 0,
                        }];
                        postData.price_display = priceName_obj;
                        // console.log(postData);return;
                        request({
                            params: {
                                r: this.url
                            },
                            method: 'post',
                            data: {
                                form: JSON.stringify(postData),
                                attrGroups: JSON.stringify(self.attrGroups),
                                expressName:JSON.stringify(this.ExpressName),
                            }
                        }).then(e => {
                            self.btnLoading = false;
                            if (e.data.code === 0) {
                                //保存成功
                                self.$message.success(e.data.msg);
                                if (this.referrer.page > 1) {
                                    url = Qs.stringify(this.referrer);
                                } else {
                                    url = 'r=mall/goods/index';
                                }
                                console.log(url);
                                window.location.href = _baseUrl + '/index.php?' + url;
                            } else {
                                self.$message.error(e.data.msg);
                            }
                        }).catch(e => {
                            console.log(e);
                        });
                    } else {
                        console.log('error submit!!');
                        self.$message.error('请填写必填参数');
                        return false;
                    }
                });
            },
            getDetail(id, url = '') {
                let self = this;
                self.cardLoading = true;
                request({
                    params: {
                        r: url ? url : this.get_goods_url,
                        id: id,
                    },
                    method: 'get',
                }).then(e => {
                    self.cardLoading = false;
                    if (e.data.code == 0) {
                        let detail = e.data.data.detail;
                        this.detail_data = e.data.data.detail;
                        this.shoppingFormData = e.data.data.shopping_voucher_setting;

                        // 初始化自定义商品名
                        this.getGoodsNameDiy();

                        //联创合伙人
                        self.forLiancDlgSelect.selection = Object.assign(
                            self.forLiancDlgSelect.selection,
                            detail['lianc_user_info']
                        );
                        detail['lianc_commission_type'] = detail['lianc_commission_type'] + "";

                        if (detail['use_attr'] === 0) {
                            detail['attr_groups'] = [];
                        }
                        if (detail.is_vip_card_goods) {
                            self.is_vip_card_goods = detail.is_vip_card_goods
                        }
                        if (this.form && this.form.extra) {
                            for (let i in this.form.extra) {
                                if (detail.use_attr == 1) {
                                    for (let j in detail.attr) {
                                        if (!detail.attr[j][i]) {
                                            detail.attr[j][i] = 0;
                                        }
                                    }
                                }
                                Vue.set(self.ruleForm, i, 0);
                            }
                        }
                        self.cats = detail.cats;
                        if (detail.cats) {
                            let cats = [];
                            for (let i in detail.cats) {
                                cats.push(detail.cats[i].value.toString());
                            }
                            detail.cats = cats;
                        }

                        self.mchCats = detail.mchCats;
                        if (detail.mchCats) {
                            let mchCats = [];
                            for (let i in detail.mchCats) {
                                mchCats.push(detail.mchCats[i].value.toString());
                            }
                            detail.mchCats = mchCats;
                        }

                        self.ruleForm = Object.assign(self.ruleForm, detail);
                        self.attrGroups = e.data.data.detail.attr_groups;
                        self.goods_warehouse = e.data.data.detail.goods_warehouse;

                        self.defaultServiceChecked = !!parseInt(self.ruleForm.is_default_services);

                        //全额积分抵扣
                        this.checkFullForeheadScore();
                        self.$emit('goods-success', self.ruleForm);

                        // 红包券赠送&抵扣
                        let infoObj = e.data.data.detail; //null
                        // 0.2 判断是否开启积分券赠送
                        self.info.enable_score = infoObj.enable_score * 1;
                        if (infoObj.enable_score == 1) {
                            self.score_setting = infoObj.score_setting;
                            // 开关这里要注意数据类型的
                            self.score_setting.expire == -1 ? self.isPermanentScore = 0 : self.isPermanentScore = 1;
                            // 并在原对象中删除这个字段
                            // delete infoObj.score_setting;
                        }
                        // 0.3 判断是否开启红包券赠送
                        self.info.enable_integral = infoObj.enable_integral * 1;
                        if (infoObj.enable_integral == 1) {
                            self.integral_setting = infoObj.integral_setting;
                            // 开关这里要注意数据类型的
                            self.integral_setting.expire == -1 ? self.isPermanent = 0 : self.isPermanent = 1;
                            // 并在原对象中删除这个字段
                            // delete infoObj.integral_setting;
                        }
                        self.info.max_deduct_integral = infoObj.max_deduct_integral
                        self.info.integral_fee_rate = infoObj.integral_fee_rate;
                        console.log(infoObj);
                        // 0.4 判断是否开启订单支付后设置
                        self.info.is_order_paid = infoObj.is_order_paid * 1;
                        if (infoObj.is_order_paid == 1) {
                            self.order_paid = infoObj.order_paid;
                        }
                        // 0.5 判断是否开启订单完结后设置
                        self.info.is_order_sales = infoObj.is_order_sales * 1;
                        if (infoObj.is_order_sales == 1) {
                            self.order_sales = infoObj.order_sales;
                        }
                    } else {
                        self.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    self.cardLoading = false;
                    console.log(e);
                });
            },
            // 标签页
            handleClick(tab, event) {
                this.$emit('change-tabs', tab.name);
                if (tab.name == "third") {
                    this.getMembers();
                }
            },
            // 获取商品服务
            getServices() {
                let self = this;
                this.service.loading = true;
                request({
                    params: {
                        r: 'mall/service/options'
                    },
                    method: 'get',
                    data: {}
                }).then(e => {
                    this.service.loading = false;
                    if (e.data.code == 0) {
                        self.service.services = e.data.data.list;
                    } else {
                        self.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    console.log(e);
                });
            },
            // 设置默认服务
            defaultService() {
                if (this.defaultServiceChecked) {
                    this.ruleForm.is_default_services = 1;
                    this.ruleForm.services = [];
                } else {
                    this.ruleForm.is_default_services = 0;
                }
            },
            // 获取会员列表
            getMembers() {
                let self = this;
                self.cardLoading = true;
                request({
                    params: {
                        r: 'mall/member-level/all-member'
                    },
                    method: 'get',
                    data: {}
                }).then(e => {
                    self.cardLoading = false;
                    if (e.data.code == 0) {
                        self.memberLevel = e.data.data.list;
                        let defaultMemberPrice = [];
                        // 以下数据用于默认规格情况下的 会员价设置
                        self.memberLevel.forEach(function(item, index) {
                            let obj = {};
                            obj['id'] = index;
                            obj['name'] = item.name;
                            obj['level'] = 'level' + parseInt(item.level);
                            defaultMemberPrice.push(obj);
                        });
                        self.defaultMemberPrice = defaultMemberPrice;
                    } else {
                        self.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    console.log(e);
                });
            },
            // 获取运费规则选项
            getFreight() {
                let self = this;
                this.freight.loading = true;
                request({
                    params: {
                        r: 'mall/postage-rules/all-list'
                    },
                    method: 'get',
                    data: {}
                }).then(e => {
                    this.freight.loading = false;
                    if (e.data.code == 0) {
                        self.freight.list = e.data.data.list;
                        // 添加商品时使用默认运费
                        self.freight.list.unshift({
                            id: 0,
                            name: '默认运费',
                            status: 1
                        })
                    } else {
                        self.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    console.log(e);
                });
            },

            // 规格组合
            makeAttrGroup(e) {
                let self = this;
                let array = [];
                self.attrGroups.forEach(function(attrGroupItem, attrGroupIndex) {
                    attrGroupItem.attr_list.forEach(function(attrListItem, attrListIndex) {
                        let object = {
                            attr_group_id: attrGroupItem.attr_group_id,
                            attr_group_name: attrGroupItem.attr_group_name,
                            attr_id: attrListItem.attr_id,
                            attr_name: attrListItem.attr_name,
                        };

                        if (!array[attrGroupIndex]) {
                            array[attrGroupIndex] = [];
                        }
                        array[attrGroupIndex].push(object)
                    });
                });

                // 2.属性排列组合
                const res = array.reduce((osResult, options) => {
                    return options.reduce((oResult, option) => {
                        if (!osResult.length) {
                            return oResult.concat(option)
                        } else {
                            return oResult.concat(osResult.map(o => [].concat(o, option)))
                        }
                    }, [])
                }, []);

                // 3.组合结果赋值
                for (let i in res) {
                    const options = Array.isArray(res[i]) ? res[i] : [res[i]];
                    const row = {
                        attr_list: options,
                        stock: 0,
                        price: 0,
                        no: '',
                        weight: 0,
                        pic_url: '',
                        distributionLevelList: [],
                    };
                    let extra = {};
                    if (this.form && this.form.extra) {
                        extra = JSON.parse(JSON.stringify(this.form.extra));
                        for (let i in extra) {
                            row[i] = 0;
                        }
                    }
                    // 动态绑定多规格会员价
                    let obj = {};
                    self.memberLevel.forEach(function(memberLevelItem, memberLevelIndex) {
                        let key = 'level' + memberLevelItem.level;
                        obj[key] = 0;
                    });
                    row['member_price'] = obj;
                    // 3-1.已设置数据的优先使用原数据
                    if (self.ruleForm.attr.length) {
                        for (let j in self.ruleForm.attr) {
                            const oldOptions = [];
                            for (let k in self.ruleForm.attr[j].attr_list) {
                                oldOptions.push(self.ruleForm.attr[j].attr_list[k].attr_name)
                            }
                            const newOptions = [];
                            for (let k in options) {
                                newOptions.push(options[k].attr_name)
                            }
                            if (oldOptions.toString() === newOptions.toString()) {
                                row['price'] = self.ruleForm.attr[j].price;
                                row['stock'] = self.ruleForm.attr[j].stock;
                                row['no'] = self.ruleForm.attr[j].no;
                                row['weight'] = self.ruleForm.attr[j].weight;
                                row['pic_url'] = self.ruleForm.attr[j].pic_url;
                                break
                            }
                        }
                    }
                    res[i] = row;
                }
                self.ruleForm.attr = res;
            },

            // 批量设置
            batchAttr(key) {
                let self = this;
                if (self.batch[key] && self.batch[key] >= 0 || key === 'no') {
                    self.ruleForm.attr.forEach(function(item, index) {
                        // 批量设置会员价
                        // 判断字符串是否出现过，并返回位置
                        if (key.indexOf('level') !== -1) {
                            item['member_price'][key] = self.batch[key];
                        } else {
                            item[key] = self.batch[key];
                        }
                    });
                }
            },
            destroyCat(value, index) {
                let self = this;
                self.ruleForm.cats.splice(self.ruleForm.cats.indexOf(value), 1)
                self.cats.splice(index, 1)
            },
            destroyCat_2(value, index) {
                let self = this;
                self.ruleForm.mchCats.splice(self.ruleForm.mchCats.indexOf(value), 1)
                self.mchCats.splice(index, 1)
            },
            // 商品视频
            videoUrl(e) {
                if (e.length) {
                    this.ruleForm.video_url = e[0].url;
                }
            },
            // 商品轮播图
            picUrl(e) {
                if (e.length) {
                    let self = this;
                    e.forEach(function(item, index) {
                        if (self.ruleForm.pic_url.length >= 9) {
                            return;
                        }
                        self.ruleForm.pic_url.push({
                            id: item.id,
                            pic_url: item.url
                        });
                    });
                }
            },
            // 是否开启规格
            checkedAttr(e) {
                if (e == 1) {
                    this.attrGroups = [];
                    this.ruleForm.goods_num = this.ruleForm.goods_num ? this.ruleForm.goods_num : 0;
                }
            },
            itemChecked(type) {
                this.ruleForm.confine_count = type ? -1 : 0;
            },
            itemOrderChecked(type) {
                this.ruleForm.confine_order_count = type ? -1 : 0;
            },
            selectGoodsWarehouse(goods_warehouse) {
                this.ruleForm.select_attr_groups = [];
                this.getDetail(goods_warehouse.id, 'mall/goods/edit')
            },
            copyGoods() {
                this.copyLoading = true;
                request({
                    params: {
                        r: 'mall/goods/collect',
                        url: this.copyUrl
                    },
                    method: 'get'
                }).then(e => {
                    this.copyLoading = false;
                    if (e.data.code === 0) {
                        let detail = e.data.data.detail;
                        if (this.form && this.form.extra) {
                            for (let i in this.form.extra) {
                                if (detail.use_attr == 1) {
                                    for (let j in detail.attr) {
                                        if (!detail.attr[j][i]) {
                                            detail.attr[j][i] = 0;
                                        }
                                    }
                                }
                                Vue.set(this.ruleForm, i, 0);
                            }
                        }

                        this.ruleForm = Object.assign(this.ruleForm, detail);
                        this.attrGroups = e.data.data.detail.attr_groups;
                        this.$message.success(e.data.msg);
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    this.copyLoading = false;
                    console.log(e);
                });
            },
            updatePicUrl(e, params) {
                this.ruleForm.pic_url[params.currentIndex].id = e[0].id;
                this.ruleForm.pic_url[params.currentIndex].pic_url = e[0].url;
            },
            getOptionName(name) {
                let newName = name;
                let num = 12;
                if (newName.length > num) {
                    newName = newName.substring(0, num) + '...';
                }
                return newName;
            },

            /*---商品服务----*/
            serviceOpen() {
                this.service.dialog = true;
                this.getServices();
            },
            serviceCancel() {
                this.service.dialog = false;
                this.service.list = [];
            },
            serviceConfirm() {
                let self = this;
                let newServices = JSON.parse(JSON.stringify(this.service.list));
                let addServices = [];
                newServices.forEach(function(item, index) {
                    let sign = true;
                    self.ruleForm.services.forEach(function(item2, index2) {
                        if (item.id == item2.id) {
                            sign = false;
                        }
                    })
                    if (sign) {
                        addServices.push(item)
                    }
                });
                this.ruleForm.services = this.ruleForm.services.concat(addServices);
                this.serviceCancel();
            },
            serviceDelete(index) {
                this.ruleForm.services.splice(index, 1);
            },
            /*---运费----*/
            freightOpen() {
                this.freight.dialog = true;
                this.getFreight();
            },
            freightCancel() {
                this.freight.checked = {};
                this.freight.dialog = false;
            },
            freightConfirm() {
                this.ruleForm.freight = JSON.parse(JSON.stringify(this.freight.checked));
                this.ruleForm.freight_id = this.ruleForm.freight.id;
                this.ExpressFlag = false;
                this.ExpressForm.forEach((el,i) => {
                    var value = this.ExpressName[i] + ',' + (el ? el : 0);
                    this.ExpressName[i] = value;
                })
                console.log(this.ExpressName);
                this.freightCancel();
            },
            freightDelete() {
                this.ruleForm.freight = null;
                this.ruleForm.freight_id = 0;
            },
            cardDelete(index) {
                this.ruleForm.cards.splice(index, 1);
            },

            // 上架状态开关，弹框文字提示
            statusChange(res) {
                if (res && this.status_change_text) {
                    this.$alert(this.status_change_text, '提示', {
                        confirmButtonText: '确定',
                        callback: action => {}
                    });
                }
            },
            selectForm(data) {
                this.ruleForm.form = data;
                this.ruleForm.form_id = data ? data.id : -1;
            },
            fullForeheadScore() {
                this.ruleForm.forehead_score = 99999999;
                this.ruleForm.forehead_score_type = 1;
                this.ruleForm.accumulative = this.ruleForm.full_forehead_score;
                if (this.ruleForm.full_forehead_score == 0) {
                    this.ruleForm.forehead_score = 0;
                }
            },
            //检查是否积分全额抵扣
            checkFullForeheadScore() {
                if (this.ruleForm.forehead_score == 99999999 && this.ruleForm.accumulative == 1) {
                    this.ruleForm.full_forehead_score = 1;
                }
            },
            change(e) {
                this.$forceUpdate();
            }
        }
    });
</script>
<style>
    .el-drawer__body {
        overflow: auto;
    }
</style>

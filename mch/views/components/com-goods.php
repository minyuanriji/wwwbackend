<?php
echo $this->render('../components/com-rich-text');
echo $this->render('../components/goods/com-dialog-select');
echo $this->render('../components/goods/com-attr');
echo $this->render('../components/goods/com-attr-select');
echo $this->render('../components/goods/com-add-cat');
echo $this->render('../components/goods/com-select-goods');
echo $this->render('../components/goods/com-area-limit');
echo $this->render('../components/goods/com-preview');
echo $this->render('../components/goods/com-attr-group');
echo $this->render('../components/goods/com-goods-form');
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
</style>
<template id="com-goods">
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;" class="com-goods" v-loading="cardLoading">
        <div class='form-body'>
            <el-form :model="cForm" :rules="cRule" ref="ruleForm" label-width="180px" size="small" class="demo-ruleForm">
                <el-tabs v-model="activeName">
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
                                        <com-add-cat ref="cats" :new-cats="ruleForm.cats" @select="selectCat"></com-add-cat>
                                    </el-form-item>
                                    <!-- mch -->
                                    <el-form-item label="多商户分类" prop="mchCats">
                                        <el-tag style="margin-right: 5px" v-for="(item,index) in mchCats" :key="item.value" v-model="ruleForm.mchCats" type="warning" closable disable-transitions @close="destroyCat_2(item.value,index)">{{item.label}}
                                        </el-tag>
                                        <el-button type="primary" @click="$refs.mchCats.openDialog()">选择分类</el-button>
                                        <el-button type="text" @click="$navigate({r:'mch/cat/edit'}, true)">添加分类
                                        </el-button>
                                        <com-add-cat ref="mchCats" :new-cats="ruleForm.mchCats" :mch_id="mch_id" @select="selectMchCat"></com-add-cat>
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
                                    <!-- 淘宝采集 -->
                                    <el-form-item label="淘宝采集" hidden>
                                        <el-input v-model="copyUrl">
                                            <template slot="append">
                                                <el-button @click="copyGoods" :loading="copyLoading">获取
                                                </el-button>
                                            </template>
                                        </el-input>
                                    </el-form-item>

                                    <!-- 商城商品编码 -->
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

                                    <el-form-item label="商品标签">
                                        <el-select v-model="ruleForm.labels" multiple placeholder="请选择标签">
                                            <el-option v-for="item in label_list" :key="item.title" :label="item.title" :value="item.title">
                                            </el-option>
                                        </el-select>
                                    </el-form-item>

                                    <!-- 商品轮播图(多张) -->
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

                                    <!-- 商品视频 -->
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

                                    <el-form-item label="上架状态" prop="status">
                                        <el-switch @change="statusChange" :active-value="1" :inactive-value="0" v-model="ruleForm.status">
                                        </el-switch>
                                    </el-form-item>

                                    <el-form-item label="是否到店消费" prop="status">
                                        <el-switch :active-value="1" :inactive-value="0" v-model="ruleForm.is_on_site_consumption">
                                        </el-switch>
                                    </el-form-item>
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
                                    <el-form-item label="商品总库存" prop="goods_num">
                                        <el-input type="number" min="0" oninput="this.value = this.value.replace(/[^0-9]/g, '');" :disabled="ruleForm.use_attr == 1 ? true : false" v-model="ruleForm.goods_num">
                                        </el-input>
                                    </el-form-item>
                                    <el-form-item label="默认规格名" prop="attr_default_name">
                                        <el-input :disabled="ruleForm.use_attr == 1" v-model="ruleForm.attr_default_name">
                                        </el-input>
                                    </el-form-item>
                                    <!-- 商品规格 -->
                                    <el-form-item prop="goods_num">
                                        <label slot="label">
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
                                    <!-- qwe 售价 -->
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
                                    <!-- 原价 -->
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

                                    <el-form-item label="是否显示销量" prop="is_show_sales">
                                        <el-switch v-model="ruleForm.is_show_sales" :active-value="1" :inactive-value="0">
                                        </el-switch>
                                    </el-form-item>

                                    <el-form-item label="是否启用虚拟销量" prop="use_virtual_sales">
                                        <el-switch v-model="ruleForm.use_virtual_sales" :active-value="1" :inactive-value="0">
                                        </el-switch>
                                    </el-form-item>

                                    <!-- 已出售量 -->
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
                                    <el-form-item label="商品货号">
                                        <el-input :disabled="ruleForm.use_attr == 1 ? true : false" v-model="ruleForm.goods_no">
                                        </el-input>
                                    </el-form-item>
                                    <el-form-item label="商品重量">
                                        <el-input oninput="this.value = this.value.replace(/[^0-9]/g, '');" :disabled="ruleForm.use_attr == 1 ? true : false" v-model="ruleForm.goods_weight">
                                            <template slot="append">克</template>
                                        </el-input>
                                    </el-form-item>
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

                                    <el-form-item v-show="false" prop="freight_id">
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
                                                    <el-radio style="padding: 10px;" v-for="item in freight.list" :label="item" :key="item.id">{{item.name}}
                                                    </el-radio>
                                                </el-radio-group>
                                            </el-card>
                                            <div slot="footer" class="dialog-footer">
                                                <el-button @click="freightCancel">取 消</el-button>
                                                <el-button type="primary" @click="freightConfirm">确 定</el-button>
                                            </div>
                                        </el-dialog>
                                    </el-form-item>
                                    <el-form-item prop="freight_id" v-if="is_form == 1">
                                        <template slot='label'>
                                            <span>自定义表单</span>
                                            <el-tooltip effect="dark" content="选择第一项（默认表单）将会根据表单列表的（默认表单）变化而变化" placement="top">
                                                <i class="el-icon-info"></i>
                                            </el-tooltip>
                                        </template>
                                        <com-goods-form v-model="ruleForm.form" @selected="selectForm" title="选择表单" url="mch/order-form/all-list"></com-goods-form>
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

                                    <el-form-item v-show="false" label="" prop="pieces">
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

                                    <el-form-item v-show="false" prop="forehead">
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
                    <slot name="tab_pane"></slot>
                </el-tabs>
            </el-form>
            <div class="bottom-div" flex="cross:center" v-if="is_save_btn == 1">
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
            // 商品详情
            is_detail: {
                type: Number,
                default: 0
            },

            //todo 仅显示售价（抽奖） 秒杀3显示
            is_price: {
                type: Number,
                default: 0
            },

            // 请求数据地址
            url: {
                type: String,
                default: 'mch/goods/edit'
            },
            // 请求数据地址
            get_goods_url: {
                type: String,
                default: 'mch/goods/edit'
            },
            // 保存之后返回地址
            referrer: {
                default: 'mch/goods/index'
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
                services: [],
                pic_url: [],
                use_attr: 0,
                goods_num: 0,
                status: 0,
                is_on_site_consumption:0,//到店消费类商品
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

                pieces: 0,
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
                is_default_services: 1,
                goods_no: '',
                goods_weight: '',
                select_attr_groups: [], // 已选择的规格
                goodsWarehouse_attrGroups: [], // 商品库商品所有的规格
                form: null,
                is_show_sales: 0,
                use_virtual_sales: 0,
                form_id: 0,
                attr_default_name: '',
                is_area_limit: 0,
                area_limit: [{
                    list: []
                }],
                full_relief_price: 0,
                fulfil_price: 0,
                cannotrefund:["1","2","3"]
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
                rules: rules,
                options: [], // 商品分类列表
                mchOptions: [], //多商户商品编辑时使用
                newOptions: [],
                cats: [], //用于前端已选的分类展示
                mchCats: [], //用于前端已选的分类展示 多商户
                attrGroups: [], //规格组
                attrGroupName: '',
                attrName: [],
                // 批量设置
                batch: {},
                dialogVisible: false, //分类选择弹框
                mchDialogVisible: false,
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
                video_type: 1,
                cardDialogVisible: false
            };
        },
        created() {
            if (getQuery('id')) {
                this.getDetail(getQuery('id'));
                this.goods_id = getQuery('id');
            }
            this.getLabels();
        },
        watch: {
            'ruleForm.detail'(newVal, oldVal) {
                this.cForm.detail = newVal
            },
            'attrGroups'(newVal, oldVal) {
                this.ruleForm.use_attr = newVal.length === 0 ? 0 : 1;
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

            loadmore() {
                if (this.page <= this.priceName_page_count) {
                    this.getGoodsNameDiy();
                }
            },
            deletePrice(index) { //删除自定义价格名
                request({
                    params: {
                        r: 'mch/goods-price-display/destroy',
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
                        r: 'mch/goods-price-display/list',
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
                            r: 'mch/goods-price-display/store',
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
                        r: 'mch/goods/label',
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
            store(formName) {
                let self = this;
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

                        let postData = JSON.parse(JSON.stringify(self.cForm));

                        var priceName_obj = [{
                            "key": "price",
                            "display_id": this.pic_value ? this.pic_value : 0,
                        }];
                        postData.price_display = priceName_obj;
                        //console.log(postData);return false;
                        request({
                            params: {
                                r: this.url
                            },
                            method: 'post',
                            data: {
                                form: JSON.stringify(postData),
                                attrGroups: JSON.stringify(self.attrGroups),
                            }
                        }).then(e => {
                            self.btnLoading = false;
                            if (e.data.code === 0) {
                                //保存成功
                                self.$message.success(e.data.msg);
                                navigateTo({
                                    r: this.referrer,
                                })
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
                    if (e.data.code == 0) {
                        let detail = e.data.data.detail;
                        this.detail_data = e.data.data.detail;
                        // 初始化自定义商品名
                        this.getGoodsNameDiy();

                        if (detail['use_attr'] === 0) {
                            detail['attr_groups'] = [];
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

                    } else {
                        self.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    self.cardLoading = false;
                    console.log(e);
                });
            },
            // 获取商品服务
            getServices() {
                let self = this;
                this.service.loading = true;
                request({
                    params: {
                        r: 'mch/service/options'
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
            // 获取运费规则选项
            getFreight() {
                let self = this;
                this.freight.loading = true;
                request({
                    params: {
                        r: 'mch/postage-rules/all-list'
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
                        item[key] = self.batch[key];
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
                this.getDetail(goods_warehouse.id, 'mch/goods/edit')
            },
            copyGoods() {
                this.copyLoading = true;
                request({
                    params: {
                        r: 'mch/goods/collect',
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
            change(e) {
                this.$forceUpdate();
            }
        }
    });
</script>
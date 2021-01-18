<?php
/**
 * @link:http://www.gdqijianshi.com/
 * copyright: Copyright (c) 2020 广东七件事集团
 * author: zal
 */
?>

<style>
    .mobile-box {
        width: 400px;
        height: 740px;
        padding: 35px 11px;
        background-color: #fff;
        border-radius: 30px;
        margin-right: 20px;
    }

    .bg-box {
        position: relative;
        border: 1px solid #e2e3e3;
        width: 750px;
        height: 1334px;
        zoom: 0.5;
    }

    .bg-pic {
        width: 100%;
        height: 100%;
        background-size: 100% 100%;
        background-position: center;
    }

    .title {
        padding: 15px 0;
        background-color: #f7f7f7;
        margin-bottom: 10px;
    }

    .component-item {
        width: 100px;
        height: 100px;
        cursor: pointer;
        position: relative;
        padding: 10px 0;
        border: 1px solid #e2e2e2;
        margin-right: 15px;
        margin-top: 15px;
        border-radius: 5px;
    }

    .component-item.active {
        border: 1px solid #7BBDFC;
    }

    .component-item-remove {
        position: absolute;
        top: 0;
        right: 0;
        cursor: pointer;
        width: 28px;
        height: 28px;
    }

    .component-attributes-box {
        color: #ff4544;
    }

    .box-card {
        margin-top: 35px;
    }

    .el-tabs__header {
        padding: 0 20px;
        height: 56px;
        line-height: 56px;
        background-color: #fff;
    }

    .form-body {
        padding: 20px 35% 20px 20px;
        background-color: #fff;
        margin-bottom: 20px;
        width: 100%;
        height: 100%;
        position: relative;
        min-width: 640px;
    }

    .button-item {
        padding: 9px 25px;
        position: absolute !important;
        bottom: -52px;
        left: 0;
    }

    .el-card, .el-tabs__content {
        overflow: visible;
    }
</style>

<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" shadow="never" body-style="background-color: #f3f3f3;padding: 0 0;"
             v-loading="cardLoading">
        <el-form v-if="ruleForm" :model="ruleForm" :rules="rules" size="small" ref="ruleForm" label-width="20%">
            <el-tabs v-model="activeName" @tab-click="handleClick">
                <el-tab-pane label="名片海报" name="first">
                    <div style="display: flex;">
                        <div class="mobile-box" flex="dir:top">
                            <div class="bg-box">
                                <div class="bg-pic"
                                     :style="{'background-image':'url('+ruleForm.business_card.bg_pic.url+')'}">
                                </div>
                                <com-image v-if="ruleForm.business_card.head.is_show == 1"
                                           mode="aspectFill"
                                           radius="50%"
                                           :style="{
                                                    position: 'absolute',
                                                    top: ruleForm.business_card.head.top + 'px',
                                                    left: ruleForm.business_card.head.left + 'px'}"
                                           :width='ruleForm.business_card.head.size + ""'
                                           :height='ruleForm.business_card.head.size + ""'
                                           src="statics/img/mall/poster/default_head.png">
                                </com-image>
                                <com-image v-if="ruleForm.business_card.qr_code.is_show == 1"
                                           mode="aspectFill"
                                           :radius="ruleForm.business_card.qr_code.type == 1 ? '50%' : '0%'"
                                           :style="{
                                                    position: 'absolute',
                                                    top: ruleForm.business_card.qr_code.top + 'px',
                                                    left: ruleForm.business_card.qr_code.left + 'px'}"
                                           :width='ruleForm.business_card.qr_code.size + ""'
                                           :height='ruleForm.business_card.qr_code.size + ""'
                                           src="statics/img/mall/poster/default_qr_code.png">
                                </com-image>
                                <span v-if="ruleForm.business_card.name.is_show == 1"
                                      :style="{
                                                    position: 'absolute',
                                                    top: ruleForm.business_card.name.top + 'px',
                                                    left: ruleForm.business_card.name.left + 'px',
                                                    fontSize: ruleForm.business_card.name.font * 2 + 'px',
                                                    color: ruleForm.business_card.name.color}">
                                          用户昵称
                                    </span>
                            </div>
                        </div>
                        <div class="form-body" v-if="ruleForm.business_card.bg_pic.url" flex="dir:top">
                            <com-attachment style="margin-bottom: 15px" :multiple="false" :max="1"
                                            v-model="ruleForm.business_card.bg_pic.url">
                                <el-tooltip class="item"
                                            effect="dark"
                                            content="建议尺寸:750 * 1334"
                                            placement="top">
                                    <el-button size="mini">
                                        {{ruleForm.business_card.bg_pic.url ? '更换背景图' : '添加背景图'}}
                                    </el-button>
                                </el-tooltip>
                            </com-attachment>
                            <div flex="wrap:wrap">
                                <div v-for="(item,index) in shareComponent"
                                     @click="componentItemClick(index)"
                                     class="component-item"
                                     :class="shareComponentKey == item.key ? 'active' : ''"
                                     flex="dir:top cross:center main:center">
                                    <img :src="item.icon_url">
                                    <div>{{item.title}}</div>
                                    <img v-if="test(index)"
                                         @click.stop="componentItemRemove(index)"
                                         class="component-item-remove"
                                         src="statics/img/mall/poster/icon_delete.png">
                                </div>
                            </div>
                            <el-card shadow="never" class="box-card" style="width: 100%">
                                <div slot="header">
                                    <span v-if="shareComponentKey == 'head'">头像设置</span>
                                    <span v-if="shareComponentKey == 'name'">昵称设置</span>
                                    <span v-if="shareComponentKey == 'qr_code'">二维码设置</span>
                                </div>
                                <div>
                                    <template v-if="shareComponentKey == 'head'">
                                        <el-form-item label="大小">
                                            <el-slider
                                                :min=40
                                                :max=300
                                                v-model="ruleForm.business_card.head.size"
                                                show-input>
                                            </el-slider>
                                        </el-form-item>
                                        <el-form-item label="上间距">
                                            <el-slider
                                                :min=0
                                                :max=1334-(ruleForm.business_card.head.size)
                                                v-model="ruleForm.business_card.head.top"
                                                show-input>
                                            </el-slider>
                                        </el-form-item>
                                        <el-form-item label="左间距">
                                            <el-slider
                                                :min=0
                                                :max=750-(ruleForm.business_card.head.size)
                                                v-model="ruleForm.business_card.head.left"
                                                show-input>
                                            </el-slider>
                                        </el-form-item>
                                    </template>
                                    <template v-else-if="shareComponentKey == 'name'">
                                        <el-form-item label="大小">
                                            <el-slider
                                                :min=12
                                                :max=40
                                                v-model="ruleForm.business_card.name.font"
                                                show-input>
                                            </el-slider>
                                        </el-form-item>
                                        <el-form-item label="上间距">
                                            <el-slider
                                                :min=0
                                                :max=1334-(ruleForm.business_card.name.font)
                                                v-model="ruleForm.business_card.name.top"
                                                show-input>
                                            </el-slider>
                                        </el-form-item>
                                        <el-form-item label="左间距">
                                            <el-slider
                                                :min=0
                                                :max=750-(ruleForm.business_card.name.font)
                                                v-model="ruleForm.business_card.name.left"
                                                show-input>
                                            </el-slider>
                                        </el-form-item>
                                        <el-form-item label="颜色">
                                            <el-color-picker
                                                style="margin-left: 20px;"
                                                color-format="rgb"
                                                v-model="ruleForm.business_card.name.color"
                                                :predefine="predefineColors">
                                            </el-color-picker>
                                        </el-form-item>
                                    </template>
                                    <template v-else-if="shareComponentKey == 'qr_code'">
                                        <el-form-item label="样式">
                                            <el-radio v-model="ruleForm.business_card.qr_code.type" :label="1">圆形</el-radio>
                                            <el-radio v-model="ruleForm.business_card.qr_code.type" :label="2">方形</el-radio>
                                        </el-form-item>
                                        <el-form-item label="大小">
                                            <el-slider
                                                :min=80
                                                :max=300
                                                v-model="ruleForm.business_card.qr_code.size"
                                                show-input>
                                            </el-slider>
                                        </el-form-item>
                                        <el-form-item label="上间距">
                                            <el-slider
                                                :min=0
                                                :max=1334-(ruleForm.business_card.qr_code.size)
                                                v-model="ruleForm.business_card.qr_code.top"
                                                show-input>
                                            </el-slider>
                                        </el-form-item>
                                        <el-form-item label="左间距">
                                            <el-slider
                                                :min=0
                                                :max=750-(ruleForm.business_card.qr_code.size)
                                                v-model="ruleForm.business_card.qr_code.left"
                                                show-input>
                                            </el-slider>
                                        </el-form-item>
                                    </template>
                                </div>
                            </el-card>
                            <el-button class="button-item" :loading="btnLoading" type="primary"
                                       @click="store('ruleForm')" size="small">保存
                            </el-button>
                        </div>
                    </div>
                </el-tab-pane>
            </el-tabs>
        </el-form>
    </el-card>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                ruleForm: null,
                shareComponent: [
                    {
                        key: 'head',
                        icon_url: 'statics/img/mall/poster/icon_head.png',
                        title: '头像',
                        is_active: true
                    },
                    {
                        key: 'name',
                        icon_url: 'statics/img/mall/poster/icon_name.png',
                        title: '昵称',
                        is_active: true
                    },
                    {
                        key: 'qr_code',
                        icon_url: 'statics/img/mall/poster/icon_qr_code.png',
                        title: '二维码',
                        is_active: true
                    },
                ],
                shareComponentKey: 'head',
                goodsComponent: [
                    {
                        key: 'head',
                        icon_url: 'statics/img/mall/poster/icon_head.png',
                        title: '头像',
                        is_active: true
                    },
                    {
                        key: 'nickname',
                        icon_url: 'statics/img/mall/poster/icon_name.png',
                        title: '昵称',
                        is_active: true
                    },
                    {
                        key: 'pic',
                        icon_url: 'statics/img/mall/poster/icon_pic.png',
                        title: '商品图片',
                        is_active: true
                    },
                    {
                        key: 'name',
                        icon_url: 'statics/img/mall/poster/icon_name.png',
                        title: '商品名称',
                        is_active: true
                    },
                    {
                        key: 'price',
                        icon_url: 'statics/img/mall/poster/icon_price.png',
                        title: '商品价格',
                        is_active: true
                    },
                    {
                        key: 'desc',
                        icon_url: 'statics/img/mall/poster/icon_desc.png',
                        title: '海报描述',
                        is_active: true
                    },
                    {
                        key: 'qr_code',
                        icon_url: 'statics/img/mall/poster/icon_qr_code.png',
                        title: '二维码',
                        is_active: true
                    },
                ],
                goodsComponentKey: 'pic',
                topicComponent: [
                    {
                        key: 'title',
                        icon_url: 'statics/img/mall/poster/icon_name.png',
                        title: '专题标题',
                        is_active: true
                    },
                    {
                        key: 'pic',
                        icon_url: 'statics/img/mall/poster/icon_pic.png',
                        title: '专题图片',
                        is_active: true
                    },
                    {
                        key: 'look',
                        icon_url: 'statics/img/mall/poster/icon_look.png',
                        title: '阅读量',
                        is_active: true
                    },
                    {
                        key: 'content',
                        icon_url: 'statics/img/mall/poster/icon_content.png',
                        title: '专题内容',
                        is_active: true
                    },
                    {
                        key: 'open_desc',
                        icon_url: 'statics/img/mall/poster/icon_point.png',
                        title: '文章提示',
                        is_active: true
                    },
                    {
                        key: 'line',
                        icon_url: 'statics/img/mall/poster/icon_line.png',
                        title: '分割线',
                        is_active: true
                    },
                    {
                        key: 'desc',
                        icon_url: 'statics/img/mall/poster/icon_desc.png',
                        title: '海报描述',
                        is_active: true
                    },
                    {
                        key: 'qr_code',
                        icon_url: 'statics/img/mall/poster/icon_qr_code.png',
                        title: '二维码',
                        is_active: true
                    },
                ],
                topicComponentKey: 'title',
                rules: {},
                predefineColors: [
                    '#000',
                    '#fff',
                    '#888',
                    '#ff4544'
                ],
                btnLoading: false,
                cardLoading: false,
                activeName: 'first',
            };
        },
        computed: {
            // 控制显示的内容

            test() {
                return function (index) {
                    var isShow = this.ruleForm.business_card[this.shareComponent[index].key].is_show;
                    return isShow == 1 ? true : false;
                }
            },
            test2() {
                return function (index) {
                    var isShow = this.ruleForm.goods[this.goodsComponent[index].key].is_show;
                    return isShow == 1 ? true : false;
                }
            },
            test3() {
                return function (index) {
                    var isShow = this.ruleForm.topic[this.topicComponent[index].key].is_show;
                    return isShow == 1 ? true : false;
                }
            },
        },
        methods: {
            store(formName) {
                this.$refs[formName].validate((valid) => {
                    let self = this;
                    if (valid) {
                        self.btnLoading = true;
                        request({
                            params: {
                                r: 'plugin/business_card/mall/setting/poster'
                            },
                            method: 'post',
                            data: {
                                form: self.ruleForm,
                            }
                        }).then(e => {
                            self.btnLoading = false;
                            if (e.data.code == 0) {
                                self.$message.success(e.data.msg);
                            } else {
                                self.$message.error(e.data.msg);
                            }
                        }).catch(e => {
                            self.$message.error(e.data.msg);
                            self.btnLoading = false;
                        });
                    } else {
                        console.log('error submit!!');
                        return false;
                    }
                });
            },
            getDetail() {
                let self = this;
                self.cardLoading = true;
                request({
                    params: {
                        r: 'plugin/business_card/mall/setting/poster',
                    },
                    method: 'get',
                }).then(e => {
                    self.cardLoading = false;
                    if (e.data.code == 0) {
                        self.ruleForm = e.data.data.detail;
                    } else {
                        self.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    console.log(e);
                });
            },
            transformData() {
            },
            handleClick(tab, event) {
                console.log(tab, event);
            },
            // 移除背景图片
            removeBgPic() {
                if (this.activeName == 'second') {
                    this.ruleForm.goods.bg_pic.url = '';
                }

                if (this.activeName == 'third') {
                    this.ruleForm.topic.bg_pic.url = '';
                }
            },
            // 添加组件
            componentItemClick(index) {
                if (this.activeName == 'first') {
                    this.shareComponent[index].is_active = true;
                    this.ruleForm.business_card[this.shareComponent[index].key].is_show = '1';
                    this.shareComponentKey = this.shareComponent[index].key;
                }
                if (this.activeName == 'second') {
                    this.goodsComponent[index].is_active = true;
                    this.ruleForm.goods[this.goodsComponent[index].key].is_show = '1';
                    this.goodsComponentKey = this.goodsComponent[index].key;
                }

                if (this.activeName == 'third') {
                    this.topicComponent[index].is_active = true;
                    this.ruleForm.topic[this.topicComponent[index].key].is_show = '1';
                    this.topicComponentKey = this.topicComponent[index].key;
                }
            },
            // 移除组件
            componentItemRemove(index) {
                if (this.activeName == 'first') {
                    this.shareComponent[index].is_active = false;
                    this.ruleForm.business_card[this.shareComponent[index].key].is_show = '0';
                    this.shareComponentKey = '';
                }
                if (this.activeName == 'second') {
                    this.goodsComponent[index].is_active = false;
                    this.ruleForm.goods[this.goodsComponent[index].key].is_show = '0';
                    this.goodsComponentKey = '';
                }

                if (this.activeName == 'third') {
                    this.topicComponent[index].is_active = false;
                    this.ruleForm.topic[this.topicComponent[index].key].is_show = '0';
                    this.topicComponentKey = '';
                }
            }
        },
        mounted: function () {
            this.getDetail();
        }
    });
</script>

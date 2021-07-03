<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Author: zal
 * Date: 2020-04-14
 * Time: 17:16
 */
?>

<style>
    .el-tabs__header {
        padding: 0 20px;
        height: 56px;
        line-height: 56px;
        background-color: #fff;
    }

    .item {
        background-color: #fff;
        width: 33%;
        height: 185px;
        margin-bottom: 10px;
        position: relative;
        padding: 20px;
        margin-right: 0.33%
    }

    .item .app-icon {
        display: flex;
        width: 85px;
        justify-content: space-between;
        position: absolute;
        right: 20px;
        top: 20px;
    }

    .item .app-icon img {
        cursor: pointer;
    }

    .item .name {
        background-color: #F4F4F5;
        color: #909399;
        width: auto;
        display: inline-block;
        padding: 0 10px;
        height: 32px;
        line-height: 32px;
        text-align: center;
        font-size: 12px;
        border-radius: 3px;
        border: 1px solid #E0E0E3;
        margin-bottom: 5px;
    }

    .el-form-item {
        margin-bottom: 0px;
    }

    .showqr .el-dialog__body {
        text-align: center;
        padding-bottom: 10px;
    }

    .el-dialog {
        min-width: 400px;
    }
</style>

<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 0 0;"
             v-loading="cardLoading">
        <el-tabs v-model="activeName" @tab-click="handleClick(activeName)">
            <el-tab-pane label="基础页面" name="base">
                <div style="display: flex;flex-wrap: wrap">
                    <div v-for="item in list" class="item">
                        <div class="name">{{item.name}}</div>
                        <div class="app-icon">
                            <el-tooltip effect="dark" content="复制链接" placement="top">
                                <img class="copy-btn" src="statics/img/mall/copy.png" data-clipboard-action="copy"
                                     :data-clipboard-target="'#base' + item.id">
                            </el-tooltip>
                            <el-tooltip effect="dark" content="生成二维码" placement="top">
                                <img src="statics/img/mall/qr.png" @click="qrcode(item)" alt="">
                            </el-tooltip>
                        </div>
                        <el-form @submit.native.prevent label-position="left" label-width="50px">
                            <el-form-item  label="H5">
                             <span :id="'base'+item.id">
                                    <span v-if="article_id && item.name == '文章详情'">{{base_url}}{{item.value}}?{{item.params[0].key}}={{article_id}}</span>
                                    <span v-else-if="cat_id && item.name == '分类'">{{base_url}}{{item.value}}?{{item.params[0].key}}={{cat_id}}</span>
                                    <span v-else-if="goods_id && item.name == '商品列表'">{{base_url}}{{item.value}}?{{item.params[0].key}}={{goods_id}}</span>
                                    <span v-else-if="topic_id && item.name == '专题列表'">{{base_url}}{{item.value}}?{{item.params[0].key}}={{topic_id}}</span>
                                    <span v-else-if="topic_detail_id && item.name == '专题详情'">{{base_url}}{{item.value}}?{{item.params[0].key}}={{topic_detail_id}}</span>
                                    <span v-else-if="detail_id && item.name == '商品详情'">{{base_url}}{{item.value}}?{{item.params[0].key}}={{detail_id}}</span>
                                    <span v-else-if="appid && item.name == '跳转小程序'">{{base_url}}{{item.value}}?{{item.params[0].key}}={{appid}}</span>
                                    <span v-else-if="appid && url && item.name == '跳转小程序'">{{base_url}}{{item.value}}?{{item.params[0].key}}={{url}}</span>
                                    <span v-else>{{base_url}}{{item.value}}</span>
                                </span>
                            </el-form-item>
                            <el-form-item label="其他">
                                <span :id="'base'+item.id">
                                    <span v-if="article_id && item.name == '文章详情'">{{item.value}}?{{item.params[0].key}}={{article_id}}</span>
                                    <span v-else-if="cat_id && item.name == '分类'">{{item.value}}?{{item.params[0].key}}={{cat_id}}</span>
                                    <span v-else-if="goods_id && item.name == '商品列表'">{{item.value}}?{{item.params[0].key}}={{goods_id}}</span>
                                    <span v-else-if="topic_id && item.name == '专题列表'">{{item.value}}?{{item.params[0].key}}={{topic_id}}</span>
                                    <span v-else-if="topic_detail_id && item.name == '专题详情'">{{item.value}}?{{item.params[0].key}}={{topic_detail_id}}</span>
                                    <span v-else-if="detail_id && item.name == '商品详情'">{{item.value}}?{{item.params[0].key}}={{detail_id}}</span>
                                    <span v-else-if="appid && item.name == '跳转小程序'">{{item.value}}?{{item.params[0].key}}={{appid}}</span>
                                    <span v-else-if="appid && url && item.name == '跳转小程序'">{{item.value}}?{{item.params[0].key}}={{url}}</span>
                                    <span v-else>{{item.value}}</span>
                                </span>
                            </el-form-item>
                            <el-form-item v-if="item.name == '文章详情'" label="参数">
                                <el-input size="small" v-model="article_id" placeholder="请填写在文章列表中相关文章的ID"></el-input>
                            </el-form-item>
                            <el-form-item v-if="item.name == '分类'" label="参数">
                                <el-input size="small" v-model="cat_id" placeholder="请填写在商品分类中相关分类的ID"></el-input>
                            </el-form-item>
                            <el-form-item v-if="item.name == '商品列表'" label="参数">
                                <el-input size="small" v-model="goods_id" placeholder="请填写在商品分类中相关分类的ID"></el-input>
                            </el-form-item>
                            <el-form-item v-if="item.name == '跳转小程序'" label="参数">
                                <el-input size="small" v-model="appid" placeholder="要打开的小程序appid"></el-input>
                            </el-form-item>
                            <el-form-item v-if="item.name == '跳转小程序'" label="参数">
                                <el-input size="small" v-model="url"
                                          placeholder="打开的页面路径，如pages/index/index"></el-input>
                            </el-form-item>
                            <el-form-item v-if="item.name == '专题列表'" label="参数">
                                <el-input size="small" v-model="topic_id" placeholder="请填写在专题分类中的ID"></el-input>
                            </el-form-item>
                            <el-form-item v-if="item.name == '专题详情'" label="参数">
                                <el-input size="small" v-model="topic_detail_id" placeholder="请填写在相关专题的ID"></el-input>
                            </el-form-item>
                            <el-form-item v-if="item.name == '商品详情'" label="参数">
                                <el-input size="small" v-model="detail_id" placeholder="请填写在商品列表中相关商品的ID"></el-input>
                            </el-form-item>
                            <el-form-item v-if="item.name == '多商户店铺'" label="参数">
                                <el-input size="small" v-model="mch_id" placeholder=""></el-input>
                            </el-form-item>
                        </el-form>
                    </div>
                </div>
            </el-tab-pane>
            <el-tab-pane label="营销页面" name="marketing">
                <div style="display: flex;flex-wrap: wrap">
                    <div v-for="item in list" class="item">
                        <div class="name">{{item.name}}</div>
                        <div class="app-icon">
                            <el-tooltip effect="dark" content="复制链接" placement="top">
                                <img class="copy-btn" src="statics/img/mall/copy.png" alt=""
                                     data-clipboard-action="copy" :data-clipboard-target="'#marketing' + item.id">
                            </el-tooltip>
                            <el-tooltip effect="dark" content="二维码" placement="top">
                                <img src="statics/img/mall/qr.png" @click="qrcode(item)" alt="">
                            </el-tooltip>
                        </div>
                        <el-form label-position="left" label-width="50px">
                            <el-form-item label="路径">
                                <span :id="'marketing'+item.id">{{item.value}}</span>
                            </el-form-item>
                        </el-form>
                    </div>
                </div>
            </el-tab-pane>
            <el-tab-pane label="插件页面" name="plugin" v-if="detail.plugin">
                <div style="display: flex;flex-wrap: wrap">
                    <div v-for="item in list" class="item">
                        <div class="name">{{item.name}}</div>
                        <div class="app-icon">
                            <el-tooltip effect="dark" content="复制链接" placement="top">
                                <img class="copy-btn" src="statics/img/mall/copy.png" alt=""
                                     data-clipboard-action="copy" :data-clipboard-target="'#plugin' + item.id">
                            </el-tooltip>
                            <el-tooltip effect="dark" content="二维码" placement="top">
                                <img src="statics/img/mall/qr.png" @click="qrcode(item)" alt="">
                            </el-tooltip>
                        </div>
                        <el-form label-position="left" label-width="50px">
                            <el-form-item label="路径">
                                <span :id="'plugin'+item.id">
                                    <span v-if="mch_id && item.name == '多商户店铺'">{{item.value}}?mch_id={{mch_id}}</span>
                                    <span v-else-if="item.goods_id && item.name.indexOf('商品详情') > -1">{{item.value}}?{{item.params[0].key}}={{item.goods_id}}</span>
                                    <span v-else-if="item.goods_id && item.name == '多商户商品'">{{item.value}}?{{item.params[0].key}}={{item.goods_id}}</span>
                                    <span v-else>{{item.value}}</span>
                                </span>
                            </el-form-item>
                            <el-form-item v-if="item.name == '多商户店铺'" label="参数">
                                <el-input size="small" v-model="mch_id" placeholder="请填写在商户列表中相关商户的ID"></el-input>
                            </el-form-item>
                            <el-form-item v-if="item.name.indexOf('商品详情') > -1 || item.name == '多商户商品'" label="参数">
                                <el-input size="small" v-if="item.name == '拼团商品详情'" v-model="pt_goods"
                                          @input="change($event,item)" placeholder="请填写在商品列表中相关商品的ID"></el-input>
                                <el-input size="small" v-if="item.name == '秒杀商品详情'" v-model="ms_goods"
                                          @input="change($event,item)" placeholder="请填写在商品列表中相关商品的ID"></el-input>
                                <el-input size="small" v-if="item.name == '砍价商品详情'" v-model="bargain_goods"
                                          @input="change($event,item)" placeholder="请填写在商品列表中相关商品的ID"></el-input>
                                <el-input size="small" v-if="item.name == '多商户商品'" v-model="mch_goods"
                                          @input="change($event,item)" placeholder="请填写在商品列表中相关商品的ID"></el-input>
                                <el-input size="small" v-if="item.name == '预约商品详情'" v-model="book_goods"
                                          @input="change($event,item)" placeholder="请填写在商品列表中相关商品的ID"></el-input>
                                <el-input size="small" v-if="item.name == '积分商品详情'" v-model="intergral_goods"
                                          @input="change($event,item)" placeholder="请填写在商品列表中相关商品的ID"></el-input>
                                <el-input size="small" v-if="item.name == '抽奖商品详情'" v-model="lottery_goods"
                                          @input="change($event,item)" placeholder="请填写在商品列表中相关商品的ID"></el-input>
                                <el-input size="small" v-if="item.name == '步数宝商品详情'" v-model="step_goods"
                                          @input="change($event,item)" placeholder="请填写在商品列表中相关商品的ID"></el-input>
                            </el-form-item>
                        </el-form>
                    </div>
                </div>
            </el-tab-pane>
            <el-tab-pane label="订单页面" name="order">
                <div style="display: flex;flex-wrap: wrap">
                    <div v-for="item in list" class="item">
                        <div class="name">{{item.name}}</div>
                        <div class="app-icon">
                            <el-tooltip effect="dark" content="复制链接" placement="top">
                                <img class="copy-btn" src="statics/img/mall/copy.png" alt=""
                                     data-clipboard-action="copy" :data-clipboard-target="'#order' + item.id">
                            </el-tooltip>
                            <el-tooltip effect="dark" content="二维码" placement="top">
                                <img src="statics/img/mall/qr.png" @click="qrcode(item)" alt="">
                            </el-tooltip>
                        </div>
                        <el-form label-position="left" label-width="50px">
                            <el-form-item label="路径">
                                <span :id="'order'+item.id">{{item.value}}</span>
                            </el-form-item>
                        </el-form>
                    </div>
                </div>
            </el-tab-pane>
            <el-tab-pane label="diy页面" name="diy" v-if="detail.diy">
                <div style="display: flex;flex-wrap: wrap">
                    <div v-for="item in list" class="item">
                        <div class="name">{{item.name}}</div>
                        <div class="app-icon">
                            <el-tooltip effect="dark" content="复制链接" placement="top">
                                <img class="copy-btn" src="statics/img/mall/copy.png" alt=""
                                     data-clipboard-action="copy" :data-clipboard-target="'#diy' + item.id">
                            </el-tooltip>
                            <el-tooltip effect="dark" content="二维码" placement="top">
                                <img src="statics/img/mall/qr.png" @click="qrcode(item)" alt="">
                            </el-tooltip>
                        </div>
                        <el-form label-position="left" label-width="50px">
                            <el-form-item label="路径">
                                <span :id="'diy'+item.id">{{item.value}}</span>
                            </el-form-item>
                        </el-form>
                    </div>
                </div>
            </el-tab-pane>
        </el-tabs>
    </el-card>
    <el-dialog class="showqr" :visible.sync="showqr" width="20%" center>
        <div class="name" style="text-align: center">{{title}}</div>
        <com-image :src="qrimg" style="margin: 20px auto 10px" height='200' width='200'></com-image>
        <span slot="footer" class="dialog-footer">
            <el-button type="primary" style="margin-bottom: 10px;" size="small" @click="down">保存二维码图片</el-button>
        </span>
    </el-dialog>
</div>
<script src="https://cdn.jsdelivr.net/clipboard.js/1.5.12/clipboard.min.js"></script>
<script>
    var clipboard = new Clipboard('.copy-btn');

    var self = this;
    clipboard.on('success', function (e) {
        self.ELEMENT.Message.success('复制成功');
        e.clearSelection();
    });
    clipboard.on('error', function (e) {
        self.ELEMENT.Message.success('复制失败，请手动复制');
    });

    const app = new Vue({
        el: '#app',
        data() {
            return {
                qrimg: '',
                showqr: false,
                list: [],
                cardLoading: false,
                activeName: 'base',
                detail: [],
                cat_id: '',
                goods_id: '',
                detail_id: '',
                topic_id: '',
                article_id: '',
                title: '',
                topic_detail_id: '',
                appid: '',
                pt_goods: '',
                ms_goods: '',
                bargain_goods: '',
                book_goods: '',
                mch_goods: '',
                intergral_goods: '',
                lottery_goods: '',
                step_goods: '',
                mch_id: '',
                base_url:''
            };
        },
        methods: {
            down() {
                var alink = document.createElement("a");
                alink.href = this.qrimg;
                alink.download = this.title;
                alink.click();
            },

            change(e, row) {
                row.goods_id = e;
            },

            handleClick(e) {
                this.list = this.detail[e]
            },

            qrcode(row) {
                this.cardLoading = true;
                let value = row.value.replace('/', '')
                // let label = row.params[0].key
                let para = {
                    r: 'mall/app-page/qrcode',
                    path: value,
                };
                if (row.params) {
                    switch (row.name) {
                        case '文章详情':
                            row.params[0].value = this.article_id
                            break;
                        case '分类':
                            row.params[0].value = this.cat_id
                            break;
                        case '商品列表':
                            row.params[0].value = this.goods_id
                            break;
                        case '专题详情':
                            row.params[0].value = this.topic_detail_id
                            break;
                        case '专题列表':
                            row.params[0].value = this.topic_id
                            break;
                        case '商品详情':
                            row.params[0].value = this.detail_id
                            break;
                        case '多商户店铺':
                            row.params[0].value = this.mch_id
                            break;
                    }
                    if (row.goods_id) {
                        row.params[0].value = row.goods_id
                    }
                    if (row.params[0].value) {
                        para.params = {};
                        para.params[row.params[0].key] = row.params[0].value
                    }
                }
                this.title = row.name;
                request({
                    params: para,
                    method: 'get',
                }).then(e => {
                    this.cardLoading = false;
                    if (e.data.code == 0) {
                        this.qrimg = e.data.data.h5_qrcode
                        this.showqr = true;
                    } else {
                        this.$message.error(e.data.msg)
                    }
                }).catch(e => {
                    this.cardLoading = false;
                    console.log(e);
                });
            },
            getList() {
                let self = this;
                self.cardLoading = true;
                request({
                    params: {
                        r: 'mall/app-page/index',
                    },
                    method: 'get',
                }).then(e => {
                    self.cardLoading = false;
                    if (e.data.code == 0) {
                        self.base_url = e.data.data.base_url;
                        self.detail = e.data.data.list;
                        if (typeof self.detail.plugin != 'undefined') {
                            self.detail.plugin.forEach(function (row) {
                                row.goods_id = ''
                            })
                        }
                        self.list = e.data.data.list.base;
                    } else {
                        self.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    console.log(e);
                });
            },
        },
        mounted: function () {
            this.getList();
        }
    });
</script>

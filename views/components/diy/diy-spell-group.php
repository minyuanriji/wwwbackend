<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Author: zal
 * Date: 2020-04-23
 * Time: 10:30
 */
?>

<template id="diy-spell_group">
    <div class="diy-spell_group">
        <div class="diy-component-preview">
            <div class="goods-list" :class="'goods-list-'+data.listStyle" :flex="cListFlex">
                <div v-for="item in cList" style="padding: 10px;" :style="cItemStyle">
                    <div class="goods-item"
                         :flex="cGoodsFlex"
                         :class="'goods-cover-'+data.goodsCoverProportion"
                         :style="cGoodsStyle">
                        <div class="goods-pic" :style="cGoodsPicStyle(item.picUrl)"></div>
                        <div v-if="data.showGoodsTag" class="goods-tag"
                             :style="'background-image: url('+data.goodsTagPicUrl+');'"></div>
                        <div :style="cGoodsInfoStyle">
                            <div class="goods-name goods-name-static">
                                <template v-if="data.showGoodsName">
                                    <div>{{item.name}}</div>
                                </template>
                            </div>
                            <div class="diy-goods-pt-info">{{item.peopleNum}}人团</div>
                            <div flex="box:last cross:bottom">
                                <div class="goods-price" :style="cPriceStyle">
                                    <div style="color: #ff4544">
                                        <span style="letter-spacing: -1px;">￥{{item.spell_groupPrice}}</span>
                                    </div>
                                </div>
                                <div v-if="cShowBuyBtn" style="padding: 0 10px;">
                                    <el-button :style="cButtonStyle" type="primary" style="font-size: 24px;">{{data.buyBtnText}}</el-button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="diy-component-edit">
            <el-form @submit.native.prevent label-width="150px">
                <el-form-item label="商品列表">
                    <draggable class="goods-list" flex v-model="data.list" ref="parentNode">
                        <div class="goods-item drag-drop" v-for="(goods,goodsIndex) in data.list"
                             :style="'background-image: url('+goods.picUrl+');'">
                            <el-button @click="deleteGoods(goodsIndex)" class="goods-delete"
                                       size="small" circle type="danger"
                                       icon="el-icon-close"></el-button>
                        </div>
                        <div class="goods-add" flex="main:center cross:center"
                             @click="goodsDialog.visible=true">
                            <i class="el-icon-plus"></i>
                        </div>
                    </draggable>
                </el-form-item>
                <el-form-item label="购买按钮颜色">
                    <el-color-picker v-model="data.buttonColor"></el-color-picker>
                </el-form-item>
                <el-form-item label="列表样式">
                    <el-radio v-model="data.listStyle" :label="-1" @change="listStyleChange">列表模式</el-radio>
                    <el-radio v-model="data.listStyle" :label="1" @change="listStyleChange">一行一个</el-radio>
                    <el-radio v-model="data.listStyle" :label="2" @change="listStyleChange">一行两个</el-radio>
                </el-form-item>
                <el-form-item label="商品封面图宽高比例" v-if="data.listStyle==1">
                    <el-radio v-model="data.goodsCoverProportion" label="1-1">1:1</el-radio>
                    <el-radio v-model="data.goodsCoverProportion" label="3-2">3:2</el-radio>
                </el-form-item>
                <el-form-item label="商品封面图填充">
                    <el-radio v-model="data.fill" :label="1">填充</el-radio>
                    <el-radio v-model="data.fill" :label="0">留白</el-radio>
                </el-form-item>
                <el-form-item label="单品样式">
                    <el-radio v-model="data.goodsStyle" :label="1">无边框</el-radio>
                    <el-radio v-model="data.goodsStyle" :label="2">有边框</el-radio>
                    <template v-if="data.listStyle!==-1">
                        <el-radio v-model="data.goodsStyle" :label="3">无边框居中</el-radio>
                        <el-radio v-model="data.goodsStyle" :label="4">有边框居中</el-radio>
                    </template>
                </el-form-item>
                <el-form-item label="显示商品名称">
                    <el-switch v-model="data.showGoodsName"></el-switch>
                </el-form-item>
                <template v-if="cShowEditBuyBtn">
                    <el-form-item label="显示购买按钮">
                        <el-switch v-model="data.showBuyBtn"></el-switch>
                    </el-form-item>
                    <el-form-item v-if="data.showBuyBtn" label="购买按钮样式">
                        <el-radio v-model="data.buyBtnStyle" :label="1">填充</el-radio>
                        <el-radio v-model="data.buyBtnStyle" :label="2">线条</el-radio>
                        <el-radio v-model="data.buyBtnStyle" :label="3">圆角填充</el-radio>
                        <el-radio v-model="data.buyBtnStyle" :label="4">圆角线条</el-radio>
                    </el-form-item>
                    <el-form-item v-if="data.showBuyBtn" label="购买按钮文字">
                        <el-input size="small" v-model="data.buyBtnText"></el-input>
                    </el-form-item>
                </template>
                <el-form-item label="显示商品角标">
                    <el-switch v-model="data.showGoodsTag"></el-switch>
                </el-form-item>
                <el-form-item label="商品角标样式" v-if="data.showGoodsTag">
                    <el-radio v-model="data.goodsTagPicUrl" v-for="tag in goodsTags" :label="tag.picUrl" :key="tag.name"
                              @change="goodsTagChange">
                        {{tag.name}}
                    </el-radio>
                    <el-radio v-model="data.customizeGoodsTag" :label="true" @change="customizeGoodsTagChange">自定义
                    </el-radio>
                </el-form-item>
                <el-form-item label="自定义商品角标" v-if="data.showGoodsTag&&data.customizeGoodsTag">
                    <com-image-upload width="64" height="64" v-model="data.goodsTagPicUrl"></com-image-upload>
                </el-form-item>
            </el-form>
        </div>
        <el-dialog title="选择商品" :visible.sync="goodsDialog.visible" @open="goodsDialogOpened">
            <el-input size="mini" v-model="goodsDialog.keyword" placeholder="根据名称搜索" :clearable="true"
                      @clear="loadGoodsList" @keyup.enter.native="loadGoodsList">
                <el-button slot="append" @click="loadGoodsList">搜索</el-button>
            </el-input>
            <el-table :data="goodsDialog.list" v-loading="goodsDialog.loading" @selection-change="goodsSelectionChange">
                <el-table-column type="selection" width="50px"></el-table-column>
                <el-table-column label="ID" prop="id" width="100px"></el-table-column>
                <el-table-column label="名称" prop="name"></el-table-column>
            </el-table>
            <div style="text-align: center">
                <el-pagination
                        v-if="goodsDialog.pagination"
                        style="display: inline-block"
                        background
                        @current-change="goodsDialogPageChange"
                        layout="prev, pager, next"
                        :page-size.sync="goodsDialog.pagination.pageSize"
                        :total="goodsDialog.pagination.totalCount">
                </el-pagination>
            </div>
            <div slot="footer">
                <el-button type="primary" @click="addGoods">确定</el-button>
            </div>
        </el-dialog>
    </div>
</template>
<script>
    Vue.component('diy-spell_group', {
        template: '#diy-spell_group',
        props: {
            value: Object,
        },
        data() {
            return {
                goodsDialog: {
                    visible: false,
                    page: 1,
                    keyword: '',
                    loading: false,
                    list: [],
                    pagination: null,
                    selected: null,
                },
                goodsTags: [
                    {
                        name: '热销',
                        picUrl: _currentPluginBaseUrl + '/images/goods-tag-rx.png',
                    },
                    {
                        name: '新品',
                        picUrl: _currentPluginBaseUrl + '/images/goods-tag-xp.png',
                    },
                    {
                        name: '折扣',
                        picUrl: _currentPluginBaseUrl + '/images/goods-tag-zk.png',
                    },
                    {
                        name: '推荐',
                        picUrl: _currentPluginBaseUrl + '/images/goods-tag-tj.png',
                    },
                ],
                data: {
                    buttonColor: '#ff4544',
                    list: [],
                    listStyle: 1,
                    fill: 1,
                    goodsCoverProportion: '1-1',
                    goodsStyle: 1,
                    showGoodsName: true,
                    showBuyBtn: true,
                    buyBtnStyle: 1,
                    buyBtnText: '去拼团',
                    showGoodsTag: false,
                    customizeGoodsTag: false,
                    goodsTagPicUrl: '',
                },
            };
        },
        created() {
            if (!this.value) {
                this.$emit('input', JSON.parse(JSON.stringify(this.data)))
            } else {
                this.data = JSON.parse(JSON.stringify(this.value));
            }
        },
        computed: {
            cList() {
                if (!this.data.list || !this.data.list.length) {
                    const item = {
                        id: 0,
                        name: '演示商品名称',
                        picUrl: '',
                        price: '300.00',
                        spell_groupPrice: '250.00',
                        peopleNum: 3,
                    };
                    return [item, item,];
                } else {
                    return this.data.list;
                }
            },
            cListFlex() {
                if (this.data.listStyle === -1) {
                    return 'dir:top';
                } else {
                    return 'dir:left';
                }
            },
            cItemStyle() {
                if (this.data.listStyle === 2) {
                    return 'width: 50%;';
                } else {
                    return 'width: 100%;';
                }
            },
            cGoodsStyle() {
                let style = 'border-radius:5px;';
                if (this.data.goodsStyle === 2 || this.data.goodsStyle === 4) {
                    style += 'border: 1px solid #e2e2e2;';
                }
                return style;
            },
            cGoodsInfoStyle() {
                let style = '';
                if (this.data.listStyle !== -1) {
                    style += 'padding:20px;';
                }else {
                    style += 'padding:10px 20px 0 0;';
                }
                if (this.data.goodsStyle === 3 || this.data.goodsStyle === 4) {
                    style += 'text-align: center;';
                }
                return style;
            },
            cPriceStyle() {
                let style = 'margin-top:10px;white-space: nowrap;overflow: hidden;text-overflow: ellipsis;color: #ff4544;line-height: 48px;';
                if (this.data.goodsStyle === 3 || this.data.goodsStyle === 4) {
                    style += 'text-align: center;width: 100%;';
                }
                return style;
            },
            cGoodsFlex() {
                if (this.data.listStyle === -1) {
                    return 'dir:left box:first';
                } else {
                    return 'dir:top';
                }
            },
            cButtonStyle() {
                console.log(this.data.buyBtnStyle);
                let style = `background: ${this.data.buttonColor};border-color: ${this.data.buttonColor};height:48px;line-height50px;padding:0 20px;`;
                if (this.data.buyBtnStyle === 3 || this.data.buyBtnStyle === 4) {
                    style += `border-radius:24px;`;
                }
                if (this.data.buyBtnStyle === 2 || this.data.buyBtnStyle === 4) {
                    style += `background:#fff;color:${this.data.buttonColor}`;
                }
                return style;
            },
            cShowBuyBtn() {
                return this.data.goodsStyle !== 3
                    && this.data.goodsStyle !== 4
                    && this.data.showBuyBtn
            },
            cShowEditBuyBtn() {
                return this.data.goodsStyle !== 3
                    && this.data.goodsStyle !== 4
            },
        },
        watch: {
            data: {
                deep: true,
                handler(newVal, oldVal) {
                    this.$emit('input', newVal, oldVal)
                },
            }
        },
        methods: {
            cGoodsPicStyle(picUrl) {
                let style = `background-image: url(${picUrl});`
                    + `background-size: ${(this.data.fill === 1 ? 'cover' : 'contain')};`;
                return style;
            },
            listStyleChange(listStyle) {
                if (listStyle === -1 && this.data.goodsStyle === 3) {
                    this.data.goodsStyle = 1;
                }
                if (listStyle === -1 && this.data.goodsStyle === 4) {
                    this.data.goodsStyle = 2;
                }
            },
            goodsDialogOpened() {
                this.loadGoodsList();
            },
            loadGoodsList() {
                this.goodsDialog.loading = true;
                this.$request({
                    params: {
                        r: 'plugin/diy/mall/template/get-goods',
                        page: this.goodsDialog.page,
                        keyword: this.goodsDialog.keyword,
                        sign: 'spell_group',
                    }
                }).then(response => {
                    this.goodsDialog.loading = false;
                    if (response.data.code === 0) {
                        this.goodsDialog.list = response.data.data.list;
                        this.goodsDialog.pagination = response.data.data.pagination;
                    }
                }).catch(e => {
                });
            },
            goodsDialogPageChange(page) {
                this.goodsDialog.page = page;
                this.loadGoodsList();
            },
            goodsSelectionChange(e) {
                if (e && e.length) {
                    this.goodsDialog.selected = e;
                } else {
                    this.goodsDialog.selected = null;
                }
            },
            addGoods() {
                if (!this.goodsDialog.selected || !this.goodsDialog.selected.length) {
                    this.goodsDialog.visible = false;
                    return;
                }
                // const total = this.data.list.length + this.goodsDialog.selected.length;
                // if (total > 10) {
                //     this.goodsDialog.visible = false;
                //     this.$message.error('商品数量不能大于10个。');
                //     return;
                // }
                for (let i in this.goodsDialog.selected) {
                    const item = {
                        id: this.goodsDialog.selected[i].id,
                        name: this.goodsDialog.selected[i].name,
                        picUrl: this.goodsDialog.selected[i].cover_pic,
                        price: this.goodsDialog.selected[i].price,
                    };
                    if (this.goodsDialog.selected[i].groups && this.goodsDialog.selected[i].groups.length) {
                        item.peopleNum = this.goodsDialog.selected[i].groups[0].people_num;
                        item.spell_groupPrice = this.goodsDialog.selected[i].groups[0].spell_group_price;
                    }
                    this.data.list.push(item);
                }
                this.goodsDialog.selected = null;
                this.goodsDialog.visible = false;
            },
            deleteGoods(index) {
                this.data.list.splice(index, 1);
            },
            goodsTagChange(e) {
                this.data.goodsTagPicUrl = e;
                this.data.customizeGoodsTag = false;
            },
            customizeGoodsTagChange(e) {
                this.data.goodsTagPicUrl = '';
                this.data.customizeGoodsTag = true;
            },
        }
    });
</script>
<style>
    .diy-spell_group .diy-component-edit .goods-list {
        line-height: normal;
        flex-wrap: wrap;
    }

    .diy-spell_group .diy-component-edit .goods-item,
    .diy-spell_group .diy-component-edit .goods-add {
        width: 50px;
        height: 50px;
        border: 1px solid #e2e2e2;
        background-position: center;
        background-size: cover;
        margin-right: 15px;
        margin-bottom: 15px;
        position: relative;
    }

    .diy-spell_group .diy-component-edit .goods-add {
        cursor: pointer;
    }

    .diy-spell_group .diy-component-edit .goods-delete {
        position: absolute;
        top: -11px;
        right: -11px;
        width: 25px;
        height: 25px;
        line-height: 25px;
        padding: 0 0;
        visibility: hidden;
    }

    .diy-spell_group .diy-component-edit .goods-item:hover .goods-delete {
        visibility: visible;
    }

    /*-------------------- 预览部分 --------------------*/
    .diy-spell_group .diy-component-preview .goods-list {
        flex-wrap: wrap;
        background-color: #fff;
        padding: 10px;
    }

    .diy-spell_group .diy-component-preview .goods-item {
        position: relative;
    }

    .diy-spell_group .diy-component-preview .goods-tag {
        position: absolute;
        top: 0;
        left: 0;
        width: 64px;
        height: 64px;
        background-position: center;
        background-size: cover;
    }

    .diy-spell_group .diy-component-preview .goods-pic {
        width: 100%;
        height: 706px;
        background-color: #e2e2e2;
        background-position: center;
        background-size: cover;
        background-repeat: no-repeat;
    }

    .diy-spell_group .diy-component-preview .goods-name {
        margin-bottom: 10px;
    }

    .diy-spell_group .diy-component-preview .goods-cover-3-2 .goods-pic {
        height: 470px;
    }

    .diy-spell_group .diy-component-preview .goods-list-2 .goods-pic {
        height: 343px;
    }

    .diy-spell_group .diy-component-preview .goods-list--1 .goods-pic {
        width: 200px;
        height: 200px;
        margin-right: 20px;
    }

    .diy-spell_group .diy-component-preview .goods-list--1 .goods-item {
        margin-bottom: 20px;
    }

    .diy-spell_group .diy-component-preview .goods-list--1 .goods-item:last-child {
        margin-bottom: 0;
    }

    .diy-spell_group .diy-component-preview .goods-name-static {
        white-space: normal;
        overflow: hidden;
        text-overflow: ellipsis;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        word-break: break-all;
        height: 80px;
        margin-bottom: 0;
    }

    .diy-spell_group .diy-goods-pt-info {
        border: 1px solid #ff4544;
        border-radius: 5px;
        font-size: 24px;
        padding: 0 8px;
        height: 35px;
        line-height: 35px;
        display: inline-block;
        color: #ff4544;
    }
</style>
<?php

Yii::$app->loadComponentView('com-dialog-select');
?>

<template id="stock-goods-edit">
    <el-dialog :visible.sync="edit.visible" width="20%" title="添加库存商品">
        <div>

            <el-form @submit.native.prevent size="small" label-width="150px">

                <el-form-item label="选择商品">
                    <div style="display: inline-block;">
                        <div flex="cross:center">
                            <div style="margin-left: 10px;">
                                <com-dialog-select :multiple="false" @selected="goodsSelect"
                                                   title="商品选择">
                                    <el-button type="text">选择商品</el-button>
                                </com-dialog-select>
                            </div>
                        </div>
                    </div>
                </el-form-item>
                <el-form-item prop="goods_warehouse_ids">
                    <div style="max-height: 300px;overflow-y: auto">
                        <div flex v-if="goods">
                            <div style="padding-right: 10px;flex-grow: 0">
                                <com-image mode="aspectFill"
                                           :src="goods.goodsWarehouse.cover_pic"></com-image>
                            </div>
                            <div style="flex-grow: 1;">
                                <com-ellipsis :line="2">{{goods.goodsWarehouse.name}}
                                </com-ellipsis>
                            </div>
                            <div style="flex-grow: 0;">
                                <el-button @click="deleteGoods"
                                           type="text" circle size="mini">
                                    <el-tooltip class="item" effect="dark"
                                                content="删除" placement="top">
                                        <img src="statics/img/mall/del.png" alt="">
                                    </el-tooltip>
                                </el-button>
                            </div>
                        </div>
                    </div>
                </el-form-item>
                <el-form-item label="商品售价">
                    <el-input placeholder="请输入内容" v-model="ruleForm.origin_price">
                        <template slot="append">元</template>
                    </el-input>
                </el-form-item>


                <el-form-item label="拿货价设置">
                    <template v-if="level_list.length" v-for="(level,i) in level_list">
                        <el-input placeholder="请输入内容" style="margin-bottom: 10px" v-model="level.stock_price">
                            <template slot="prepend">{{level.name}}</template>
                            <template slot="append">元/件</template>
                        </el-input>
                    </template>
                </el-form-item>
                <el-form-item label="平级奖设置">
                    <template v-if="equal_level_list.length" v-for="(level,i) in equal_level_list">
                        <el-input placeholder="请输入平级奖" style="margin-bottom: 10px" v-model="level.equal_price"
                                  @input="onInput">
                            <template slot="prepend">{{level.name}}</template>
                            <template slot="append">元/件</template>
                        </el-input>
                    </template>
                </el-form-item>
                <el-form-item label="补货奖励">
                    <template v-if="fill_level_list.length" v-for="(level,i) in fill_level_list">
                        <el-input placeholder="请输入补货奖励" style="margin-bottom: 10px" v-model="level.fill_price"
                                  @input="onInput">
                            <template slot="prepend">{{level.name}}</template>
                            <template slot="append">元/件</template>
                        </el-input>
                    </template>
                </el-form-item>
                <el-form-item label="越级奖励">
                    <template v-if="over_level_list.length" v-for="(level,i) in over_level_list">
                        <el-input placeholder="请输入越级奖励" style="margin-bottom: 10px" v-model="level.over_price"
                                  @input="onInput">
                            <template slot="prepend">{{level.name}}</template>
                            <template slot="append">元/件</template>
                        </el-input>
                    </template>
                </el-form-item>
            </el-form>
        </div>
        <span slot="footer" class="dialog-footer">
            <el-button @click="editCancel" type="default" size="small">取消</el-button>
            <el-button type="primary" :loading="edit.btnLoading" style="margin-bottom: 10px;" size="small"
                       @click="editSave">保存</el-button>
        </span>
    </el-dialog>
</template>
<script>
    Vue.component('stock-goods-edit', {
        template: '#stock-goods-edit',
        props: {
            value: {
                type: Boolean,
                default: false
            },
            edit_row: {
                type: Object,
                default: null
            }
        },
        data() {
            return {
                edit: {
                    visible: false,
                    id: '',
                    btnLoading: false,
                },
                ruleForm: {
                    origin_price: 0.00,
                    price_list: [],
                    goods_id: 0,
                    level_list: [],
                    equal_level_list: [],
                    fill_level_list: [],
                    over_level_list: [],
                },
                level_list: [],
                equal_level_list: [],
                fill_level_list: [],
                over_level_list: [],
                goods: null,
            }
        },
        watch: {
            value() {
                if (this.value) {
                    this.edit.visible = true;
                    if (this.edit_row.id > 0) {
                        this.edit.id = this.edit_row.id;
                        this.loadData();
                    } else {
                        this.edit.id = 0;
                    }
                } else {
                    this.edit.visible = false;
                }
            },
            'edit.visible'() {
                if (!this.edit.visible) {
                    this.editCancel();
                }
                if (this.edit.id) {
                }
            }
        },
        mounted() {
            this.getLevelList();
            this.edit.id = this.edit_row.id;
        },
        methods: {
            onInput(e) {
                this.$forceUpdate();
            },
            loadData() {
                request({
                    params: {
                        r: 'plugin/stock/mall/goods/edit',
                        stock_goods_id: this.edit.id
                    },
                    method: 'get',
                }).then(response => {
                    this.edit.btnLoading = false;
                    if (response.data.code == 0) {
                        this.ruleForm = response.data.data.stock_goods;
                        this.goods = response.data.data.goods;
                        this.goods.goodsWarehouse = response.data.data.goods_warehouse;
                        this.level_list = response.data.data.stock_goods.agent_price;
                        this.equal_level_list = response.data.data.stock_goods.equal_level_list;
                        this.fill_level_list = response.data.data.stock_goods.fill_level_list;
                        this.over_level_list = response.data.data.stock_goods.over_level_list;
                    } else {
                        this.$message.error(response.data.msg);
                    }
                }).catch(response => {
                    this.edit.btnLoading = false;
                });
            },
            deleteGoods() {
                this.goods = null;
                this.ruleForm.goods_id = null;
            },
            goodsSelect(param) {
                if (param) {
                    this.goods = param
                    this.ruleForm.origin_price = this.goods.price;
                    this.ruleForm.goods_id = this.goods.id;
                }
            },
            agentClick(row) {
                this.edit.id = row.id
            },
            getLevelList() {
                this.edit.btnLoading = true;
                request({
                    params: {
                        r: 'plugin/stock/mall/level/enable-list',
                    },
                    method: 'get',
                }).then(response => {
                    this.edit.btnLoading = false;
                    if (response.data.code == 0) {
                        this.level_list = response.data.data.list;
                        this.equal_level_list = response.data.data.equal_level_list;
                        this.fill_level_list = response.data.data.fill_level_list;
                        this.over_level_list = response.data.data.over_level_list;
                    } else {
                        this.$message.error(response.data.msg);
                    }
                }).catch(response => {
                    this.edit.btnLoading = false;
                });
            },
            editCancel() {
                this.$emit('input', false);
            },
            editSave() {
                if (this.ruleForm.goods_id == 0) {
                    this.$message.error('请选择商品');
                    return;
                }
                this.edit.btnLoading = true;
                this.ruleForm.level_list = this.level_list;
                this.ruleForm.equal_level_list = this.equal_level_list;
                this.ruleForm.fill_level_list = this.fill_level_list;
                this.ruleForm.over_level_list = this.over_level_list;
                request({
                    params: {
                        r: 'plugin/stock/mall/goods/edit',
                    },
                    method: 'post',
                    data: this.ruleForm
                }).then(response => {
                    this.edit.btnLoading = false;
                    if (response.data.code == 0) {
                        this.$message.success('添加成功');
                        this.ruleForm = {
                            origin_price: 0.00,
                            price_list: [],
                            is_alone: 1,
                            goods_id: 0,
                            level_list: [],
                            equal_level_list: [],
                            fill_level_list: [],
                            over_level_list:[],
                        }
                        this.goods = null;
                        this.level_list = [];
                        this.editCancel();
                    } else {
                        this.$message.error(response.data.msg);
                    }
                }).catch(response => {
                    this.edit.btnLoading = false;
                });
            },
            keyUp() {
                console.log('key up')
            }
        }
    });
</script>

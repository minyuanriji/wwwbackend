<?php

Yii::$app->loadComponentView('com-dialog-select');
?>

<template id="upgrade-bag-edit">
    <el-dialog :visible.sync="edit.visible" width="20%" title="添加礼包方案">
        <div>

            <el-form @submit.native.prevent size="small" label-width="150px">

                <el-form-item label="配置名称">
                    <el-input v-model="ruleForm.name">
                    </el-input>
                </el-form-item>

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
                <el-form-item>
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

                <el-form-item label="选择等级">
                    <el-select v-model="ruleForm.level" placeholder="请选择">
                        <el-option
                                v-for="item in level_list"
                                :key="item.level"
                                :label="item.name"
                                :value="item.level">
                        </el-option>
                    </el-select>
                </el-form-item>
                <el-form-item label="开启关联库存">
                    <el-switch
                            v-model="ruleForm.is_stock"
                            :active-value="1"
                            :inactive-value="0">
                    </el-switch>
                </el-form-item>
                <el-form-item label="库存商品ID">
                    <el-input v-model="ruleForm.stock_goods_id">
                    </el-input>
                </el-form-item>
                <el-form-item label="包含库存数量">
                    <el-input v-model="ruleForm.stock_num">
                        <template slot="append">件</template>
                    </el-input>
                </el-form-item>
                <el-form-item label="库存单价">
                    <el-input v-model="ruleForm.unit_price">
                        <template slot="append">元</template>
                    </el-input>
                </el-form-item>


                <el-form-item label="计算方式">
                    <el-radio-group v-model="ruleForm.compute_type">
                        <el-radio :label="0">订单完成</el-radio>
                        <el-radio :label="1">支付后</el-radio>
                    </el-radio-group>
                </el-form-item>

                <el-form-item label="是否启用">
                    <el-switch
                            v-model="ruleForm.is_enable"
                            :active-value="1"
                            :inactive-value="0">
                    </el-switch>
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
    Vue.component('upgrade-bag-edit', {
        template: '#upgrade-bag-edit',
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
                    name: '',
                    goods_id: 0,
                    level: 0,
                    is_stock: 0,
                    stock_num: 0,
                    unit_price: 0,
                    is_enable: 0,
                    stock_goods_id: 0,
                    compute_type:0
                },
                level_list: [],
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
            //   console.log(this.edit_row)
            this.edit.id = this.edit_row.id;
        },
        methods: {
            onInput(e) {
                this.$forceUpdate();
            },
            loadData() {
                request({
                    params: {
                        r: 'plugin/stock/mall/level/upgrade-bag-detail',
                        bag_id: this.edit.id
                    },
                    method: 'get',
                }).then(response => {
                    this.edit.btnLoading = false;
                    if (response.data.code == 0) {
                        this.ruleForm = response.data.data.bag
                        this.goods = {

                            goodsWarehouse: response.data.data.goods

                        }
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
                request({
                    params: {
                        r: 'plugin/stock/mall/level/upgrade-bag',
                    },
                    method: 'post',
                    data: this.ruleForm
                }).then(response => {
                    this.edit.btnLoading = false;
                    if (response.data.code == 0) {
                        this.$message.success('添加成功');
                        this.ruleForm = {
                            goods_id: 0,
                            level: 0,
                            is_stock: 0,
                            stock_num: 0,
                            unit_price: 0,
                            stock_goods_id: 0
                        }
                        this.goods = null;
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

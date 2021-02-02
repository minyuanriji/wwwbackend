<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-05-19
 * Time: 14:19
 */
?>
<template id="com-goods-area" v-cloak>
    <div v-loading="loading" class="com-goods-area" v-if="goods_id!=0">
        <el-form-item label="是否开启独立区域分红设置" prop="is_alone">
            <el-switch :active-value="1" :inactive-value="0" v-model="form.is_alone">
            </el-switch>
        </el-form-item>

        <template v-if="form.is_alone==1">
            <el-form-item label="佣金类型" prop="price_type">
                <el-radio-group v-model="form.price_type">
                    <el-radio :label="0">百分比</el-radio>
                    <el-radio :label="1">固定金额</el-radio>
                </el-radio-group>
            </el-form-item>
            <el-form-item label="佣金配置">
                <template>
                    <el-row style="margin-bottom:10px">
                        <el-col :span="4">
                            省代
                        </el-col>
                        <el-col :span="8">
                            <el-input type="number"
                                      v-model="form.province_price"
                                      placeholder="输入数值" @input="onInput">
                                <template slot="append" v-if="form.price_type==0">%</template>
                                <template slot="append" v-if="form.price_type==1">元</template>
                            </el-input>
                        </el-col>
                    </el-row>
                    <el-row style="margin-bottom:10px">
                        <el-col :span="4">
                            市代
                        </el-col>
                        <el-col :span="8">
                            <el-input type="number"
                                      v-model="form.city_price"
                                      placeholder="输入数值" @input="onInput">
                                <template slot="append" v-if="form.price_type==0">%</template>
                                <template slot="append" v-if="form.price_type==1">元</template>
                            </el-input>
                        </el-col>
                    </el-row>
                    </el-col>
                    <el-row style="margin-bottom:10px">
                        <el-col :span="4">
                            区代
                        </el-col>
                        <el-col :span="8">
                            <el-input type="number"
                                      v-model="form.district_price"
                                      placeholder="输入数值" @input="onInput">
                                <template slot="append" v-if="form.price_type==0">%</template>
                                <template slot="append" v-if="form.price_type==1">元</template>
                            </el-input>
                        </el-col>
                    </el-row>
                    </el-col>
                    <el-row style="margin-bottom:10px">
                        <el-col :span="4">
                            镇代
                        </el-col>
                        <el-col :span="8">
                            <el-input type="number"
                                      v-model="form.town_price"
                                      placeholder="输入数值" @input="onInput">
                                <template slot="append" v-if="form.price_type==0">%</template>
                                <template slot="append" v-if="form.price_type==1">元</template>
                            </el-input>
                        </el-col>
                    </el-row>
                    </el-col>
                </template>
            </el-form-item>
        </template>

        <el-form-item>
            <el-button type="primary" style="margin-top: 10px" size="small" @click="saveAreaGoodsSetting">保存设置
            </el-button>
        </el-form-item>

    </div>
</template>
<script>
    Vue.component('com-goods-area', {
        template: '#com-goods-area',
        props: {
            goods: Number,
            goods_id: String,
            goods_type: String,
        },
        data() {
            return {
                form: {
                    goods_id: 0,
                    is_alone: 0,
                    goods_type: 0,
                    price_type: 0,
                    province_price: 0,
                    city_price: 0,
                    district_price: 0,
                    town_price: 0,
                },
                loading: false,
            }
        },

        mounted() {
            if (this.goods_id == 0) {
                this.$alert('请先保存商品后重试！', '经销设置提示', {
                    confirmButtonText: '确定',
                });
                return;
            }
            this.form.goods_id = this.goods_id;
            this.form.goods_type = this.goods_type;
            this.loadData();
        },
        methods: {
            onInput(e) {
                this.$forceUpdate();
            },
            saveAreaGoodsSetting() {
                let self = this;
                request({
                    params: {
                        r: 'plugin/area/mall/goods/goods-setting',
                    },
                    method: 'post',
                    data: {
                        form: this.form
                    }
                }).then(e => {
                    self.loading = false;
                    if (e.data.code == 0) {
                        self.$message.success(e.data.msg);
                    } else {
                        self.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    console.log(e);
                });
            },

            // 获取经销设置
            loadData() {
                let self = this;
                self.loading = true;
                request({
                    params: {
                        r: 'plugin/area/mall/goods/goods-setting',
                        goods_id: self.goods_id,
                        goods_type: self.goods_type
                    },
                    method: 'get',
                    data: {}
                }).then(e => {
                    self.loading = false;
                    if (e.data.code == 0) {
                        self.form = e.data.data.setting;
                    } else {
                        self.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    console.log(e);
                });
            },

        }
    });
</script>
<style>
    .com-goods-area .box {
        border-top: 1px solid #E8EAEE;
        border-left: 1px solid #E8EAEE;
        border-right: 1px solid #E8EAEE;
        padding: 16px;
    }

    .com-goods-area .box .batch {
        margin-left: -10px;
        margin-right: 20px;
    }

    .com-goods-area .el-select .el-input {
        width: 130px;
    }

    .com-goods-area .detail {
        width: 100%;
    }

    .com-goods-area .detail .el-input-group__append {
        padding: 0 10px;
    }

    .com-goods-area input::-webkit-outer-spin-button,
    .com-goods-area input::-webkit-inner-spin-button {
        -webkit-appearance: none;
    }

    .com-goods-area input[type="number"] {
        -moz-appearance: textfield;
    }

    .com-goods-area .el-table .cell {
        text-align: center;
    }

    .com-goods-area .el-table thead.is-group th {
        background: #ffffff;
    }
</style>
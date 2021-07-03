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
<template id="com-goods-agent" v-cloak>
    <div v-loading="loading" class="com-goods-agent" v-if="goods_id!=0">
        <el-form-item label="是否开启独立经销设置" prop="is_alone">
            <el-switch :active-value="1" :inactive-value="0" v-model="form.is_alone">
            </el-switch>
        </el-form-item>

        <template v-if="form.is_alone==1">
            <el-form-item label="经销佣金类型" prop="agent_price_type">

                <el-radio-group v-model="form.agent_price_type">
                    <el-radio  :label="0">百分比</el-radio>
                    <el-radio   :label="1">固定金额</el-radio>
                </el-radio-group>
            </el-form-item>
            <el-form-item label="团队佣金配置">

                <template>
                    <!--普通经销佣金设置 -->
                    <template>
                        <el-col :span="12">
                            <el-row>
                                <el-col :span="4">
                                    <div class="grid-content bg-purple">经销商等级</div>
                                </el-col>
                                <el-col :span="8">
                                    <div class="grid-content bg-purple-light">提成</div>
                                </el-col>
                            </el-row>

                            <el-row v-for="(item,index) in form.goods_agent_level_list" style="margin-bottom:10px">
                                <el-col :span="4">
                                    {{item.level_name}}
                                </el-col>
                                <el-col :span="8">
                                    <el-input type="number"
                                              v-model="form.goods_agent_level_list[index].agent.agent_price"
                                              placeholder="输入数值" @input="onInput">
                                        <template slot="append" v-if="form.agent_price_type==0">%</template>
                                        <template slot="append" v-if="form.agent_price_type==1">元</template>
                                    </el-input>
                                </el-col>
                            </el-row>
                        </el-col>
                    </template>
                </template>
            </el-form-item>

            <el-form-item label="平级佣金类型" prop="equal_price_type">

                <el-radio-group v-model="form.equal_price_type">
                    <el-radio  :label="0">百分比</el-radio>
                    <el-radio   :label="1">固定金额</el-radio>
                </el-radio-group>

            </el-form-item>
            <el-form-item label="平级奖佣金配置">
                <template>
                    <!--普通经销佣金设置 -->
                    <template>
                        <el-col :span="12">
                            <el-row>
                                <el-col :span="4">
                                    <div class="grid-content bg-purple">经销商等级</div>
                                </el-col>
                                <el-col :span="8">
                                    <div class="grid-content bg-purple-light">提成</div>
                                </el-col>
                            </el-row>
                            <el-row v-for="(item,index) in form.goods_agent_level_list" style="margin-bottom:10px">
                                <el-col :span="4">
                                    {{item.level_name}}
                                </el-col>
                                <el-col :span="8">
                                    <el-input type="number"
                                              v-model="form.goods_agent_level_list[index].equal.equal_price"
                                              placeholder="输入数值" @input="onInput">
                                        <template slot="append" v-if="form.equal_price_type==0">%</template>
                                        <template slot="append" v-if="form.equal_price_type==1">元</template>
                                    </el-input>
                                </el-col>
                            </el-row>
                        </el-col>
                    </template>
                </template>
            </el-form-item>

            <el-form-item label="越级佣金配置">
                <template>
                    <!--普通经销佣金设置 -->
                    <template>
                        <el-col :span="12">
                            <el-row>
                                <el-col :span="4">
                                    <div class="grid-content bg-purple">经销商等级</div>
                                </el-col>
                                <el-col :span="8">
                                    <div class="grid-content bg-purple-light">提成</div>
                                </el-col>
                            </el-row>
                            <el-row v-for="(item,index) in form.goods_agent_level_list" style="margin-bottom:10px">
                                <el-col :span="4">
                                    {{item.level_name}}
                                </el-col>
                                <el-col :span="8">
                                    <el-input type="number"
                                              v-model="form.goods_agent_level_list[index].over.over_agent_price"
                                              placeholder="输入数值" @input="onInput">
                                        <template slot="append">%</template>
                                    </el-input>
                                </el-col>
                            </el-row>
                        </el-col>
                    </template>
                </template>
            </el-form-item>
        </template>

        <el-form-item>
            <el-button type="primary" style="margin-top: 10px" size="small" @click="saveAgentGoodsSetting">保存经销设置
            </el-button>
        </el-form-item>

    </div>
</template>
<script>
    Vue.component('com-goods-agent', {
        template: '#com-goods-agent',
        props: {
            goods: Number,
            goods_id: String,
            goods_type: String,
        },
        data() {
            return {
                form: {
                    goods_id: 0,
                    agent_level_list: [],
                    is_alone: 0,
                    goods_type: 0,
                    equal_price_type: 0,
                    agent_price_type: 0,
                    goods_agent_level_list: [],
                },
                level_list: [],
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

            saveAgentGoodsSetting() {
                let self = this;
                request({
                    params: {
                        r: 'plugin/agent/mall/goods/goods-setting',
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
                        r: 'plugin/agent/mall/goods/goods-setting',
                        goods_id: self.goods_id,
                        goods_type: self.goods_type
                    },
                    method: 'get',
                    data: {}
                }).then(e => {
                    self.loading = false;
                    if (e.data.code == 0) {
                        self.level_list = e.data.data.level_list;
                        self.form.is_alone = e.data.data.setting.is_alone;
                        self.form.equal_price_type = e.data.data.setting.equal_price_type;
                        self.form.agent_price_type = e.data.data.setting.agent_price_type;
                        self.form.goods_agent_level_list = e.data.data.goods_agent_level_list
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
    .com-goods-agent .box {
        border-top: 1px solid #E8EAEE;
        border-left: 1px solid #E8EAEE;
        border-right: 1px solid #E8EAEE;
        padding: 16px;
    }

    .com-goods-agent .box .batch {
        margin-left: -10px;
        margin-right: 20px;
    }

    .com-goods-agent .el-select .el-input {
        width: 130px;
    }

    .com-goods-agent .detail {
        width: 100%;
    }

    .com-goods-agent .detail .el-input-group__append {
        padding: 0 10px;
    }

    .com-goods-agent input::-webkit-outer-spin-button,
    .com-goods-agent input::-webkit-inner-spin-button {
        -webkit-appearance: none;
    }

    .com-goods-agent input[type="number"] {
        -moz-appearance: textfield;
    }

    .com-goods-agent .el-table .cell {
        text-align: center;
    }

    .com-goods-agent .el-table thead.is-group th {
        background: #ffffff;
    }
</style>
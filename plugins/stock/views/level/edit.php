<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-05-08
 * Time: 17:50
 */
Yii::$app->loadComponentView('com-dialog-select');
Yii::$app->loadComponentView('com-select-cat');
?>
<div id="app" v-cloak>
    <el-card class="box-card" v-loading="cardLoading" shadow="never" style="border:0"
             body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <el-breadcrumb separator="/">
                <el-breadcrumb-item>
                    <span style="color: #409EFF;cursor: pointer"
                          @click="$navigate({r:'mall/stock/level'})">
                        代理商等级
                    </span>
                </el-breadcrumb-item>
                <el-breadcrumb-item>编辑代理商等级</el-breadcrumb-item>
            </el-breadcrumb>
        </div>
        <div class="form-body">
            <el-form :model="ruleForm" :rules="rules" size="small" ref="ruleForm" label-width="150px">
                <el-row>
                    <el-col :span="24">
                        <el-form-item label="代理商等级权重" prop="level">
                            <el-select style="width: 100%" v-model="ruleForm.level" placeholder="请选择">
                                <el-option
                                        v-for="item in weights"
                                        :key="item.level"
                                        :label="item.name"
                                        :value="item.level"
                                        :disabled="item.disabled">
                                </el-option>
                            </el-select>
                        </el-form-item>
                        <el-form-item label="代理商等级名称" prop="name">
                            <el-input v-model="ruleForm.name" placeholder="请输入代理商等级名称"></el-input>
                        </el-form-item>

                        <el-form-item label="是否开启越级奖" prop="is_over" required>
                            <el-switch
                                    v-model="ruleForm.is_over"
                                    :active-value="1"
                                    :inactive-value="0">
                            </el-switch>
                        </el-form-item>

                        <el-form-item label="是否开启平级奖" prop="is_equal" required>
                            <el-switch
                                    v-model="ruleForm.is_equal"
                                    :active-value="1"
                                    :inactive-value="0">
                            </el-switch>
                        </el-form-item>

                        <el-form-item label="补货奖" prop="is_fill" required>
                            <el-switch
                                    v-model="ruleForm.is_fill"
                                    :active-value="1"
                                    :inactive-value="0">
                            </el-switch>
                        </el-form-item>

                        <el-form-item label="扣库存比例">
                            <el-input v-model.number="ruleForm.sub_stock_rate" type="number">
                                <template slot="append">%</template>
                            </el-input>
                        </el-form-item>
                        <el-form-item label="代理商服务费类型" prop="service_price_type" required>
                            <el-radio-group v-model="ruleForm.service_price_type">
                                <el-radio :label="0">百分比</el-radio>
                                <el-radio :label="1">固定金额</el-radio>
                            </el-radio-group>
                        </el-form-item>
                        <el-form-item label="代理商服务费">
                            <el-input v-model.number="ruleForm.service_price" type="number">
                                <template slot="append" v-if="ruleForm.service_price_type==0">%</template>
                                <template slot="append" v-if="ruleForm.service_price_type==1">元</template>
                            </el-input>
                        </el-form-item>
                        <el-form-item label="是否启用自动升级" prop="is_auto_upgrade">
                            <el-switch
                                    v-model="ruleForm.is_auto_upgrade"
                                    :active-value="1"
                                    :inactive-value="0">
                            </el-switch>
                        </el-form-item>
                        <template v-if="ruleForm.is_auto_upgrade == 1">
                            <el-form-item label="条件升级">
                                <el-form-item>
                                    <el-switch
                                            v-model="ruleForm.upgrade_type_condition"
                                            :active-value="1"
                                            :inactive-value="0"
                                            active-text="开启"
                                            inactive-text="关闭"
                                    >
                                    </el-switch>
                                </el-form-item>
                                <el-form-item v-if="ruleForm.upgrade_type_condition==1">
                                    <el-radio-group v-model="ruleForm.condition_type">
                                        <el-radio :label="1">满足其一方可升级</el-radio>
                                        <el-radio :label="2">满足所有才可升级</el-radio>
                                    </el-radio-group>
                                </el-form-item>

                                <el-checkbox-group v-model="ruleForm.checked_condition_keys" class="check-group"
                                                   v-if="ruleForm.upgrade_type_condition==1&&ruleForm.condition_type>0">
                                    <el-col :span="24">
                                        <el-checkbox :label="ruleForm.checked_condition_values[0].key">
                                            条件1：一次性订商品ID
                                        </el-checkbox>
                                        <el-input v-model="ruleForm.checked_condition_values[0].value.val"
                                                  placeholder="填写商品ID">

                                        </el-input>
                                        的数量
                                        <el-input v-model="ruleForm.checked_condition_values[0].value.val1">
                                            <template slot="append">件</template>
                                        </el-input>
                                    </el-col>
                                    <el-col :span="24">
                                        <el-checkbox :label="ruleForm.checked_condition_values[1].key">
                                            条件2：团队中代理商权重为
                                        </el-checkbox>
                                        <el-input v-model="ruleForm.checked_condition_values[1].value.val"
                                                  placeholder="填写等级权重">

                                        </el-input>
                                        <div>， 的人数满</div>
                                        <el-input v-model="ruleForm.checked_condition_values[1].value.val1">
                                            <template slot="append">个</template>
                                        </el-input>
                                    </el-col>
                                </el-checkbox-group>
                            </el-form-item>
                            <el-form-item>
                                <el-col :span="24" v-if="showConditionMsg"><span
                                            style="color: #ff4444">{{conditionMsg}}</span></el-col>
                            </el-form-item>

                        </template>
                        <el-form-item label="是否启用" prop="is_use">
                            <el-switch
                                    v-model="ruleForm.is_use"
                                    :active-value="1"
                                    :inactive-value="0">
                            </el-switch>
                        </el-form-item>
                        <el-form-item label="等级说明" prop="detail">
                            <el-input type="textagent" :rows="3" placeholder="请输入等级说明"
                                      v-model="ruleForm.detail" maxlength="80" show-word-limit></el-input>
                        </el-form-item>
                    </el-col>
                </el-row>
            </el-form>
        </div>
        <el-button class="button-item" :loading="btnLoading" type="primary" @click="submitForm('ruleForm')"
                   size="small">保存
        </el-button>
    </el-card>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                msg: 1,
                options: [],//会员等级列表
                level: 0,
                form: {
                    type: [],
                },
                checked_condition_values: [
                    {
                        key: 0,
                        value: {
                            val: '',//商品ID
                            val1: '',//数量
                        }
                    },
                    {
                        key: 1,
                        value: {
                            val: '',//等级权重
                            val1: '',//人数
                        }
                    },
                ],
                checked_condition_keys: [],
                showConditionMsg: false,
                conditionMsg: '',
                weights: [],
                ruleForm: {
                    level: '',
                    name: '',
                    status: 0,
                    is_equal: 0,
                    is_over: 0,
                    is_fill: 0,
                    condition_type: 1,
                    service_price: 0,
                    is_auto_upgrade: 1,
                    service_price_type: 0,
                    upgrade_type_condition: 0,
                    sub_stock_rate: 100,
                    rule: '',
                    checked_condition_values: [
                        {
                            key: 0,
                            value: {
                                val: '',//商品ID
                                val1: '',//数量
                            }
                        },
                        {
                            key: 1,
                            value: {
                                val: '',//等级权重
                                val1: '',//人数
                            }
                        },
                    ],
                    checked_condition_keys: [],
                },
                rules: {
                    level: [
                        {required: true, message: '请选择代理商等级', trigger: 'change'},
                    ],
                    name: [
                        {required: true, message: '请输入代理商等级名称', trigger: 'change'},
                    ],
                    is_use: [
                        {required: true, message: '请选择代理商等级状态', trigger: 'change'},
                    ],
                    detail: [
                        {required: true, message: '等级说明不能为空', trigger: 'change'},
                    ],
                },
                btnLoading: false,
                cardLoading: false,
            };
        },
        mounted() {
            if (getQuery('id')) {
                this.loadData();
            }
            this.getSetting();
        },
        methods: {
            close(e) {
                this.visible = false;
            },

            submitForm(formName) {
                let self = this;
                self.showConditionMsg = false;
                if (this.ruleForm.upgrade_type_condition == 1) {
                    if (this.ruleForm.checked_condition_keys.length == 0) {
                        this.$message.error('请完善升级条件');
                        return;
                    }
                    let checked_condition_values = this.ruleForm.checked_condition_values;
                    this.ruleForm.checked_condition_keys.forEach(v => {
                        self.conditionMsg = '请完善条件：' + (parseInt(v) + 1);
                        if (v == 0) {
                            if (!checked_condition_values[v].value.val || !checked_condition_values[v].value.val1) {
                                self.showConditionMsg = true;
                                return;
                            }
                        }
                        if (v > 0) {
                            if (!checked_condition_values[v].value.val) {
                                self.showConditionMsg = true;
                                return;
                            }
                        }
                    })
                    if (self.showConditionMsg) {
                        return
                    }
                    if (this.ruleForm.checked_condition_keys.length > 0) {
                        if (this.ruleForm.condition_type == 0) {
                            this.$message.error('请选择条件升级的方式升级类型');
                            return;
                        }
                    }
                }
                this.$refs[formName].validate((valid) => {

                    if (valid) {
                        self.btnLoading = true;
                        request({
                            params: {
                                r: 'plugin/stock/mall/level/edit'
                            },
                            method: 'post',
                            data: {
                                form: self.ruleForm,
                            }
                        }).then(e => {
                            self.btnLoading = false;
                            if (e.data.code == 0) {
                                navigateTo({
                                    r: 'plugin/stock/mall/level/index'
                                })
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
            loadData() {
                this.cardLoading = true;
                request({
                    params: {
                        r: 'plugin/stock/mall/level/edit',
                        id: getQuery('id'),
                    },
                    method: 'get'
                }).then(e => {
                    this.cardLoading = false;
                    if (e.data.code == 0) {
                        if (e.data.data.detail) {
                            this.ruleForm = e.data.data.detail;
                            this.weights = e.data.data.weights;
                            if (!e.data.data.detail.checked_condition_keys) {
                                this.ruleForm.checked_condition_keys = this.checked_condition_keys
                                this.ruleForm.checked_condition_values = this.checked_condition_values
                            }
                        }
                    } else {
                        this.$message.error(e.data.msg);
                    }
                    console.log(this.ruleForm);
                }).catch(e => {
                    console.log(e);
                });
            },
            getSetting() {
                this.cardLoading = true;
                request({
                    params: {
                        r: 'plugin/stock/mall/level/setting',
                    },
                    method: 'get',
                }).then(res => {
                    this.cardLoading = false;
                    if (res.data.code == 0) {
                        this.weights = res.data.data.weights;
                        this.level = res.data.data.level
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    this.cardLoading = false;
                    console.log(e);
                });
            }
        }
    });
</script>

<style>
    .form-body {
        padding: 20px;
        background-color: #fff;
        margin-bottom: 20px;
        padding-right: 20%;
        min-width: 900px;
    }

    .form-body .el-form-item {
        padding-right: 25%;
        min-width: 850px;
    }

    .form-button {
        margin: 0;
    }

    .form-button .el-form-item__content {
        margin-left: 0 !important;
    }

    .button-item {
        padding: 9px 25px;
    }

    .check-group {
        font-size: 14px !important;
    }

    .check-group .el-col {
        display: flex;
    }

    .check-group .el-input {
        margin: 0 5px;
    }

    .check-group .el-col .el-checkbox {
        display: flex;
        align-items: center;
    }

    .check-group .el-col .el-input {
        width: 100px;
    }

    .check-group .el-col .el-input .el-input__inner {
        height: 30px;
        width: 100px;
    }

    .el-checkbox-group .el-col {
        margin-bottom: 10px;
    }
</style>
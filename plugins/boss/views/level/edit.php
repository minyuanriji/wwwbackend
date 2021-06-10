<?php
Yii::$app->loadComponentView('com-dialog-select');
Yii::$app->loadComponentView('com-select-cat');
?>
<div id="app" v-cloak>
    <el-card class="box-card" v-loading="cardLoading" shadow="never" style="border:0"
             body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <el-breadcrumb separator="/">
                <el-breadcrumb-item>
                    <span style="color: #409EFF;cursor: pointer">
                        股东等级
                    </span>
                </el-breadcrumb-item>
                <el-breadcrumb-item>编辑股东等级</el-breadcrumb-item>
            </el-breadcrumb>
        </div>
        <div class="form-body">
            <el-form :model="ruleForm" :rules="rules" size="small" ref="ruleForm" label-width="150px">
                <el-row>
                    <el-col :span="24">
                        <!--<el-form-item label="股东等级权重" prop="level">
                            <el-select style="width: 100%" v-model="ruleForm.level" placeholder="请选择">
                                <el-option
                                        v-for="item in weights"
                                        :key="item.level"
                                        :label="item.name"
                                        :value="item.level"
                                        :disabled="item.disabled">
                                </el-option>
                            </el-select>
                        </el-form-item>-->
                        <el-form-item label="股东等级名称" prop="name">
                            <el-input v-model="ruleForm.name" placeholder="请输入股东等级名称"></el-input>
                        </el-form-item>

<!--
                        <el-form-item label="永久分红">
                            <el-input v-model.number="ruleForm.price" type="number">
                                <template slot="append">%</template>
                            </el-input>
                        </el-form-item>

                        <el-form-item label="开启额外分红" prop="is_extra">
                            <el-switch
                                    v-model="ruleForm.is_extra"
                                    :active-value="1"
                                    :inactive-value="0">
                            </el-switch>

                        </el-form-item>

                        <el-form-item label="额外分红佣金" prop="extra_price">
                            <el-input v-model.number="ruleForm.extra_price" type="number">
                                <template slot="append">%</template>
                            </el-input>
                        </el-form-item>
                        <el-form-item  label="存在额外分红的条件" prop="extra_type">
                            <el-radio-group v-model="ruleForm.extra_type">
                                <el-radio :label="1">购物升级的股东</el-radio>
                                <el-radio :label="2">条件升级的股东</el-radio>
                                <el-radio :label="3">以上条件均可</el-radio>
                            </el-radio-group>
                        </el-form-item>
                        <el-form-item label="额外分红开启上限" prop="extra_is_limit">
                            <el-switch
                                    v-model="ruleForm.extra_is_limit"
                                    :active-value="1"
                                    :inactive-value="0">
                            </el-switch>
                        </el-form-item>

                        <el-form-item label="上限金额">
                            <el-input v-model.number="ruleForm.extra_limit_price" type="number">
                                <template slot="append">元</template>
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
                                            <div style="display: flex;align-items: center">
                                                条件1： 一级客户消费满
                                                <el-input v-model="ruleForm.checked_condition_values[0].value.val">
                                                    <template slot="append">元</template>
                                                </el-input>
                                                的人数满
                                                <el-input v-model="ruleForm.checked_condition_values[0].value.val1">
                                                    <template slot="append">个</template>
                                                </el-input>
                                                ，
                                                团队客户消费满
                                                <el-input v-model="ruleForm.checked_condition_values[0].value.val2">
                                                    <template slot="append">元</template>
                                                </el-input>
                                                的人数满
                                                <el-input v-model="ruleForm.checked_condition_values[0].value.val3">
                                                    <template slot="append">个</template>
                                                </el-input>
                                            </div>

                                        </el-checkbox>
                                    </el-col>
                                    <el-col :span="24">
                                        <el-checkbox :label="ruleForm.checked_condition_values[1].key">
                                            条件2：直推
                                        </el-checkbox>
                                        <el-input v-model="ruleForm.checked_condition_values[1].value.val">
                                            <template slot="append">人</template>
                                        </el-input>
                                        ，

                                        并且一级团队中购买商品ID
                                        <el-input v-model="ruleForm.checked_condition_values[1].value.val1">
                                        </el-input>
                                        的人数
                                        <el-input v-model="ruleForm.checked_condition_values[1].value.val2">
                                            <template slot="append">个</template>
                                        </el-input>
                                    </el-col>

                                    <el-col :span="24">
                                        <el-checkbox :label="ruleForm.checked_condition_values[2].key">
                                            条件3：个人业绩
                                        </el-checkbox>
                                        <el-input v-model="ruleForm.checked_condition_values[2].value.val">
                                            <template slot="append">元</template>
                                        </el-input>
                                        ，
                                        团队业绩
                                        <el-input v-model="ruleForm.checked_condition_values[2].value.val2">
                                            <template slot="append">元</template>
                                        </el-input>
                                    </el-col>

                                    <el-col :span="24">
                                        <el-checkbox :label="ruleForm.checked_condition_values[3].key">
                                            条件4： 二级客户数量
                                        </el-checkbox>
                                        <el-input v-model="ruleForm.checked_condition_values[3].value.val">
                                            <template slot="append">个</template>
                                        </el-input>
                                        ，
                                        二级客户订单金额
                                        <el-input v-model="ruleForm.checked_condition_values[3].value.val2">
                                            <template slot="append">元</template>
                                        </el-input>
                                    </el-col>
                                </el-checkbox-group>
                            </el-form-item>
                            <el-form-item>
                                <el-col :span="24" v-if="showConditionMsg"><span
                                            style="color: #ff4444">{{conditionMsg}}</span></el-col>
                            </el-form-item>
                            <el-form-item label="购买买商品升级">
                                <el-form-item>
                                    <el-switch
                                            v-model="ruleForm.upgrade_type_goods"
                                            :active-value="1"
                                            :inactive-value="0"
                                            active-text="开启"
                                            inactive-text="关闭"
                                    >
                                    </el-switch>
                                </el-form-item>
                                <el-form-item v-if="ruleForm.upgrade_type_goods==1">
                                    <el-radio-group v-model="ruleForm.buy_goods_type">
                                        <el-radio :label="0">订单完成
                                        </el-radio>
                                        <el-radio :label="1">支付完成
                                        </el-radio>
                                    </el-radio-group>
                                </el-form-item>


                                <el-form-item v-if="ruleForm.upgrade_type_goods==1">
                                    <el-radio-group v-model="ruleForm.goods_type">
                                        <el-radio :label="1">任意商品
                                        </el-radio>
                                        <el-radio :label="2">
                                            <div style="display: inline-block;">
                                                <div flex="cross:center">
                                                    <div>指定商品</div>
                                                    <div style="margin-left: 10px;"
                                                         v-if="ruleForm.goods_type==2">
                                                        <com-dialog-select :multiple="true" @selected="goodsSelect"
                                                                           title="商品选择">
                                                            <el-button type="text">选择商品</el-button>
                                                        </com-dialog-select>
                                                    </div>
                                                </div>
                                            </div>
                                        </el-radio>
                                    </el-radio-group>
                                </el-form-item>
                            </el-form-item>
                            <el-form-item prop="goods_warehouse_ids" v-if="ruleForm.upgrade_type_goods==1">
                                <template v-if="ruleForm.goods_type==2">
                                    <div style="color: #ff4544;">最多可添加20个商品</div>
                                    <div style="max-height: 300px;overflow-y: auto">
                                        <el-table :data="ruleForm.goods_list" :show-header="false" border>
                                            <el-table-column label="">
                                                <template slot-scope="scope">
                                                    <div flex>
                                                        <div style="padding-right: 10px;flex-grow: 0">
                                                            <com-image mode="aspectFill"
                                                                       :src="scope.row.cover_pic"></com-image>
                                                        </div>
                                                        <div style="flex-grow: 1;">
                                                            <com-ellipsis :line="2">{{scope.row.name}}
                                                            </com-ellipsis>
                                                        </div>
                                                        <div style="flex-grow: 0;">
                                                            <el-button @click="deleteGoods(scope.$index)"
                                                                       type="text" circle size="mini">
                                                                <el-tooltip class="item" effect="dark"
                                                                            content="删除" placement="top">
                                                                    <img src="statics/img/mall/del.png" alt="">
                                                                </el-tooltip>
                                                            </el-button>
                                                        </div>
                                                    </div>
                                                </template>
                                            </el-table-column>
                                        </el-table>
                                    </div>
                                </template>
                            </el-form-item>
                        </template>-->
                        <!--<el-form-item label="是否启用" prop="is_enable">
                            <el-switch
                                    v-model="ruleForm.is_enable"
                                    :active-value="1"
                                    :inactive-value="0">
                            </el-switch>
                        </el-form-item>-->
                        <el-form-item label="等级说明" prop="detail">
                            <el-input type="textboss" :rows="3" placeholder="请输入等级说明"
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
/*                options: [],//会员等级列表
                level: 0,
                form: {
                    type: [],
                },
                checked_condition_values: [
                    {
                        key: 0,
                        value: {
                            val: '',//消费
                            val1: '',//人数
                            val2: '',//消费
                            val3: ''//人数
                        }
                    },
                    {
                        key: 1,
                        value: {
                            val: '',//直推人数
                            val1: '',//购买商品
                            val2: '',//人数
                        }
                    },
                    {
                        key: 2,
                        value: {
                            val: '',   //个人业绩
                            val1: '',//团队业绩
                        }
                    },
                    {
                        key: 3,
                        value: {
                            val: '',//二级客户订单数量
                            val1: '',//二级客户订单金额
                        }
                    },
                ],
                checked_condition_keys: [],
                showConditionMsg: false,
                conditionMsg: '',
                weights: [],*/
                ruleForm: {
                    // level: '',
                    name: '',
/*                    condition_type: 1,
                    price: 0,
                    is_extra:0,
                    extra_price:0,
                    extra_type: 1,
                    extra_is_limit:0,
                    extra_limit_price:0,
                    buy_goods_type: 0,
                    is_auto_upgrade: 1,
                    goods_type: 0,
                    upgrade_type_condition: 0,
                    upgrade_type_goods: 0,
                    checked_condition_values: [
                        {
                            key: 0,
                            value: {
                                val: '',//消费
                                val1: '',//人数
                                val2: '',//消费
                                val3: ''//人数
                            }
                        },
                        {
                            key: 1,
                            value: {
                                val: '',//直推人数
                                val1: '',//购买商品
                                val2: '',//人数
                            }
                        },
                        {
                            key: 2,
                            value: {
                                val: '',   //个人业绩
                                val1: '',//团队业绩
                            }
                        },
                        {
                            key: 3,
                            value: {
                                val: '',//二级客户订单数量
                                val1: '',//二级客户订单金额
                            }
                        },

                    ],
                    goods_warehouse_ids: [],
                    checked_condition_keys: [],
                    goods_list: [],*/
                },
                rules: {
                    /*level: [
                        {required: true, message: '请选择股东等级', trigger: 'change'},
                    ],*/
                    name: [
                        {required: true, message: '请输入股东等级名称', trigger: 'change'},
                    ],
                    is_enable: [
                        {required: true, message: '请选择股东等级状态', trigger: 'change'},
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
            goodsSelect(param) {
                for (let j in param) {
                    let item = param[j];
                    if (this.ruleForm.goods_warehouse_ids.length >= 20) {
                        this.$message.error('指定商品不能大于20个');
                        return;
                    }
                    let flag = true;
                    for (let i in this.ruleForm.goods_warehouse_ids) {
                        if (this.ruleForm.goods_warehouse_ids[i] == item.goods_warehouse_id) {
                            flag = false;
                            break;
                        }
                    }
                    if (flag) {
                        this.ruleForm.goods_warehouse_ids.push(item.goods_warehouse_id);
                        this.ruleForm.goods_list.push({
                            id: item.goods_warehouse_id,
                            name: item.name,
                            cover_pic: item.goodsWarehouse.cover_pic,
                        });
                    }
                }
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
                                r: 'plugin/boss/mall/level/edit'
                            },
                            method: 'post',
                            data: {
                                form: self.ruleForm,
                            }
                        }).then(e => {
                            self.btnLoading = false;
                            if (e.data.code == 0) {
                                navigateTo({
                                    r: 'plugin/boss/mall/level/index'
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
                        r: 'plugin/boss/mall/level/edit',
                        id: getQuery('id'),
                    },
                    method: 'get'
                }).then(e => {
                    this.cardLoading = false;
                    if (e.data.code == 0) {
                        if (e.data.data.detail) {
                            this.ruleForm = e.data.data.detail;
                            this.weights = e.data.data.weights;
                            if (e.data.data.detail.goods_warehouse_ids == "" || e.data.data.detail.goods_warehouse_ids == null) {
                                this.ruleForm.goods_warehouse_ids = [];
                                this.ruleForm.goods_list = [];
                            }
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
            deleteGoods(index) {
                this.ruleForm.goods_list.splice(index, 1);
                this.ruleForm.goods_warehouse_ids.splice(index, 1);
            },
            getSetting() {
                this.cardLoading = true;
                request({
                    params: {
                        r: 'plugin/boss/mall/level/setting',
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
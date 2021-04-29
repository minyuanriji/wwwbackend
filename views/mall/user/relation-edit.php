<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-05-07
 * Time: 17:26
 */
Yii::$app->loadComponentView('com-dialog-select');
Yii::$app->loadComponentView('com-select-cat');
?>
<div id="app" v-cloak>
    <el-card v-loading="loading" class="box-card" shadow="never"
             style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <span>关系设置</span>
            <div style="float: right; margin: -5px 0">
                <el-button class="button-item" :loading="btnLoading" type="primary"
                           v-show="ruleForm.use_relation==1" @click="submit('ruleForm')" size="small">
                    保存
                </el-button>
            </div>
        </div>
        <div class="form-body">

            <el-form :model="ruleForm" size="small" ref="ruleForm" label-width="150px">
                <el-card>
                    <div slot="header">关系设置</div>
                    <div>
                        <el-row>
                            <el-col :span="12">
                                <el-form-item label="启用关系链" prop="use_relation">
                                    <el-switch v-model="ruleForm.use_relation" :active-value="1"
                                               :inactive-value="0"></el-switch>
                                </el-form-item>
                                <div v-show="ruleForm.use_relation==1">
                                    <el-form-item label="获得发展下线权利条件">
                                        <el-radio-group v-model="ruleForm.get_power_way">
                                            <el-radio :label="1" border>无条件</el-radio>
<!--                                            <el-radio :label="2" border>申请</el-radio>-->
                                            <el-radio :label="3" border>或</el-radio>
                                            <el-radio :label="4" border>与</el-radio>
                                        </el-radio-group>
                                        <div>
                                            <span class="text-danger">[或]满足以下任意条件都可以升级</span><br>
                                            <span class="text-danger">[与]满足以下所有条件才可以升级</span><br>
                                        </div>
                                        <div v-if="ruleForm.get_power_way>2">
                                            <el-form-item>
                                                <el-row :gutter="20">
                                                    <el-col :span="8">
                                                        <el-switch
                                                                v-model="ruleForm.buy_num_selected"
                                                                active-text="消费次数"
                                                                :active-value="1"
                                                                :inactive-value="0">
                                                        </el-switch>
                                                    </el-col>
                                                    <el-col :span="26">
                                                        <el-input v-model="ruleForm.buy_num" type="number">
                                                            <template slot="append">次</template>
                                                        </el-input>
                                                    </el-col>
                                                </el-row>
                                            </el-form-item>
                                            <el-form-item>
                                                <el-row :gutter="20">
                                                    <el-col :span="8">
                                                        <el-switch
                                                                v-model="ruleForm.buy_price_selected"
                                                                active-text="消费金额"
                                                                :active-value="1"
                                                                :inactive-value="0">
                                                        </el-switch>

                                                    </el-col>
                                                    <el-col :span="26">
                                                        <el-input v-model="ruleForm.buy_price" type="number">
                                                            <template slot="append">元</template>
                                                        </el-input>
                                                    </el-col>
                                                </el-row>
                                            </el-form-item>
                                            <el-form-item>
                                                <el-col :span="8">
                                                    <el-switch
                                                            v-model="ruleForm.buy_goods_selected"
                                                            active-text="购买商品"
                                                            :active-value="1"
                                                            :inactive-value="0">
                                                    </el-switch>

                                                </el-col>
                                            </el-form-item>
                                            <el-form-item v-if="ruleForm.buy_goods_selected==1">
                                                <el-col :span="26">
                                                    <el-radio-group v-model="ruleForm.buy_goods_way">
                                                        <el-radio :label="1">任意商品</el-radio>
                                                        <el-radio :label="2">
                                                            <div style="display: inline-block;">
                                                                <div flex="cross:center">
                                                                    <div>指定商品</div>
                                                                    <div style="margin-left: 10px;"
                                                                         v-if="ruleForm.buy_goods_way==2">
                                                                        <com-dialog-select :multiple="true"
                                                                                           @selected="goodsSelect"
                                                                                           title="商品选择">
                                                                            <el-button type="text">选择商品</el-button>
                                                                        </com-dialog-select>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </el-radio>
                                                        <el-radio :label="3">
                                                            <div style="display: inline-block;">
                                                                <div flex="cross:center">
                                                                    <div>指定分类</div>
                                                                    <div style="margin-left: 10px;"
                                                                         v-if="ruleForm.buy_goods_way==3">
                                                                        <div style="display: inline-block">
                                                                            <el-button type="text"
                                                                                       @click="cat_show=true">选择分类
                                                                            </el-button>
                                                                        </div>
                                                                    </div>
                                                                    <com-select-cat :show="cat_show"
                                                                                    v-model="ruleForm.cat_list"
                                                                                    @cancel="cat_show=false"></com-select-cat>
                                                                </div>
                                                            </div>
                                                        </el-radio>
                                                    </el-radio-group>
                                                </el-col>
                                            </el-form-item>
                                            <el-form-item prop="goods_ids">
                                                <template v-if="ruleForm.buy_goods_way==2">
                                                    <div style="color: #ff4544;">最多可添加20个商品</div>
                                                    <div style="max-height: 300px;overflow-y: auto">
                                                        <el-table :data="ruleForm.goods_list" :show-header="false"
                                                                  border>
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
                                                                            <el-button
                                                                                    @click="deleteGoods(scope.$index)"
                                                                                    type="text" circle size="mini">
                                                                                <el-tooltip class="item" effect="dark"
                                                                                            content="删除"
                                                                                            placement="top">
                                                                                    <img src="statics/img/mall/del.png"
                                                                                         alt="">
                                                                                </el-tooltip>
                                                                            </el-button>
                                                                        </div>
                                                                    </div>
                                                                </template>
                                                            </el-table-column>
                                                        </el-table>
                                                    </div>
                                                </template>
                                                <template v-if="ruleForm.buy_goods_way == 3">
                                                    <label>已选择分类：</label>
                                                    <el-tag style="margin-right: 5px;margin-bottom: 5px;"
                                                            v-for="(item,index) in ruleForm.cat_list"
                                                            :key="item.value"
                                                            v-model="ruleForm.cat_list"
                                                            type="warning"
                                                            closable
                                                            disable-transitions
                                                            @close="deleteCat(item.value,index)">
                                                        {{item.label}}
                                                    </el-tag>
                                                </template>
                                                <el-radio-group v-model="ruleForm.buy_compute_way"
                                                                style="margin-top: 20px">
                                                    <el-radio :label="1" border>付款后</el-radio>
                                                    <el-radio :label="2" border>完成后</el-radio>
                                                </el-radio-group>
                                                <div>
                                                    <span class="text-danger">消费条件统计的方式</span><br>
                                                </div>
                                            </el-form-item>
                                        </div>
                                    </el-form-item>
                                    <el-form-item label="成为下线的条件">
                                        <el-radio-group v-model="ruleForm.become_child_way">
                                            <el-radio :label="1" border>首次点击分享链接</el-radio>
                                            <el-radio :label="2" border>首次下单</el-radio>
                                            <el-radio :label="3" border>首次付款</el-radio>
                                        </el-radio-group>
                                    </el-form-item>
                                    <el-form-item label="申请协议" prop="protocol" v-if="ruleForm.get_power_way>1">
                                        <el-input type="textarea"
                                                  :rows="4"
                                                  placeholder="申请协议"
                                                  v-model="ruleForm.protocol">
                                        </el-input>
                                    </el-form-item>
                                    <el-form-item label="用户须知" prop="notice" v-if="ruleForm.get_power_way>1">
                                        <el-input type="textarea"
                                                  :rows="4"
                                                  placeholder="用户须知"
                                                  v-model="ruleForm.notice">
                                        </el-input>
                                    </el-form-item>
                                    <el-form-item label="待审核页面背景图片" prop="status_pic_url" title="选择图片"
                                                  v-if="ruleForm.get_power_way>1">
                                        <com-attachment :multiple="false" :max="1" @selected="picUrlStatus">
                                            <el-tooltip class="item"
                                                        effect="dark"
                                                        content="建议尺寸:750 * 300"
                                                        placement="top">
                                                <el-button size="mini">选择图片</el-button>
                                            </el-tooltip>
                                        </com-attachment>
                                        <com-gallery :show-delete="true" @deleted="deleteUrlStatus"
                                                     :url="ruleForm.status_pic_url"></com-gallery>
                                    </el-form-item>
                                </div>
                            </el-col>
                        </el-row>
                    </div>
                </el-card>
                <el-card style="margin-top: 10px" shadow="never">
                    <div slot="header">提现设置</div>
                    <div>
                        <el-row>
                            <el-col :span="14">
                                <el-form-item label="开启收入提现" prop="is_income_cash">
                                    <el-switch v-model="ruleForm.is_income_cash" :active-value="1"
                                               :inactive-value="0"></el-switch>
                                </el-form-item>
                                <el-form-item label="提现方式" prop="cash_type" required>
                                    <label slot="label">提现方式
                                        <el-tooltip class="item" effect="dark"
                                                    content="自动打款支付，需要申请相应小程序的相应功能，
                                                    例如：微信需要申请企业付款到零钱功能"
                                                    placement="top">
                                            <i class="el-icon-info"></i>
                                        </el-tooltip>
                                    </label>
                                    <el-checkbox-group v-model="ruleForm.cash_type">
                                        <el-checkbox label="auto">自动打款</el-checkbox>
                                        <el-checkbox label="wechat">微信线下转账</el-checkbox>
                                        <el-checkbox label="alipay">支付宝线下转账</el-checkbox>
                                        <el-checkbox label="bank">银行卡线下转账</el-checkbox>
                                        <el-checkbox label="balance">余额提现</el-checkbox>
                                    </el-checkbox-group>
                                </el-form-item>
                                <el-form-item label="最少提现额度" prop="min_money" required>
                                    <el-input type="number" v-model.number="ruleForm.min_money">
                                        <template slot="append">元</template>
                                    </el-input>
                                </el-form-item>
                                <el-form-item label="每日提现上限" prop="day_max_money" required>
                                    <label slot="label">每日提现上限
                                        <el-tooltip class="item" effect="dark"
                                                    content="-1元表示不限制每日提现金额"
                                                    placement="top">
                                            <i class="el-icon-info"></i>
                                        </el-tooltip>
                                    </label>
                                    <el-input type="number" v-model.number="ruleForm.day_max_money">
                                        <template slot="append">元</template>
                                    </el-input>
                                </el-form-item>
                                <el-form-item label="提现手续费" prop="cash_service_fee" required>
                                    <label slot="label">提现手续费
                                        <el-tooltip class="item" effect="dark"
                                                    content="0表示不设置提现手续费"
                                                    placement="top">
                                            <i class="el-icon-info"></i>
                                        </el-tooltip>
                                    </label>
                                    <el-input type="number" v-model.number="ruleForm.cash_service_fee">
                                        <template slot="append">%</template>
                                    </el-input>
                                    <div>
                                        <span class="text-danger">提现手续费额外从提现中扣除</span><br>
                                        例如：<span style="color: #F56C6C;font-size: 12px">10%</span>的提现手续费：<br>
                                        提现<span style="color: #F56C6C;font-size: 12px">100</span>元，扣除手续费<span
                                                style="color: #F56C6C;font-size: 12px">10</span>元，
                                        实际到手<span style="color: #F56C6C;font-size: 12px">90</span>元
                                    </div>
                                </el-form-item>
                            </el-col>
                        </el-row>
                    </div>
                </el-card>

<!--                <el-form-item v-show="ruleForm.use_relation==1">-->
<!--                    <el-button class="button-item" :loading="btnLoading" type="primary"-->
<!--                               @click="submit('ruleForm')" size="small">-->
<!--                        保存-->
<!--                    </el-button>-->
<!--                </el-form-item>-->
            </el-form>

            <el-card style="margin-top: 10px" >
                <div slot="header">重建用户关系链</div>
                <div>
                    <div v-if="rebuild.status == -1 || rebuild.status == 2" >
                        <el-button @click="rebuild_link" :loading="rebuildLoading" type="danger">{{rebuildLoading ? '请稍等' : '重建用户关系链'}}</el-button>
                        <div v-if="rebuild.start != ''" style="margin-top:10px;">上一次执行时间：{{rebuild.start}}</div>
                        <div v-if="rebuild.error != ''" style="margin-top:10px;">上一次执行结果：{{rebuild.error}}</div>
                        <div v-if="rebuild.long != ''" style="margin-top:10px;">执行耗时：{{rebuild.long}}秒</div>
                    </div>
                    <div v-else style="color:gray;">
                        <div>执行状态：
                            <span v-if="rebuild.status == 0">等待执行</span>
                            <span v-if="rebuild.status == 1">运行中</span>
                        </div>
                        <div>执行开始时间：{{rebuild.start}}</div>
                        <div>已执行时间：{{rebuild.long}}秒</div>
                    </div>
                </div>
            </el-card>
        </div>
    </el-card>
</div>
<script>
    new Vue({
        el: '#app',
        data() {
            return {
                loading: false,
                btnLoading: false,
                cat_show: false,
                show_share_level: false,
                dataLoading: false,
                submitLoading: false,
                dialogInDialogVisible: false,
                name: '',
                rebuildLoading: false,
                rebuild: {
                    status: -1,
                    error: '',
                    start: '',
                    long: ''
                },
                ruleForm: {
                    use_relation: '0',
                    is_income_cash: 0,
                    cat_list: [],
                    goods_ids: [],
                    goods_list: [],
                    notice: '',
                    protocol: '',
                    status_pic_url: '',
                    buy_goods_way: 0,
                    buy_compute_way: 0,
                    become_child_way: '',
                    buy_num_selected: 0,
                    buy_num: 0,
                    buy_price_selected: 0,
                    buy_price: 0,
                    buy_goods_selected: 0,
                    cat_ids: [],
                    cash_type: ['auto'],
                    day_max_money: -1,
                    min_money: 0,
                    cash_service_fee: 0,
                },
                rules: {

                    cash_type: [
                        {message: '请选择提现方式', required: true}
                    ],
                    day_max_money: [
                        {message: '必须填写每日提现上限', required: true},
                        {type: 'number', message: '每日提现上限必须是数字', trigger: 'blur'}
                    ],
                    min_money: [
                        {message: '必须填写最少提现金额', required: true},
                        {type: 'number', message: '最少提现金额必须是数字', trigger: 'blur'}
                    ],
                    cash_service_fee: [
                        {message: '必须填写提现手续费', required: true},
                        {type: 'number', message: '提现手续费必须是数字', trigger: 'blur'}
                    ],
                }
            };
        },
        created() {
            this.loadData();
        },
        methods: {
            picUrlStatus(list) {
                this.ruleForm.status_pic_url = list[0].url;
            },
            deleteUrlStatus() {
                this.ruleForm.status_pic_url = '';
            },
            goodsSelect(param) {
                for (let j in param) {
                    let item = param[j];
                    if (this.ruleForm.goods_ids.length >= 20) {
                        this.$message.error('指定商品不能大于20个');
                        return;
                    }
                    let flag = true;
                    for (let i in this.ruleForm.goods_ids) {
                        if (this.ruleForm.goods_ids[i] == item.goods_warehouse_id) {
                            flag = false;
                            break;
                        }
                    }
                    if (flag) {
                        this.ruleForm.goods_ids.push(item.goods_warehouse_id);
                        this.ruleForm.goods_list.push({
                            id: item.goods_warehouse_id,
                            name: item.name,
                            cover_pic: item.goodsWarehouse.cover_pic,
                        });
                    }
                }
            },
            loadData() {
                this.loading = true;

                let self = this;
                request({
                    params: {
                        r: 'mall/user/relation-edit',
                    },
                }).then(e => {
                    this.loading = false;

                    if (e.data.code === 0) {
                        this.ruleForm = e.data.data.relation;
                        this.rebuild = e.data.data.rebuild;
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {
                });
            },


            deleteGoods(index) {
                this.ruleForm.goods_list.splice(index, 1);
                this.ruleForm.goods_ids.splice(index, 1);
            },
            deleteCat(value, index) {
                this.ruleForm.cat_list.splice(index, 1);
                this.ruleForm.cat_ids.splice(index, 1);
            },
            submit(formName) {
                this.$refs[formName].validate(valid => {
                    if (valid) {
                        this.submitLoading = true;

                        let cat_ids = [];
                        if (this.ruleForm.cat_list.length) {


                            this.ruleForm.cat_list.forEach(function (item) {

                                cat_ids.push(item.value);
                            })

                            this.ruleForm.cat_ids = cat_ids;
                        }


                        request({
                            params: {
                                r: 'mall/user/relation-edit',
                            },
                            method: 'post',
                            data: this.ruleForm,
                        }).then(e => {
                            this.submitLoading = false;
                            console.log(e);

                            if (e.data.code === 0) {
                                this.$message.success(e.data.msg);
                            } else {
                                this.$message.error(e.data.msg);
                            }
                        }).catch(e => {
                        });
                    } else {

                    }
                });
            },

            rebuild_link(){
                this.$confirm('执行重建用户关系链将暂停用户的一切相关操作，在任务运行结束前，用户将无法注册与登录...你确定?', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    var self = this;
                    self.rebuildLoading = true;
                    request({
                        params: {
                            r: 'mall/user/relation-rebuild',
                        },
                        method: 'post',
                    }).then(e => {
                        self.rebuildLoading = false;
                        if (e.data.code === 0) {
                            self.rebuild.status = e.data.data.status;
                            self.rebuild.start = e.data.data.start;
                            self.rebuild.long = e.data.data.start;
                            this.$message.success(e.data.msg);
                        } else {
                            this.$message.error(e.data.msg);
                        }
                    }).catch(e => {
                    });
                }).catch(() => {});
            }
        }
    });
</script>
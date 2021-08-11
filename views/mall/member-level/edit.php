<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * author: zal
 */
Yii::$app->loadComponentView('com-dialog-select');
Yii::$app->loadComponentView('com-select-cat');
Yii::$app->loadComponentView('com-rich-text')

?>
<style>
    .form-body {
        padding: 20px;
        background-color: #fff;
        margin-bottom: 20px;
        padding-right: 20%;
        min-width: 900px;
    }

    .form-body .el-form-item {
        padding-right: 50%;
        min-width: 850px;
    }

    .form-button {
        margin: 0;
    }

    .form-button .el-form-item__content {
        margin-left: 0!important;
    }

    .button-item {
        padding: 9px 25px;
    }
</style>
<div id="app" v-cloak>
    <el-card class="box-card" v-loading="cardLoading" shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <el-breadcrumb separator="/">
                <el-breadcrumb-item><span style="color: #409EFF;cursor: pointer"
                                          @click="$navigate({r:'mall/member-level/index'})">会员等级</span></el-breadcrumb-item>
                <el-breadcrumb-item>会员设置</el-breadcrumb-item>
            </el-breadcrumb>
        </div>
        <div class="form-body">
            <el-form :model="ruleForm" :rules="rules" size="small" ref="ruleForm" label-width="150px">
                <el-row>
                    <el-col :span="24">
                        <el-form-item label="会员等级" prop="level">
                            <el-select style="width: 100%" v-model="ruleForm.level" placeholder="请选择">
                                <el-option
                                        v-for="item in options"
                                        :key="item.level"
                                        :label="item.name"
                                        :value="item.level"
                                        :disabled="item.disabled">
                                </el-option>
                            </el-select>
                        </el-form-item>
                        <el-form-item label="等级名称" prop="name">
                            <el-input v-model="ruleForm.name" placeholder="请输入等级名称"></el-input>
                        </el-form-item>
                        <el-form-item prop="discount" prop="discount">
                            <template slot='label'>
                                <span>折扣</span>
                                <el-tooltip effect="dark" content="请输入0.1~10之间的数字"
                                        placement="top">
                                    <i class="el-icon-info"></i>
                                </el-tooltip>
                            </template>
                            <el-input placeholder="请输入折扣" min="0.1" type="number" v-model="ruleForm.discount">
                                <template slot="append">折</template>
                            </el-input>
                        </el-form-item>
                        <el-form-item label="会员状态" prop="status">
                            <el-switch
                                    v-model="ruleForm.status"
                                    active-value="1"
                                    inactive-value="0">
                            </el-switch>
                        </el-form-item>
                        <el-form-item label="会员图标" prop="pic_url">
                            <com-attachment :multiple="false" :max="1" @selected="picUrl">
                                <el-tooltip class="item" effect="dark" content="建议尺寸44*44" placement="top">
                                    <el-button size="mini">选择文件</el-button>
                                </el-tooltip>
                            </com-attachment>
                            <com-image width="80px" height="80px" mode="aspectFill" :src="ruleForm.pic_url"></com-image>
                        </el-form-item>
                        <el-form-item label="会员背景图" prop="bg_pic_url">
                            <com-attachment :multiple="false" :max="1" @selected="bgPicUrl">
                                <el-tooltip class="item" effect="dark" content="建议尺寸660*320" placement="top">
                                    <el-button size="mini">选择文件</el-button>
                                </el-tooltip>
                            </com-attachment>
                            <com-image width="80px" height="80px" mode="aspectFill" :src="ruleForm.bg_pic_url"></com-image>
                        </el-form-item>
                        <el-form-item label="自动升级">
                            <el-switch
                                    v-model="ruleForm.auto_update"
                                    active-value="1"
                                    inactive-value="0">
                            </el-switch>
                        </el-form-item>
                        <template v-if="ruleForm.auto_update == 1">
                    
                            <el-form-item label="累计金额升级">
                                <el-form-item>
                                    <el-switch
                                            v-model="ruleForm.upgrade_type_condition"
                                            active-value="1"
                                            inactive-value="0"
                                            active-text="开启"
                                            inactive-text="关闭"
                                    >
                                    </el-switch>
                                </el-form-item>
                                <el-form-item v-if="ruleForm.upgrade_type_condition==1">
                                    <el-input placeholder="请输入金额" min="0" type="number" v-model="ruleForm.money">
                                        <template slot="prepend">累计完成订单金额满</template>
                                        <template slot="append">元</template>
                                    </el-input>
                                </el-form-item>
                            </el-form-item>

                            <el-form-item label="购买买商品升级">
                                <el-form-item>
                                    <el-switch
                                            v-model="ruleForm.upgrade_type_goods"
                                            active-value="1"
                                            inactive-value="0"
                                            active-text="开启"
                                            inactive-text="关闭"
                                    >
                                    </el-switch>
                                </el-form-item>
                                <el-form-item v-if="ruleForm.upgrade_type_goods==1">
                                    <el-radio-group v-model="ruleForm.goods_type">
                                        <el-radio label="1">任意商品

                                        </el-radio>
                                        <el-radio label="2">
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
                                    <div style="color: #ff4544;">最多可添加150个商品</div>
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
                            <el-form-item label="订单状态升级">
                                <el-switch
                                        v-model="ruleForm.buy_compute_way"
                                        active-value="1"
                                        inactive-value="2"
                                        active-text="付款后"
                                        inactive-text="完成后"
                                >
                                </el-switch>
                            </el-form-item>
                        </template>
                        <el-form-item label="会员是否可购买">
                            <el-switch
                                    v-model="ruleForm.is_purchase"
                                    active-value="1"
                                    inactive-value="0">
                            </el-switch>
                        </el-form-item>
                        <el-form-item v-if="ruleForm.is_purchase == 1" label="购买价格" prop="price">
                            <el-input placeholder="请输入购买价格" min="0" type="number" v-model="ruleForm.price">
                                <template slot="append">元</template>
                            </el-input>
                        </el-form-item>
                    </el-col>
                </el-row>
                <el-form-item label="会员权益(多条)" prop="benefits" style="padding-right: 0">
                    <el-table
                            style="margin-bottom: 15px;"
                            v-if="ruleForm.benefits.length > 0"
                            :data="ruleForm.benefits"
                            border
                            style="width: 100%;">
                        <el-table-column
                                label="权益标题"
                                width="180">
                            <template slot-scope="scope">
                                <el-input v-model="scope.row.title" placeholder="请输入标题"></el-input>
                            </template>
                        </el-table-column>
                        <el-table-column
                                label="权益图标"
                                width="180">
                            <template slot-scope="scope">
                                <div flex="box:first">
                                    <div flex="cross:center" style="margin-right: 10px;">
                                        <com-attachment :multiple="false" :params="scope.row" :max="1"
                                                        @selected="benefitsPicUrl">
                                            <el-tooltip class="item" effect="dark" content="建议尺寸80*80" placement="top">
                                                <el-button size="mini">选择图片</el-button>
                                            </el-tooltip>
                                        </com-attachment>
                                    </div>
                                    <div>
                                        <com-image mode="aspectFill" :src="scope.row.pic_url">
                                    </div>
                                </div>
                            </template>
                        </el-table-column>
                        <el-table-column
                                label="权益内容" width="600">
                            <template slot-scope="scope">
                                <el-input type="textarea"
                                          v-model="scope.row.content"
                                          placeholder="请输入内容">
                                </el-input>
                            </template>
                        </el-table-column>
                        <el-table-column
                                label="操作">
                            <template slot-scope="scope">
                                <el-button size="small" @click="destroyRigths(scope.$index)" type="text" circle>
                                    <el-tooltip class="item" effect="dark" content="删除" placement="top">
                                        <img src="statics/img/mall/del.png" alt="">
                                    </el-tooltip>
                                </el-button>
                            </template>
                        </el-table-column>
                    </el-table>
                    <el-button type="text" @click="addBenefits">
                        <i class="el-icon-plus" style="font-weight: bolder;margin-left: 5px;"></i>
                        <span style="color: #353535;font-size: 14px">新增权益</span>
                    </el-button>
                </el-form-item>

                <el-form-item label="会员规则" prop="rules">
                    <com-rich-text style="width: 455px" v-model="ruleForm.rules"></com-rich-text>
                </el-form-item>
            </el-form>
        </div>
        <el-button class="button-item" :loading="btnLoading" type="primary" @click="store('ruleForm')" size="small">保存</el-button>
    </el-card>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                options: [],//会员等级列表
                ruleForm: {
                    pic_url: '',
                    bg_pic_url: '',
                    level: '',
                    name: '',
                    money: '',
                    discount: '',
                    status: '0',
                    price: '',
                    benefits: [],
                    is_purchase: '1',
                    auto_update: '1',//累计满金额自动升级
                    goods_type: '0',
                    upgrade_type_condition: '0',
                    upgrade_type_goods: '0',
                    rules: '',
                    goods_warehouse_ids: [],
                    checked_condition_keys: [],
                    goods_list: [],
                },
                rules: {
                    level: [
                        {required: true, message: '请选择会员等级', trigger: 'change'},
                    ],
                    name: [
                        {required: true, message: '请输入会员名称', trigger: 'change'},
                    ],
                    pic_url: [
                        {required: true, message: '请选择会员图标', trigger: 'change'},
                    ],
                    bg_pic_url: [
                        {required: true, message: '请选择会员背景图', trigger: 'change'},
                    ],
                    money: [
                        {required: true, message: '请输入会员升级条件金额', trigger: 'change'},
                    ],
                    discount: [
                        {required: true, message: '请输入会员折扣', trigger: 'change'},
                    ],
                    status: [
                        {required: true, message: '请选择会员状态', trigger: 'change'},
                    ],
                    price: [
                        {required: true, message: '请输入会员价格', trigger: 'change'},
                    ],
//                    benefits: [
//                        {required: true, message: '请添加会员权益', trigger: 'change'},
//                    ],
//                    rules: [
//                        {required: true, message: '请添加会员规则', trigger: 'change'},
//                    ],
                },
                btnLoading: false,
                cardLoading: false,
            };
        },
        methods: {
            goodsSelect(param) {
                for (let j in param) {
                    let item = param[j];
                    if (this.ruleForm.goods_warehouse_ids.length >= 150) {
                        this.$message.error('指定商品不能大于150个');
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
            store(formName) {
                let self = this;
                if (this.ruleForm.upgrade_type_condition == 1 && this.ruleForm.upgrade_type_goods == 1) {
                    this.$message.error('自动升级请二选一，不能同时选择');
                    return;
                }
                this.$refs[formName].validate((valid) => {
                    if (valid) {
                        self.btnLoading = true;
                        request({
                            params: {
                                r: 'mall/member-level/edit'
                            },
                            method: 'post',
                            data: {
                                form: self.ruleForm,
                            }
                        }).then(e => {
                            self.btnLoading = false;
                            if (e.data.code == 0) {
                                navigateTo({
                                    r: 'mall/member-level/index'
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
            getDetail() {
                let self = this;
                self.cardLoading = true;
                request({
                    params: {
                        r: 'mall/member-level/edit',
                        id: getQuery('id')
                    },
                    method: 'get',
                }).then(e => {
                    self.cardLoading = false;
                    if (e.data.code == 0) {
                        console.log(e.data.data.detail);
                        self.ruleForm = e.data.data.detail;
                        if (e.data.data.detail.goods_warehouse_ids == "" || e.data.data.detail.goods_warehouse_ids == null) {
                            this.ruleForm.goods_warehouse_ids = [];
                            this.ruleForm.goods_list = [];
                        }
                    } else {
                        self.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    console.log(e);
                });
            },
            picUrl(e) {
                if (e.length) {
                    this.ruleForm.pic_url = e[0].url;
                    this.$refs.ruleForm.validateField('pic_url');
                }
            },
            bgPicUrl(e) {
                if (e.length) {
                    this.ruleForm.bg_pic_url = e[0].url;
                    this.$refs.ruleForm.validateField('bg_pic_url');
                }
            },
            benefitsPicUrl(e, params) {
                if (e.length) {
                    params.pic_url = e[0].url;
                }
            },
            // 会员等级列表
            getOptions() {
                let self = this;
                request({
                    params: {
                        r: 'mall/member-level/options',
                    },
                    method: 'get',
                }).then(e => {
                    if (e.data.code == 0) {
                        self.options = e.data.data.list;
                    } else {
                        self.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    console.log(e);
                });
            },
            // 添加权益
            addBenefits() {
                this.ruleForm.benefits.push({
                    id: 0,
                    title: '',
                    pic_url: '',
                    content: '',
                })
            },
            // 删除权益
            destroyRigths(index) {
                this.ruleForm.benefits.splice(index, 1);
            },
            deleteGoods(index) {
                this.ruleForm.goods_list.splice(index, 1);
                this.ruleForm.goods_warehouse_ids.splice(index, 1);
            },
        },
        mounted: function () {
            if (getQuery('id')) {
                this.getDetail();
            }
            this.getOptions();
        }
    });
</script>

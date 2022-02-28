<?php
Yii::$app->loadComponentView('com-rich-text');
?>
<div id="edit_app" v-cloak>

    <el-dialog :title="dailogTitle" :visible.sync="dialogFormVisible" :before-close="cancelDialog">

        <el-tabs v-model="activeName" @tab-click="handleClick">

            <el-tab-pane label="基础信息" name="basics_info">
                <el-form :rules="rules" ref="formData" label-width="110px" :model="formData" size="small">
                    <el-form-item label="标题" prop="title">
                        <el-input v-model="formData.title"></el-input>
                    </el-form-item>
                    <el-form-item label="到期时间" prop="expired_at">
                        <el-date-picker v-model="formData.expired_at" type="date" placeholder="选择日期"></el-date-picker>
                    </el-form-item>
                    <el-form-item label="描述" prop="descript">
                        <el-input type="textarea" :rows="2" placeholder="请输入描述" v-model="formData.descript"></el-input>
                    </el-form-item>
                    <el-form-item label="封面" prop="cover_pic">
                        <com-attachment :multiple="false" :max="1" v-model="formData.cover_pic">
                            <el-tooltip class="item"
                                        effect="dark"
                                        content="建议尺寸:240 * 240"
                                        placement="top">
                                <el-button size="mini">选择文件</el-button>
                            </el-tooltip>
                        </com-attachment>
                        <com-image mode="aspectFill" width='80px' height='80px' :src="formData.cover_pic"></com-image>
                    </el-form-item>
                    <el-form-item prop="pic_url">
                        <template slot="label">
                            <span>轮播图(多张)</span>
                        </template>
                        <div class="pic-url-remark">
                            建议像素750*750,可拖拽使其改变顺序，最多支持上传5张
                        </div>
                        <div flex="dir:left">
                            <template v-if="formData.pic_url.length">
                                <draggable v-model="formData.pic_url" flex="dif:left">
                                    <div v-for="(item,index) in formData.pic_url" :key="index"
                                         style="margin-right: 20px;position: relative;cursor: move;">
                                        <com-attachment @selected="updatePicUrl" :params="{'currentIndex': index}">
                                            <com-image mode="aspectFill" width="100px" height='100px'
                                                       :src="item.pic_url">
                                            </com-image>
                                        </com-attachment>
                                        <el-button class="del-btn" size="mini" type="danger" icon="el-icon-close" circle
                                                   @click="delPic(index)"></el-button>
                                    </div>
                                </draggable>
                            </template>
                            <template v-if="formData.pic_url.length < 5">
                                <com-attachment style="margin-bottom: 10px;" :multiple="true" :max="9"
                                                @selected="picUrl">
                                    <el-tooltip class="item" effect="dark" content="建议尺寸:750 * 750" placement="top">
                                        <div flex="main:center cross:center" class="add-image-btn">
                                            + 添加图片
                                        </div>
                                    </el-tooltip>
                                </com-attachment>
                            </template>
                        </div>
                    </el-form-item>
                    <el-row>
                        <el-col :span="12">
                            <el-form-item label="库存" prop="max_stock">
                                <el-input type="number" style="width:150px" v-model="formData.max_stock">
                                    <template slot="append">件</template>
                                </el-input>
                            </el-form-item>
                        </el-col>
                        <el-col :span="12">
                            <el-form-item label="价格" prop="price">
                                <el-input type="number" style="width:150px" v-model="formData.price">
                                    <template slot="append">元</template>
                                </el-input>
                            </el-form-item>
                        </el-col>
                    </el-row>
                    <el-row>
                        <el-col :span="12">
                            <el-form-item label="利润" prop="profit_price">
                                <el-input type="number" style="width:150px" v-model="formData.profit_price">
                                    <template slot="append">元</template>
                                </el-input>
                            </el-form-item>
                        </el-col>
                        <el-col :span="12">
                            <el-form-item label="限购" prop="purchase_limits_num">
                                <el-input type="number" style="width:150px" v-model="formData.purchase_limits_num">
                                    <template slot="append">件</template>
                                </el-input>
                            </el-form-item>
                        </el-col>
                    </el-row>

                    <el-form-item label="支付模式" prop="allow_currency">
                        <el-radio v-model="formData.allow_currency" label="money">现金</el-radio>
                        <el-radio v-model="formData.allow_currency" label="integral">金豆</el-radio>
                        <el-card shadow="never" v-if="formData.allow_currency == 'money'">
                            <el-form-item label="返金豆" prop="integral_enable">
                                <el-switch
                                        v-model="formData.integral_enable"
                                        active-text="启用"
                                        inactive-text="关闭">
                                </el-switch>
                            </el-form-item>
                            <el-form-item label="数量" prop="integral_give_num" v-if="formData.integral_enable">
                                <el-input type="number" style="width:250px" v-model="formData.integral_give_num">
                                    <template slot="append">金豆券</template>
                                </el-input>
                            </el-form-item>
                            <el-form-item label="返积分" prop="score_enable">
                                <el-switch
                                        v-model="formData.score_enable"
                                        active-text="启用"
                                        inactive-text="关闭">
                                </el-switch>
                                <div v-if="formData.score_enable">
                                    <el-switch v-model="formData.score_give_settings.is_permanent" :active-value="1"
                                               :inactive-value="0" active-text="永久有效" inactive-text="限时有效"></el-switch>

                                    <div style="margin-top:10px;width:250px">
                                        <el-input type="number" :min="0"
                                                  v-model="formData.score_give_settings.integral_num" placeholder="">
                                            <template slot="append">积分券</template>
                                        </el-input>
                                    </div>

                                    <div v-if="!formData.score_give_settings.is_permanent">
                                        <div style="margin-top:10px;width:250px">
                                            <el-input type="number" :min="0"
                                                      v-model="formData.score_give_settings.period" placeholder="">
                                                <template slot="append">月</template>
                                            </el-input>
                                        </div>
                                        <div style="margin-top:10px;width:250px">
                                            <el-input type="number" v-model="formData.score_give_settings.expire"
                                                      placeholder="">
                                                <template slot="append">有效期(天)</template>
                                            </el-input>
                                        </div>
                                    </div>
                                </div>
                            </el-form-item>
                        </el-card>
                    </el-form-item>

                    <el-form-item label="拼团" prop="group_enable">
                        <el-switch
                                v-model="formData.group_enable"
                                active-text="启用"
                                inactive-text="关闭">
                        </el-switch>
                        <el-card v-if="formData.group_enable" shadow="never">
                            <el-form-item label="拼团价" prop="group_price">
                                <el-input type="number" style="width:150px" v-model="formData.group_price"></el-input>
                            </el-form-item>
                            <el-form-item label="人数" prop="group_need_num">
                                <el-input type="number" style="width:150px"
                                          v-model="formData.group_need_num"></el-input>
                            </el-form-item>
                            <el-form-item label="有效期" prop="group_expire_time">
                                <el-input placeholder="请输入内容" style="width:150px" v-model="formData.group_expire_time">
                                    <template slot="append">时</template>
                                </el-input>
                            </el-form-item>
                        </el-card>
                    </el-form-item>

                    <el-form-item label="虚拟销量" prop="virtual_sales">
                        <el-input v-model="formData.virtual_sales" type="number"></el-input>
                    </el-form-item>

                    <el-form-item label="详情" prop="detail">
                        <com-rich-text v-model="formData.detail" :value="formData.detail"></com-rich-text>
                    </el-form-item>
                </el-form>
                <div style="float: right">
                    <el-button @click="cancelDialog()">取 消</el-button>
                    <el-button :loading="btnLoading" type="primary" @click="save()">确 定</el-button>
                </div>
            </el-tab-pane>

            <el-tab-pane label="红包设置" name="shopping_setting" v-if="shoppingFormData.gift_id > 0">
                <el-form ref="shoppingFormData" :rules="shoppingFormRule" label-width="15%" :model="shoppingFormData"
                         size="small">
                    <div style="margin-left: 36px;margin-bottom: 20px">
                        赠送红包设置
                        <el-switch
                                v-model="shoppingSwitchOpen"
                                active-text="开启"
                                inactive-text="关闭">
                        </el-switch>
                    </div>

                    <div v-if="shoppingSwitchOpen">
                        <el-form-item label="赠送比例" prop="give_value">
                            <div>
                                <el-input :disabled="shoppingFormProgressData.loading" type="number" min="0" max="100"
                                          placeholder="请输入内容" v-model="shoppingFormData.give_value" style="width:300px;">
                                    <el-select v-model="shoppingFormData.give_type" slot="prepend" placeholder="请选择"
                                               style="width:110px;">
                                        <el-option label="按比例" value="1"></el-option>
                                        <el-option label="按固定值" value="2"></el-option>
                                    </el-select>
                                    <template slot="append">{{shoppingFormData.give_type == 1 ? "%" : "券"}}</template>
                                </el-input>
                            </div>
                            <el-table :data="shoppingFormData.recommender" border style="margin-top:10px;width:100%">
                                <el-table-column label="级别" width="110" align="center">
                                    <template slot-scope="scope">
                                        <span v-if="scope.row.type == 'branch_office'">分公司</span>
                                        <span v-if="scope.row.type == 'partner'">合伙人</span>
                                        <span v-if="scope.row.type == 'store'">VIP会员</span>
                                        <span v-if="scope.row.type == 'user'">普通用户</span>
                                    </template>
                                </el-table-column>
                                <el-table-column label="比例">
                                    <template slot-scope="scope">
                                        <el-input type="number" min="0" max="100" placeholder="请输入内容"
                                                  v-model="scope.row.give_value" style="width:260px;">
                                            <el-select v-model="scope.row.give_type" slot="prepend" placeholder="请选择"
                                                       style="width:110px;">
                                                <el-option label="按比例" value="1"></el-option>
                                                <el-option label="按固定值" value="2"></el-option>
                                            </el-select>
                                            <template slot="append">{{scope.row.give_type == 1 ? "%" : "券"}}</template>
                                        </el-input>
                                    </template>
                                </el-table-column>
                            </el-table>
                        </el-form-item>
                        <el-form-item label="启动日期" prop="start_at">
                            <el-date-picker :disabled="shoppingFormProgressData.loading" v-model="shoppingFormData.start_at"
                                            type="date"
                                            placeholder="选择日期"></el-date-picker>
                        </el-form-item>
                    </div>
                </el-form>
                <div style="float: right" v-if="shoppingSwitchOpen">
                    <el-button type="primary" @click="shoppingSave">确 定</el-button>
                </div>
            </el-tab-pane>

        </el-tabs>
    </el-dialog>
</div>
<script>
    function initFormData() {
        return {
            title: '',
            cover_pic: '',
            pic_url: [],
            max_stock: 0,
            expired_at: '',
            price: 0,
            profit_price: 0,
            purchase_limits_num: 1,
            descript: '',
            group_enable: false,
            group_price: 0,
            group_need_num: 0,
            group_expire_time: '',
            allow_currency: 'money',
            integral_enable: false,
            integral_give_num: 0,
            score_enable: false,
            score_give_settings: {
                is_permanent: 0,
                integral_num: 0,
                period: 1,
                period_unit: "month",
                expire: 30
            },
            detail: '',
            virtual_sales: 0,
        };
    }

    const editApp = new Vue({
        el: '#edit_app',
        data: {
            dailogTitle: '',
            dialogFormVisible: false,
            btnLoading: false,
            formData: initFormData(),
            activeName: 'basics_info',
            shoppingFormData: {
                give_type: "1",
                give_value: 0,
                start_at: '',
                recommender: [
                    {type: 'branch_office', give_type: "1", give_value: 0},
                    {type: 'partner', give_type: "1", give_value: 0},
                    {type: 'store', give_type: "1", give_value: 0},
                    {type: 'user', give_type: "1", give_value: 0}
                ],
                gift_id: 0,
            },
            shoppingFormRule: {
                give_value: [
                    {required: true, message: '赠送比例不能为空', trigger: 'change'},
                ],
                start_at: [
                    {required: true, message: '启动日期不能为空', trigger: 'change'},
                ]
            },
            shoppingFormProgressData: {
                loading: false,
            },
            rules: {
                title: [
                    {required: true, message: '标题不能为空', trigger: 'change'}
                ],
                expired_at: [
                    {required: true, message: '到期时间不能为空', trigger: 'change'}
                ],
                descript: [
                    {required: true, message: '描述不能为空', trigger: 'change'}
                ],
                cover_pic: [
                    {required: true, message: '封面不能为空', trigger: 'change'}
                ],
                pic_url: [
                    {required: true, message: '轮播图不能为空', trigger: 'change'}
                ],
                max_stock: [
                    {required: true, message: '库存不能为空', trigger: 'change'}
                ],
                price: [
                    {required: true, message: '价格不能为空', trigger: 'change'}
                ],
                profit_price: [
                    {required: true, message: '利润不能为空', trigger: 'change'}
                ],
                purchase_limits_num: [
                    {required: true, message: '限购数量不能为空', trigger: 'change'}
                ],
                allow_currency: [
                    {required: true, message: '请选择支付模式', trigger: 'change'}
                ]
            },
            savedCallFn: null,
            shoppingSwitchOpen:false,
        },
        methods: {
            shoppingSave() {
                let that = this;
                that.shoppingFormData.gift_id = that.formData.id;
                let do_request = function () {
                    that.shoppingFormProgressData.loading = true;
                    request({
                        params: {
                            r: "plugin/giftpacks/mall/giftpacks/shopping-voucher-setting"
                        },
                        method: "post",
                        data: that.shoppingFormData
                    }).then(e => {
                        that.shoppingFormProgressData.loading = false;
                        if (e.data.code == 0) {
                            that.$message.success(e.data.msg);
                            that.dialogFormVisible = false;
                        } else {
                            that.$message.error(e.data.msg);
                        }
                    }).catch(e => {
                        that.$message.error(e.data.msg);
                        that.shoppingFormProgressData.loading = true;
                    });
                };
                this.$refs['shoppingFormData'].validate((valid) => {
                    if (valid) {
                        do_request();
                    }
                });
            },
            handleClick(tab, event) {

            },
            cancelDialog() {
                this.dialogFormVisible = false;
                location.reload();
            },
            show(title, row, fn) {
                this.dailogTitle = title;
                this.dialogFormVisible = true;
                this.savedCallFn = fn;
                if (row != null) {
                    var groupEnable = row.group_enable == 1 ? true : false;
                    var integralEnable = row.integral_enable == 1 ? true : false;
                    var scoreEnable = row.score_enable == 1 ? true : false;
                    this.formData = Object.assign(initFormData(), row);
                    this.formData['group_enable'] = groupEnable;
                    this.formData['integral_enable'] = integralEnable;
                    this.formData['score_enable'] = scoreEnable;
                    this.shoppingFormData = row.shopping_voucher_setting;
                    this.shoppingFormData.gift_id = row.id;
                    this.shoppingSwitchOpen = row.shopping_voucher_setting.is_open == 1 ? true : false;
                } else {
                    this.formData = initFormData();
                }
            },
            hide() {
                this.dialogFormVisible = false;
            },
            save() {
                let formData = JSON.parse(JSON.stringify(this.formData));
                this.$refs['formData'].validate((valid) => {
                    let self = this;
                    if (valid) {
                        self.btnLoading = true;
                        formData['integral_enable'] = formData['integral_enable'] ? 1 : 0;
                        formData['group_enable'] = formData['group_enable'] ? 1 : 0;
                        formData['group_expire_time'] = 3600 * formData['group_expire_time'];
                        formData['score_enable'] = formData['score_enable'] ? 1 : 0;
                        request({
                            params: {
                                r: 'plugin/giftpacks/mall/giftpacks/edit'
                            },
                            method: 'post',
                            data: formData
                        }).then(e => {
                            self.btnLoading = false;
                            if (e.data.code == 0) {
                                self.$message.success(e.data.msg);
                                self.hide();
                                self.savedCallFn();
                                location.reload();
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
            // 商品轮播图
            picUrl(e) {
                if (e.length) {
                    let self = this;
                    e.forEach(function (item, index) {
                        if (self.formData.pic_url.length >= 5) {
                            return;
                        }
                        self.formData.pic_url.push({
                            id: item.id,
                            pic_url: item.url
                        });
                    });
                }
            },
            delPic(index) {
                this.formData.pic_url.splice(index, 1)
            },
            updatePicUrl(e, params) {
                this.formData.pic_url[params.currentIndex].id = e[0].id;
                this.formData.pic_url[params.currentIndex].pic_url = e[0].url;
            },
        }
    });
</script>
<style>
    #edit_app .add-image-btn {
        width: 100px;
        height: 100px;
        color: #419EFB;
        border: 1px solid #e2e2e2;
        cursor: pointer;
    }

    #edit_app .pic-url-remark {
        font-size: 13px;
        color: #c9c9c9;
        margin-bottom: 12px;
    }

    #edit_app .add-image-btn {
        width: 100px;
        height: 100px;
        color: #419EFB;
        border: 1px solid #e2e2e2;
        cursor: pointer;
    }

    #edit_app .del-btn {
        position: absolute;
        right: -8px;
        top: -8px;
        padding: 4px 4px;
    }

    .form-body {
        padding: 10px 20px;
        background-color: #fff;
        margin-bottom: 20px;
    }

    .form-button {
        margin: 0;
    }

    .form-button .el-form-item__content {
        margin-left: 0 !important;
    }

    .button-item {
        padding: 9px 25px;
        margin-bottom: 20px;
    }

    .open-img .el-dialog {
        margin-top: 0 !important;
    }

    .click-img {
        width: 100%;
    }

    .el-input-group__append {
        background-color: #fff
    }
</style>

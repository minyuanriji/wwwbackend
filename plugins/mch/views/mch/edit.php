<style>
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
<div id="app" v-cloak>
    <el-card class="box-card" v-loading="cardLoading" shadow="never" style="border:0"
             body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <el-breadcrumb separator="/">
                <el-breadcrumb-item>
                     <span style="color: #409EFF;cursor: pointer"
                           @click="$navigate({r:'plugin/mch/mall/mch/index'})">商户列表</span>
                </el-breadcrumb-item>
                <el-breadcrumb-item>编辑商户</el-breadcrumb-item>
            </el-breadcrumb>
        </div>
        <div class="form-body">
            <el-form :model="ruleForm" :rules="rules" ref="ruleForm" label-width="160px" size="small">
                <el-tabs v-model="activeName">
                    <el-tab-pane label="基本信息" name="basic">
                        <el-form-item label="绑定用户" prop="user_id">
                            <el-input style="display: none;" v-model="ruleForm.user_id"></el-input>
                            <el-input disabled v-model="nickname">
                                <template slot="append">
                                    <el-button @click="getUsers" type="primary">选择</el-button>
                                </template>
                            </el-input>
                        </el-form-item>
                        <el-form-item label="联系人" prop="realname">
                            <el-input v-model="ruleForm.realname"></el-input>
                        </el-form-item>
                        <el-form-item label="联系电话" prop="mobile">
                            <el-input v-model="ruleForm.mobile"></el-input>
                        </el-form-item>
                        <el-form-item label="微信号" prop="wechat">
                            <el-input v-model="ruleForm.wechat"></el-input>
                        </el-form-item>
                        <el-form-item label="所售类目" prop="mch_common_cat_id">
                            <el-select v-model="ruleForm.mch_common_cat_id" placeholder="请选择">
                                <el-option
                                        v-for="item in commonCats"
                                        :key="item.id"
                                        :label="item.name"
                                        :value="item.id">
                                </el-option>
                            </el-select>
                        </el-form-item>
                        <el-form-item label="是否开业" prop="status">
                            <el-switch
                                    v-model="ruleForm.status"
                                    active-value="1"
                                    inactive-value="0">
                            </el-switch>
                        </el-form-item>
                        <el-form-item label="好店推荐" prop="is_recommend">
                            <el-switch
                                    v-model="ruleForm.is_recommend"
                                    active-value="1"
                                    inactive-value="0">
                            </el-switch>
                        </el-form-item>

                        <el-form-item label="提现手续费" prop="transfer_rate">
                            <label slot="label">服务费
                                <el-tooltip class="item" effect="dark"
                                            content="0表示不设置服务费"
                                            placement="top">
                                    <i class="el-icon-info"></i>
                                </el-tooltip>
                            </label>
                            <el-input type="number" v-model.number="ruleForm.transfer_rate">
                                <template slot="append">%</template>
                            </el-input>
                            <div>
                                <span class="text-danger">服务费额外从提现中扣除</span><br>
                                例如：<span style="color: #F56C6C;font-size: 12px">10%</span>的提现服务费：<br>
                                提现<span style="color: #F56C6C;font-size: 12px">100</span>元，扣除服务费<span
                                        style="color: #F56C6C;font-size: 12px">10</span>元，
                                实际到手<span style="color: #F56C6C;font-size: 12px">90</span>元
                            </div>
                        </el-form-item>
                        <el-form-item label="红包券额外扣取比例" prop="transfer_rate">
                            <label slot="label">红包券扣取比例
                                <el-tooltip class="item" effect="dark"
                                            content="0表示不设置"
                                            placement="top">
                                    <i class="el-icon-info"></i>
                                </el-tooltip>
                            </label>
                            <el-input type="number" v-model.number="ruleForm.integral_fee_rate">
                                <template slot="append">%</template>
                            </el-input>
                            <div>
                                <span class="text-danger">使用红包券支付需额外支付的红包券数额</span><br>
                                例如：设置<span style="color: #F56C6C;font-size: 12px">10%</span><br>
                                使用红包券抵扣<span style="color: #F56C6C;font-size: 12px">100</span>元时，需要额外收取<span
                                        style="color: #F56C6C;font-size: 12px">10</span>的红包券，
                                最终需要<span style="color: #F56C6C;font-size: 12px">110</span>的红包券
                            </div>
                        </el-form-item>
                        <el-form-item label="排序" prop="sort">
                            <label slot="label">排序
                                <el-tooltip class="item" effect="dark"
                                            content="升序，数字越小排的越靠前"
                                            placement="top">
                                    <i class="el-icon-info"></i>
                                </el-tooltip>
                            </label>
                            <el-input type="number" v-model.number="ruleForm.sort"></el-input>
                        </el-form-item>

                        <el-form-item label="商户账号" prop="username">
                            <el-input v-model="ruleForm.username"></el-input>
                        </el-form-item>
                        <el-form-item v-if="!ruleForm.admin_id" label="商户密码" prop="password">
                            <el-input type="password" v-model="ruleForm.password"></el-input>
                        </el-form-item>

                    </el-tab-pane>
                    <el-tab-pane label="店铺信息" name="store">
                        <el-form-item label="店铺名称" prop="name">
                            <el-input v-model="ruleForm.name"></el-input>
                        </el-form-item>
                        <el-form-item label="店铺Logo" prop="logo">
                            <com-attachment :multiple="false" :max="1" v-model="ruleForm.logo">
                                <el-tooltip class="item"
                                            effect="dark"
                                            content="建议尺寸:240 * 240"
                                            placement="top">
                                    <el-button size="mini">选择文件</el-button>
                                </el-tooltip>
                            </com-attachment>
                            <com-image mode="aspectFill" width='80px' height='80px' :src="ruleForm.logo">
                            </com-image>
                        </el-form-item>
                        <el-form-item label="店铺背景图" prop="bg_pic_url">
                            <com-attachment :multiple="false" :max="1" @selected="picUrl">
                                <el-tooltip class="item"
                                            effect="dark"
                                            content="建议尺寸:750 * 200"
                                            placement="top">
                                    <el-button size="mini">选择文件</el-button>
                                </el-tooltip>
                            </com-attachment>
                            <com-image mode="aspectFill" width='80px' height='80px'
                                       :src="ruleForm.bg_pic_url && ruleForm.bg_pic_url.length ? ruleForm.bg_pic_url[0].pic_url : ''">
                            </com-image>
                        </el-form-item>
                        <el-form-item label="省市区" prop="district">
                            <el-cascader
                                    :options="district"
                                    :props="props"
                                    v-model="ruleForm.district">
                            </el-cascader>
                        </el-form-item>
                        <el-form-item label="店铺地址" prop="address">
                            <el-input v-model="ruleForm.address"></el-input>
                        </el-form-item>
                        <el-form-item label="客服电话" prop="service_mobile">
                            <el-input v-model="ruleForm.service_mobile"></el-input>
                        </el-form-item>
                    </el-tab-pane>
                    <el-tab-pane v-if="is_audit == 1 && ruleForm.form_data.length > 0" label="自定义审核资料"
                                 name="customize_review">
                        <template v-for="item in ruleForm.form_data">
                            <el-form-item v-if="item.key == 'text'" :label="item.label">
                                <el-input disabled v-model="item.value" type="text"></el-input>
                            </el-form-item>
                            <el-form-item v-if="item.key == 'textarea'" :label="item.label">
                                <el-input disabled v-model="item.value" type="textarea"></el-input>
                            </el-form-item>
                            <el-form-item v-if="item.key == 'date'" :label="item.label">
                                <el-input disabled v-model="item.value" type="text"></el-input>
                            </el-form-item>
                            <el-form-item v-if="item.key == 'time'" :label="item.label">
                                <el-input disabled v-model="item.value" type="text"></el-input>
                            </el-form-item>
                            <el-form-item v-if="item.key == 'radio'" :label="item.label">
                                <el-radio disabled v-model="item.value" :label="item.value">{{item.value}}
                                </el-radio>
                            </el-form-item>
                            <el-form-item v-if="item.key == 'checkbox'" :label="item.label">
                                <el-checkbox disabled v-for="cItem in item.value" :checked='true'>{{cItem}}
                                </el-checkbox>
                            </el-form-item>
                            <el-form-item v-if="item.key == 'img_upload'" :label="item.label">
                                <template v-if="item.img_type == 2 || Array.isArray(item.value)">
                                    <div flex="dir:left">
                                        <div v-for="imgItem in item.value" @click="dialogImgShow(imgItem)">
                                            <com-image style="margin-right: 10px;"
                                                       mode="aspectFill"
                                                       width="100px"
                                                       height='100px'
                                                       :src="imgItem">
                                            </com-image>
                                        </div>
                                    </div>
                                </template>
                                <template v-else>
                                    <div @click="dialogImgShow(item.value)">
                                        <com-image mode="aspectFill"
                                                   width="100px"
                                                   height='100px'
                                                   :src="item.value">
                                        </com-image>
                                    </div>
                                </template>
                            </el-form-item>
                        </template>
                    </el-tab-pane>
                    <el-tab-pane label="结算信息" name="settle_info">
                        <el-form-item label="银行名称" prop="settle_bank">
                            <el-input v-model="ruleForm.settle_bank"></el-input>
                        </el-form-item>
                        <el-form-item label="开户人" prop="settle_realname">
                            <el-input v-model="ruleForm.settle_realname"></el-input>
                        </el-form-item>
                        <el-form-item label="银行卡号" prop="settle_num">
                            <el-input v-model="ruleForm.settle_num"></el-input>
                        </el-form-item>
                    </el-tab-pane>
                    <el-tab-pane label="购物券赠送" name="give_shopping_voucher">
                        <el-alert title="说明：编辑完成请点击确定。" type="info" :closable="false" style="margin-bottom: 20px;color: red;"></el-alert>
                        <el-form ref="formData" :rules="formRule" :model="formData" size="small">
                            <el-form-item label="赠送比例" prop="give_value">
                                <el-input type="number" min="0" max="100" placeholder="请输入内容" v-model="formData.give_value" style="width:260px;">
                                    <template slot="append">%</template>
                                </el-input>
                            </el-form-item>
                            <el-form-item label="启动日期" prop="start_at">
                                <el-date-picker v-model="formData.start_at" type="date" placeholder="选择日期"></el-date-picker>
                            </el-form-item>
                        </el-form>
                        <div class="dialog-footer">
                            <el-button type="primary" @click="saveShoppingVoucher">确 定</el-button>
                        </div>
                    </el-tab-pane>
                    <el-tab-pane label="积分赠送" name="give_score">
                        <el-alert title="说明：编辑完成请点击确定。" type="info" :closable="false" style="margin-bottom: 20px;color: red;"></el-alert>
                        <el-form ref="scoreFormData" :rules="scoreFormRule" :model="scoreFormData" size="small">
                            <el-form-item label="返积分" prop="score_enable">
                                <el-switch
                                        v-model="scoreFormData.score_enable"
                                        active-text="启用"
                                        inactive-text="关闭">
                                </el-switch>
                                <div v-if="scoreFormData.score_enable">
                                    <el-switch v-model="scoreFormData.score_give_settings.is_permanent" :active-value="1" :inactive-value="0" active-text="永久有效" inactive-text="限时有效"></el-switch>

                                    <div style="margin-top:10px;width:250px">
                                        <el-input type="number" :min="0" :max="100" v-model="scoreFormData.rate" placeholder="">
                                            <template slot="append">%</template>
                                        </el-input>
                                    </div>

                                    <div v-if="!scoreFormData.score_give_settings.is_permanent">
                                        <div style="margin-top:10px;width:250px">
                                            <el-input type="number" :min="0" v-model="scoreFormData.score_give_settings.period" placeholder="">
                                                <template slot="append">月</template>
                                            </el-input>
                                        </div>
                                        <div style="margin-top:10px;width:250px">
                                            <el-input type="number" v-model="scoreFormData.score_give_settings.expire" placeholder="" >
                                                <template slot="append">有效期(天)</template>
                                            </el-input>
                                        </div>
                                    </div>
                                </div>
                            </el-form-item>

                            <el-form-item label="启动日期" prop="start_at">
                                <el-date-picker v-model="scoreFormData.start_at" type="date" placeholder="选择日期"></el-date-picker>
                            </el-form-item>

                        </el-form>
                        <div class="dialog-footer">
                            <el-button type="primary" @click="saveScore">确 定</el-button>
                        </div>
                    </el-tab-pane>
                </el-tabs>
            </el-form>
        </div>
        <el-button class="button-item" :loading="btnLoading" type="primary"
                   v-if="activeName == 'basic' || activeName == 'store' || activeName == 'customize_review' || activeName == 'settle_info'"                     @click="store('ruleForm')" size="small">
            保存
        </el-button>
        <el-dialog title="用户列表" :visible.sync="dialogUsersVisible">
            <template>
                <el-input clearable style="width: 260px;margin-bottom: 20px;" size="small" :disabled="ruleForm.is_all"
                          v-model="ruleForm.keyword"
                          @keyup.enter.native="getUsers"
                          @clear="getUsers"
                          placeholder="输入用户ID、昵称搜索">
                    <template slot="append">
                        <el-button size="small" @click="getUsers">搜索</el-button>
                    </template>
                </el-input>
                <el-table
                        v-loading="tableLoading"
                        :data="users"
                        tooltip-effect="dark"
                        style="width: 100%">
                    <el-table-column
                            prop="id"
                            label="ID"
                            width="80">
                    </el-table-column>
                    <el-table-column
                            label="头像">
                        <template slot-scope="scope">
                            <com-image mode="aspectFill" :src="scope.row.avatar"></com-image>
                        </template>
                    </el-table-column>
                    <el-table-column
                            prop="nickname"
                            label="昵称">
                        <template slot-scope="scope">
                            <div flex="dir:left">
                                <img src="statics/img/mall/ali.png" v-if="scope.row.userInfo.platform == 'aliapp'"
                                     alt="">
                                <img src="statics/img/mall/wx.png" v-else-if="scope.row.userInfo.platform == 'wxapp'"
                                     alt="">
                                <img src="statics/img/mall/toutiao.png"
                                     v-else-if="scope.row.userInfo.platform == 'ttapp'" alt="">
                                <img src="statics/img/mall/baidu.png" v-else-if="scope.row.userInfo.platform == 'bdapp'"
                                     alt="">
                                <span style="margin-left: 10px;">{{scope.row.nickname}}</span>
                            </div>
                        </template>
                    </el-table-column>
                    <el-table-column
                            label="操作"
                            width="120">
                        <template slot-scope="scope">
                            <el-button @click="selectUser(scope.row)" type="primary" plain size="mini">添加</el-button>
                        </template>
                    </el-table-column>
                </el-table>
            </template>
        </el-dialog>
        <el-dialog :visible.sync="dialogImg" width="45%" class="open-img">
            <img :src="click_img" class="click-img" alt="">
        </el-dialog>
    </el-card>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                scoreFormData: {
                    list: [],
                    rate:0,
                    is_all:0,
                    score_enable: false,
                    do_page: 1,
                    do_search: null,
                    give_type: 1,
                    give_value: 0,
                    start_at: '',
                    score_give_settings: {
                        is_permanent: 0,
                        integral_num: 0,
                        period: 1,
                        period_unit: "month",
                        expire: 30
                    }
                },
                scoreFormRule:{
                    start_at:[
                        {required: true, message: '启动日期不能为空', trigger: 'change'},
                    ]
                },
                formData: {
                    list: [],
                    is_all:0,
                    do_page: 1,
                    do_search: null,
                    give_type: 1,
                    give_value: 0,
                    start_at: ''
                },
                formRule:{
                    give_value: [
                        {required: true, message: '赠送比例不能为空', trigger: 'change'},
                    ],
                    start_at:[
                        {required: true, message: '启动日期不能为空', trigger: 'change'},
                    ]
                },
                ruleForm: {
                    user_id: 0,
                    status: '0',
                    is_recommend: '0',
                    realname: '',
                    review_status: '',
                    review_remark: '',
                    wechat: '',
                    mobile: '',
                    address: '',
                    mch_common_cat_id: '',
                    name: '',
                    logo: '',
                    bg_pic_url: [],
                    transfer_rate: 0,
                    account_money: 0,
                    sort: 100,
                    longitude: '',
                    latitude: '',
                    service_mobile: '',
                    district: [],
                    form_data: [],
                    integral_fee_rate: 0,
                    settle_bank: '',
                    settle_realname: '',
                    settle_num: ''
                },
                rules: {
                    user_id: [
                        {required: true, message: '小程序用户', trigger: 'change'},
                    ],
                    realname: [
                        {required: true, message: '联系人', trigger: 'change'},
                    ],
                    mobile: [
                        {required: true, message: '联系人电话', trigger: 'change'},
                    ],
                    transfer_rate: [
                        {required: true, message: '店铺手续费', trigger: 'change'},
                    ],
                    sort: [
                        {required: true, message: '店铺排序', trigger: 'change'},
                    ],
                    mch_common_cat_id: [
                        {required: true, message: '所售类目', trigger: 'change'},
                    ],
                    is_recommend: [
                        {required: true, message: '好店推荐', trigger: 'change'},
                    ],
                    status: [
                        {required: true, message: '是否开业', trigger: 'change'},
                    ],
                },
                btnLoading: false,
                tableLoading: false,
                cardLoading: false,
                commonCats: [],
                district: [],
                props: {
                    value: 'id',
                    label: 'name',
                    children: 'list'
                },
                dialogUsersVisible: false,
                users: [],
                nickname: '',//用户展示的用户名
                is_review: 0,
                is_audit: 0,//审核状态是否显示,添加商户时不显示
                navigateToUrl: 'plugin/mch/mall/mch/index',
                isNewEdit: 1,
                dialogImg: false,
                click_img: '',
                activeName: 'basic',
            };
        },
        watch: {},
        methods: {
            saveScore(){
                let that = this;
                let do_request = function(){
                    that.btnLoading = true;
                    request({
                        params: {
                            r: "plugin/integral_card/admin/from-store/batch-save"
                        },
                        method: "post",
                        data: that.scoreFormData
                    }).then(e => {
                        that.btnLoading = true;
                        if (e.data.code == 0) {
                            that.$message.success('保存成功');
                        } else {
                            that.$message.error(e.data.msg);
                        }
                    }).catch(e => {
                        that.$message.error(e.data.msg);
                        that.btnLoading = true;
                    });
                };
                this.$refs['formData'].validate((valid) => {
                    if (valid) {
                        do_request();
                    }
                });
            },
            saveShoppingVoucher(){
                let that = this;
                let do_request = function(){
                    that.btnLoading = true;
                    request({
                        params: {
                            r: "plugin/shopping_voucher/mall/from-store/batch-save"
                        },
                        method: "post",
                        data: that.formData
                    }).then(e => {
                        that.btnLoading = true;
                        if (e.data.code == 0) {
                            that.$message.success('保存成功');
                        } else {
                            that.$message.error(e.data.msg);
                        }
                    }).catch(e => {
                        that.$message.error(e.data.msg);
                        that.btnLoading = true;
                    });
                };
                this.$refs['formData'].validate((valid) => {
                    if (valid) {
                        do_request();
                    }
                });
            },
            getDetail() {
                this.cardLoading = true;
                request({
                    params: {
                        r: 'plugin/mch/mall/mch/edit',
                        id: getQuery('id'),
                    },
                }).then(e => {
                    this.cardLoading = false;
                    if (e.data.code == 0) {
                        this.review = e.data.data.review;
                        this.ruleForm = e.data.data.detail;
                        this.nickname = this.ruleForm.user.nickname;
                        this.formData.list = e.data.data.detail.give_shopping_params;
                        this.scoreFormData.list = e.data.data.detail.give_shopping_params;
                        this.formData.give_value = e.data.data.detail.give_shopping_voucher.give_value;
                        this.formData.start_at = e.data.data.detail.give_shopping_voucher.start_at;
                        this.scoreFormData.rate = e.data.data.detail.give_score.rate;
                        this.scoreFormData.start_at = e.data.data.detail.give_score.start_at;
                        this.scoreFormData.score_give_settings = e.data.data.detail.give_score.score_give_settings;
                        this.scoreFormData.score_enable = e.data.data.detail.give_score.score_enable;
                    }
                }).catch(e => {
                });
            },
            store(formName) {
                this.$refs[formName].validate((valid) => {
                    let self = this;
                    if (valid) {
                        self.btnLoading = true;
                        request({
                            params: {
                                r: 'plugin/mch/mall/mch/edit'
                            },
                            method: 'post',
                            data: {
                                form: self.ruleForm,
                                is_review: self.is_review
                            }
                        }).then(e => {
                            self.btnLoading = false;
                            if (e.data.code == 0) {
                                self.$message.success(e.data.msg);
                                navigateTo({
                                    r: self.navigateToUrl
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
            // 获取类目列表
            getCommonCatList() {
                request({
                    params: {
                        r: 'plugin/mch/mall/common-cat/all-list',
                    },
                }).then(e => {
                    if (e.data.code == 0) {
                        this.commonCats = e.data.data.list;
                    }
                }).catch(e => {
                });
            },
            addShareTitle() {
                let self = this;
                if (self.shareTitle) {
                    if (self.ruleForm.share_title.indexOf(self.shareTitle) === -1) {
                        self.ruleForm.share_title.push(self.shareTitle);
                        self.shareTitle = '';
                    }
                }
            },
            deleteShareTitle(index) {
                this.ruleForm.share_title.splice(index);
            },
            itemChecked(type) {
                if (type === 1) {
                    this.ruleForm.sponsor_num = this.isSponsorNum ? -1 : 0
                } else if (type === 2) {
                    this.ruleForm.help_num = this.isHelpNum ? -1 : 0
                } else if (type === 3) {
                    this.ruleForm.sponsor_count = this.isSponsorCount ? -1 : 0
                } else {
                }
            },
            getUsers() {
                let self = this;
                self.btnLoading = true;
                if (!self.ruleForm.is_all) {
                    self.dialogUsersVisible = true;
                    self.tableLoading = true;
                }
                request({
                    params: {
                        r: 'plugin/mch/mall/mch/search-user',
                        keyword: self.ruleForm.keyword
                    },
                    method: 'get',
                }).then(e => {
                    self.btnLoading = false;
                    self.tableLoading = false;
                    if (e.data.code == 0) {
                        self.users = e.data.data.list;
                    } else {
                        self.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    self.$message.error(e.data.msg);
                    self.btnLoading = false;
                    self.tableLoading = false;
                });
            },
            selectUser(row) {
                this.ruleForm.user_id = row.id;
                this.nickname = row.nickname;
                this.dialogUsersVisible = false;
            },
            // 店铺背景图
            picUrl(e) {
                if (e.length) {
                    let self = this;
                    self.ruleForm.bg_pic_url = [];
                    e.forEach(function (item, index) {
                        self.ruleForm.bg_pic_url.push({
                            id: item.id,
                            pic_url: item.url
                        });
                    });
                }
            },
            // 获取省市区列表
            getDistrict() {
                request({
                    params: {
                        r: 'district/index',
                        level: 3
                    },
                }).then(e => {
                    if (e.data.code == 0) {
                        this.district = e.data.data.district;
                    }
                }).catch(e => {
                });
            },
            dialogImgShow(imgUrl) {
                this.dialogImg = true;
                this.click_img = imgUrl;
            }
        },
        mounted: function () {
            if (getQuery('is_review')) {
                this.is_review = getQuery('is_review');
                this.navigateToUrl = 'plugin/mch/mall/mch/review';
            }
            if (getQuery('id')) {
                this.getDetail();
                this.isNewEdit = 0;
            }
            this.is_audit = getQuery('id') ? 1 : 0;
            this.getCommonCatList();
            this.getDistrict();
        }
    });
</script>

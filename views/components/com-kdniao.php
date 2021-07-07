<style>
    .form-body {
        background-color: #fff;
        padding: 20px 50% 20px 0;
    }

    .button-item {
        margin-top: 12px;
        padding: 9px 25px;
    }

</style>

<div id="com-kdniao" v-cloak>
    <el-card v-loading="loading" style="border:0" shadow="never" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <el-form :model="ruleForm"
                 :rules="rules"
                 ref="ruleForm"
                 label-width="170px"
                 size="small">
                <div class="title" style="margin-top: 0">
                    <span>快递鸟设置</span>
                </div>
                <div class="form-body">
                    <el-form-item class="switch" label="阿里云APPCODE" prop="express_aliapy_code">
                        <el-input v-model="ruleForm.express_aliapy_code"></el-input>
                        <span>
                            <span style="color:#666666">用户获取物流信息,</span>
                            <el-link href="https://market.aliyun.com/products/56928004/cmapi023201.html"
                                    type="primary"
                                    target="_blank"
                                    :underline="false"
                            >阿里云接口申请</el-link>
                        </span>
                    </el-form-item>
                    <el-form-item prop="kdniao_mch_id">
                        <template slot='label'>
                            <span>快递鸟商户ID</span>
                            <el-tooltip effect="dark" content="快递鸟只用于电子面单功能"
                                        placement="top">
                                <i class="el-icon-info"></i>
                            </el-tooltip>
                        </template>
                        <el-input v-model="ruleForm.kdniao_mch_id"></el-input>
                    </el-form-item>
                    <el-form-item label="快递鸟API KEY" prop="kdniao_api_key">
                        <el-input v-model="ruleForm.kdniao_api_key"></el-input>
                    </el-form-item>
                </div>
            <el-button :loading="submitLoading" class="button-item" size="small" type="primary"
                       @click="submit('ruleForm')">保存
            </el-button>
        </el-form>
    </el-card>
</div>

<script>
    Vue.component('com-kdniao', {
        template: '#com-kdniao',
        data() {
            return {
                loading: false,
                submitLoading: false,
                mall: null,
                active_setting: 'is_comment',
                activeName: 'first',
                checkList: [],
                ruleForm: {
                    name: '',
                    setting: {},
                    recharge: {},
                },
                rules: {

                },
                otherInfo: null,
            };
        },
        created() {
            this.loadData();
        },
        methods: {
            loadData() {
                this.loading = true;
                request({
                    params: {
                        r: 'mall/setting/setting',
                    },
                }).then(e => {
                    this.loading = false;
                    if (e.data.code === 0) {
                        let detail = e.data.data.detail;
                        detail.setting.latitude_longitude = detail.setting.latitude + ',' + detail.setting.longitude;

                        this.ruleForm = detail.setting;
                        this.otherInfo = detail;

                        // this.ruleForm = e.data.data.detail;
                        // let setting = this.ruleForm.setting;
                        // this.ruleForm.setting.latitude_longitude = setting.latitude + ',' + setting.longitude;
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {
                });
            },
            submit(formName) {
                this.$refs[formName].validate(valid => {
                    if (valid) {
                        let para = Object.assign({
                            setting: this.ruleForm
                        }, this.otherInfo);
                        this.submitLoading = true;
                        request({
                            params: {
                                r: 'mall/setting/setting',
                            },
                            method: 'post',
                            data: {
                                ruleForm: JSON.stringify(para)
                            },
                        }).then(e => {
                            this.submitLoading = false;
                            if (e.data.code === 0) {
                                this.$message.success(e.data.msg);
                            } else {
                                this.$message.error(e.data.msg);
                            }
                        }).catch(e => {
                        });
                    } else {
                        this.$message.error('部分参数验证不通过');
                    }
                });
            },
            handleClick(tab, event) {
                console.log(tab, event);
            },
        },
    });
</script>
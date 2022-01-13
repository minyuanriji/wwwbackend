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
}

.img-type .el-form-item__content {
    width: 100% !important;
}
</style>

<div id="app" v-cloak v-cloak>
    <el-card class="box-card" shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;"
             v-loading="cardLoading">
        <div slot="header">
            <div>
                <span>智慧经营</span>
            </div>
        </div>
        <div class="form-body">

            <el-tabs v-model="activeName">
                <el-tab-pane label="全局设置" name="first">
                    <el-form :model="DBSet.ruleForm" :rules="DBSet.rules" ref="ruleDBForm" label-width="200px" size="big">
                        <el-form-item label="数据库配置">
                            <el-card class="box-card" style="width:35%" v-if="!DBSet.is_set">
                                <el-form-item label="IP" prop="db_host" label-width="70px" required>
                                    <el-input v-model="DBSet.ruleForm.db_host" placeholder="请输入数据库的IP地址"></el-input>
                                </el-form-item>
                                <el-form-item label="端口" prop="db_port" label-width="70px" required style="margin-top:10px;">
                                    <el-input v-model="DBSet.ruleForm.db_port" placeholder="请输入数据库的连接端口"></el-input>
                                </el-form-item>
                                <el-form-item label="数据库" prop="db_name" label-width="70px" required style="margin-top:10px;">
                                    <el-input v-model="DBSet.ruleForm.db_name" placeholder="请输入数据库名称"></el-input>
                                </el-form-item>
                                <el-form-item label="字符集" prop="db_charset" label-width="70px" required style="margin-top:10px;">
                                    <el-input v-model="DBSet.ruleForm.db_charset" placeholder="请输入数据库字符集"></el-input>
                                </el-form-item>
                                <el-form-item label="用户名" prop="db_user" label-width="70px" required style="margin-top:10px;">
                                    <el-input v-model="DBSet.ruleForm.db_user" placeholder="请输入用户名"></el-input>
                                </el-form-item>
                                <el-form-item label="密码" prop="db_pass" label-width="70px" required style="margin-top:10px;">
                                    <el-input v-model="DBSet.ruleForm.db_pass" placeholder="请输入密码"></el-input>
                                </el-form-item>
                                <el-form-item label="表前缀" prop="db_tb_prefix" label-width="70px"  style="margin-top:10px;">
                                    <el-input v-model="DBSet.ruleForm.db_tb_prefix" placeholder="请输入数据表前缀"></el-input>
                                </el-form-item>
                                <el-form-item label-width="70px" required style="margin-top:10px;">
                                    <el-button :loading="DBSet.btnLoading" @click="saveDBInfo" type="primary" size="big">保存</el-button>
                                </el-form-item>
                            </el-card>
                            <template v-else>
                                <el-button @click="DBSet.is_set=false" type="default" size="big">重新设置</el-button>
                            </template>

                        </el-form-item>
                    </el-form>
                    <el-form :model="ruleForm" :rules="rules" ref="ruleForm" label-width="200px" size="big">
                        <el-form-item label="项目域名" prop="host_url">
                            <el-input v-model="ruleForm.host_url" placeholder="请输入数字经营项目域名" style="width:350px;"></el-input>
                        </el-form-item>
                        <el-form-item label="">
                            <el-button :loading="btnLoading" @click="globalSave" type="primary" size="big">保存</el-button>
                        </el-form-item>
                    </el-form>
                </el-tab-pane>
                <el-tab-pane label="微信设置" name="second">
                    <el-form :model="WechatSet.ruleForm" :rules="WechatSet.rules" ref="ruleWechatForm" label-width="200px" size="big">
                        <el-form-item label="服务商应用ID" prop="sp_appid">
                            <el-input v-model="WechatSet.ruleForm.sp_appid" placeholder="请输入" style="width:350px;"></el-input>
                        </el-form-item>
                        <el-form-item label="服务商商户号" prop="sp_mchid">
                            <el-input v-model="WechatSet.ruleForm.sp_mchid" placeholder="请输入" style="width:350px;"></el-input>
                        </el-form-item>
                        <el-form-item label="API私钥路径" prop="sp_mchid">
                            <el-input v-model="WechatSet.ruleForm.apiclient_key" placeholder="请输入" style="width:350px;"></el-input>
                        </el-form-item>
                        <el-form-item label="API证书路径" prop="apiclient_cert">
                            <el-input v-model="WechatSet.ruleForm.apiclient_cert" placeholder="请输入" style="width:350px;"></el-input>
                        </el-form-item>
                        <el-form-item label="证书序列号" prop="cert_serial">
                            <el-input v-model="WechatSet.ruleForm.cert_serial" placeholder="请输入" style="width:350px;"></el-input>
                        </el-form-item>
                        <el-form-item label="平台证书路径" prop="wechat_cert">
                            <el-input v-model="WechatSet.ruleForm.wechat_cert" placeholder="请输入" style="width:350px;"></el-input>
                        </el-form-item>
                        <el-form-item label="分账接收方类型" prop="wechat_fz_type">
                            <el-select v-model="WechatSet.ruleForm.wechat_fz_type" placeholder="请选择" style="width:150px;">
                                <el-option label="商户号" value="MERCHANT_ID"></el-option>
                            </el-select>
                        </el-form-item>
                        <el-form-item label="分账接收方" prop="wechat_fz_account">
                            <el-input v-model="WechatSet.ruleForm.wechat_fz_account" placeholder="请输入内容" style="width:350px;"></el-input>
                        </el-form-item>
                        <el-form-item label="分账个人接收方姓名" prop="wechat_fz_name">
                            <el-input v-model="WechatSet.ruleForm.wechat_fz_name" placeholder="请输入内容" style="width:350px;"></el-input>
                        </el-form-item>
                        <el-form-item>
                            <el-button :loading="WechatSet.btnLoading" @click="saveWechatInfo" type="primary" size="big">保存</el-button>
                        </el-form-item>
                    </el-form>
                </el-tab-pane>
                <el-tab-pane label="支付宝设置" name="third">
                    <el-form :model="AliSet.ruleForm" :rules="AliSet.rules" ref="ruleAliForm" label-width="200px" size="big">
                        <el-form-item label="分账接收方类型" prop="ali_fz_type">
                            <el-select v-model="AliSet.ruleForm.ali_fz_type" placeholder="请选择" style="width:150px;">
                                <el-option label="用户号" value="userId"></el-option>
                            </el-select>
                        </el-form-item>
                        <el-form-item label="分账接收方" prop="ali_fz_account">
                            <el-input v-model="AliSet.ruleForm.ali_fz_account" placeholder="请输入内容" style="width:350px;"></el-input>
                        </el-form-item>
                        <el-form-item>
                            <el-button :loading="AliSet.btnLoading" @click="saveAliInfo" type="primary" size="big">保存</el-button>
                        </el-form-item>
                    </el-form>
                </el-tab-pane>
            </el-tabs>
        </div>
    </el-card>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                activeName: "first",
                ruleForm:{
                    host_url: ''
                },
                rules: {
                    host_url: [
                        {required: true, message: '请输入数字经营项目域名', trigger: 'change'},
                    ],
                },
                btnLoading: false,
                DBSet:{
                    is_set: true,
                    ruleForm:{
                        db_host: '',
                        db_port: '',
                        db_name: '',
                        db_charset: '',
                        db_user: '',
                        db_pass: '',
                        db_tb_prefix: ''
                    },
                    rules: {
                        db_host: [
                            {required: true, message: '请输入数据库IP地址', trigger: 'change'},
                        ],
                        db_port: [
                            {required: true, message: '请输入数据库的连接端口', trigger: 'change'},
                        ],
                        db_name: [
                            {required: true, message: '请输入数据库名称', trigger: 'change'},
                        ],
                        db_charset: [
                            {required: true, message: '请输入数据库字符集', trigger: 'change'},
                        ],
                        db_user: [
                            {required: true, message: '请输入用户名', trigger: 'change'},
                        ],
                        db_pass: [
                            {required: true, message: '请输入密码', trigger: 'change'},
                        ]
                    },
                    btnLoading: false
                },
                WechatSet:{
                    ruleForm: {
                        sp_appid: '',
                        sp_mchid: '',
                        apiclient_key: '',
                        apiclient_cert: '',
                        cert_serial: '',
                        wechat_cert: '',
                        wechat_fz_account: '',
                        wechat_fz_type: 'MERCHANT_ID',
                        wechat_fz_name: '',
                    },
                    rules: {
                        sp_appid: [
                            {required: true, message: '请输入服务商应用ID', trigger: 'change'},
                        ],
                        sp_mchid: [
                            {required: true, message: '请输入服务商商户号', trigger: 'change'},
                        ],
                        apiclient_key: [
                            {required: true, message: '请输入API私钥路径', trigger: 'change'},
                        ],
                        apiclient_cert: [
                            {required: true, message: '请输入API证书路径', trigger: 'change'},
                        ],
                        cert_serial: [
                            {required: true, message: '请输入证书序列号', trigger: 'change'},
                        ],
                        wechat_cert: [
                            {required: true, message: '请输入平台证书路径', trigger: 'change'},
                        ],
                        wechat_fz_type: [
                            {required: true, message: '请选择分账接收方类型', trigger: 'change'},
                        ],
                        wechat_fz_account: [
                            {required: true, message: '请输入分账接收方', trigger: 'change'},
                        ],
                        wechat_fz_name: [
                            {required: true, message: '请输入分账接收方姓名', trigger: 'change'},
                        ]
                    },
                    btnLoading: false
                },
                AliSet:{
                    ruleForm: {
                        ali_fz_account: '',
                        ali_fz_type: 'userId'
                    },
                    rules: {
                        ali_fz_type: [
                            {required: true, message: '请选择分账接收方类型', trigger: 'change'},
                        ],
                        ali_fz_account: [
                            {required: true, message: '请输入分账接收方', trigger: 'change'},
                        ],
                    },
                    btnLoading: false
                },
                cardLoading: false
            };
        },
        mounted: function () {
            this.getSetting();
        },
        methods: {
            globalSave(){
                let that = this;
                this.$refs['ruleForm'].validate((valid) => {
                    if (valid) {
                        that.btnLoading = true;
                        request({
                            params: {
                                r: 'plugin/smart_shop/mall/setting/save'
                            },
                            method: 'post',
                            data: {form:that.ruleForm}
                        }).then(e => {
                            that.btnLoading = false;
                            if (e.data.code == 0) {
                                that.$message.success("保存成功");
                            } else {
                                that.$message.error(e.data.msg);
                            }
                        }).catch(e => {
                            that.$message.error("请求失败");
                            that.btnLoading = false;
                        });
                    }
                });
            },
            saveDBInfo(){
                let that = this;
                this.$refs['ruleDBForm'].validate((valid) => {
                    if (valid) {
                        that.DBSet.btnLoading = true;
                        request({
                            params: {
                                r: 'plugin/smart_shop/mall/setting/save'
                            },
                            method: 'post',
                            data: {form:that.DBSet.ruleForm}
                        }).then(e => {
                            that.DBSet.btnLoading = false;
                            if (e.data.code == 0) {
                                that.DBSet.is_set = true;
                            } else {
                                that.$message.error(e.data.msg);
                            }
                        }).catch(e => {
                            that.$message.error("请求失败");
                            that.DBSet.btnLoading = false;
                        });
                    }
                });
            },
            setDBInfo(set){
                //this.DBSet.ruleForm = Object.assign(this.DBSet.ruleForm, set);
                this.DBSet.is_set = (typeof set['db_host'] != "undefined" && set.db_host.length > 0) ? true : false;
            },
            saveWechatInfo(){
                let that = this;
                this.$refs['ruleWechatForm'].validate((valid) => {
                    if (valid) {
                        that.WechatSet.btnLoading = true;
                        request({
                            params: {
                                r: 'plugin/smart_shop/mall/setting/save'
                            },
                            method: 'post',
                            data: {wechat: 1, form:that.WechatSet.ruleForm}
                        }).then(e => {
                            that.WechatSet.btnLoading = false;
                            if (e.data.code == 0) {
                                that.$message.success("保存成功");
                            } else {
                                that.$message.error(e.data.msg);
                            }
                        }).catch(e => {
                            that.$message.error("请求失败");
                            that.WechatSet.btnLoading = false;
                        });
                    }
                });
            },
            setWechatInfo(set){
                let key;
                for(key in set){
                    if(typeof this.WechatSet.ruleForm[key] != "undefined"){
                        this.WechatSet.ruleForm[key] = set[key];
                    }
                }
                //this.WechatSet.ruleForm.wechat_fz_account = set.wechat_fz_account;
                //this.WechatSet.ruleForm.wechat_fz_type = set.wechat_fz_type;
            },
            saveAliInfo(){
                let that = this;
                this.$refs['ruleAliForm'].validate((valid) => {
                    if (valid) {
                        that.AliSet.btnLoading = true;
                        request({
                            params: {
                                r: 'plugin/smart_shop/mall/setting/save'
                            },
                            method: 'post',
                            data: {form:that.AliSet.ruleForm}
                        }).then(e => {
                            that.AliSet.btnLoading = false;
                            if (e.data.code == 0) {
                                that.$message.success("保存成功");
                            } else {
                                that.$message.error(e.data.msg);
                            }
                        }).catch(e => {
                            that.$message.error("请求失败");
                            that.AliSet.btnLoading = false;
                        });
                    }
                });
            },
            setAliInfo(set){
                this.AliSet.ruleForm.ali_fz_account = set.ali_fz_account;
                this.AliSet.ruleForm.ali_fz_type = set.ali_fz_type;
            },
            getSetting(){
                request({
                    params: {
                        r: 'plugin/smart_shop/mall/setting/index',
                    },
                }).then(e => {
                    if (e.data.code == 0) {
                        this.setDBInfo(e.data.data.setting);
                        this.setWechatInfo(e.data.data.setting);
                        this.setAliInfo(e.data.data.setting);
                    }
                }).catch(e => {
                });
            }
        }
    });
</script>

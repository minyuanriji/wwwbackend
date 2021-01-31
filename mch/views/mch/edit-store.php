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
                <el-breadcrumb-item>店铺信息</el-breadcrumb-item>
            </el-breadcrumb>
        </div>
        <div class="form-body">
            <el-form :model="ruleForm" :rules="rules" ref="ruleForm" label-width="160px" size="small">
                <el-row>
                    <el-card shadow="never" style="margin-bottom: 20px">
                        <el-col :span="12">
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
                        </el-col>
                    </el-card>

                </el-row>
            </el-form>
        </div>
        <el-button class="button-item" :loading="btnLoading" type="primary" @click="store('ruleForm')" size="small">保存</el-button>
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
                ruleForm: {
                    address: '',
                    name: '',
                    logo: '',
                    bg_pic_url: [],
                    longitude: '',
                    latitude: '',
                    service_mobile: '',
                    district: [],
                    form_data: [],
                },
                rules: {
                    name: [
                        {required: true, message: '店铺名称', trigger: 'change'},
                    ],
                    logo: [
                        {required: true, message: '店铺Logo', trigger: 'change'},
                    ],
                    bg_pic_url: [
                        {required: true, message: '店铺背景图', trigger: 'change'},
                    ],
                    address: [
                        {required: true, message: '店铺详细地址', trigger: 'change'},
                    ],
                    district: [
                        {required: true, message: '店铺省市区', trigger: 'change'},
                    ],
                    service_mobile: [
                        {required: true, message: '客服电话', trigger: 'change'},
                    ]
                },
                btnLoading: false,
                tableLoading: false,
                cardLoading: false,
                dialogImg: false,
                click_img: '',
                district: [],
                props: {
                    value: 'id',
                    label: 'name',
                    children: 'list'
                }
            };
        },
        methods: {
            getDetail() {
                this.cardLoading = true;
                request({
                    params: {
                        r: 'mch/mch/edit-store'
                    },
                }).then(e => {
                    this.cardLoading = false;
                    if (e.data.code == 0) {
                        this.ruleForm = e.data.data.detail;
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
                                r: 'mch/mch/edit-store'
                            },
                            method: 'post',
                            data: {
                                form: self.ruleForm
                            }
                        }).then(e => {
                            self.btnLoading = false;
                            if (e.data.code == 0) {
                                self.$message.success(e.data.msg);
                                navigateTo({
                                    r: 'mch/mch/edit-store'
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
            this.getDetail();
            this.getDistrict();
        }
    });
</script>

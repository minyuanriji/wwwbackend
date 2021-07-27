<style>
    .form-body {
        padding: 20px 0;
        background-color: #fff;
        margin-bottom: 20px;
        padding-right: 50%;
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
    .text {
        cursor: pointer;
        color: #419EFB;
    }
</style>

<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" class="box-card" body-style="background-color: #f3f3f3;padding: 10px 0 0;"
             class="box-card" v-loading="cardLoading">
        <div slot="header">
            <div flex="cross:center box:first">
                <div><span @click="$navigate({r:'plugin/mch/mall/apps/index'})" class="text">APP管理</span>/发布版本</div>
                <div flex="dir:right">
                    <div>
                        <el-button @click="store('ruleForm')" class="button-item" :loading="btnLoading" type="primary" size="small">保存</el-button>
                    </div>
                </div>
            </div>
        </div>
        <div class="form-body">
            <el-form :model="ruleForm" :rules="rules" size="small" ref="ruleForm" label-width="120px">
                <el-form-item label="版本号" prop="version_code">
                    <template slot='label'>
                        <span>版本号</span>
                        <el-tooltip effect="dark" content="大于0的整数" placement="top">
                            <i class="el-icon-info"></i>
                        </el-tooltip>
                    </template>
                    <el-input type="number" v-model="ruleForm.version_code"></el-input>
                </el-form-item>
                <el-form-item label="版本名称" prop="version_name">
                    <template slot='label'>
                        <span>版本名称</span>
                        <el-tooltip effect="dark" content="例如：v1.0.0" placement="top">
                            <i class="el-icon-info"></i>
                        </el-tooltip>
                    </template>
                    <el-input v-model="ruleForm.version_name"></el-input>
                </el-form-item>
                <el-form-item label="APP文件" prop="download_link">
                    <el-input v-if="ruleForm.download_link != ''" placeholder="" v-model="ruleForm.download_link" :disabled="true"></el-input>
                    <el-upload
                            class="upload-demo"
                            action="?r=plugin/mch/mall/apps/upload"
                            :on-preview="handlePreview"
                            :on-remove="handleRemove"
                            :before-upload="beforeUpload"
                            :before-remove="beforeRemove"
                            :on-success="handleSuccess"
                            :limit="1"
                            :on-exceed="handleExceed"
                            :file-list="fileList">
                        <el-button size="small" type="primary">点击上传</el-button>
                    </el-upload>

                </el-form-item>
                <el-form-item label="更新内容" prop="content">
                    <el-input type="textarea" :rows="2" v-model="ruleForm.content"></el-input>
                </el-form-item>

            </el-form>
        </div>
    </el-card>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                fileList: [],
                ruleForm: {
                    version_code: '',
                    version_name: '',
                    download_link: '',
                    content: ''
                },
                rules: {
                    version_code: [
                        {required: true, message: '请输入版本号', trigger: 'change'},
                    ],
                    version_name: [
                        {required: true, message: '请输入版本名称', trigger: 'change'},
                    ],
                    download_link: [
                        {required: true, message: '请上传app文件', trigger: 'change'},
                    ]
                },
                btnLoading: false,
                cardLoading: false,
            };
        },
        methods: {
            beforeUpload(){
                this.ruleForm.download_link = '';
            },
            handleRemove(file, fileList) {
                this.ruleForm.download_link = '';
            },
            handlePreview(file) {},
            handleExceed(files, fileList) {},
            beforeRemove(file, fileList) {
                return this.$confirm(`确定移除 ${ file.name }？`);
            },
            handleSuccess(res, file, fileList){
                this.ruleForm.download_link = res.data.url;
            },
            store(formName) {
                this.$refs[formName].validate((valid) => {
                    let self = this;
                    if (valid) {
                        self.btnLoading = true;
                        request({
                            params: {
                                r: 'plugin/mch/mall/apps/edit',
                                id: getQuery('id'),
                                platform: getQuery('platform')
                            },
                            method: 'post',
                            data: {
                                form: self.ruleForm,
                            }
                        }).then(e => {
                            self.btnLoading = false;
                            if (e.data.code == 0) {
                                self.$message.success(e.data.msg);
                                navigateTo({
                                    r: 'plugin/mch/mall/apps/index'
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
                        r: 'plugin/mch/mall/apps/edit',
                        id: getQuery('id')
                    },
                    method: 'get',
                }).then(e => {
                    self.cardLoading = false;
                    if (e.data.code == 0) {
                        self.ruleForm = e.data.data;
                    } else {
                        self.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    console.log(e);
                });
            },
        },
        mounted: function () {
            if (getQuery('id')) {
                this.getDetail();
            }
        }
    });
</script>

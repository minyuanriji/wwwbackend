
<div id="app" v-cloak>
    <el-card class="box-card" v-loading="cardLoading" shadow="never" style="border:0"
             body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <el-breadcrumb separator="/">
                <el-breadcrumb-item>
                    <span style="color: #409EFF;cursor: pointer"
                          @click="$navigate({r:'plugin/perform_distribution/mall/level/index'})">
                        等级设置
                    </span>
                </el-breadcrumb-item>
                <el-breadcrumb-item>编辑等级</el-breadcrumb-item>
            </el-breadcrumb>
        </div>
        <div class="form-body">
            <el-form :model="ruleForm" :rules="rules" size="small" ref="ruleForm" label-width="150px">
                <el-row>
                    <el-col :span="24">
                        <el-form-item label="等级权重" prop="level">
                            <el-select v-model="ruleForm.level" placeholder="请选择" style="width:300px;">
                                <el-option v-for="level in 9" :value="level" :label="'等级'+level"></el-option>
                            </el-select>
                        </el-form-item>
                        <el-form-item label="等级名称" prop="name">
                            <el-input v-model="ruleForm.name" placeholder="请输入代理商等级名称"  style="width:300px;"></el-input>
                        </el-form-item>
                        <el-form-item label=" ">
                            <el-button class="button-item" :loading="btnLoading" type="primary" @click="submitForm('ruleForm')" size="small">保存</el-button>
                        </el-form-item>
                    </el-col>
                </el-row>
            </el-form>
        </div>

    </el-card>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                ruleForm: {
                    level: '',
                    name: ''
                },
                rules: {
                    level: [
                        {required: true, message: '请选择等级权重', trigger: 'change'},
                    ],
                    name: [
                        {required: true, message: '请输入等级名称', trigger: 'change'},
                    ]
                },
                btnLoading: false,
                cardLoading: false,
            };
        },
        mounted() {
            if (getQuery('id')) {
                this.loadData();
            }
        },
        methods: {
            submitForm(formName) {
                let self = this;
                this.$refs[formName].validate((valid) => {
                    if (valid) {
                        self.btnLoading = true;
                        request({
                            params: {
                                r: 'plugin/perform_distribution/mall/level/edit'
                            },
                            method: 'post',
                            data: self.ruleForm
                        }).then(e => {
                            self.btnLoading = false;
                            if (e.data.code == 0) {
                                navigateTo({
                                    r: 'plugin/perform_distribution/mall/level/index'
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
                        r: 'plugin/perform_distribution/mall/level/edit',
                        id: getQuery('id'),
                    },
                    method: 'get'
                }).then(e => {
                    this.cardLoading = false;
                    if (e.data.code == 0) {
                        if (e.data.data.detail) {
                            this.ruleForm = e.data.data.detail;
                        }
                    } else {
                        this.$message.error(e.data.msg);
                    }
                    console.log(this.ruleForm);
                }).catch(e => {
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
<?php

?>
<div id="app" v-cloak>
    <el-card class="box-card" v-loading="loading" shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <div>
                <span>基础设置</span>
            </div>
        </div>
        <div class="form_box">
            <el-form :model="ruleForm" size="small" ref="ruleForm" label-width="150px">
                <el-card class="box-card" shadow="never">
                    <el-form-item label="描述">
                        <el-input type="textarea" :rows="4" placeholder="请输入内容" v-model="ruleForm.descript" style="width:500px;"></el-input>
                    </el-form-item>
                </el-card>
            </el-form>
            <el-button @click="update('ruleForm')" :loading="btnLoading" class="button-item" type="primary" style="margin-top: 24px;"size="small">保存</el-button>
        </div>

    </el-card>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                loading: false,
                btnLoading: false,
                ruleForm: {
                    descript: ""
                }
            }
        },
        mounted: function () {
            this.loadData();
        },
        methods: {
            loadData() {
                this.loading = true;
                request({
                    params: {
                        r: 'plugin/oil/mall/oil/setting',
                    },
                    method: 'get'
                }).then(e => {
                    this.loading = false;
                    if (e.data.code == 0) {

                    }
                }).catch(e => {
                    this.loading = false;
                })
            },
            update(formName) {
                this.$refs[formName].validate((valid) => {
                    if (valid) {
                        this.btnLoading = true;
                        request({
                            params: {
                                r: 'plugin/oil/mall/oil/setting',
                            },
                            method: 'post',
                            data: {settings:this.ruleForm}
                        }).then(e => {
                            this.btnLoading = false;
                            if (e.data.code == 0) {
                                this.$message.success(e.data.msg);
                            } else {
                                this.$message.error(e.data.msg);
                            }
                        }).catch(e => {
                            this.$message.error(e);
                            this.btnLoading = false;
                        })
                    } else {
                        this.btnLoading = false;
                        console.log('error submit!!');
                        return false;
                    }
                });
            },
        }
    });
</script>
<style>
    .form_box {
        background-color: #f3f3f3;
        padding: 0 0 20px;
    }

    .button-item {
        margin-top: 12px;
        padding: 9px 25px;
    }

    .el-input-group__append {
        background-color: #fff;
        color: #353535;
    }
</style>
<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-05-08
 * Time: 16:11
 */
Yii::$app->loadComponentView('com-dialog-select');
Yii::$app->loadComponentView('com-select-cat');
Yii::$app->loadComponentView('wechat/com-tags-edit');
?>
<div id="app" v-cloak>
    <el-card class="box-card" v-loading="loading" shadow="never" style="border:0"
             body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <div>
                <span>标签库(名片前台的客户详情页面中的自动标签为空时，随机调用该标签库8个标签)</span>
            </div>
        </div>
        <div class="form_box">
            <el-form :model="ruleForm" :rules="rules" size="small" ref="ruleForm" label-width="150px" @submit.native.prevent>
                <el-card style="margin-top: 10px" shadow="never">

                    <el-col :span="14">
                            <el-form-item label="标签名" prop="tag_list" style="display: flex;align-items: center" content="输入后，按回车可以添加多个标签">
                                <com-tags-edit v-model.stop="ruleForm.tag_list" style="margin: 0">输入后，按回车可以添加多个标签</com-tags-edit>
                            </el-form-item>
                    </el-col>
                </el-card>
            </el-form>
            <el-button :loading="btnLoading" class="button-item" type="primary" style="margin-top: 24px;"
                       @click="store('ruleForm')" size="small">保存
            </el-button>
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
                cat_show: false,
                show_share_level: false,
                ruleForm: {
                    company_name: "",
                    card_token: "",
                    company_logo:"",
                    company_address:"",
                    companys: "",
                    tag_list: ""
                },
                rules: {

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
                        r: 'plugin/business_card/mall/setting/tag',
                    },
                    method: 'get'
                }).then(e => {
                    this.loading = false;
                    if (e.data.code == 0) {
                        this.ruleForm = Object.assign(this.ruleForm, e.data.data.setting);

                        this.ruleForm.tag_list = e.data.data.setting;
                    }
                }).catch(e => {
                    this.loading = false;
                })
            },
            store(formName) {
                this.$refs[formName].validate((valid) => {
                    if (valid) {
                        this.btnLoading = true;
                        request({
                            params: {
                                r: 'plugin/business_card/mall/setting/tag',
                            },
                            method: 'post',
                            data: this.ruleForm
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

    .el-form-item > div{
        margin: 0 !important;
    }
</style>

<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Author: zal
 * Date: 2020-04-11
 * Time: 09:12
 */
Yii::$app->loadComponentView('com-rich-text');
?>
<style>
    .my-img {
        height: 50px;
        border: 1px solid #d7dae2;
        border-radius: 2px;
        margin-top: 10px;
        background-color: #e2e2e2;
        overflow: hidden;
    }

    .form-body {
        display: flex;
        justify-content: center;
    }

    .form-body .el-form {
        width: 450px;
        margin-top: 10px;
    }

    .currency-width {
        width: 300px;
    }

    .currency-width .el-input__inner {
        height: 35px;
        line-height: 35px;
        border-radius: 8px;
    }

    .isAppend .el-input__inner {
        border-top-right-radius: 0;
        border-bottom-right-radius: 0;
    }

    .form-body .currency-width .el-input-group__append {
        width: 80px;
        background-color: #2E9FFF;
        color: #fff;
        padding: 0;
        line-height: 35px;
        height: 35px;
        text-align: center;
        border-radius: 8px;
        border-top-left-radius: 0;
        border-bottom-left-radius: 0;
        border: 0;
    }

    .preview {
        height: 75px;
        line-height: 75px;
        text-align: center;
        width: 200px;
        background-color: #F7F7F7;
        color: #BBBBBB;
        margin-top: 10px;
        font-size: 12px;
    }

    .qr-title:first-of-type {
        margin-top: 0;
    }

    .qr-title {
        color: #BBBBBB;
        font-size: 13px;
        margin-top: 10px;
    }

    .line {
        border: none;
        border-bottom: 1px solid #e2e2e2;
        margin: 40px 0;
    }

    .title {
        margin-bottom: 20px;
    }

    .submit-btn {
        height: 32px;
        width: 65px;
        line-height: 32px;
        text-align: center;
        border-radius: 16px;
        padding: 0;
    }
</style>
<div id="app" v-cloak>
    <el-card shadow="never" v-loading="loading">
        <div style="margin-bottom: 20px">签到规则</div>
        <div class='form-body' ref="body">
            <el-form label-position="left" label-width="150px" :model="form" ref="form">

                <el-form-item label="签到规则" style="width: 600px;">
                    <com-rich-text :simple-attachment="true" v-model="form.rule"></com-rich-text>
                </el-form-item>
                <!-- 分割线 -->
                <hr :style="line" class="line">
                <el-form-item>
                    <el-button class="submit-btn" type="primary" @click="submit" :loading="submitLoading">保存</el-button>
                </el-form-item>
            </el-form>
        </div>
    </el-card>
</div>
<script>
    new Vue({
        el: '#app',
        data() {
            return {
                loading: false,
                submitLoading: false,
                rule:'',
                line: {
                    width: '450px',
                    marginLeft: '-150px'
                },
                form: {
                    rule: '',
                },
                params: {
                    r: 'plugin/sign_in/mall/index/agreement'
                },
            };
        },
        created() {
            this.loadData();
            this.$nextTick(function () {
                this.line.width = this.$refs.body.clientWidth + 'px';
                this.line.marginLeft = -(this.$refs.body.clientWidth - 450) / 2 + 'px';
            })
        },
        methods: {
            loadData() {
                this.loading = true;
                this.$request({
                    params: {
                        r: 'plugin/sign_in/mall/index/agreement',
                    },
                }).then(e => {
                    console.log(e);
                    this.loading = false;
                    if (e.data.code === 0) {
                        if (e.data.data.rule) {
                            this.form = e.data.data.rule;
                        }
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {
                });
            },
            submit() {
                this.submitLoading = true;
                this.$request({
                    params: {
                        r: 'plugin/sign_in/mall/index/agreement',
                    },
                    method: 'post',
                    data: {
                        setting: this.form,
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
            },
            updateSuccess(e) {
                this.$message.success('上传成功')
            }
        }
    });
</script>
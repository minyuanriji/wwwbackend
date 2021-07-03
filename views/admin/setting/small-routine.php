<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Author: zal
 * Date: 2020-04-11
 * Time: 09:12
 */
?>

<style>
    .form-body {
        display: flex;
        justify-content: center;
    }

    .form-body .el-form {
        width: 750px;
        margin-top: 10px;
    }
</style>

<div id="app" v-cloak>
    <el-card shadow="never" v-loading="loading">
        <div style="margin-bottom: 20px">域名设置</div>
        <div class='form-body' ref="body">
            <el-form label-position="left" label-width="180px" :model="form" ref="form">
                <el-form-item label="小程序业务域名校验文件">
                    <com-upload @complete="updateSuccess" :accept="'text/plain'" :params="params"
                                v-model="form.file" :simple="true">
                        <el-button size="small">上传文件</el-button>
                    </com-upload>
                    <div class="preview">仅支持上传 .txt 格式文件</div>
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
                form: {
                    file: '',
                },
                submitLoading: false,
                params: {
                    r: 'admin/setting/upload-file'
                }
            };
        },
        created() {
        },
        methods: {
            updateSuccess(e) {
                this.$message.success('上传成功')
            }
        }
    });
</script>

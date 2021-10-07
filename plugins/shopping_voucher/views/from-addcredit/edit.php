<?php
echo $this->render("../com/com-tab-from");
?>
<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">

        <com-tab-from :current="activeName"></com-tab-from>

        <div class="table-body">
            <el-alert title="说明：话费充值，成功后可获得赠送购物券" type="info" :closable="false" style="margin-bottom: 20px;"></el-alert>

            <el-tabs v-model="search.type" @tab-click="tab_assets">
                <el-tab-pane label="快充呗" name="kbc"></el-tab-pane>
            </el-tabs>

            <div class="form-body" v-if="tab_index==0">
                <el-form :model="ruleForm" size="small" ref="ruleForm" label-width="120px">
                    <el-form-item prop="id" label="ID" style="display: none">
                        <el-input v-model="ruleForm.id"></el-input>
                    </el-form-item>
                    快充：
                    <el-form-item label="第一次赠送" prop="fast_one_give">
                        <el-input type="number" v-model="ruleForm.fast_one_give" style="width: 200px;">
                            <template slot="append">%</template>
                        </el-input>
                    </el-form-item>
                    <el-form-item label="后续赠送" prop="fast_follow_give">
                        <el-input type="number" v-model="ruleForm.fast_follow_give" style="width: 200px;">
                            <template slot="append">%</template>
                        </el-input>
                    </el-form-item>
                    慢充：
                    <el-form-item label="第一次赠送" prop="slow_one_give">
                        <el-input type="number" v-model="ruleForm.slow_one_give" style="width: 200px;">
                            <template slot="append">%</template>
                        </el-input>
                    </el-form-item>
                    <el-form-item label="后续赠送" prop="slow_follow_give">
                        <el-input type="number" v-model="ruleForm.slow_follow_give" style="width: 200px;">
                            <template slot="append">%</template>
                        </el-input>
                    </el-form-item>
                </el-form>
            </div>
            <div style="margin-left: 100px">
                <el-button class="button-item" :loading="btnLoading" type="primary" @click="store()" size="small">保存</el-button>
            </div>
        </div>
    </el-card>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                ruleForm: {
                    fast_one_give: 0,
                    fast_follow_give: 0,
                    slow_one_give: 0,
                    slow_follow_give: 0,
                    sdk_key: '',
                    id: 0,
                },
                tab_index:0,
                activeName: 'addcredit',
                editDialogVisible: false,
                pagination: null,
                loading: false,
                dialogContent: false,
                ratioForm:'',
                ratioLoading: false,
                // 搜索内容
                search: {
                    type: 'kbc',
                },
            };
        },
        methods: {
            // 切换
            tab_assets(e) {
                this.tab_index = e.index;
                this.getList();
            },
            store() {
                this.$refs.ruleForm.validate((valid) => {
                    let self = this;
                    if (valid) {
                        self.btnLoading = true;
                        self.ruleForm.sdk_key = self.search.type;
                        request({
                            params: {
                                r: 'plugin/shopping_voucher/mall/from-addcredit/edit'
                            },
                            method: 'post',
                            data: {
                                form: self.ruleForm,
                            }
                        }).then(e => {
                            self.btnLoading = false;
                            if (e.data.code == 0) {
                                self.$message.success(e.data.msg);
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
                this.listLoading = true;
                request({
                    params: {
                        r: 'plugin/shopping_voucher/mall/from-addcredit/edit',
                        sdk_key: this.search.type,
                    },
                }).then(e => {
                    if (e.data.code === 0) {
                        this.ruleForm = e.data.data;
                    } else {
                        this.$message.error(e.data.msg);
                    }
                    this.listLoading = false;
                }).catch(e => {
                    this.listLoading = false;
                });
            },
        },
        mounted: function() {
            this.getDetail();
        }
    });
</script>

<style>
    .table-body {
        padding: 20px;
        background-color: #fff;
    }

    .input-item {
        display: inline-block;
        width: 250px;
        margin: 0 0 20px 0px;
    }

    .input-item .el-input__inner {
        border-right: 0;
    }

    .input-item .el-input__inner:hover{
        border: 1px solid #dcdfe6;
        border-right: 0;
        outline: 0;
    }

    .input-item .el-input__inner:focus{
        border: 1px solid #dcdfe6;
        border-right: 0;
        outline: 0;
    }

    .input-item .el-input-group__append {
        background-color: #fff;
        border-left: 0;
        width: 10%;
        padding: 0;
    }

    .input-item .el-input-group__append .el-button {
        padding: 0;
    }

    .input-item .el-input-group__append .el-button {
        margin: 0;
    }

</style>
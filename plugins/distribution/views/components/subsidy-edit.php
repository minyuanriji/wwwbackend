<template id="subsidy-edit">
    <el-dialog :visible.sync="edit.visible" width="20%" title="修改补贴配置">
        <div v-loading="edit.loading">
            <el-form size="small" label-width="150px">
                <el-form-item label="补贴名称">
                    <el-col :span="13">
                        <el-input v-model="ruleForm.name"></el-input>
                    </el-col>
                </el-form-item>

                <el-form-item label="分销商等级">
                    <el-col :span="13">
                        <el-select v-model="ruleForm.distribution_level" placeholder="请选择分销商等级">
                            <el-option
                                    v-for="item in level_list"
                                    :label="item.name"
                                    :value="item.level">
                            </el-option>
                        </el-select>
                    </el-col>
                </el-form-item>

                <el-form-item label="佣金">
                    <el-col :span="13">
                        <el-input v-model="ruleForm.price">
                            <template slot="append">元/人</template>
                        </el-input>
                    </el-col>
                </el-form-item>

                <el-form-item label="推荐人数">
                    <el-col :span="13">
                        <el-input v-model="ruleForm.min_num">
                            <template slot="append">人</template>

                        </el-input>


                    </el-col>
                </el-form-item>
                <el-form-item label="小于人数">
                    <el-col :span="13">
                        <el-input v-model="ruleForm.max_num">
                            <template slot="append">人</template>
                        </el-input>


                    </el-col>
                </el-form-item>


                <el-form-item label="是否启用" prop="is_enable">
                    <el-switch
                            v-model="ruleForm.is_enable"
                            :active-value="1"
                            :inactive-value="0">
                    </el-switch>
                </el-form-item>


            </el-form>
        </div>
        <span slot="footer" class="dialog-footer">
            <el-button @click="editCancel" type="default" size="small">取消</el-button>
            <el-button type="primary" :loading="edit.btnLoading" style="margin-bottom: 10px;" size="small"
                       @click="editSave">保存</el-button>
        </span>
    </el-dialog>
</template>
<script>
    Vue.component('subsidy-edit', {
        template: '#subsidy-edit',
        props: {
            value: {
                type: Boolean,
                default: false
            },
            row: {
                type: Object,
                default: null
            },

        },
        data() {
            return {
                level_list: [],
                weight_list: [],
                ruleForm: {
                    distribution_level: '',
                    min_num: '',
                    max_num: '',
                    price: 0,
                    is_enable: 0,
                    name: '',
                },
                edit: {
                    visible: false,
                    level: '',
                    btnLoading: false,
                    loading: false,
                }
            };
        },
        watch: {
            row() {
                if (this.row) {
                    this.ruleForm = this.row
                    this.ruleForm.is_enable = parseInt(this.row.is_enable);
                }
            },

            value() {
                if (this.value) {
                    this.edit.visible = true;
                    this.getLevel();

                } else {
                    this.edit.id = '';
                    this.edit.visible = false;
                }
            },
            'edit.visible'() {
                if (!this.edit.visible) {
                    this.editCancel();
                }
            }
        },
        methods: {
            getLevel() {
                this.edit.loading = true;
                request({
                    params: {
                        r: 'plugin/distribution/mall/level/enable-list',
                    }
                }).then(res => {
                    console.log(res)
                    this.edit.loading = false;
                    if (res.data.code == 0) {
                        this.level_list = res.data.data.list;
                    } else {
                        this.$message.error(res.data.msg);
                    }
                }).catch(res => {
                    this.edit.loading = false;
                });
            },
            editCancel() {
                this.$emit('input', false);
            },
            editSave() {

                this.edit.btnLoading = true;
                request({
                    params: {
                        r: 'plugin/distribution/mall/level/subsidy-edit',
                    },
                    method: 'post',
                    data: this.ruleForm
                }).then(response => {

                    this.edit.btnLoading = false;
                    if (response.data.code == 0) {
                        this.$message.success('修改成功');
                        this.editCancel();
                        this.$emit('success', true);
                    } else {
                        this.$message.error(response.data.msg);
                    }
                }).catch(response => {
                    this.edit.btnLoading = false;
                });
            }
        }
    });
</script>


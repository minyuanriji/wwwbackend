<template id="team-edit">
    <el-dialog :visible.sync="edit.visible" width="20%" title="配置团队奖励">
        <div v-loading="edit.loading">
            <el-form size="small" label-width="150px">
                <el-form-item label="等级名称">
                    <el-col :span="13">
                        <el-input v-model="ruleForm.name"></el-input>

                    </el-col>
                </el-form-item>
                <el-form-item label="上级等级">
                    <el-col :span="13">
                        <el-select v-model="ruleForm.parent_level" placeholder="请选择上级分销商等级">
                            <el-option
                                    v-for="item in level_list"
                                    :label="item.name"
                                    :value="item.level">
                            </el-option>
                        </el-select>
                    </el-col>
                </el-form-item>
                <el-form-item label="下级分销商等级">
                    <el-col :span="13">
                        <el-select v-model="ruleForm.child_level" placeholder="请选择下级分销商等级">
                            <el-option
                                    v-for="item in level_list"
                                    :label="item.name"
                                    :value="item.level">
                            </el-option>
                        </el-select>
                    </el-col>
                </el-form-item>
                <el-form-item label="佣金类型" prop="price_type">
                    <el-radio-group v-model="ruleForm.price_type">
                        <el-radio :label="0">百分比</el-radio>
                        <el-radio :label="1">固定金额</el-radio>
                    </el-radio-group>
                </el-form-item>
                <el-form-item label="佣金">
                    <el-col :span="13">
                        <el-input v-model="ruleForm.price"></el-input>
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
    Vue.component('team-edit', {
        template: '#team-edit',
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
                ruleForm: {
                    id:0,
                    child_level: '',
                    parent_level: '',
                    price: '',
                    price_type: 0,
                    is_enable: 0,
                    name: '',
                },
                edit: {
                    visible: false,
                    btnLoading: false,
                    loading: false,
                }
            };
        },
        watch: {
            row() {
                if (this.row) {
                    this.ruleForm = this.row
                    this.ruleForm.price_type = parseInt(this.row.price_type);
                    this.ruleForm.is_enable = parseInt(this.row.is_enable);
                }else {
                    this.ruleForm =  {
                        id:0,
                        child_level: '',
                        parent_level: '',
                        price: '',
                        price_type: 0,
                        is_enable: 0,
                        name: '',
                    };
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
                        r: 'plugin/distribution/mall/level/team-edit',
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


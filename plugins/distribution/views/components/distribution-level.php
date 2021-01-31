<template id="distribution-level">
    <el-dialog :visible.sync="edit.visible" width="20%" title="修改分销商等级">
        <div v-loading="edit.loading">
            <el-form size="small" label-width="150px">
                <el-form-item label="等级名称">
                    <el-select size="small" v-model="edit.level" class="select">
                        <el-option :key="index" :label="item.name" :value="item.level" :disabled="distribution.level == item.level"
                                   v-for="(item, index) in edit.list"></el-option>
                    </el-select>
                </el-form-item>
            </el-form>
        </div>
        <span slot="footer" class="dialog-footer">
            <el-button @click="editCancel" type="default" size="small">取消</el-button>
            <el-button type="primary" :loading="edit.btnLoading" style="margin-bottom: 10px;" size="small" @click="editSave">保存</el-button>
        </span>
    </el-dialog>
</template>
<script>
    Vue.component('distribution-level', {
        template: '#distribution-level',
        props: {
            value: {
                type: Boolean,
                default: false
            },
            distribution: Object,
        },
        data() {
            return {
                edit: {
                    visible: false,
                    level: '',
                    btnLoading: false,
                    list: [],
                    loading: false,
                }
            };
        },
        watch: {
            value() {
                if (this.value) {
                    this.edit.visible = true;
                    this.edit.level = this.distribution.level;
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
                    this.edit.loading = false;
                    if (res.data.code == 0) {
                        this.edit.list =res.data.data.list;
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
                        r: 'plugin/distribution/mall/distribution/level-change',
                    },
                    method: 'post',
                    data: {
                        level: this.edit.level,
                        id: this.distribution.user_id
                    }
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


<template id="apply-edit">
    <el-dialog :visible.sync="edit.visible" width="20%" title="添加区域代理">
        <div>
            <el-form @submit.native.prevent size="small" label-width="150px">
                <el-form-item label="审核状态" prop="status">
                    <el-radio-group v-model="status">

                        <el-radio :label="1">通过</el-radio>
                        <el-radio :label="2">不通过</el-radio>
                    </el-radio-group>
                </el-form-item>
            </el-form>

            <el-form @submit.native.prevent size="small" label-width="150px">
                <el-form-item label="申请备注" prop="marks">
                    <el-input type="textarea"
                              :rows="4"
                              placeholder="备注"
                              v-model="marks">
                    </el-input>
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
    Vue.component('apply-edit', {
        template: '#apply-edit',
        props: {
            value: {
                type: Boolean,
                default: false,
            },
            row: {
                type: Object,
                default: null,
            },
        },
        data() {
            return {
                edit: {
                    visible: false,
                    keyword: '',
                    nickname: '',
                    id: '',
                    btnLoading: false,
                },
                status:'',
                marks:'',
                user_id: 0,
                apply_id:0,
                district: [],
                address: null,
                town_list: [],
                province_id: 0,
                city_id: 0,
                district_id: 0,
                town_id: '',
                props: {
                    value: 'id',
                    label: 'name',
                    children: 'list'
                },
                level_list: [
                    {
                        name: '省代',
                        level: 4
                    },
                    {
                        name: '市代',
                        level: 3
                    },
                    {
                        name: '区代',
                        level: 2
                    },
                    {
                        name: '镇代',
                        level: 1
                    },
                ],
                level: ''
            }
        },
        watch: {
            value() {
                if (this.value) {
                    this.user_id = this.row.user_id;
                    this.apply_id = this.row.id;
                    this.edit.visible = true;
                }
            },

            'edit.visible'() {
                if (!this.edit.visible) {
                    this.editCancel();
                }
            }
        },
        created() {
            this.getDistrict(1);
        },
        methods: {
            levelChange(e) {
                this.getDistrict(e);
            },
            querySearchAsync(queryString, cb) {
                this.edit.keyword = queryString;
                this.get_user(cb);
            },
            get_user(cb) {
                request({
                    params: {
                        r: 'plugin/distribution/mall/distribution/search-user',
                        keyword: this.edit.keyword
                    }
                }).then(res => {
                    if (res.data.code == 0) {
                        cb(res.data.data.list)
                    } else {
                        this.$message.error(res.data.msg);
                    }
                });
            },
            areaClick(row) {
                this.edit.id = row.id
            },

            editCancel() {
                this.$emit('input', false);
                navigateTo({
                    r: 'plugin/distribution/mall/distribution/apply',
                })
            },
            editSave() {
                this.edit.btnLoading = true;
                if (this.user_id == 0) {
                    this.$message.error('请确定用户信息');
                    return;
                }

                request({
                    params: {
                        r: 'plugin/distribution/mall/distribution/edit',
                    },
                    method: 'post',
                    data: {
                        id: this.user_id,
                        level: this.level,
                        apply_id:this.apply_id,
                        apply_marks:this.marks,
                        apply_status:this.status

                    }
                }).then(response => {
                    this.edit.btnLoading = false;
                    if (response.data.code == 0) {
                        this.$message.success('添加成功');
                        this.editCancel();
                    } else {
                        this.$message.error(response.data.msg);
                    }
                }).catch(response => {
                    this.edit.btnLoading = false;
                });
            },
            keyUp() {
                console.log('key up')
            }
        }
    });
</script>

<template id="boss-edit">
    <el-dialog :visible.sync="edit.visible" width="20%" title="添加股东">
        <div>
            <el-form @submit.native.prevent size="small" label-width="150px">
                <el-form-item label="用户昵称">
                    <el-autocomplete size="small" v-model="edit.nickname" value-key="nickname"
                                     @keyup.enter.native="keyUp"
                                     :fetch-suggestions="querySearchAsync" placeholder="请输入用户昵称"
                                     @select="bossClick"></el-autocomplete>
                </el-form-item>
            </el-form>
            <el-form @submit.native.prevent size="small" label-width="150px">
                <el-form-item label="等级">
                    <el-select v-model="level" placeholder="请选择股东等级">
                        <el-option
                                v-for="item in level_list"
                                :label="item.name"
                                :value="item.level">
                        </el-option>
                    </el-select>
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
    Vue.component('boss-edit', {
        template: '#boss-edit',
        props: {
            value: {
                type: Boolean,
                default: false
            }
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
                level_list: [],
                level: 0
            }
        },
        watch: {
            value() {
                if (this.value) {
                    this.edit.visible = true;
                } else {
                    this.edit.id = '';
                    this.edit.nickname = '';
                    this.edit.keyword = '';
                    this.edit.visible = false;
                }
            }
            ,
            'edit.visible'() {
                if (!this.edit.visible) {
                    this.editCancel();
                }
            }
        },
        created() {

            this.getLevelList();

        },
        methods: {
            querySearchAsync(queryString, cb) {
                this.edit.keyword = queryString;
                this.get_user(cb);
            },
            get_user(cb) {
                request({
                    params: {
                        r: 'plugin/boss/mall/boss/search-user',
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
            bossClick(row) {
                this.edit.id = row.id
            },
            getLevelList() {
                this.edit.btnLoading = true;
                request({
                    params: {
                        r: 'plugin/boss/mall/level/enable-list',
                    },
                    method: 'get',
                }).then(response => {
                    this.edit.btnLoading = false;
                    if (response.data.code == 0) {
                        this.level_list = response.data.data.list
                    } else {
                        this.$message.error(response.data.msg);
                    }
                }).catch(response => {
                    this.edit.btnLoading = false;
                });
            },
            editCancel() {
                this.$emit('input', false);
                navigateTo({
                    r: 'plugin/boss/mall/boss/index',
                })
            },
            editSave() {
                this.edit.btnLoading = true;
                request({
                    params: {
                        r: 'plugin/boss/mall/boss/edit',
                    },
                    method: 'post',
                    data: {
                        id: this.edit.id,
                        level: this.level
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

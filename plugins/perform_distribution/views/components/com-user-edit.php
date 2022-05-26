<?php

?>

<template id="com-user-edit">
    <el-dialog :visible.sync="dialogVisible" width="30%" :title="cTitle" :close-on-click-modal="false">
        <div>
            <el-form @submit.native.prevent label-width="150px">
                <el-card shadow="always">
                    <div style="display: flex;align-items: center">
                        <com-image :src="data.user.avatar_url"></com-image>
                        <div style="display: flex;flex-direction: column;margin-left:20px;justify-content: space-between">
                            <span>(ID:{{data.user_id}}) {{data.user.nickname}}</span>
                            <span>手机：{{data.user.mobile}}</span>
                            <span><a  :href="'?r=mall/user/edit&id=' + data.user_id" target="_blank">详情</a>
                            </span>
                        </div>
                    </div>
                </el-card>

                <el-form-item label="等级" prop="level" style="margin-top:20px;">
                    <el-select v-model="ruleForm.level_id" placeholder="请选择" style="width:260px;">
                        <el-option v-for="level in levels" :value="level.id" :label="level.name"></el-option>
                    </el-select>
                </el-form-item>

            </el-form>
        </div>
        <span slot="footer" class="dialog-footer">
            <el-button @click="editCancel" type="default">取消</el-button>
            <el-button type="primary" :loading="btnLoading" style="margin-bottom: 10px;"@click="editSave">保存</el-button>
        </span>
    </el-dialog>
</template>
<script>
    Vue.component('com-user-edit', {
        template: '#com-user-edit',
        props: {
            value: {
                type: Boolean,
                default: false
            },
            data: Object
        },
        data() {
            return {
                btnLoading: false,
                dialogVisible: false,
                cardLoading: false,
                ruleForm: {},
                levels: []
            }
        },
        computed: {
            cTitle (){
                return this.data.id != 0 ? '编辑用户' : '添加用户';
            }
        },
        watch: {
            value() {
                if (this.value) {
                    this.dialogVisible = true;
                } else {
                    this.dialogVisible = false;
                }
            },
            dialogVisible() {
                if (!this.dialogVisible) {
                    this.editCancel();
                }
            },
            data:{
                handler(newVal){
                    this.ruleForm = newVal;
                    this.ruleForm.level_id = this.ruleForm.level_id ? parseInt(this.ruleForm.level_id) : '';
                },
                deep: true
            }
        },
        mounted() {
            this.ruleForm.level_id = this.ruleForm.level_id ? parseInt(this.ruleForm.level_id) : '';
            this.ruleForm = this.data;
            this.getLevel();
        },
        methods: {
            getLevel(){
                this.cardLoading = true;
                request({
                    params: {
                        r: 'plugin/perform_distribution/mall/level/index',
                        page: 1
                    },
                    method: 'get',
                }).then(e => {
                    if(e.data.code == 0){
                        this.cardLoading = false;
                        this.levels = e.data.data.list;
                    }else{
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    console.log(e);
                });
            },
            editSave(){

                if(!this.ruleForm.level_id){
                    this.$message.error("请选择等级");
                    return;
                }

                this.btnLoading = true;
                request({
                    params: {
                        r: 'plugin/perform_distribution/mall/member/edit',
                    },
                    method: 'post',
                    data: this.ruleForm
                }).then(res => {
                    this.btnLoading = false;
                    if (res.data.code == 0) {
                        this.$message.success('保存成功');
                        this.editCancel();
                        this.$emit('on-save');
                    } else {
                        this.$message.error(res.data.msg);
                    }
                }).catch(res => {
                    this.btnLoading = false;
                });
            },
            editCancel() {
                this.$emit('input', false);
            }
        }
    });
</script>

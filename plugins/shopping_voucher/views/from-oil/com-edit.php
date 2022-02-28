<?php

?>
<template id="com-edit">
    <div class="com-edit">
        <el-dialog width="70%" :title="dialogTitle" :visible.sync="dialogVisible" :close-on-click-modal="false" @close="close">

            <el-form ref="formData" :rules="formRule" :model="formData" label-width="15%" size="small">
                <el-form-item label="指定平台" prop="plat_id">
                    <el-select v-model="formData.plat_id" placeholder="请选择" style="width:260px;">
                        <el-option :label="plat.name" :value="plat.id" v-for="plat in platList"></el-option>
                    </el-select>
                </el-form-item>
                <el-form-item label="首次赠送" prop="first_give_value">
                    <el-input type="number" min="0" max="100" placeholder="请输入内容" v-model="formData.first_give_value" style="width:260px;">
                        <el-select v-model="formData.first_give_type" slot="prepend" placeholder="请选择" style="width:110px;">
                            <el-option label="按比例" value="1"></el-option>
                            <el-option label="按固定值" value="2"></el-option>
                        </el-select>
                        <template slot="append">{{formData.first_give_type == 1 ? "%" : "券"}}</template>
                    </el-input>
                </el-form-item>
                <el-form-item label="第二次赠送" prop="second_give_value">
                    <el-input type="number" min="0" max="100" placeholder="请输入内容" v-model="formData.second_give_value" style="width:260px;">
                        <el-select v-model="formData.second_give_type" slot="prepend" placeholder="请选择" style="width:110px;">
                            <el-option label="按比例" value="1"></el-option>
                            <el-option label="按固定值" value="2"></el-option>
                        </el-select>
                        <template slot="append">{{formData.first_give_type == 1 ? "%" : "券"}}</template>
                    </el-input>
                </el-form-item>
                <el-form-item label="启动日期" prop="start_at">
                    <el-date-picker v-model="formData.start_at" type="date" placeholder="选择日期"></el-date-picker>
                </el-form-item>
                <el-form-item>
                    <el-button @click="save" size="big" type="primary">确认保存</el-button>
                </el-form-item>
            </el-form>

        </el-dialog>

    </div>
</template>

<script>

    Vue.component('com-edit', {
        template: '#com-edit',
        props: {
            visible: Boolean,
            editData: Object
        },
        data() {
            return {
                dialogTitle: "设置加油券红包赠送",
                activeName: "first",
                dialogVisible: false,
                formDialogVisible: false,
                formData: {
                    plat_id: "",
                    first_give_type: "1",
                    first_give_value: "",
                    second_give_type: "1",
                    second_give_value: "",
                    start_at: ""
                },
                formRule:{
                    plat_id: [
                        {required: true, message: '请选择平台', trigger: 'change'},
                    ],
                    first_give_value: [
                        {required: true, message: '首次赠送配置值不能为空', trigger: 'change'},
                    ],
                    second_give_value: [
                        {required: true, message: '第二次赠送配置值不能为空', trigger: 'change'},
                    ],
                    start_at:[
                        {required: true, message: '启动日期不能为空', trigger: 'change'},
                    ]
                },
                platList: []
            };
        },
        watch: {
            visible(val, oldVal){
                this.dialogVisible = val;
            }
        },
        mounted: function () {
            this.getPlatList();
        },
        methods: {
            getPlatList(){
                let params = Object.assign({
                    r: 'plugin/shopping_voucher/mall/from-oil/search-oil-plateform'
                }, {});
                params['page'] = typeof page != "undefined" ? page : 1;
                request({
                    params
                }).then(e => {
                    if (e.data.code === 0) {
                        this.platList = e.data.data.list;
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {

                });
            },
            save(){
                let that = this;
                let do_request = function(){
                    request({
                        params: {
                            r: "plugin/shopping_voucher/mall/from-oil/batch-save"
                        },
                        method: "post",
                        data: that.formData
                    }).then(e => {
                        if (e.data.code == 0) {
                            that.$emit('update');
                        } else {
                            that.$message.error(e.data.msg);
                        }
                    }).catch(e => {
                        that.$message.error(e.data.msg);
                    });
                };
                this.$refs['formData'].validate((valid) => {
                    if (valid) {
                        do_request();
                    }
                });
            },
            close(){
                this.$emit('close');
            }
        }
    });
</script>
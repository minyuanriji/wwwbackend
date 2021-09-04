<?php
Yii::$app->loadComponentView('store/com-dialog-select');
?>
<template id="com-edit">
    <div class="com-edit">
        <el-dialog :title="dialogTitle" :visible.sync="dialogVisible" :close-on-click-modal="false" @close="close">
            <el-form :rules="rules" ref="formData" label-width="20%" :model="formData" size="small">
                <el-form-item label="选择商户" prop="mch_id">
                    <div style="display:flex" v-if="formData.mch_id > 0" >
                        <div style="margin-right: 10px;">
                            <com-image mode="aspectFill" :src="formData.cover_url"></com-image>
                        </div>
                        <div style="justify-content:flex-start;display:flex;flex-direction:column">
                            <div>{{formData.name}}</div>
                            <div>ID:{{formData.mch_id}}</div>
                        </div>
                    </div>
                    <com-dialog-select :multiple="false" @selected="storeSelect" title="门店选择">
                        <el-button type="primary" size="small">指定门店</el-button>
                    </com-dialog-select>
                </el-form-item>
                <el-form-item label="赠送比例" prop="give_value">
                    <el-input type="number" min="0" max="100" placeholder="请输入内容" v-model="formData.give_value" style="width:300px;">
                        <template slot="append">%</template>
                    </el-input>
                </el-form-item>
                <el-form-item label="启动日期" prop="start_at">
                    <el-date-picker v-model="formData.start_at" type="date" placeholder="选择日期"></el-date-picker>
                </el-form-item>
            </el-form>

            <div slot="footer" class="dialog-footer">
                <el-button @click="close">取 消</el-button>
                <el-button :loading="btnLoading" type="primary" @click="save">确 定</el-button>
            </div>

        </el-dialog>


    </div>
</template>

<script>
    function initFormData(){
        return {
            id: 0,
            mch_id: '',
            store_id: '',
            give_type: 1,
            give_value:0,
            name: '',
            cover_url: '',
            start_at: ''
        };
    }

    Vue.component('com-edit', {
        template: '#com-edit',
        props: {
            visible: Boolean,
            editData: Object
        },
        data() {
            return {
                dialogTitle: "添加商户",
                activeName: "first",
                dialogVisible: false,
                formData: initFormData(),
                rules: {
                    mch_id: [
                        {required: true, message: '请设置商户', trigger: 'change'},
                    ],
                    give_value: [
                        {required: true, message: '请设置赠送比例', trigger: 'change'},
                    ],
                },
                btnLoading: false
            };
        },
        watch: {
            visible(val, oldVal){
                this.dialogVisible = val;
            },
            editData(val, oldVal){
                this.formData = Object.assign(initFormData(), val);
            }
        },
        methods: {
            save(){
                let that = this;
                this.$refs['formData'].validate((valid) => {
                    if (valid) {
                        that.btnLoading = true;
                        request({
                            params: {
                                r: 'plugin/shopping_voucher/mall/from-store/edit'
                            },
                            method: 'post',
                            data: that.formData
                        }).then(e => {
                            that.btnLoading = false;
                            if (e.data.code == 0) {
                                that.$message.success(e.data.msg);
                                that.$emit('update');
                            } else {
                                that.$message.error(e.data.msg);
                            }
                        }).catch(e => {
                            that.$message.error(e.data.msg);
                            that.btnLoading = false;
                        });
                    }
                });
            },
            storeSelect(data){
                this.formData.mch_id    = data.store.mch_id;
                this.formData.store_id  = data.store.id;
                this.formData.name      = data.store.name;
                this.formData.cover_url = data.store.cover_url;
            },
            close(){
                this.$emit('close');
            }
        }
    });
</script>
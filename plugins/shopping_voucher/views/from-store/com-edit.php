<?php
Yii::$app->loadComponentView('store/com-shop-store-list');
?>
<template id="com-edit">
    <div class="com-edit">
        <el-dialog :title="dialogTitle" :visible.sync="dialogVisible" :close-on-click-modal="false" @close="close">
            <el-form ref="formData" label-width="20%" :model="formData" size="small">
                <el-form-item label="选择商户" prop="mch_id">
                        <el-table :data="formData.store" style="width: 100%">
                            <el-table-column prop="mch_id" label="ID" width="180"></el-table-column>
                            <el-table-column prop="name" label="店铺名" width="180"></el-table-column>
                            <el-table-column prop="cover_url" label="店铺图片" width="180">
                                <template slot-scope="scope">
                                    <com-image mode="aspectFill" :src="scope.row.cover_url"></com-image>
                                </template>
                            </el-table-column>
                        </el-table>
                    <com-shop-store-list :multiple="false" @selected="storeSelect" title="门店选择">
                        <el-button type="primary" size="small">指定门店</el-button>
                    </com-shop-store-list>
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
        return  [{
                    mch_id: '',
                    store_id: '',
                    give_type: 1,
                    name: '',
                    cover_url: '',
                }];
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
                formData: {
                    store: [
                        {
                            mch_id:'',
                            store_id:'',
                            name:'',
                            cover_url:'',
                            give_type:'',
                        }
                    ]
                },
                btnLoading: false,
                StoreData:[],
            };
        },
        watch: {
            visible(val, oldVal){
                this.dialogVisible = val;
            },
            /*editData(val, oldVal){
                this.formData = '';//Object.assign(initFormData(), val);
            }*/
        },
        methods: {
            save(){
                let that = this;
                this.$refs['formData'].validate((valid) => {
                    if (valid) {
                        that.btnLoading = true;
                        request({
                            params: {
                                r: 'plugin/shopping_voucher/mall/from-store/batch-save'
                            },
                            method: 'post',
                            data: that.formData
                        }).then(e => {
                            that.btnLoading = false;
                            if (e.data.code == 0) {
                                that.$message.success(e.data.msg);
                                that.$emit('update');
                                that.formData.store = initFormData();
                                that.formData.give_value = '';
                                that.formData.start_at = '';
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
                for (i=0;i<data.length;i++) {
                    if(this.StoreData.indexOf(data[i])==-1){
                        this.StoreData.push({
                            mch_id:data[i].store.mch_id,
                            store_id:data[i].store.id,
                            name:data[i].store.name,
                            cover_url:data[i].store.cover_url,
                            give_type:1,
                        })
                    }
                }
                this.formData.store = this.StoreData;
                this.StoreData=[];
            },
            close(){
                this.$emit('close');
            }
        }
    });
</script>
<?php
Yii::$app->loadComponentView('goods/com-dialog-select');
?>
<template id="com-edit">
    <div class="com-edit">
        <el-dialog :title="dialogTitle" :visible.sync="dialogVisible" :close-on-click-modal="false" @close="close">
            <el-form :rules="rules" ref="formData" label-width="20%" :model="formData" size="small">
                <el-form-item label="选择商品" prop="goods_id">
                    <div style="display:flex" v-if="formData.goods_id > 0" >
                        <div style="margin-right: 10px;">
                            <com-image mode="aspectFill" :src="formData.cover_pic"></com-image>
                        </div>
                        <div style="justify-content:flex-start;display:flex;flex-direction:column">
                            <div>{{formData.name}}</div>
                            <div>ID:{{formData.goods_id}}</div>
                        </div>
                    </div>
                    <com-dialog-select :multiple="false" @selected="goodsSelect" title="商品选择">
                        <el-button type="primary" size="small">指定商品</el-button>
                    </com-dialog-select>
                </el-form-item>
                <el-form-item label="购物券价" prop="voucher_price">
                    <el-input v-model="formData.voucher_price" style="width:35%;">
                        <template slot="append">元</template>
                    </el-input>
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
            goods_id: 0,
            name: '',
            cover_pic: '',
            voucher_price: 0.00
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
                dialogTitle: "添加商品",
                activeName: "first",
                dialogVisible: false,
                selectGoodsDialogVisible: false,
                formData: initFormData(),
                rules: {
                    goods_id: [
                        {required: true, message: '请设置商品', trigger: 'change'},
                    ],
                    voucher_price: [
                        {required: true, message: '请设置购物券价格', trigger: 'change'},
                    ]
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
            goodsSelect(goods){
                this.formData.goods_id = goods.id;
                this.formData.name = goods.name;
                this.formData.cover_pic = goods.goodsWarehouse.cover_pic;
                this.formData.voucher_price = goods.price;
            },
            save(){
                this.btnLoading = true;
                let that = this;
                this.$refs['formData'].validate((valid) => {
                    if (valid) {
                        request({
                            params: {
                                r: 'plugin/shopping_voucher/mall/target-alibaba-distribution-goods/edit'
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
            close(){
                this.$emit('close');
            }
        }
    });
</script>
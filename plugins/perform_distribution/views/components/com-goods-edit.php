<?php
Yii::$app->loadComponentView('goods/com-select-goods');
?>

<template id="com-goods-edit">
    <el-dialog :visible.sync="dialogVisible" width="30%" :title="cTitle" :close-on-click-modal="false">
        <div>
            <el-form @submit.native.prevent size="small" label-width="150px">
                <el-card shadow="always">
                    <div style="display: flex;align-items: center">
                        <com-image :src="data.goods.cover_pic"></com-image>
                        <div style="display: flex;flex-direction: column;margin-left:20px;">
                            <span>{{data.goods.name}}</span>
                            <span style="margin-top:10px;">
                                价格：<b style="color: darkred">{{data.goods.price}}</b>
                                <a style="margin-left:5px;" :href="'?r=mall/goods/edit&id=' + data.goods_id" target="_blank">详情</a>
                            </span>
                        </div>
                    </div>
                </el-card>

                <!--<el-form-item label="选择商品">
                    <div style="display: inline-block;">
                        <div flex="cross:center">
                            <div style="margin-left: 10px;">
                                <com-select-goods :multiple="false" @selected="goodsSelect" title="商品选择">
                                    <el-button type="text">选择商品</el-button>
                                </com-select-goods>
                            </div>
                        </div>
                    </div>
                </el-form-item>-->

            </el-form>
        </div>
        <span slot="footer" class="dialog-footer">
            <el-button @click="editCancel" type="default" size="small">取消</el-button>
            <el-button type="primary" :loading="btnLoading" style="margin-bottom: 10px;" size="small" @click="editSave">保存</el-button>
        </span>
    </el-dialog>
</template>
<script>
    Vue.component('com-goods-edit', {
        template: '#com-goods-edit',
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
                ruleForm: {},
            }
        },
        computed: {
            cTitle (){
                return this.data.id != 0 ? '编辑商品' : '添加商品';
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
                    this.ruleForm = this.newVal;
                },
                deep: true
            }
        },
        mounted() {
            this.ruleForm = this.data;
        },
        methods: {
            goodsSelect(e) {
                if (e) {

                }
            },
            editSave(){
                this.btnLoading = true;
                request({
                    params: {
                        r: 'plugin/perform_distribution/mall/goods/edit',
                    },
                    method: 'post',
                    data: this.data
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

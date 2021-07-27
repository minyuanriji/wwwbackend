<?php

Yii::$app->loadComponentView('com-dialog-select');
?>

<template id="agent-order-edit">
    <el-dialog :visible.sync="edit.visible" width="20%" title="发货信息">
        <div>
            <el-form @submit.native.prevent size="small" label-width="150px">
                <el-form-item label="物流名称">
                    <el-input v-model="ruleForm.express_name">
                    </el-input>
                </el-form-item>
                <el-form-item label="物流单号">
                    <el-input v-model="ruleForm.express_no">
                    </el-input>
                </el-form-item>

            </el-form>
        </div>
        <span slot="footer" class="dialog-footer">
            <el-button @click="editCancel" type="default" size="small">取消</el-button>
            <el-button type="primary" :loading="edit.btnLoading" style="margin-bottom: 10px;" size="small"
                       @click="editSave">确认发货</el-button>
        </span>
    </el-dialog>
</template>
<script>
    Vue.component('agent-order-edit', {
        template: '#agent-order-edit',
        props: {
            value: {
                type: Boolean,
                default: false
            },
            edit_row: {
                type: Object,
                default: null
            }
        },
        data() {
            return {
                edit: {
                    visible: false,
                    id: '',
                    btnLoading: false,
                },
                ruleForm: {
                    express_no: '',
                    express_name: '',
                    order_id: '',
                },
                level_list: [],
                goods: null,
            }
        },
        watch: {
            value() {
                if (this.value) {
                    this.edit.visible = true;
                    if (this.edit_row.id > 0) {
                        console.log(this.edit_row)
                        this.ruleForm.order_id = this.edit_row.id;
                    } else {
                        this.edit.id = 0;
                    }
                } else {
                    this.edit.visible = false;
                }
            },
            'edit.visible'() {
                if (!this.edit.visible) {
                    this.editCancel();
                }
                if (this.edit.id) {
                }
            }
        },
        mounted() {
            this.edit.id = this.edit_row.id;
        },
        methods: {
            onInput(e) {
                this.$forceUpdate();
            },
            agentClick(row) {
                this.edit.id = row.id
            },
            editCancel() {
                this.$emit('input', false);
            },
            editSave() {
                if (this.ruleForm.order_id == 0) {
                    this.$message.error('请选择订单');
                    return;
                }
                this.edit.btnLoading = true;
                request({
                    params: {
                        r: 'plugin/stock/mall/stock/agent-order-send',
                    },
                    method: 'post',
                    data: this.ruleForm
                }).then(response => {
                    this.edit.btnLoading = false;
                    if (response.data.code == 0) {
                        this.$message.success(response.data.msg);
                        this.editCancel();

                    } else {
                        this.$message.error(response.data.msg);
                    }
                }).catch(response => {
                    this.edit.btnLoading = false;
                });
            },
            keyUp() {

            }
        }
    });
</script>

<?php
Yii::$app->loadComponentView('store/com-dialog-select');
?>
<template id="com-update">
    <div class="com-update">
        <el-dialog :title="dialogTitle" :visible.sync="updateDialogVisible" :close-on-click-modal="false" @close="close">
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
                <el-form-item label="返积分" prop="score_enable">
                    <el-switch
                            v-model="formData.score_enable"
                            active-text="启用"
                            inactive-text="关闭">
                    </el-switch>
                    <div v-if="formData.score_enable">
                        <el-switch v-model="formData.score_give_settings.is_permanent" :active-value="1" :inactive-value="0" active-text="永久有效" inactive-text="限时有效"></el-switch>

                        <div style="margin-top:10px;width:250px">
                            <el-input type="number" :min="0" :max="100" v-model="formData.rate" placeholder="">
                                <template slot="append">%</template>
                            </el-input>
                        </div>

                        <div v-if="!formData.score_give_settings.is_permanent">
                            <div style="margin-top:10px;width:250px">
                                <el-input type="number" :min="0" v-model="formData.score_give_settings.period" placeholder="">
                                    <template slot="append">月</template>
                                </el-input>
                            </div>
                            <div style="margin-top:10px;width:250px">
                                <el-input type="number" v-model="formData.score_give_settings.expire" placeholder="" >
                                    <template slot="append">有效期(天)</template>
                                </el-input>
                            </div>
                        </div>
                    </div>
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
            name: '',
            cover_url: '',
            start_at: '',
            score_enable: false,
            score_give_settings: {
                is_permanent: 0,
                integral_num: 0,
                period: 1,
                period_unit: "month",
                expire: 30
            }
        };
    }

    Vue.component('com-update', {
        template: '#com-update',
        props: {
            visible: Boolean,
            editData: Object
        },
        data() {
            return {
                dialogTitle: "添加商户",
                activeName: "first",
                updateDialogVisible: false,
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
                this.updateDialogVisible = val;
            },
            editData(val, oldVal){
                var scoreEnable = val.enable_score == 1 ? true : false;
                this.formData = Object.assign(initFormData(), val);
                this.formData['score_enable'] = scoreEnable;
            }
        },
        methods: {
            save(){
                let that = this;
                this.$refs['formData'].validate((valid) => {
                    if (valid) {
                        that.btnLoading = true;
                        var formData = that.formData;
                        request({
                            params: {
                                r: 'plugin/integral_card/admin/from-store/edit'
                            },
                            method: 'post',
                            data: formData
                        }).then(e => {
                            that.btnLoading = false;
                            if (e.data.code == 0) {
                                that.$message.success(e.data.msg);
                                that.$emit('up_update');
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
                this.$emit('up_close');
            }
        }
    });
</script>
<template id="com-edit">
    <div class="com-edit">
        <el-dialog width="30%" title="设置积分赠送" :visible.sync="dialogVisible" :close-on-click-modal="false" @close="close">
            <el-form v-if="formData" ref="formData" :rules="formRule" label-width="15%" :model="formData" size="small">

                <el-form-item label="名称" prop="name">
                    <el-input v-model="formData.name" placeholder=""></el-input>
                </el-form-item>

                <el-form-item label="开始日期" prop="start_at">
                    <el-date-picker v-model="formData.start_at" type="date" placeholder="选择日期"></el-date-picker>
                </el-form-item>

                <el-form-item label="结束日期" prop="end_at">
                    <el-date-picker v-model="formData.end_at" type="date" placeholder="选择日期"></el-date-picker>
                </el-form-item>

                <el-form-item label="赠送配置" prop="number">
                    <el-switch v-model="formData.score_give_settings.is_permanent" :active-value="1" :inactive-value="0" active-text="永久有效" inactive-text="限时有效"></el-switch>

                    <div style="margin-top:10px;width:200px">
                        <el-input type="number" :min="0"  v-model="formData.number" placeholder=""></el-input>
                    </div>

                    <div v-if="!formData.score_give_settings.is_permanent">
                        <div style="margin-top:10px;width:200px">
                            <el-input type="number" :min="0" v-model="formData.score_give_settings.period" placeholder="">
                                <template slot="append">月</template>
                            </el-input>
                        </div>
                        <div style="margin-top:10px;width:200px">
                            <el-input type="number" v-model="formData.score_give_settings.expire" placeholder="" >
                                <template slot="append">有效期(天)</template>
                            </el-input>
                        </div>
                    </div>
                </el-form-item>

                <el-form-item label="状态">
                    <el-switch
                            v-model="formData.score_enable"
                            active-text="启用"
                            inactive-text="关闭">
                    </el-switch>
                </el-form-item>

            </el-form>
            <div slot="footer" class="dialog-footer">
                <el-button @click="close">取 消</el-button>
                <el-button :loading="loading" type="primary" @click="save">确 定</el-button>
            </div>
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
                activeName: "first",
                dialogVisible: false,
                loading: false,
                formData: '',
                formRule:{
                    name:[
                        {required: true, message: '名称不能为空', trigger: 'change'},
                    ],
                    number:[
                        {required: true, message: '数量不能为空', trigger: 'change'},
                    ],
                    start_at:[
                        {required: true, message: '开始日期不能为空', trigger: 'change'},
                    ],
                    end_at:[
                        {required: true, message: '结束日期不能为空', trigger: 'change'},
                    ]
                }
            };
        },
        watch: {
            visible(val, oldVal){
                this.dialogVisible = val;
            },
            editData:{
                handler(val, old){
                    let formData = val.id ? val : {
                        name: '',
                        number:0,
                        score_enable: false,
                        enable_score: 0,
                        start_at: '',
                        end_at: '',
                        score_give_settings: {
                            is_permanent: 1,
                            integral_num: 0,
                            period: 1,
                            period_unit: "month",
                            expire: 30
                        }
                    }
                    formData['score_enable'] = formData.enable_score == 1 ? true : false;
                    this.formData = JSON.parse(JSON.stringify(formData));
                },
                deep:true // 必须加这个属性
            }
        },
        mounted: function () {
            this.dialogVisible = this.visible;
        },
        methods: {
            save(){
                let that = this;
                this.$refs['formData'].validate((valid) => {
                    if (valid) {
                        that.loading = true;
                        request({
                            params: {
                                r: "plugin/integral_card/admin/from-free/edit"
                            },
                            method: "post",
                            data: that.formData
                        }).then(e => {
                            that.loading = false;
                            if (e.data.code == 0) {
                                that.$emit('update');
                            } else {
                                that.$message.error(e.data.msg);
                            }
                        }).catch(e => {
                            that.loading = false;
                            that.$message.error(e.data.msg);
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
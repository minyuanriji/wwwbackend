<template id="com-edit">
    <div class="com-edit">
        <el-dialog :title="dialogTitle" :visible.sync="dialogVisible" :close-on-click-modal="false" @close="close">
            <el-form :rules="rules" ref="formData" label-width="20%" :model="formData" size="small">
                <el-form-item label="联盟类型" prop="ali_type">
                    <el-select v-model="formData.ali_type" placeholder="请选择" style="width:200px;">
                        <el-option label="淘宝联盟" value="ali"></el-option>
                    </el-select>
                </el-form-item>
                <el-form-item label="备注" prop="remark">
                    <el-input type="textarea" :rows="2" placeholder="备注内容" v-model="formData.remark" style="width:300px;"></el-input>
                </el-form-item>
                <el-form-item label="排序" prop="sort">
                    <el-input v-model="formData.sort" type="number" min="0" placeholder="排序" style="width:200px;"></el-input>
                </el-form-item>
                <el-form-item label="是否启用" prop="is_open">
                    <el-switch v-model="formData.is_open" :active-value="1" :inactive-value="0"></el-switch>
                </el-form-item>
                <el-form-item label="APP KEY" >
                    <el-input v-model="formData.settings_data.app_key" placeholder="" style="width:300px;"></el-input>
                </el-form-item>
                <el-form-item label="SECRET KEY" >
                    <el-input v-model="formData.settings_data.secret_key" placeholder="" style="width:300px;"></el-input>
                </el-form-item>
                <el-form-item label="妈妈广告位ID" >
                    <el-input v-model="formData.settings_data.adzone_id" placeholder="" style="width:300px;"></el-input>
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
            ali_type: "ali",
            remark: "",
            settings_data: {
                app_key:'',
                secret_key: '',
                adzone_id: ''
            },
            is_open: 0,
            sort: 0
        };
    }

    Vue.component('com-edit', {
        template: '#com-edit',
        props: {
            visible: Boolean,
            record: Object
        },
        data() {
            return {
                dialogTitle: "添加账号",
                activeName: "first",
                dialogVisible: false,
                formData: initFormData(),
                rules: {
                    ali_type: [
                        {required: true, message: '联盟类型不能为空', trigger: 'change'},
                    ],
                    remark: [
                        {required: true, message: '备注不能为空', trigger: 'change'},
                    ],
                    sort: [
                        {required: true, message: '排序不能为空', trigger: 'change'},
                    ],
                },
                btnLoading: false
            };
        },
        created() {},
        watch: {
            visible(val, oldVal){
                this.dialogVisible = val;
            },
            record(val, oldVal){
                this.formData = Object.assign(initFormData(), val);
                if(typeof this.formData['id'] == "undefined" || parseInt(this.formData['id']) <= 0){
                    this.dialogTitle = "添加账号";
                }else{
                    this.dialogTitle = "编辑账号";
                }
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
                                r: 'plugin/taolijin/mall/ali/edit'
                            },
                            method: 'post',
                            data: that.formData
                        }).then(e => {
                            that.btnLoading = false;
                            if (e.data.code == 0) {
                                that.$message.success(e.data.msg);
                                that.update();
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
            },
            update(){
                this.$emit('update');
            }
        }
    });
</script>
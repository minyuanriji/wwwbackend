<template id="com-edit">
    <div class="com-edit">
        <el-dialog :title="dialogTitle" :visible.sync="dialogVisible" :close-on-click-modal="false" @close="close">
            <el-form :rules="rules" ref="formData" label-width="20%" :model="formData" size="small">
                <el-form-item label="应用名称" prop="name">
                    <el-input v-model="formData.name" style="width:350px"></el-input>
                </el-form-item>
                <el-form-item label="类型" prop="type">
                    <el-select v-model="formData.type" placeholder="请选择">
                        <el-option label="社交电商" value="distribution"></el-option>
                    </el-select>
                </el-form-item>
                <el-form-item label="APP KEY" prop="app_key">
                    <el-input v-model="formData.app_key" style="width:350px"></el-input>
                </el-form-item>
                <el-form-item label="SECRET" prop="secret">
                    <el-input v-model="formData.secret" style="width:350px"></el-input>
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
            name: '',
            type: '',
            app_key: '',
            secret: ''
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
                dialogTitle: "添加应用",
                activeName: "first",
                dialogVisible: false,
                formData: initFormData(),
                rules: {
                    name: [
                        {required: true, message: '应用名称不能为空', trigger: 'change'},
                    ],
                    type: [
                        {required: true, message: '类型不能为空', trigger: 'change'},
                    ],
                    app_key: [
                        {required: true, message: 'APP KEY不能为空', trigger: 'change'},
                    ],
                    secret: [
                        {required: true, message: 'SECRET不能为空', trigger: 'change'},
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
            save(){
                let that = this;
                this.$refs['formData'].validate((valid) => {
                    if (valid) {
                        that.btnLoading = true;
                        request({
                            params: {
                                r: 'plugin/alibaba/mall/app/edit'
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
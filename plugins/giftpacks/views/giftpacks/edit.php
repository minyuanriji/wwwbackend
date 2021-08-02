<div id="edit_app" v-cloak>

    <el-dialog :title="dailogTitle" :visible.sync="dialogFormVisible">

        <el-form :rules="rules" ref="formData" label-width="80px" :model="formData" size="small">
            <el-form-item label="标题" prop="title">
                <el-input v-model="formData.title"></el-input>
            </el-form-item>
            <el-form-item label="封面" prop="cover_pic">
                <com-attachment :multiple="false" :max="1" v-model="formData.cover_pic">
                    <el-tooltip class="item"
                                effect="dark"
                                content="建议尺寸:240 * 240"
                                placement="top">
                        <el-button size="mini">选择文件</el-button>
                    </el-tooltip>
                </com-attachment>
                <com-image mode="aspectFill" width='80px' height='80px' :src="formData.cover_pic"></com-image>
            </el-form-item>
            <el-form-item label="价格">
                <el-input type="number" style="width:150px" v-model="formData.price"></el-input>
            </el-form-item>
        </el-form>

        <div slot="footer" class="dialog-footer">
            <el-button @click="dialogFormVisible = false">取 消</el-button>
            <el-button :loading="btnLoading" type="primary" @click="save()">确 定</el-button>
        </div>
    </el-dialog>
</div>
<script>
    const editApp = new Vue({
        el: '#edit_app',
        data: {
            dailogTitle: '',
            dialogFormVisible: false,
            btnLoading: false,
            formData: {
                title: '',
                cover_pic: '',
                price: 0
            },
            rules: {
                title: [
                    {required: true, message: '标题不能为空', trigger: 'change'}
                ],
                cover_pic: [
                    {required: true, message: '封面不能为空', trigger: 'change'}
                ],
                price: [
                    {required: true, message: '价格不能为空', trigger: 'change'}
                ]
            },
            savedCallFn : null
        },
        methods: {
            show(title, row, fn){
                this.dailogTitle = title;
                this.dialogFormVisible = true;
                this.savedCallFn = fn;
                if(row != null){
                    this.formData = row;
                }
            },
            hide(){
                this.dialogFormVisible = false;
            },
            save() {
                this.$refs['formData'].validate((valid) => {
                    let self = this;
                    if (valid) {
                        self.btnLoading = true;
                        request({
                            params: {
                                r: 'plugin/giftpacks/mall/giftpacks/edit'
                            },
                            method: 'post',
                            data: self.formData
                        }).then(e => {
                            self.btnLoading = false;
                            if (e.data.code == 0) {
                                self.$message.success(e.data.msg);
                                self.hide();
                                self.savedCallFn();
                            } else {
                                self.$message.error(e.data.msg);
                            }
                        }).catch(e => {
                            self.$message.error(e.data.msg);
                            self.btnLoading = false;
                        });
                    } else {
                        console.log('error submit!!');
                        return false;
                    }
                });
            },
        }
    });
</script>
<style>
    .form-body {
        padding: 10px 20px;
        background-color: #fff;
        margin-bottom: 20px;
    }

    .form-button {
        margin: 0;
    }

    .form-button .el-form-item__content {
        margin-left: 0 !important;
    }

    .button-item {
        padding: 9px 25px;
        margin-bottom: 20px;
    }

    .open-img .el-dialog {
        margin-top: 0 !important;
    }

    .click-img {
        width: 100%;
    }

    .el-input-group__append {
        background-color: #fff
    }
</style>

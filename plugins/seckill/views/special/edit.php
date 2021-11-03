<style>
    .button-item {
        padding: 9px 25px;
    }
    .add-image-btn {
        width: 100px;
        height: 100px;
        color: #419EFB;
        border: 1px solid #e2e2e2;
        cursor: pointer;
    }
    .del-btn {
        height: 35px;
    }
</style>
<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" class="box-card" body-style="padding: 10px 10px;" class="box-card" v-loading="cardLoading">
        <div slot="header">
            <div>
                <span>秒杀编辑</span>
            </div>
        </div>

        <div style="background: white;">
            <el-form :model="ruleForm" :rules="rules" size="medium" ref="ruleForm" label-width="200px">
                <el-form-item prop="name" label="专题名称">
                    <el-input v-model="ruleForm.name"></el-input>
                </el-form-item>

                <el-form-item label="封面图" prop="pic_url">
                    <template v-if="ruleForm.pic_url.length">
                        <draggable v-model="ruleForm.pic_url" flex="dif:left">
                            <com-attachment @selected="updatePicUrl">
                                <com-image mode="aspectFill" width="100px" height='100px' :src="ruleForm.pic_url"></com-image>
                            </com-attachment>
                            <el-button class="del-btn" size="mini" type="danger" icon="el-icon-close" circle @click="delPic()"></el-button>
                        </draggable>
                    </template>
                    <template v-if="ruleForm.pic_url.length < 1">
                        <com-attachment style="margin-bottom: 10px;" :multiple="true" :max="9" @selected="picUrl">
                            <el-tooltip class="item" effect="dark" content="建议尺寸:750 * 750" placement="top">
                                <div flex="main:center cross:center" class="add-image-btn">
                                    + 添加图片
                                </div>
                            </el-tooltip>
                        </com-attachment>
                    </template>
                </el-form-item>

                <el-form-item label="开始时间" prop="start_time">
                    <el-date-picker
                            v-model="ruleForm.start_time"
                            type="datetime"
                            placeholder="选择日期时间">
                    </el-date-picker>
                </el-form-item>

                <el-form-item label="结束时间" prop="end_time">
                    <el-date-picker
                            v-model="ruleForm.end_time"
                            type="datetime"
                            placeholder="选择日期时间">
                    </el-date-picker>
                </el-form-item>
            </el-form>
            <div style="margin-top: -5px;margin-left: 200px">
                <el-button class="button-item" :loading="btnLoading" type="primary" @click="store('ruleForm')" size="small">保存</el-button>
            </div>
        </div>
    </el-card>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                ruleForm: {
                    name: '',
                    start_time: '',
                    end_time: '',
                    pic_url:'',
                },
                rules: {
                    name: [
                        {required: true, message: '请输入名称', trigger: 'change'},
                    ],
                    start_time: [
                        {required: true, message: '请输入开始时间', trigger: 'change'},
                    ],
                    end_time: [
                        {required: true, message: '请输入结束时间', trigger: 'change'},
                    ],
                    pic_url: [
                        {required: true, message: '请添加轮播图', trigger: 'change'},
                    ],
                },
                keyword: '',
                btnLoading: false,
            };
        },
        methods: {
            updatePicUrl(e) {
                if (e.length) {
                    this.ruleForm.pic_url = e[0].url;
                }
            },
            picUrl(e) {
                if (e.length) {
                    this.ruleForm.pic_url = e[0].url;
                }
            },
            delPic() {
                this.ruleForm.pic_url = '';
            },
            store(formName) {
                this.$refs[formName].validate((valid) => {
                    let self = this;
                    if (valid) {
                        self.btnLoading = true;
                        request({
                            params: {
                                r: 'plugin/seckill/mall/special/special/edit'
                            },
                            method: 'post',
                            data: {
                                form: self.ruleForm,
                            }
                        }).then(e => {
                            self.btnLoading = false;
                            if (e.data.code == 0) {
                                self.$message.success(e.data.msg);
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
            getDetail() {
                let self = this;
                self.cardLoading = true;
                request({
                    params: {
                        r: 'plugin/seckill/mall/special/special/edit',
                        seckill_id: getQuery('id')
                    },
                    method: 'get',
                }).then(e => {
                    self.cardLoading = false;
                    if (e.data.code == 0) {
                        self.ruleForm = e.data.data;
                    } else {
                        self.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    console.log(e);
                });
            },
        },
        mounted: function () {
            if (getQuery('id')) {
                this.getDetail();
            }
        }
    });
</script>

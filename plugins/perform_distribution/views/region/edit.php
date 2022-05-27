<div id="app" v-cloak>
    <el-card class="box-card" v-loading="cardLoading" shadow="never" style="border:0"
             body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <el-breadcrumb separator="/">
                <el-breadcrumb-item>
                    <span style="color: #409EFF;cursor: pointer"
                          @click="$navigate({r:'plugin/perform_distribution/mall/region/index'})">
                        区域设置
                    </span>
                </el-breadcrumb-item>
                <el-breadcrumb-item>编辑区域</el-breadcrumb-item>
            </el-breadcrumb>
        </div>
        <div class="form-body">
            <el-form :model="ruleForm" :rules="rules" ref="ruleForm" label-width="150px">
                <el-form-item label="区域名称" prop="name">
                    <el-input v-model="ruleForm.name" style="width:350px"></el-input>
                </el-form-item>
                <el-form-item label="地址" prop="address">
                    <el-cascader @change="addressChange" :options="district" :props="props" v-model="cityArr"></el-cascader>
                    <el-input v-if="ruleForm.city_id" placeholder="详细地址" v-model="ruleForm.address" style="display:block;width:350px;margin-top:5px;"></el-input>
                </el-form-item>
                <el-form-item label=" ">
                    <el-button class="button-item" :loading="btnLoading" type="primary" @click="submitForm('ruleForm')">保存</el-button>
                </el-form-item>
            </el-form>
        </div>
    </el-card>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                cardLoading: false,
                btnLoading: false,
                district: [],
                cityArr: null,
                props: {
                    value: 'id',
                    label: 'name',
                    children: 'list'
                },
                ruleForm: {
                    name: '',
                    address: '',
                    province_id: '',
                    city_id: '',
                    district_id: ''
                },
                rules: {
                    name: [
                        {required: true, message: '请输入区域名称', trigger: 'change'},
                    ],
                    address: [
                        {required: true, message: '请设置地址', trigger: 'blur'},
                    ]
                }
            };
        },
        mounted() {
            if (getQuery('id')) {
                this.loadData();
            }
            this.getDistrict(1);
        },
        methods: {
            addressChange(e) {
                this.ruleForm.province_id = this.cityArr[0];
                this.ruleForm.city_id = this.cityArr[1];
                this.ruleForm.district_id = this.cityArr.length >= 3 ? this.cityArr[2] : 0;
            },
            getDistrict(level) { // 获取省市区列表
                if (level == 4) {
                    level1 = 1;
                } else if (level == 3) {
                    level1 = 2;
                } else if (level == 2) {
                    level1 = 3;
                } else {
                    level1 = 4;
                }
                request({
                    params: {
                        r: 'district/index',
                        level: level1
                    },
                }).then(e => {
                    if (e.data.code == 0) {
                        this.district = e.data.data.district;
                    }
                }).catch(e => {
                });
            },
            submitForm(formName) {
                let self = this, ruleForm = this.ruleForm;
                ruleForm['id'] = getQuery('id');
                this.$refs[formName].validate((valid) => {
                    if (valid) {
                        self.btnLoading = true;
                        request({
                            params: {
                                r: 'plugin/perform_distribution/mall/region/edit'
                            },
                            method: 'post',
                            data: ruleForm
                        }).then(e => {
                            self.btnLoading = false;
                            if (e.data.code == 0) {
                                self.$message.success(e.data.msg);
                            } else {
                                self.$message.error(e.data.msg);
                            }
                            navigateTo({
                                r: 'plugin/perform_distribution/mall/region/index'
                            })
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
            loadData() {
                this.cardLoading = true;
                request({
                    params: {
                        r: 'plugin/perform_distribution/mall/region/edit',
                        id: getQuery('id'),
                    },
                    method: 'get'
                }).then(e => {
                    this.cardLoading = false;
                    if (e.data.code == 0) {
                        if (e.data.data) {
                            this.ruleForm = e.data.data.detail;
                            this.cityArr = [this.ruleForm.province_id, this.ruleForm.city_id, this.ruleForm.district_id];
                        }
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    console.log(e);
                });
            }
        }
    });
</script>

<style>

</style>
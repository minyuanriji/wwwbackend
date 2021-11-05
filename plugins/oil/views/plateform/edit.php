<style>
    .button-item {
        padding: 9px 25px;
    }

</style>
<div id="app" v-cloak>


    <el-card shadow="never" style="border:0" class="box-card" body-style="padding: 10px 10px;" class="box-card" v-loading="cardLoading">

        <div slot="header">
            <div>
                <span>平台编辑</span>
                <div style="float: right;margin-top: -5px">
                    <el-button class="button-item" :loading="btnLoading" type="primary" @click="store('ruleForm')" size="small">保存</el-button>
                </div>
            </div>
        </div>

        <div style="background: white;">
            <el-form :model="ruleForm" :rules="rules" size="medium" ref="ruleForm" label-width="200px" style="">
                <el-tabs v-model="activeName">
                    <el-tab-pane label="基础信息" name="Basics" style="padding:20px 0;background: white">
                        <el-form-item prop="name" label="平台名称">
                            <el-input v-model="ruleForm.name" style="width:350px;"></el-input>
                        </el-form-item>
                        <el-form-item label="SDK目录" prop="sdk_src" >
                            <el-input  v-model="ruleForm.sdk_src" style="width:350px;"></el-input>
                        </el-form-item>
                        <el-form-item label="自定义参数" prop="sdk_src">
                            <el-card class="box-card" style="width:60%;">
                                <el-table :data="paramsList" highlight-current-row style="width: 100%">
                                    <el-table-column property="type" label="类型" width="100" align="center">
                                        <template slot-scope="scope">
                                            <span v-if="scope.row.type=='image'">图片</span>
                                            <span v-if="scope.row.type=='input'">输入</span>
                                        </template>
                                    </el-table-column>
                                    <el-table-column property="name" label="名称" width="150" ></el-table-column>
                                    <el-table-column label="内容">
                                        <template slot-scope="scope">
                                            <span v-if="scope.row.type=='input'">
                                                <el-input v-model="scope.row.value"></el-input>
                                            </span>
                                            <span v-if="scope.row.type=='image'">
                                                <com-attachment :multiple="false" :max="1" v-model="scope.row.value">
                                                    <el-tooltip class="item"
                                                                effect="dark"
                                                                content="建议尺寸:240 * 240"
                                                                placement="top">
                                                        <el-button size="mini">选择文件</el-button>
                                                    </el-tooltip>
                                                </com-attachment>
                                                <com-image mode="aspectFill" width='80px' height='80px' :src="scope.row.value" style="margin-top:3px;"></com-image>
                                            </span>
                                        </template>
                                    </el-table-column>
                                    <el-table-column label="操作" width="110" align="center">
                                        <template slot-scope="scope">
                                            <el-button @click="delParam(scope.row)" type="text" circle size="mini">
                                                <el-tooltip class="item" effect="dark" content="删除" placement="top">
                                                    <img src="statics/img/mall/del.png" alt="">
                                                </el-tooltip>
                                            </el-button>
                                        </template>
                                    </el-table-column>
                                </el-table>
                                <div style="margin-top: 20px">
                                    <template v-if="new_param_edit">
                                        <el-select v-model="new_param_type" placeholder="类型" size="big" style="width:150px;">
                                            <el-option value="input" label="输入框"></el-option>
                                            <el-option value="image" label="图片上传"></el-option>
                                        </el-select>
                                        <el-input  v-model="new_param_name" size="big" style="width:300px;"></el-input>
                                        <el-button @click="newParam" icon="el-icon-edit-outline" size="big" type="danger">保存</el-button>
                                        <el-button @click="new_param_edit=false" size="big">取消</el-button>
                                    </template>
                                    <el-button v-if="!new_param_edit" @click="new_param_edit=true" icon="el-icon-plus" size="big">添加</el-button>
                                </div>
                            </el-card>
                        </el-form-item>
                    </el-tab-pane>
                    <el-tab-pane label="限制" name="limit" style="padding:20px 0px;background: white">
                        <el-form-item label="限制地区" prop="region_deny_list">
                            <el-table :data="region_deny_list" border style="width: 40%">
                                <el-table-column prop="province" label="省" align="center"></el-table-column>
                                <el-table-column prop="city" label="市" align="center"></el-table-column>
                                <el-table-column prop="district" label="区"  align="center"></el-table-column>
                                <el-table-column label="操作"  align="center" width="100">
                                    <template slot-scope="scope">
                                        <el-button @click="deleteRegion(scope.$index)" type="text" circle size="mini">
                                            <el-tooltip class="item" effect="dark" content="删除" placement="top">
                                                <img src="statics/img/mall/del.png" alt="">
                                            </el-tooltip>
                                        </el-button>
                                    </template>
                                </el-table-column>
                            </el-table>

                            <div v-if="is_new_region" style="margin-top:10px;">
                                <el-cascader size="big"
                                             :options="district"
                                             :props="props"
                                             v-model="new_region_arr"
                                             clearable>
                                </el-cascader>
                                <el-button :loading="new_region_loading" @click="newRegion" type="danger" size="big" >确定</el-button>
                                <el-button @click="is_new_region=false" size="big" >取消</el-button>
                            </div>
                            <el-button v-else @click="is_new_region=true" icon="el-icon-plus" size="big" style="margin-top:10px;">添加</el-button>

                        </el-form-item>
                    </el-tab-pane>
                    <el-tab-pane v-if="ruleForm.id > 0" label="产品信息" name="product" style="padding:20px 20px;background:white">

                        <div style="">
                            <el-table v-loading="loading" :data="ruleForm.products" >
                                <el-table-column prop="name" label="编号" width="100" align="center"></el-table-column>
                                <el-table-column prop="price" label="价格" width="200" align="center"></el-table-column>
                                <el-table-column label="操作" align="center">
                                    <template slot-scope="scope">
                                        <el-button @click="delProduct(ruleForm.id, scope.row.name, scope.$index)" circle type="text" size="mini">
                                            <el-tooltip class="item" effect="dark" content="删除" placement="top">
                                                <img src="statics/img/mall/del.png" alt="">
                                            </el-tooltip>
                                        </el-button>
                                    </template>
                                </el-table-column>

                            </el-table>
                            <el-button :loading="btnLoading" type="primary" @click="addProduct" size="big" style="margin-top:10px;">新增</el-button>
                        </div>

                    </el-tab-pane>

                </el-tabs>
            </el-form>
        </div>

    </el-card>

    <!-- 添加产品 -->
    <el-dialog title="添加产品" :visible.sync="dialogProduct" width="30%">
        <el-form :model="productForm" label-width="80px" :rules="productFormRules" ref="productForm">
            <el-form-item label="编号" prop="product_key" size="small">
                <el-input type="number" v-model="productForm.product_key"></el-input>
            </el-form-item>
            <el-form-item label="金额" prop="product_price" size="small">
                <el-input type="number" v-model="productForm.product_price"></el-input>
            </el-form-item>
            <el-form-item label="排序" prop="sort" size="small">
                <el-input type="number" min="0" v-model="productForm.sort"></el-input>
            </el-form-item>
        </el-form>
        <div slot="footer" class="dialog-footer">
            <el-button @click="dialogProduct = false">取消</el-button>
            <el-button :loading="pdLoading" type="primary" @click="productSubmit">添加</el-button>
        </div>
    </el-dialog>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                is_new_region: false,
                new_region_arr: [],
                new_region_loading: false,
                region_deny_list: [],
                platList: [],
                props: {
                    value: 'id',
                    label: 'name',
                    children: 'list',
                    checkStrictly: true
                },
                district: [],
                districtMap: {},
                activeName: 'Basics',
                ruleForm: {
                    id: 0,
                    name: '',
                    sdk_src: '',
                    region_deny: [],
                    products: []
                },
                paramsList: [],
                new_param_type: '',
                new_param_name: '',
                new_param_edit: false,
                rules: {
                    name: [
                        {required: true, message: '请输入平台名称', trigger: 'change'},
                    ],
                    sdk_src: [
                        {required: true, message: '请输入平台SDK目录', trigger: 'change'},
                    ]
                },
                loading: false,
                btnLoading: false,
                cardLoading: false,
                keyword: '',
                dialogProduct: false,
                productForm: {
                    plateform_id: 0,
                    product_key: '',
                    product_price: 0,
                    sort: 0
                },
                pdLoading: false,
                productFormRules: {
                    product_key: [
                        {required: true, message: '请传入产品编号', trigger: 'blur'},
                    ],
                    product_price: [
                        {required: true, message: '金额不能为空', trigger: 'blur'},
                    ],
                    sort: [
                        {required: true, message: '排序不能为空', trigger: 'blur'},
                    ],
                },
            };
        },
        methods: {
            delParam(row){
                this.$confirm('删除该条数据, 是否继续?', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    let i, newParamsList = [];
                    for(i=0; i < this.paramsList.length; i++){
                        if(this.paramsList[i].name != row.name){
                            newParamsList.push(this.paramsList[i]);
                        }
                    }
                    this.paramsList = newParamsList;
                }).catch(() => {

                });

            },
            newParam(){
                if(this.new_param_type == ''){
                    this.$message.error("请选择参数类型");
                    return;
                }
                if(this.new_param_name == ''){
                    this.$message.error("请输入参数名称");
                    return;
                }
                if(!this.new_param_name.match(/^[a-z_]+[0-9]*$/i)){
                    this.$message.error("参数名称只能为英文字符、下划线、数字组成，且首字符不能为数字");
                    return;
                }
                let i;
                for(i=0; i < this.paramsList.length; i++){
                    if(this.paramsList[i].name == this.new_param_name){
                        this.$message.error("此参数名称已在列表中");
                        return;
                    }
                }
                this.paramsList.push({
                    type: this.new_param_type,
                    name: this.new_param_name,
                    value: ''
                });
                this.new_param_edit = false;
                this.new_param_type = '';
                this.new_param_name = '';
            },
            deleteRegion(index){
                let that = this;
                this.$confirm('你确定要删除吗?', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    let i, region_deny_list = [];
                    for(i=0; i < that.region_deny_list.length; i++){
                        if(i != index){
                            region_deny_list.push(that.region_deny_list[i]);
                        }
                    }
                    that.region_deny_list = region_deny_list;
                });
            },
            newRegion(){
                if(this.new_region_arr.length <= 0){
                    this.$message.error("请选择地区");
                    return;
                }

                this.new_region_loading = true;

                let i, newItem = {province_id:0}, id, region = null;
                for(i=0; i < this.new_region_arr.length; i++){
                    id = this.new_region_arr[i];
                    if(typeof this.districtMap[id] == "undefined")
                        continue;
                    region = this.districtMap[id];

                    if(region['level'] == "province"){
                        newItem['province_id'] = id;
                        newItem['province'] = region.name;
                    }

                    if(region['level'] == "city"){
                        newItem['city_id'] = id;
                        newItem['city'] = region.name;
                    }

                    if(region['level'] == "district"){
                        newItem['district_id'] = id;
                        newItem['district'] = region.name;
                    }
                }

                if(newItem.province_id != 0){
                    this.region_deny_list.push(newItem);
                }

                this.is_new_region = false;
                this.new_region_loading = false;
            },
            handleClick(tab, event) {
                this.activeName = tab.name;
            },
            store(formName) {
                this.$refs[formName].validate((valid) => {
                    let self = this;
                    if (valid) {
                        self.btnLoading = true;
                        self.ruleForm.region_deny = self.region_deny_list;
                        self.ruleForm['params'] = self.paramsList;
                        if (getQuery('id')) {
                            self.ruleForm['id'] = getQuery('id');
                        }
                        request({
                            params: {
                                r: 'plugin/oil/mall/plateform/edit'
                            },
                            method: 'post',
                            data: self.ruleForm
                        }).then(e => {
                            self.btnLoading = false;
                            if (e.data.code == 0) {
                                self.$message.success(e.data.msg);
                                navigateTo({
                                    r: 'plugin/oil/mall/plateform/list'
                                })
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
                        r: 'plugin/oil/mall/plateform/edit',
                        id: getQuery('id')
                    },
                    method: 'get',
                }).then(e => {
                    self.cardLoading = false;
                    if (e.data.code == 0) {
                        self.region_deny_list = e.data.data.region_deny;
                        self.paramsList = e.data.data.params;
                        self.ruleForm = e.data.data;
                    } else {
                        self.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    console.log(e);
                });
            },
            delProduct(plateform_id, product_key, index){
                this.$confirm('你确定要删除吗?', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    let self = this;
                    request({
                        params: {
                            r: 'plugin/oil/mall/plateform/del-product'
                        },
                        method: 'post',
                        data: {
                            plateform_id:plateform_id,
                            product_key:product_key
                        }
                    }).then(e => {
                        if (e.data.code == 0) {
                            this.$message.success(e.data.msg);
                            self.getDetail();
                        } else {
                            this.$message.error(e.data.msg);
                        }
                    }).catch(e => {
                        this.$message.error(e.data.msg);
                    });
                }).catch(() => {

                });
            },

            addProduct() {
                this.dialogProduct = true;
                this.productForm.plateform_id = this.ruleForm.id;
            },

            productSubmit() {
                var self = this;
                this.$refs.productForm.validate((valid) => {
                    if (valid) {
                        let para = Object.assign({}, self.productForm);
                        self.pdLoading = true;
                        request({
                            params: {
                                r: 'plugin/oil/mall/plateform/add-product',
                            },
                            method: 'post',
                            data: para,
                        }).then(e => {
                            if (e.data.code === 0) {
                                self.pdLoading = false;
                                self.dialogProduct = false;
                                self.getDetail();
                            } else {
                                self.$message.error(e.data.msg);
                            }
                            self.pdLoading = false;
                        }).catch(e => {
                            self.pdLoading = false;
                        });
                    }
                });
            },
            // 获取省市区列表
            getDistrict() {
                request({
                    params: {
                        r: 'district/index',
                        level: 3
                    },
                }).then(e => {
                    if (e.data.code == 0) {
                        this.district = e.data.data.district;
                        let i,j,k;
                        for(i=0; i < this.district.length; i++){
                            for(j=0; j< this.district[i].list.length; j++){
                                for(k=0; k < this.district[i].list[j].list.length; k++){
                                    this.districtMap[this.district[i].list[j].list[k].id] = {
                                        name:this.district[i].list[j].list[k].name,
                                        level:this.district[i].list[j].list[k].level
                                    }
                                }
                                this.districtMap[this.district[i].list[j].id] = {
                                    name:this.district[i].list[j].name,
                                    level:this.district[i].list[j].level
                                }
                            }
                            this.districtMap[this.district[i].id] = {
                                name:this.district[i].name,
                                level:this.district[i].level
                            }
                        }
                    }
                }).catch(e => {

                });
            }
        },
        mounted: function () {
            this.getDistrict();
            if (getQuery('id')) {
                this.getDetail();
            }
        }
    });
</script>

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
            <el-form :model="ruleForm" :rules="rules" size="medium" ref="ruleForm" label-width="200px">
                <el-tabs v-model="activeName">
                    <el-tab-pane label="基础信息" name="Basics" style="padding:20px 0;background: white">
                        <el-form-item prop="name" label="平台名称">
                            <el-input v-model="ruleForm.name"></el-input>
                        </el-form-item>
                        <el-form-item label="SdkDir目录" prop="sdk_dir">
                            <el-input  v-model="ruleForm.sdk_dir"></el-input>
                        </el-form-item>
                        <el-form-item label="ClassDir目录" prop="class_dir">
                            <el-input  v-model="ruleForm.class_dir"></el-input>
                        </el-form-item>
                        <el-form-item label="服务费比例" prop="transfer_rate">
                            <el-input type="number" v-model="ruleForm.transfer_rate" style="width: 500px;">
                                <template slot="append">%</template>
                            </el-input>
                            <div style="color: red;font-size: 12px">请输入服务费0-100</div>
                        </el-form-item>
                        <el-form-item label="金豆收费比例" prop="ratio">
                            <el-input type="number" v-model="ruleForm.ratio" style="width: 500px;">
                                <template slot="append">%</template>
                            </el-input>
                            <div style="color: red;font-size: 12px">例如：10   充值100元 金豆扣取110</div>
                        </el-form-item>
                        <el-form-item label="推荐人" prop="parent_id">
                            <el-autocomplete v-model="ruleForm.parent_name"
                                             value-key="nickname"
                                             :fetch-suggestions="querySearchAsync"
                                             placeholder="请输入搜索内容"
                                             @select="inviterClick">
                            </el-autocomplete>
                        </el-form-item>
                        <el-form-item label="自定义参数" prop="params">
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
                        <el-form-item label="快充" prop="enable_fast">
                            <el-switch v-model="ruleForm.enable_fast"
                                       active-text="启用"
                                       inactive-text="关闭"
                                       active-value="1"
                                       inactive-value="0">
                            </el-switch>
                        </el-form-item>
                        <el-form-item label="慢充" prop="enable_slow">
                            <el-switch v-model="ruleForm.enable_slow"
                                       active-text="启用"
                                       inactive-text="关闭"
                                       active-value="1"
                                       inactive-value="0">
                            </el-switch>
                        </el-form-item>
                        <el-form-item label="禁止号码" prop="pattern_deny">
                            <el-input type="textarea" :rows="2" placeholder="请输入内容" v-model="ruleForm.pattern_deny" style="width:500px"></el-input>
                            <div style="padding:5px 5px;border:1px solid #ddd;border-radius:3px;background:#fbfbfb;line-height:20px;color:gray;margin-top:10px;width:500px;">
                                <div><span>举例：</span></div>
                                <div>19*，禁止19开头的号码充值</div>
                                <div>*84，禁止84结尾的号码充值</div>
                            </div>
                        </el-form-item>
                        <el-form-item label="服务商" prop="allow_plats">
                            <el-checkbox-group v-model="platList">
                                <el-checkbox label="电信"></el-checkbox>
                                <el-checkbox label="移动"></el-checkbox>
                                <el-checkbox label="联通"></el-checkbox>
                            </el-checkbox-group>
                        </el-form-item>
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
                    <el-tab-pane label="产品信息" name="product" style="padding:20px 20px;background:white">

                        <div style="">
                            <el-table v-loading="listLoading" :data="ruleForm.product_json_data" >

                                <el-table-column prop="product_id" label="ID" width="100"></el-table-column>
                                <el-table-column prop="price" label="价格" width="100"></el-table-column>

                                <el-table-column label="类型">
                                    <template slot-scope="scope">
                                        <com-ellipsis v-if="scope.row.type =='slow'">慢充</com-ellipsis>
                                        <com-ellipsis v-else="">快充</com-ellipsis>
                                    </template>
                                </el-table-column>

                                <el-table-column label="操作" width="220">
                                    <template slot-scope="scope">
                                        <el-button @click="delProduct(ruleForm.id, scope.row.product_id, scope.$index)" circle type="text" size="mini">
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
            <el-form-item label="产品ID" prop="product_id" size="small">
                <el-input type="number" v-model="productForm.product_id"></el-input>
            </el-form-item>
            <el-form-item label="金额" prop="product_price" size="small">
                <el-input type="number" v-model="productForm.product_price"></el-input>
            </el-form-item>
            <el-form-item label="类型" prop="product_type">
                <el-radio v-model="productForm.product_type" label="1">快充</el-radio>
                <el-radio v-model="productForm.product_type" label="2">慢充</el-radio>
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
                listLoading: false,
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
                    name: '',
                    sdk_dir: '',
                    ratio: '',
                    parent_id: '',
                    transfer_rate: '',
                    class_dir: '',
                    product_json_data: '',
                    enable_fast: "1",
                    enable_slow: "0",
                    pattern_deny: "",
                    allow_plats: [],
                    region_deny: []
                },
                rules: {
                    name: [
                        {required: true, message: '请输入平台名称', trigger: 'change'},
                    ],
                    sdk_dir: [
                        {required: true, message: '请输入平台SDK目录', trigger: 'change'},
                    ],
                    class_dir: [
                        {required: true, message: '请输入平台class目录', trigger: 'change'},
                    ],
                    transfer_rate: [
                        {required: true, message: '请输入服务费0-100', trigger: 'change'},
                    ],
                    ratio: [
                        {required: true, message: '金豆收费比例', trigger: 'change'},
                    ],
                    /*parent_id: [
                        {required: true, message: '请选择推荐人', trigger: 'change'},
                    ]*/
                },
                btnLoading: false,
                cardLoading: false,
                keyword: '',
                dialogProduct: false,
                productForm: {
                    product_id: '',
                    product_price: '',
                    product_type: '',
                    type: '',
                    price: '',
                    id:'',
                },
                pdLoading: false,
                productFormRules: {
                    product_id: [
                        {required: true, message: '请传入产品ID', trigger: 'blur'},
                    ],
                    product_price: [
                        {required: true, message: '金额不能为空', trigger: 'blur'},
                    ],
                    product_type: [
                        {required: true, message: '类型不能为空', trigger: 'change'},
                    ],
                },
                paramsList: [],
                new_param_type: '',
                new_param_name: '',
                new_param_edit: false,
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

            querySearchAsync(queryString, cb) {
                this.keyword = queryString;
                this.searchUser(cb);
            },

            inviterClick(row) {
                this.ruleForm.parent_id = row.id;
            },

            searchUser(cb) {
                request({
                    params: {
                        r: 'mall/user/get-can-bind-inviter',
                        keyword: this.keyword,
                        user_id: this.ruleForm.parent_id,
                    },
                }).then(e => {
                    if (e.data.code === 0) {
                        cb(e.data.data.list);
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {
                });
            },

            store(formName) {
                this.$refs[formName].validate((valid) => {
                    let self = this;
                    if (valid) {
                        self.btnLoading = true;
                        self.ruleForm.allow_plats = self.getPlatCodes();
                        self.ruleForm.region_deny = self.region_deny_list;
                        self.ruleForm['params'] = self.paramsList;
                        request({
                            params: {
                                r: 'plugin/addcredit/mall/plateforms/plateforms/edit'
                            },
                            method: 'post',
                            data: {
                                form: self.ruleForm,
                            }
                        }).then(e => {
                            self.btnLoading = false;
                            if (e.data.code == 0) {
                                self.$message.success(e.data.msg);
                                navigateTo({
                                    r: 'plugin/addcredit/mall/plateforms/plateforms/index'
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
            setPlatList(allow_plats){
                let i, platList = [], codes = [];
                if(allow_plats != null && allow_plats.length > 0){
                    codes = allow_plats.split(",");
                }
                for(i=0; i < codes.length; i++){
                    if(codes[i] == "10000"){
                        platList.push("电信");
                    }
                    if(codes[i] == "10086"){
                        platList.push("移动");
                    }
                    if(codes[i] == "10010"){
                        platList.push("联通");
                    }
                }
                this.platList = platList;
            },
            getPlatCodes: function(){
                let i, platCodes = [];
                for(i=0; i < this.platList.length; i++){
                    if(this.platList[i] == "电信"){
                        platCodes.push(10000);
                    }
                    if(this.platList[i] == "移动"){
                        platCodes.push(10086);
                    }
                    if(this.platList[i] == "联通"){
                        platCodes.push(10010);
                    }
                }
                return platCodes.join(",");
            },
            getDetail() {
                let self = this;
                self.cardLoading = true;
                request({
                    params: {
                        r: 'plugin/addcredit/mall/plateforms/plateforms/edit',
                        id: getQuery('id')
                    },
                    method: 'get',
                }).then(e => {
                    self.cardLoading = false;
                    if (e.data.code == 0) {
                        self.setPlatList(e.data.data.allow_plats);
                        self.region_deny_list = e.data.data.region_deny;
                        self.ruleForm = e.data.data;
                        self.paramsList = e.data.data.params;
                    } else {
                        self.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    console.log(e);
                });
            },

            delProduct(id, product_id, index){
                this.$confirm('你确定要删除吗?', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    request({
                        params: {
                            r: 'plugin/addcredit/mall/plateforms/plateforms/del-product'
                        },
                        method: 'post',
                        data: {
                            id:id,
                            product_id:product_id
                        }
                    }).then(e => {
                        if (e.data.code == 0) {
                            this.$message.success(e.data.msg);
                            this.ruleForm.product_json_data.splice(index, 1)
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
                this.productForm.id = this.ruleForm.id;
            },

            productSubmit() {
                var self = this;
                this.$refs.productForm.validate((valid) => {
                    if (valid) {
                        let para = Object.assign({}, self.productForm);
                        self.pdLoading = true;
                        request({
                            params: {
                                r: 'plugin/addcredit/mall/plateforms/plateforms/add-product',
                            },
                            method: 'post',
                            data: para,
                        }).then(e => {
                            if (e.data.code === 0) {
                                self.pdLoading = false;
                                self.dialogProduct = false;
                                location.reload();
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

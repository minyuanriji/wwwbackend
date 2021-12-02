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
<div id="app" v-cloak>
    <el-card class="box-card" v-loading="cardLoading" shadow="never" style="border:0"
             body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <el-breadcrumb separator="/">
                <el-breadcrumb-item>
                     <span style="color: #409EFF;cursor: pointer"
                           @click="$navigate({r:'plugin/mch/mall/group/list'})">连锁店管理</span>
                </el-breadcrumb-item>
                <el-breadcrumb-item>编辑连锁店</el-breadcrumb-item>
            </el-breadcrumb>
        </div>
        <div class="form-body">
            <el-tabs v-model="activeName">
                <el-tab-pane label="基本信息" name="basic">
                    <el-form :model="ruleForm" :rules="rules" ref="ruleForm" label-width="120px" size="small">
                        <el-form-item label="总店" prop="mch_id">
                            <template v-if="!ruleForm.mch_id">
                                <el-card class="box-card" >
                                    <el-input size="big" style="width:350px;" @keyup.enter.native="searchMchList" size="small" placeholder="搜索商户~" v-model="searchMch.keyword" clearable
                                              @clear='searchMchList'>
                                        <el-select v-model="searchMch.keyword1" slot="prepend" placeholder="请选择" style="width:120px;">
                                            <el-option label="商户名称" value="store_name"></el-option>
                                            <el-option label="绑定用户" value="user_name"></el-option>
                                            <el-option label="商户ID" value="mch_id"></el-option>
                                            <el-option label="手机号" value="mobile"></el-option>
                                        </el-select>
                                        <el-button @click="searchMchList" slot="append" icon="el-icon-search" ></el-button>
                                    </el-input>
                                    <el-table v-loading="searchMch.listLoading" :data="searchMch.list" border style="margin-top:10px;width: 70%">
                                        <el-table-column prop="id" label="商户ID" width="100" align="center"></el-table-column>
                                        <el-table-column label="商户名称" width="260">
                                            <template slot-scope="scope">
                                                <div flex="cross:center">
                                                    <com-image width="25" height="25" :src="scope.row.store.cover_url"></com-image>
                                                    <div style="margin-left: 10px;width: 200px;overflow:hidden;text-overflow: ellipsis;">{{scope.row.store.name}}</div>
                                                </div>
                                            </template>
                                        </el-table-column>
                                        <el-table-column label="手机号" prop="mobile" width="150"></el-table-column>
                                        <el-table-column label="操作">
                                            <template slot-scope="scope">
                                                <el-button @click="chooseIt(scope.row)" type="text" circle size="mini">
                                                    <el-tooltip class="item" effect="dark" content="确定" placement="top">
                                                        <img src="statics/img/mall/pass.png" alt="">
                                                    </el-tooltip>
                                                </el-button>
                                            </template>
                                        </el-table-column>
                                    </el-table>
                                    <div style="margin-top: 20px;">
                                        <el-pagination
                                                hide-on-single-page
                                                @current-change="pagination"
                                                background
                                                layout="prev, pager, next, jumper"
                                                :page-count="searchMch.pageCount">
                                        </el-pagination>
                                    </div>
                                </el-card>
                            </template>
                            <template v-else>
                                <el-card v-loading="cardLoading" class="box-card">
                                    <div style="display:flex">
                                        <com-image width="65" height="65" :src="cover_url"></com-image>
                                        <div style="padding-left:15px;display:flex;flex-direction:column;justify-content:flex-start">
                                            <div>{{name}}</div>
                                            <div>ID:{{ruleForm.mch_id}}</div>
                                        </div>
                                        <el-button v-if="isNew" @click="clearIt" type="text" circle size="mini" style="margin-left:50px;">
                                            <el-tooltip class="item" effect="dark" content="重新选择" placement="top">
                                                <img src="statics/img/mall/nopass.png" alt="">
                                            </el-tooltip>
                                        </el-button>
                                    </div>
                                </el-card>
                            </template>

                            <el-button v-if="isNew && ruleForm.mch_id > 0" @click="saveGroup" style="margin-top:20px;" :loading="btnLoading" type="primary" size="big">
                                保存
                            </el-button>

                        </el-form-item>

                    </el-form>

                </el-tab-pane>

                <!-- 子店管理 -->
                <el-tab-pane label="店铺管理" name="mch_list" v-if="!isNew">
                    <div style="display:flex;justify-content:space-between">
                        <el-input size="big" style="width:350px;" @keyup.enter.native="searchItemList" size="small" placeholder="关键词搜索" v-model="searchItem.keyword" clearable
                                  @clear='searchItemList'>
                            <el-button @click="searchItemList" slot="append" icon="el-icon-search" ></el-button>
                        </el-input>
                        <el-button @click="newItem.dialogVisible = true" type="primary" size="big">添加店铺</el-button>
                    </div>
                    <el-table v-loading="searchItem.listLoading" :data="searchItem.list" border style="margin-top:10px;">
                        <el-table-column prop="id" label="ID" width="100" align="center"></el-table-column>
                        <el-table-column label="商户名称" width="500">
                            <template slot-scope="scope">
                                <div flex="cross:center">
                                    <com-image width="25" height="25" :src="scope.row.cover_url"></com-image>
                                    <div style="margin-left: 10px;width: 300px;overflow:hidden;text-overflow: ellipsis;">
                                        <span v-if="scope.row.mch_id == ruleForm.mch_id" style="color:darkgreen">【总店】</span>
                                        {{scope.row.name}}(商户ID:{{scope.row.mch_id}})
                                    </div>
                                </div>
                            </template>
                        </el-table-column>
                        <el-table-column label="手机号" prop="mobile" width="150"></el-table-column>
                        <el-table-column label="操作">
                            <template slot-scope="scope">
                                <el-button v-if="scope.row.mch_id != ruleForm.mch_id" @click="deleteItem(scope.row)" type="text" circle size="mini">
                                    <el-tooltip class="item" effect="dark" content="删除" placement="top">
                                        <img src="statics/img/mall/del.png" alt="">
                                    </el-tooltip>
                                </el-button>
                            </template>
                        </el-table-column>
                    </el-table>
                    <div style="margin-top: 20px;">
                        <el-pagination
                                hide-on-single-page
                                @current-change="pagination2"
                                background
                                layout="prev, pager, next, jumper"
                                :page-count="searchItem.pageCount">
                        </el-pagination>
                    </div>
                </el-tab-pane>
            </el-tabs>
        </div>
    </el-card>
    <el-dialog title="添加店铺" :visible.sync="newItem.dialogVisible">
        <el-input size="big" style="width:350px;" @keyup.enter.native="searchMchList" size="small" placeholder="搜索商户~" v-model="searchMch.keyword" clearable
                  @clear='searchMchList'>
            <el-select v-model="searchMch.keyword1" slot="prepend" placeholder="请选择" style="width:120px;">
                <el-option label="商户名称" value="store_name"></el-option>
                <el-option label="绑定用户" value="user_name"></el-option>
                <el-option label="商户ID" value="mch_id"></el-option>
                <el-option label="手机号" value="mobile"></el-option>
            </el-select>
            <el-button @click="searchMchList" slot="append" icon="el-icon-search" ></el-button>
        </el-input>
        <el-table v-loading="searchMch.listLoading" :data="searchMch.list" border style="margin-top:10px;">
            <el-table-column prop="id" label="商户ID" width="100" align="center"></el-table-column>
            <el-table-column label="商户名称" width="260">
                <template slot-scope="scope">
                    <div flex="cross:center">
                        <com-image width="25" height="25" :src="scope.row.store.cover_url"></com-image>
                        <div style="margin-left: 10px;width: 200px;overflow:hidden;text-overflow: ellipsis;">{{scope.row.store.name}}</div>
                    </div>
                </template>
            </el-table-column>
            <el-table-column label="手机号" prop="mobile" width="150"></el-table-column>
            <el-table-column label="操作">
                <template slot-scope="scope">
                    <el-button @click="addNewItem(scope.row)" type="text" circle size="mini">
                        <el-tooltip class="item" effect="dark" content="确定" placement="top">
                            <img src="statics/img/mall/pass.png" alt="">
                        </el-tooltip>
                    </el-button>
                </template>
            </el-table-column>
        </el-table>
        <div style="margin-top: 20px;">
            <el-pagination
                    hide-on-single-page
                    @current-change="pagination"
                    background
                    layout="prev, pager, next, jumper"
                    :page-count="searchMch.pageCount">
            </el-pagination>
        </div>
    </el-dialog>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                isNew: true,
                name: '',
                cover_url: '',
                ruleForm: {
                    id: 0,
                    mch_id: 0
                },
                rules: {
                    mch_id: [
                        {required: true, message: '总店信息', trigger: 'change'},
                    ]
                },
                btnLoading: false,
                tableLoading: false,
                cardLoading: false,
                activeName: 'basic',
                searchMch: {
                    list: [],
                    listLoading: false,
                    page: 1,
                    pageCount: 0,
                    pagination: null,
                    keyword: '',
                    keyword1: 'store_name'
                },
                searchItem:{
                    list: [],
                    listLoading: false,
                    page: 1,
                    pageCount: 0,
                    pagination: null,
                    keyword: '',
                    keyword1: 'store_name'
                },
                newItem:{
                    dialogVisible: false
                }
            }
        },
        watch: {},
        mounted: function () {
            if(getQuery("id")){
                this.isNew = false;
                this.ruleForm.id = getQuery("id");
                this.getDetail();
                this.getItemList();
            }else{
                this.isNew = true;
            }
            this.getMchList();
        },
        methods: {
            getDetail() {
                this.cardLoading = true;
                request({
                    params: {
                        r: 'plugin/mch/mall/group/edit',
                        id: getQuery('id'),
                    },
                }).then(e => {
                    this.cardLoading = false;
                    if (e.data.code == 0) {
                        let detail = e.data.data.detail;
                        this.name = detail.store.name;
                        this.cover_url = detail.store.cover_url;
                        this.ruleForm.mch_id = detail.mch_id;
                    }
                }).catch(e => {

                });
            },
            saveGroup(){
                let that = this;
                let do_request = function(){
                    that.btnLoading = true;
                    let url;
                    if(that.isNew){
                        url  = "plugin/mch/mall/group/new";
                    }else{
                        url = "plugin/mch/mall/group/update";
                    }
                    request({
                        params: {
                            r: url
                        },
                        method: "post",
                        data: that.ruleForm
                    }).then(e => {
                        that.btnLoading = false;
                        if (e.data.code == 0) {
                            that.$message.success('保存成功');
                            navigateTo({
                                r: 'plugin/mch/mall/group/list'
                            })
                        } else {
                            that.$message.error(e.data.msg);
                        }
                    }).catch(e => {
                        that.$message.error(e.data.msg);
                        that.btnLoading = false;
                    });
                };
                this.$refs['ruleForm'].validate((valid) => {
                    if (valid) {
                        do_request();
                    }
                });
            },
            chooseIt(item){

                this.name = item.store.name;
                this.cover_url = item.store.cover_url;
                this.ruleForm.mch_id = item.id;
            },
            clearIt(){
                this.name = '';
                this.cover_url = '';
                this.ruleForm.mch_id = 0;
            },

            pagination2(currentPage) {
                this.searchItem.page = currentPage;
                this.getItemList();
            },
            searchItemList(){
                this.searchItem.page = 1;
                this.getItemList();
            },
            getItemList() {
                let self = this;
                self.searchItem.listLoading = true;
                request({
                    params: {
                        r: 'plugin/mch/mall/group/item-list',
                        page: self.searchItem.page,
                        group_id: getQuery("id"),
                        keyword: self.searchItem.keyword.trim(),
                        keyword1: self.searchItem.keyword1.trim()
                    },
                    method: 'get',
                }).then(e => {
                    self.searchItem.listLoading = false;
                    self.searchItem.list = e.data.data.list;
                    self.searchItem.pageCount = e.data.data.pagination.page_count;
                }).catch(e => {
                    self.searchItem.listLoading = false;
                });
            },
            addNewItem(item){
                let that = this;
                that.newItem.dialogVisible = false;
                that.searchItem.listLoading = true;
                request({
                    params: {
                        r: 'plugin/mch/mall/group/add-item'
                    },
                    method: "post",
                    data: {
                        group_id: getQuery("id"),
                        mch_id: item.id
                    }
                }).then(e => {
                    if (e.data.code == 0) {
                        that.getItemList();
                    } else {
                        that.searchItem.listLoading = false;
                        that.newItem.dialogVisible = true;
                        that.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    that.searchItem.listLoading = false;
                    that.newItem.dialogVisible = true;
                    that.$message.error("请求失败");
                });
            },
            deleteItem(item){
                let self = this;
                self.$confirm('删除该条数据, 是否继续?', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    self.searchItem.listLoading = true;
                    request({
                        params: {
                            r: 'plugin/mch/mall/group/delete-item',
                        },
                        method: 'post',
                        data: {
                            id: item.id,
                        }
                    }).then(e => {
                        self.searchItem.listLoading = false;
                        if (e.data.code === 0) {
                            self.getItemList();
                        } else {
                            self.$message.error(e.data.msg);
                        }
                    }).catch(e => {
                        self.$message.error("请求失败");
                        self.searchItem.listLoading = false;
                    });
                }).catch(() => {

                });
            },

            pagination(currentPage) {
                this.searchMch.page = currentPage;
                this.getMchList();
            },
            searchMchList(){
                this.searchMch.page = 1;
                this.getMchList();
            },
            getMchList() {
                let self = this;
                self.searchMch.listLoading = true;
                request({
                    params: {
                        r: 'plugin/mch/mall/group/search-mch',
                        page: self.searchMch.page,
                        keyword: self.searchMch.keyword.trim(),
                        keyword1: self.searchMch.keyword1.trim()
                    },
                    method: 'get',
                }).then(e => {
                    self.searchMch.listLoading = false;
                    self.searchMch.list = e.data.data.list;
                    self.searchMch.pageCount = e.data.data.pagination.page_count;
                }).catch(e => {
                    self.searchMch.listLoading = false;
                });
            }
        }
    });
</script>

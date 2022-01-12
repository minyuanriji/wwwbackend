<template id="com-add-smartshop">
    <div class="com-add-smartshop">
        <el-button type="primary" @click="showDialog" size="big">添加智慧门店</el-button>
        <el-dialog title="选择智慧门店" :visible.sync="dialogVisible" style="width:100%">
            <el-card class="box-card">
                <table style="width:100%;" class="smartshop-search">
                    <tr>
                        <td>
                            <div style="display: flex;">
                                <span style="width:150px;text-align: right;">门店ID：</span>
                                <el-input v-model="search.store_id" placeholder="输入门店ID搜索"></el-input>
                            </div>
                        </td>
                        <td>
                            <div style="display: flex;">
                                <span style="width:150px;text-align: right;">门店名称：</span>
                                <el-input v-model="search.store_name" placeholder="输入门店名称搜索"></el-input>
                            </div>
                        </td>
                        <td>
                            <div style="display: flex;">
                                <span style="width:150px;text-align: right;">商户ID：</span>
                                <el-input v-model="search.merchant_id" placeholder="输入商户ID搜索"></el-input>
                            </div>
                        </td>

                    </tr>
                    <tr>
                        <td>
                            <div style="display: flex;">
                                <span style="width:150px;text-align: right;">商户名称：</span>
                                <el-input v-model="search.merchant_name" placeholder="输入商户名称搜索"></el-input>
                            </div>
                        </td>
                        <td >
                            <div style="display: flex;">
                                <span style="width:150px;text-align: right;">手机号：</span>
                                <el-input v-model="search.mobile"  placeholder="输入手机号搜索"></el-input>
                            </div>
                        </td>
                        <td></td>
                    </tr>
                    <tr>
                        <td colspan="3" style="text-align: center">
                            <el-button @click="searchReset" type="default" size="big">重置</el-button>
                            <el-button @click="searchGo" type="primary" size="big">搜索</el-button>
                        </td>
                    </tr>
                </table>
            </el-card>
            <el-table @selection-change="handleSelectionChange" v-loading="listLoading"  :data="list" border style="margin-top:20px;width: 100%">
                <el-table-column type="selection" width="55"></el-table-column>
                <el-table-column prop="merchant_id" label="商户ID" width="100" align="center"></el-table-column>
                <el-table-column prop="merchant_name" label="商户名称" width="200"></el-table-column>
                <el-table-column prop="mobile" label="手机" width="150" align="center"></el-table-column>
                <el-table-column prop="store_id" label="门店ID" width="100" align="center"></el-table-column>
                <el-table-column label="门店名称" width="200">
                    <template slot-scope="scope">
                        <div flex="cross:center">
                            <com-image width="25" height="25" :src="scope.row.store_logo"></com-image>
                            <div style="margin-left: 10px;width: 140px;overflow:hidden;text-overflow: ellipsis;">{{scope.row.store_name}}</div>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column label="门店地址">
                    <template slot-scope="scope">
                        {{scope.row.province}}{{scope.row.city}} {{scope.row.address}}
                    </template>
                </el-table-column>
            </el-table>
            <div style="margin-top: 20px;">
                <el-pagination
                        hide-on-single-page
                        @current-change="pagination"
                        background
                        layout="prev, pager, next, jumper"
                        :page-count="pageCount">
                </el-pagination>
            </div>
            <div style="text-align:center;margin-top:30px;">
                <el-button @click="chooseConfirm" type="danger" size="big">确定选择</el-button>
            </div>
        </el-dialog>
    </div>
</template>

<script>
    Vue.component('com-add-smartshop', {
        template: '#com-add-smartshop',
        props: {

        },
        data() {
            return {
                dialogVisible: false,
                list: [],
                listLoading: false,
                page: 1,
                pageCount: 0,
                selection: [],
                search: {
                    store_id: '',
                    store_name: '',
                    merchant_id: '',
                    merchant_name: '',
                    mobile: ''
                }
            };
        },
        created() {
            this.getList();
        },
        watch: {},
        methods: {
            chooseConfirm(){
                if(!this.selection || this.selection.length <= 0){
                    this.$message.error("请选择门店");
                    return;
                }
                this.$emit('confirm', this.selection);
                this.dialogVisible = false;
            },
            showDialog(){
                this.dialogVisible = true;
            },
            searchGo(){
                this.page = 1;
                this.getList();
            },
            searchReset(){
                this.search = {store_id: '', store_name: '', merchant_id: '', merchant_name: '', mobile: ''};
                this.page = 1;
                this.getList();
            },
            getList() {
                let self = this, params;
                self.listLoading = true;
                params = Object.assign({
                    r: 'plugin/smart_shop/mall/merchant/get-smartshop',
                    page: self.page
                }, this.search);
                request({
                    params: params,
                    method: 'get',
                }).then(e => {
                    if(e.data.code == 0){
                        self.listLoading = false;
                        self.list = e.data.data.list;
                        self.pageCount = e.data.data.pagination.page_count;
                    }else{
                        self.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    self.listLoading = false;
                    self.$message.error("请求失败");
                });
            },
            handleSelectionChange(val) {
                this.selection = val;
            },
            pagination(currentPage) {
                let self = this;
                self.page = currentPage;
                self.getList();
            },
        }
    });
</script>
<style>
.smartshop-search td{padding: 6px 0;}
</style>
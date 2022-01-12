
<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <div>
                <span>分账商户</span>
                <div style="float: right;margin-top: -5px">
                    <el-button type="primary" @click="edit" size="small">添加分账商户</el-button>
                </div>
            </div>
        </div>
        <div class="table-body">
            <el-card class="box-card">
                <table style="width:100%;" class="search-merchant">
                    <tr>
                        <td>
                            <div style="">
                                <span style="width:150px;text-align: right;">商户名称：</span>
                                <el-input v-model="search.name" placeholder="输入商户名称搜索"></el-input>
                            </div>
                        </td>
                        <td>
                            <div style="display: flex;">
                                <span style="width:150px;text-align: right;">商户ID：</span>
                                <el-input v-model="search.mch_id" placeholder="输入商户ID搜索"></el-input>
                            </div>
                        </td>
                        <td>
                            <div style="display: flex;">
                                <span style="width:150px;text-align: right;">手机号：</span>
                                <el-input v-model="search.mobile"  placeholder="输入手机号搜索"></el-input>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="3" style="text-align: center;padding-top:20px;">
                            <el-button @click="searchReset" type="default" size="big">重置</el-button>
                            <el-button @click="searchGo" type="danger" size="big">搜索</el-button>
                        </td>
                    </tr>
                </table>
            </el-card>
            <el-table v-loading="loading" :data="list" border style="margin-top:20px;width: 100%">

                <el-table-column prop="bsh_mch_id" label="商户ID" width="100" align="center"></el-table-column>

                <el-table-column :show-overflow-tooltip="true" label="商户信息" width="200">
                    <template slot-scope="scope">
                        <div flex="cross:center">
                            <com-image width="25" height="25" :src="scope.row.cover_url"></com-image>
                            <div style="margin-left: 10px;width: 140px;overflow:hidden;text-overflow: ellipsis;">{{scope.row.name}}</div>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column label="手机" prop="mobile" width="150" align="center"></el-table-column>
                <el-table-column label="服务费（%）" prop="transfer_rate" width="150" align="center"></el-table-column>

                <el-table-column prop="created_at" width="150" label="添加日期" align="center">
                    <template slot-scope="scope">
                        {{scope.row.created_at|dateTimeFormat('Y-m-d')}}
                    </template>
                </el-table-column>

                <el-table-column label="操作">
                    <template slot-scope="scope">
                        <el-button @click="edit(scope.row.id)" type="text" circle size="mini">
                            <el-tooltip class="item" effect="dark" content="编辑" placement="top">
                                <img src="statics/img/mall/edit.png" alt="">
                            </el-tooltip>
                        </el-button>
                        <el-button @click="deleteIt(scope.row)" type="text" circle size="mini">
                            <el-tooltip class="item" effect="dark" content="删除" placement="top">
                                <img src="statics/img/mall/del.png" alt="">
                            </el-tooltip>
                        </el-button>
                    </template>
                </el-table-column>
            </el-table>

            <div flex="box:last cross:center">
                <div></div>
                <div>
                    <el-pagination
                            v-if="list.length > 0"
                            style="display: inline-block;float: right;"
                            background :page-size="pagination.pageSize"
                            @current-change="pageChange"
                            layout="prev, pager, next" :current-page="pagination.current_page"
                            :total="pagination.total_count">
                    </el-pagination>
                </div>
            </div>

        </div>
    </el-card>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                list: [],
                pagination: null,
                loading: false,
                search: {
                    page: 1,
                    name: '',
                    mch_id: '',
                    mobile: ''
                }
            };
        },
        methods: {
            searchReset(){
                this.search = {page:1, name: '', mch_id: '', mobile: ''};
                this.getList();
            },
            searchGo(){
                this.page = 1;
                this.getList();
            },
            getList() {
                let self = this;
                self.loading = true;
                let params = {
                    r: 'plugin/smart_shop/mall/merchant/index'
                };
                params = Object.assign(params, this.search);
                request({
                    params: params,
                    method: 'get',
                }).then(e => {
                    self.loading = false;
                    if (e.data.code == 0) {
                        self.list = e.data.data.list;
                        self.pagination = e.data.data.pagination;
                    } else {
                        self.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    self.loading = false;
                });
            },
            pageChange(page) {
                this.search.page = page;
                this.getList();
            },
            edit(id) {
                navigateTo({
                    r: 'plugin/smart_shop/mall/merchant/edit',
                    id: id,
                });
            },
            deleteIt(row){
                console.log(row);
                let self = this;
                self.$confirm('你确定要删除分账商户吗?', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    self.loading = true;
                    request({
                        params: {
                            r: 'plugin/smart_shop/mall/merchant/delete'
                        },
                        method: 'post',
                        data: {id:row.id}
                    }).then(e => {
                        self.loading = false;
                        if (e.data.code == 0) {
                            self.getList();
                        } else {
                            self.$message.error(e.data.msg);
                        }
                    }).catch(e => {
                        self.$message.error("请求失败");
                        self.loading = false;
                    });
                }).catch((e) => {

                });
            }
        },
        mounted: function () {
            this.getList();
        }
    });
</script>

<style>
    .search-merchant td > div{display: flex;align-items: center}
    .table-body {
        padding: 20px;
        background-color: #fff;
    }

    .input-item {
        width: 250px;
        margin: 0 0 20px;
    }

    .input-item .el-input__inner {
        border-right: 0;
    }

    .input-item .el-input__inner:hover{
        border: 1px solid #dcdfe6;
        border-right: 0;
        outline: 0;
    }

    .input-item .el-input__inner:focus{
        border: 1px solid #dcdfe6;
        border-right: 0;
        outline: 0;
    }

    .input-item .el-input-group__append {
        background-color: #fff;
        border-left: 0;
        width: 10%;
        padding: 0;
    }

    .input-item .el-input-group__append .el-button {
        padding: 0;
    }

    .el-input-group__append .el-button {
        margin: 0;
    }

    .sort-input {
        width: 100%;
        background-color: #F3F5F6;
        height: 32px;
    }

    .sort-input span {
        height: 32px;
        width: 100%;
        line-height: 32px;
        display: inline-block;
        padding: 0 10px;
        font-size: 13px;
    }

    .sort-input .el-input__inner {
        height: 32px;
        line-height: 32px;
        background-color: #F3F5F6;
        float: left;
        padding: 0 10px;
        border: 0;
    }
</style>
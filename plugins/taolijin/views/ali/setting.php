<?php
echo $this->render("com-edit");
?>
<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <span>联盟设置</span>
        </div>
        <div class="table-body">

            <div class="input-item" style="width:150px;">
                <el-select v-model="search.ali_type" placeholder="联盟类型" size="small" style="margin-right:15px;">
                    <el-option label="淘宝联盟" value="ali"></el-option>
                </el-select>
            </div>
            <div class="input-item">
                <el-input @keyup.enter.native="loadData" size="small" placeholder="请输入搜索内容" v-model="search.keyword"
                          clearable @clear="toSearch">
                    <el-button slot="append" icon="el-icon-search" @click="toSearch"></el-button>
                </el-input>
            </div>

            <div style="float: right">
                <el-button type="primary" size="small" style="padding: 9px 15px !important;"  @click="newAcc">添加账号</el-button>
            </div>
            <el-table @sort-change="sortReload" :data="list" border v-loading="loading" size="small" style="margin-bottom: 15px;"
                      @selection-change="handleSelectionChange">
                <el-table-column sortable="custom" prop="id" width="110" label="ID"></el-table-column>
                <el-table-column width="150" label="联盟类型">
                    <template slot-scope="scope">
                        <a :href="'?r=plugin/taolijin/mall/order/list&ali_id='+scope.row.id">
                            <span v-if="scope.row.ali_type == 'ali'">淘宝联盟</span>
                        </a>
                    </template>
                </el-table-column>
                <el-table-column width="150" label="是否使用">
                    <template slot-scope="scope">
                        <el-switch @change="changeOpen(scope.row)" v-model="scope.row.is_open" :active-value="1" :inactive-value="0"></el-switch>
                    </template>
                </el-table-column>
                <el-table-column prop="sort" width="150" label="排序"></el-table-column>
                <el-table-column prop="remark" width="350" label="备注"></el-table-column>
                <el-table-column prop="scope" width="100" label="添加时间">
                    <template slot-scope="scope">
                        {{scope.row.created_at|dateTimeFormat('Y-m-d')}}
                    </template>
                </el-table-column>
                <el-table-column prop="scope" width="100" label="更新时间">
                    <template slot-scope="scope">
                        {{scope.row.updated_at|dateTimeFormat('Y-m-d')}}
                    </template>
                </el-table-column>
                <el-table-column label="操作">
                    <template slot-scope="scope">
                        <el-button @click="editAcc(scope.row)" type="text" circle size="mini">
                            <el-tooltip class="item" effect="dark" content="编辑" placement="top">
                                <img src="statics/img/mall/edit.png" alt="">
                            </el-tooltip>
                        </el-button>
                        <el-button @click="delAcc(scope.row)" type="text" circle size="mini">
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

    <com-edit :visible="editDialogVisible"
              :record="editData"
              @update="update"
              @close="editDialogVisible = false"></com-edit>
</div>




<script>
    const app = new Vue({
        el: '#app',
        data: {
            editDialogVisible: false,
            editData: {},
            loading: false,
            list: [],
            pagination: null,
            search: {
                keyword: '',
                page: 1,
                ali_type: 'ali',
                sort_prop: '',
                sort_type: '',
            },
            selections: []
        },
        mounted() {
            this.loadData();
            if(getQuery("act") == "getInviteCode"){
                this.newInviteCode();
            }
        },
        methods: {
            newAcc(){
                this.editData = {};
                this.editDialogVisible = true;
            },
            editAcc(row){
                this.editData = row;
                this.editDialogVisible = true;
            },
            update(){
                this.editDialogVisible = false;
                this.loadData();
            },
            sortReload(column){
                this.search.sort_prop = column.prop;
                this.search.sort_type = column.order == "descending" ? 0 : 1;
                this.loadData();
            },
            handleSelectionChange(val) {
                this.selections = val;
            },
            loadData() {
                this.loading = true;
                let params = {
                    r: 'plugin/taolijin/mall/ali/list'
                };
                params = Object.assign(params, this.search);
                request({
                    params: params,
                    method: 'get',
                }).then(e => {
                    this.loading = false;
                    if (e.data.code == 0) {
                        this.list = e.data.data.list;
                        this.pagination = e.data.data.pagination;
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    this.loading = false;
                });
            },
            changeOpen(row){
                let that = this;
                that.loading = true;
                request({
                    params: {
                        r: 'plugin/taolijin/mall/ali/change-open'
                    },
                    method: 'post',
                    data: {id:row.id, is_open:row.is_open}
                }).then(e => {
                    that.loading = false;
                    if (e.data.code == 0) {
                        that.$message.success(e.data.msg);
                    } else {
                        that.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    that.$message.error(e.data.msg);
                    that.loading = false;
                });
            },
            newInviteCode(){
                let that = this;
                that.loading = true;
                request({
                    params: {
                        r: 'plugin/taolijin/mall/ali/new-invite-code'
                    },
                    method: 'post',
                    data: {
                        open_uid: getQuery("open_uid"),
                        ali_id: getQuery("ali_id"),
                        access_token: getQuery("access_token")
                    }
                }).then(e => {
                    that.loading = false;
                    if (e.data.code == 0) {
                        that.editAcc(e.data.data.ali_data);
                    } else {
                        that.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    that.loading = false;
                });
            },
            delAcc(row){
                let that = this;
                that.$confirm('删除该条数据, 是否继续?', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    that.loading = true;
                    request({
                        params: {
                            r: 'plugin/taolijin/mall/ali/delete'
                        },
                        method: 'post',
                        data: {id:row.id}
                    }).then(e => {
                        that.loading = false;
                        if (e.data.code == 0) {
                            that.loadData();
                        } else {
                            that.$message.error(e.data.msg);
                        }
                    }).catch(e => {
                        that.$message.error(e.data.msg);
                        that.loading = false;
                    });
                });

            },
            toSearch() {
                this.search.page = 1;
                this.loadData();
            },
            pageChange(page) {
                this.search.page = page;
                this.loadData();
            },
        }
    });
</script>
<style>

    .table-body {
        padding: 20px;
        background-color: #fff;
    }

    .table-body .el-button {
        padding: 0 !important;
        border: 0;
        margin: 0 5px;
    }

    .input-item {
        width: 250px;
        margin: 0 0 20px;
        display: inline-block;
    }

</style>
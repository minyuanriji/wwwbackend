<style>
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
<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <div>
                <span>平台列表</span>
                <div style="float: right;margin-top: -5px">
                    <el-button type="primary" @click="edit" size="small">添加平台</el-button>
                </div>
            </div>
        </div>
        <div class="table-body">
            <div class="input-item">
                <el-input @keyup.enter.native="search" size="small" placeholder="请输入搜索内容" v-model="keyword" clearable @clear="getList">
                    <el-button slot="append" icon="el-icon-search" @click="search"></el-button>
                </el-input>
            </div>
            <el-table v-loading="loading" :data="list" border style="width: 100%">

                <el-table-column prop="id" label="ID" width="100" align="center"></el-table-column>

                <el-table-column label="平台名称" width="200" align="center">
                    <template slot-scope="scope">
                        <com-ellipsis :line="1">{{scope.row.name}}</com-ellipsis>
                    </template>
                </el-table-column>

                <el-table-column label="SDK目录" width="150" align="center">
                    <template slot-scope="scope">
                        <com-ellipsis :line="1">{{scope.row.sdk_src}}</com-ellipsis>
                    </template>
                </el-table-column>


                <el-table-column label="启用/关闭" width="150" align="center">
                    <template slot-scope="scope">
                        <el-switch @change="switchEnabled(scope.row)"
                                v-model="scope.row.is_enabled"
                                active-value="1"
                                inactive-value="0">
                        </el-switch>
                    </template>
                </el-table-column>

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
                    page: 1
                },
            };
        },
        methods: {
            switchEnabled(row){
                this.loading = true;
                let self = this;
                request({
                    params: {
                        r: 'plugin/oil/mall/plateform/switch-enabled'
                    },
                    method: 'post',
                    data: {
                        id: row.id,
                        enabled: row.is_enabled
                    }
                }).then(e => {
                    self.loading = false;
                    if (e.data.code === 0) {
                        self.getList();
                        self.$message.success(e.data.msg);
                    } else {
                        self.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    self.loading = false;
                    self.$message.error("request fail");
                });
            },
            getList() {
                let self = this;
                self.loading = true;
                let params = {
                    r: 'plugin/oil/mall/plateform/list'
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
                    r: 'plugin/oil/mall/plateform/edit',
                    id: id,
                });
            }
        },
        mounted: function () {
            this.getList();
        }
    });
</script>

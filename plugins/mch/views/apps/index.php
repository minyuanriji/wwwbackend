<style>
    .table-body {
        padding: 20px;
        background-color: #fff;
        position: relative;
        z-index: 1;
    }

    .input-item {
        width: 250px;
        margin: 0 0 20px;
    }

    .input-item .el-input__inner {
        border-right: 0;
    }

    .input-item .el-input__inner:hover {
        border: 1px solid #dcdfe6;
        border-right: 0;
        outline: 0;
    }

    .input-item .el-input__inner:focus {
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

    .input-item .el-input-group__append .el-button {
        margin: 0;
    }

    .table-body .el-table .el-button {
        padding: 0 !important;
        border: 0;
        margin: 0 5px;
    }

    .table-body .el-form-item {
        margin-bottom: 0;
    }
</style>
<div id="app" v-cloak>
    <el-card class="box-card" shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <div>
                <span>APP管理</span>
                <div style="float: right;margin-top: -5px">
                    <el-button type="primary" @click="edit" size="small">发布版本</el-button>
                </div>
            </div>
        </div>
        <div class="table-body">
            <el-tabs v-model="platform" @tab-click="handleClick">
                <el-tab-pane label="安卓（android）" name="android"></el-tab-pane>
                <el-tab-pane label="苹果（ios）" name="ios"></el-tab-pane>
            </el-tabs>
            <el-table v-loading="listLoading" :data="list" border style="width: 100%">
                <el-table-column prop="id" label="编号" width="90"></el-table-column>
                <el-table-column label="版本号" prop="version_code" width="110"></el-table-column>
                <el-table-column label="版本名称" prop="version_name" width="110"></el-table-column>
                <el-table-column prop="score" label="发布时间" width="100">
                    <template slot-scope="scope">
                        {{scope.row.created_at|dateTimeFormat('Y-m-d H:i:s')}}
                    </template>
                </el-table-column>
                <el-table-column label="内容" prop="content" width="200"></el-table-column>
                <el-table-column label="下载链接" prop="download_link"></el-table-column>
                <el-table-column fixed="right" width="90" label="操作">
                    <template slot-scope="scope">
                        <el-button @click="edit(scope.row.id)" type="text" circle size="mini">
                            <el-tooltip class="item" effect="dark" content="编辑" placement="top">
                                <img src="statics/img/mall/edit.png" alt="">
                            </el-tooltip>
                        </el-button>
                        <el-button @click="destroy(scope.row, scope.$index)" type="text" circle size="mini">
                            <el-tooltip class="item" effect="dark" content="删除" placement="top">
                                <img src="statics/img/mall/del.png" alt="">
                            </el-tooltip>
                        </el-button>
                    </template>
                </el-table-column>
            </el-table>
            <div style="text-align: right;margin: 20px 0;">
                <el-pagination
                    @current-change="pagination"
                    background
                    layout="prev, pager, next, jumper"
                    :page-count="pageCount">
                </el-pagination>
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
                listLoading: false,
                page: 1,
                pageCount: 0,
                platform: 'android'
            };
        },
        methods: {
            edit(id) {
                if (id) {
                    navigateTo({
                        r: 'plugin/mch/mall/apps/edit',
                        id: id,
                        platform: this.platform
                    });
                } else {
                    navigateTo({
                        r: 'plugin/mch/mall/apps/edit',
                        platform: this.platform
                    });
                }
            },
            destroy(row, index) {
                let self = this;
                self.$confirm('删除该条数据, 是否继续?', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    self.listLoading = true;
                    request({
                        params: {
                            r: 'plugin/mch/mall/apps/destroy',
                        },
                        method: 'post',
                        data: {
                            id: row.id,
                        }
                    }).then(e => {
                        self.listLoading = false;
                        if (e.data.code === 0) {
                            self.list.splice(index, 1);
                            self.$message.success(e.data.msg);
                        } else {
                            self.$message.error(e.data.msg);
                        }
                    }).catch(e => {
                        console.log(e);
                    });
                }).catch(() => {
                    self.$message.info('已取消删除')
                });
            },
            pagination(currentPage) {
                let self = this;
                self.page = currentPage;
                self.getList();
            },
            getList() {
                let self = this;
                self.listLoading = true;
                request({
                    params: {
                        r: 'plugin/mch/mall/apps/index',
                        page: self.page,
                        platform: self.platform
                    },
                    method: 'get',
                }).then(e => {
                    self.listLoading = false;
                    self.list = e.data.data.list;
                    self.pageCount = e.data.data.pagination.page_count;
                }).catch(e => {
                    console.log(e);
                });
            },
            handleClick(tab, event) {
                this.page = 1;
                this.getList();
            }
        },
        mounted: function () {
            this.getList();
        }
    });
</script>

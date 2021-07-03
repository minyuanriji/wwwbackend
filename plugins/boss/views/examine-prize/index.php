<div id="app" v-cloak>
    <el-card shadow="never" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <div>
                <span>审核</span>
            </div>
        </div>
        <div class="table-body">
            <div class="input-item">
                <el-input @keyup.enter.native="search" size="small"
                          placeholder="请输入名称进行搜索"
                          v-model="keyword"
                          clearable
                          @clear="search">
                    <el-button slot="append" icon="el-icon-search" @click="search"></el-button>
                </el-input>
            </div>
            <el-table
                    v-loading="listLoading"
                    :data="list"
                    border
                    style="width: 100%">

                <el-table-column
                        prop="id"
                        label="ID"
                        width="80">
                </el-table-column>

                <el-table-column prop="award_sn" label="奖池期数" width="180">
                    <template slot-scope="scope">
                        <com-ellipsis :line="1">{{scope.row.awards_cycle}}</com-ellipsis>
                    </template>
                </el-table-column>

                <el-table-column label="用户昵称" width="250">
                    <template slot-scope="scope">
                        <com-ellipsis :line="1">{{scope.row.nickname}}</com-ellipsis>
                    </template>
                </el-table-column>

                <el-table-column label="手机号">
                    <template slot-scope="scope">
                        <com-ellipsis :line="1">{{scope.row.mobile}}</com-ellipsis>
                    </template>
                </el-table-column>

                <el-table-column label="状态">
                    <template slot-scope="scope">
                        <span v-if="scope.row.status==0" style="color: red;">未打款</span>
                        <span v-if="scope.row.status==1" style="color: green;">已打款</span>
                        <span v-if="scope.row.status==2" style="color: blue;">已取消</span>
                    </template>
                </el-table-column>

                <el-table-column label="金额">
                    <template slot-scope="scope">
                        <com-ellipsis :line="1">{{scope.row.money}}</com-ellipsis>
                    </template>
                </el-table-column>

                <el-table-column label="发放时间" width="170">
                    <template slot-scope="scope">
                        <com-ellipsis :line="1">{{scope.row.send_date}}</com-ellipsis>
                    </template>
                </el-table-column>

                <el-table-column
                        label="操作"
                        width="180">
                    <template slot-scope="scope">

                        <el-button circle size="mini" type="text" @click="examine(scope.row.id)">
                            <el-tooltip class="item" effect="dark" content="审核" placement="top">
                                <img v-if="scope.row.status!=1" src="statics/img/mall/payment.png" alt="">
                            </el-tooltip>
                        </el-button>

                    </template>
                </el-table-column>

            </el-table>

            <div style="text-align: right;margin: 20px 0;">
                <el-pagination
                        @current-change="pagination"
                        background
                        layout="prev, pager, next"
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
                keyword: '',
                listLoading: false,
                page: 1,
                pageCount: 0,
                remarksForm: {
                    id: '',
                    money: ''
                },
                remarksLoading: false,
                dialogContent: false,
                currentChangeOwnerMall: {
                    name: ''
                },
                changeOwnerDialogVisible: false,
                adminList: null,
                adminListLoading: false,
                adminListForm: {
                    page: 0,
                },
                userIds: {
                    type: Array,
                    default: function () {
                        return [];
                    }
                },
            };
        },
        mounted: function () {
            this.getList();
        },
        methods: {
            remarks(id) {
                this.dialogContent = true;
                this.remarksForm = {
                    id: id,
                    money: ''
                }
            },

            showChangeOwnerDialog(row) {
                console.log(row);
                this.currentChangeOwnerMall = row;
                this.changeOwnerDialogVisible = true;
                if (!this.adminList) {
                    this.loadAdminList();
                }
            },

            loadAdminList() {
                this.adminListLoading = true;
                this.$request({
                    params: {
                        r: 'plugin/boss/mall/prize/platform-users',
                        page: this.adminListForm.page,
                        keyword: this.keyword,
                    }
                }).then(e => {
                    this.adminListLoading = false;
                    if (e.data.code === 0) {
                        for (let i in e.data.data.list) {
                            e.data.data.list[i].loading = false;
                        }
                        this.adminList = e.data.data.list;
                        this.adminListPagination = e.data.data.pagination;
                    } else {
                    }
                }).catch(e => {
                });
            },
            search() {
                this.page = 1;
                this.getList();
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
                        r: 'plugin/boss/mall/examine-prize/index',
                        page: self.page,
                        keyword: this.keyword
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
            examine(id) {
                this.$confirm('确认打款？', '提示', {
                    type: 'warning',
                }).then(e => {
                    request({
                        params: {
                            r: 'plugin/boss/mall/examine-prize/examine',
                            id: id
                        },
                        method: 'get'
                    }).then(e => {
                        if (e.data.code === 0) {
                            this.$message.success(e.data.msg);
                            this.getList();
                        } else {
                            this.$message.error(e.data.msg);
                        }
                    }).catch(e => {
                    });
                }).catch(e => {
                });
            },
            switchStatus(row) {
                let self = this;
                self.listLoading = true;
                request({
                    params: {
                        r: 'plugin/boss/mall/prize/switch-status',
                    },
                    method: 'post',
                    data: {
                        id: row.id,
                    }
                }).then(e => {
                    self.listLoading = false;
                    if (e.data.code === 0) {
                        self.$message.success(e.data.msg);
                    } else {
                        self.$message.error(e.data.msg);
                    }
                    self.getList();
                }).catch(e => {
                    console.log(e);
                });
            },
            demo(row) {
                console.log("测试")
            },

            //删除奖池
            prizeDelete(row, index) {
                let self = this;
                self.$confirm('删除该奖池, 是否继续?', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    self.listLoading = true;
                    request({
                        params: {
                            r: 'plugin/boss/mall/prize/delete',
                        },
                        method: 'post',
                        data: {
                            id: row.id,
                        }
                    }).then(e => {
                        self.listLoading = false;
                        if (e.data.code === 0) {
                            self.$message.success(e.data.msg);
                            self.list.splice(index, 1);
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
        }
    });
</script>

<style>
    .table-body {
        padding: 20px;
        background-color: #fff;
    }

    .input-item {
        display: inline-block;
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

    .table-body .el-button {
        padding: 0 !important;
        border: 0;
        margin: 0 5px;
    }
</style>
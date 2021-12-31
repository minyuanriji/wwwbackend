<div id="app" v-cloak>
    <el-card shadow="never" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <div>
                <span>奖金池</span>
                <div style="float: right; margin: -5px 0">
                    <el-button type="primary" @click="$navigate({r: 'plugin/boss/mall/prize/edit'})" size="small">
                        添加奖金池
                    </el-button>
                </div>
            </div>
        </div>
        <div class="table-body">
            <div class="input-item">
                <el-input @keyup.enter.native="search" size="small"
                          placeholder="请输入名称或编号进行搜索"
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

                <el-table-column prop="award_sn" label="编号" width="180">
                    <template slot-scope="scope">
                        <com-ellipsis :line="1">{{scope.row.award_sn}}</com-ellipsis>
                    </template>
                </el-table-column>

                <el-table-column label="奖池名称">
                    <template slot-scope="scope">
                        <com-ellipsis :line="1">{{scope.row.name}}</com-ellipsis>
                    </template>
                </el-table-column>

                <el-table-column label="是否启用" >
                    <template slot-scope="scope">
                        <el-switch v-model="scope.row.status"
                                   @change="switchChange(scope.row)"
                                   active-color="#13ce66"
                                   inactive-color="#ff4949"
                                   :active-value="1"
                                   :inactive-value="0">
                        </el-switch>
                    </template>
                </el-table-column>

                <el-table-column label="周期">
                    <template slot-scope="scope">
                        <com-ellipsis :line="1">{{scope.row.period}}{{scope.row.period_unit}}</com-ellipsis>
                    </template>
                </el-table-column>

                <el-table-column label="奖金池金额">
                    <template slot-scope="scope">
                        <com-ellipsis :line="1">{{scope.row.money}}</com-ellipsis>
                    </template>
                </el-table-column>

                <el-table-column label="比例">
                    <template slot-scope="scope">
                        <com-ellipsis :line="1">{{scope.row.rate}}%</com-ellipsis>
                    </template>
                </el-table-column>

                <el-table-column label="包含等级名称">
                    <template slot-scope="scope">
                        <com-ellipsis :line="1" v-if="scope.row.level_name" v-for="item in scope.row.level_name">
                            <div>{{ item.name }}</div>
                        </com-ellipsis>
                    </template>
                </el-table-column>

                <el-table-column label="创建时间" width="170">
                    <template slot-scope="scope">
                        <com-ellipsis :line="1">{{scope.row.created_at}}</com-ellipsis>
                    </template>
                </el-table-column>

                <el-table-column
                        label="操作"
                        width="350">
                    <template slot-scope="scope">

                        <el-button circle size="mini" type="text" @click="edit(scope.row.id)">
                            <el-tooltip class="item" effect="dark" content="编辑" placement="top">
                                <img src="statics/img/mall/edit.png" alt="">
                            </el-tooltip>
                        </el-button>

                        <!--<el-button circle size="mini" type="text" @click="edit(scope.row.id)">
                            <el-tooltip class="item" effect="dark" content="审核" placement="top">
                                <img src="statics/img/mall/order/detail.png" alt="">
                            </el-tooltip>
                        </el-button>-->

                        <el-button circle size="mini" type="text" @click="prizeDelete(scope.row, scope.$index)">
                            <el-tooltip class="item" effect="dark" content="删除" placement="top">
                                <img src="statics/img/mall/del.png" alt="">
                            </el-tooltip>
                        </el-button>

                        <el-button circle size="mini" type="text" @click="startDistribution(scope.row, scope.$index)">
                            <el-tooltip class="item" effect="dark" content="开始发放" placement="top">
                                <img src="statics/img/mall/start-distribution.png" alt="">
                            </el-tooltip>
                        </el-button>

                        <el-button type="text" size="mini" circle plain style="margin-left: 10px;margin-top: 10px"
                                   @click="addChangeOwnerDialog(scope.row)">
                            <el-tooltip class="item" effect="dark" content="添加股东" placement="top">
                                <img src="statics/img/mall/addUser.png" alt="">
                            </el-tooltip>
                        </el-button>


                        <el-button circle size="mini" type="text" @click="showChangeOwnerDialog(scope.row)">
                            <el-tooltip class="item" effect="dark" content="查看股东" placement="top">
                                <img src="statics/img/mall/showUser.png" alt="">
                            </el-tooltip>
                        </el-button>

                        <el-button type="text" size="mini" circle style="margin-left: 10px;margin-top: 10px"
                                   @click.native="remarks(scope.row.id)">
                            <el-tooltip class="item" effect="dark" content="充值" placement="top">
                                <img src="statics/img/mall/pay.png" alt="">
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

    <el-dialog title="充值" :visible.sync="dialogContent">
        <el-form :model="remarksForm">
            <el-form-item label="金额">
                <el-input v-model="remarksForm.money" type="number" placeholder="请输入金额" min="0" style="width: 220px"></el-input>
                <el-input style="display: none" :readonly="true" v-model="remarksForm.id"></el-input>
            </el-form-item>
        </el-form>
        <div slot="footer" class="dialog-footer">
            <el-button @click="dialogContent = false">取 消</el-button>
            <el-button type="primary" @click="remarksSubmit" :loading="remarksLoading">确 定</el-button>
        </div>
    </el-dialog>

    <!-- 添加用户 -->
    <el-dialog title="添加股东" :visible.sync="changeOwnerDialogVisible" width="30%">
        <div class="input-item">
            <el-input size="small" placeholder="请输入搜索内容" type="text" clearable  v-model="keywords">
                <el-button slot="append" @click="loadBossList('add')" icon="el-icon-search"></el-button>
            </el-input>
        </div>
        <div>
<!--            <el-button @click="upDown(1)" size="mini">批量添加</el-button>-->
        </div>
        <el-table v-loading="adminListLoading" :data="adminList" style="margin-bottom: 20px;">
            <el-table-column align='center' type="selection" width="60"></el-table-column>
            <el-table-column align="center" prop="id" label="id"></el-table-column>
            <el-table-column align="center" prop="user_id" label="用户ID"></el-table-column>
            <el-table-column align="center" prop="level_name" label="等级名称"></el-table-column>
            <el-table-column align="center" prop="nickname" label="用户名"></el-table-column>
            <el-table-column align="center" label="操作">
                <template slot-scope="scope">
                    <el-button :loading="scope.row.loading" plain size="mini" type="primary"
                               @click="changeToOwner(scope.row)">选择
                    </el-button>
                </template>
            </el-table-column>
        </el-table>
        <el-pagination
                v-if="adminListPagination"
                style="text-align: center"
                background
                @current-change="adminListPageChange"
                layout="prev, pager, next"
                :page-count="adminListPagination.page_count">
        </el-pagination>
    </el-dialog>

    <!-- 查看股东 -->
    <el-dialog title="查看股东" :visible.sync="showConsumerList" width="30%">
        <div class="input-item">
            <el-input size="small" placeholder="请输入搜索内容" type="text" clearable v-model="keyword">
                <el-button slot="append" @click="loadAdminList('show')" icon="el-icon-search"></el-button>
            </el-input>
        </div>
        <div>
            <!--            <el-button @click="upDown(1)" size="mini">批量添加</el-button>-->
        </div>
        <el-table v-loading="adminListLoading" :data="consumerList" style="margin-bottom: 20px;">
            <el-table-column align='center' type="selection" width="60"></el-table-column>
            <el-table-column align="center" prop="id" label="id"></el-table-column>
            <el-table-column align="center" prop="user_id" label="用户ID"></el-table-column>
            <el-table-column align="center" prop="nickname" label="用户名"></el-table-column>
            <el-table-column align="center" label="操作">
                <template slot-scope="scope">
                    <el-button :loading="scope.row.loading" plain size="mini" type="primary"
                               @click="remove(scope.row)">移除
                    </el-button>
                </template>
            </el-table-column>
        </el-table>
        <el-pagination
                v-if="adminListPagination"
                style="text-align: center"
                background
                @current-change="adminListPageChange"
                layout="prev, pager, next"
                :page-count="adminListPagination.page_count">
        </el-pagination>
    </el-dialog>

</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                list: [],
                keyword: '',
                keywords: '',
                listLoading: false,
                page: 1,
                pageCount: 0,
                status : true,
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
                consumer: {
                    name: ''
                },
                scope: {
                    row: {
                        status : true
                    }
                },
                showConsumerList: false,
                consumerList: null,
            };
        },
        mounted: function () {
            this.getList();
        },
        methods: {
            switchChange(e) {
                console.log(e);
                let self = this;
                request({
                    params: {
                        r: 'plugin/boss/mall/prize/is-enable',
                    },
                    method: 'post',
                    data: {
                        id : e.id,
                        status : e.status,
                    }
                }).then(e => {
                    if (e.data.code == 0) {
                        self.$message.success(e.data.msg);
                    } else {
                        self.$message.error(e.data.msg);
                        this.search();
                    }
                }).catch(e => {
                    console.log(e);
                });
            },

            //充值
            remarksSubmit() {
                this.remarksLoading = true;
                request({
                    params: {
                        r: 'plugin/boss/mall/prize/recharge',
                    },
                    method: 'post',
                    data:{
                        money: this.remarksForm.money,
                        id: this.remarksForm.id
                    }
                }).then(e => {
                    this.remarksLoading = false;
                    if (e.data.code == 0) {
                        this.dialogContent = false;
                        this.search();
                        this.$message.success(e.data.msg);
                    } else {
                        this.dialogContent = false;
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    this.remarksLoading = false;
                    this.dialogContent = false;
                    this.$message.error(e.data.msg);
                });
            },

            remarks(id) {
                this.dialogContent = true;
                this.remarksForm = {
                    id: id,
                    money: ''
                }
            },

            showChangeOwnerDialog(row) {
                console.log(row);
                this.consumer = row;
                this.showConsumerList = true;
                this.loadAdminList('show');
            },

            addChangeOwnerDialog(row) {
                console.log(row);
                this.currentChangeOwnerMall = row;
                this.changeOwnerDialogVisible = true;
                this.loadBossList('add');
            },

            //添加用户
            changeToOwner(row) {
                console.log(row);
                const content = '确认将`' + row.nickname + '`添加至`' + this.currentChangeOwnerMall.name + '`奖池下?';
                this.$confirm(content, '提示').then(e => {
                    row.loading = true;
                    this.$request({
                        params: {
                            r: "plugin/boss/mall/prize/user-edit",
                        },
                        method: 'post',
                        data:{
                            user_ids: [row.user_id],
                            award_id: this.currentChangeOwnerMall.id,
                        }
                    }).then(e => {
                        row.loading = false;
                        if (e.data.code === 0) {
                            this.changeOwnerDialogVisible = false;
                            this.$message.success(e.data.msg);
                            this.loadBossList('add');
                        } else {
                            this.$message.error(e.data.msg);
                        }
                    }).catch(e => {
                    });
                }).catch(e => {
                });
            },

            //移除用户
            remove(row) {
                console.log(row);
                const content = '确认将`' + row.nickname + '`从`' + this.currentChangeOwnerMall.name + '`奖池移除?';
                this.$confirm(content, '提示').then(e => {
                    row.loading = true;
                    this.$request({
                        params: {
                            r: "plugin/boss/mall/prize/user-edit",
                        },
                        method: 'post',
                        data:{
                            user_ids: [row.user_id],
                            award_id: this.currentChangeOwnerMall.id,
                            ids: [row.id],
                        }
                    }).then(e => {
                        row.loading = false;
                        if (e.data.code === 0) {
                            this.changeOwnerDialogVisible = false;
                            this.$message.success(e.data.msg);
                            this.loadAdminList('show');
                        } else {
                            this.$message.error(e.data.msg);
                        }
                    }).catch(e => {
                    });
                }).catch(e => {
                });
            },

            loadAdminList(type) {
                this.adminListLoading = true;
                this.$request({
                    params: {
                        r: 'plugin/boss/mall/prize/platform-users',
                        page: this.adminListForm.page,
                        keyword: this.keyword,
                        award_id: this.consumer.id,
                        type: type,
                    }
                }).then(e => {
                    this.adminListLoading = false;
                    if (e.data.code === 0) {
                        for (let i in e.data.data.list) {
                            e.data.data.list[i].loading = false;
                        }
                        this.consumerList = e.data.data.list;
                        this.adminListPagination = e.data.data.pagination;
                    } else {
                    }
                }).catch(e => {
                });
            },

            loadBossList(type) {
                this.adminListLoading = true;
                this.$request({
                    params: {
                        r: 'plugin/boss/mall/prize/platform-users',
                        page: this.adminListForm.page,
                        keyword: this.keywords,
                        award_id: this.consumer.id,
                        type: type,
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
                        r: 'plugin/boss/mall/prize/index',
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
            edit(id) {
                if (id) {
                    navigateTo({
                        r: 'plugin/boss/mall/prize/edit',
                        id: id,
                    });
                } else {
                    navigateTo({
                        r: 'plugin/boss/mall/prize/edit',
                    });
                }
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

            //开始发放
            startDistribution(row, index) {
                let self = this;
                self.$confirm('确定开始发放？', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    self.listLoading = true;
                    request({
                        params: {
                            r: 'plugin/boss/mall/prize/distribution',
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
                    }).catch(e => {
                        console.log(e);
                    });
                }).catch(() => {
                    self.$message.info('已取消发放')
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
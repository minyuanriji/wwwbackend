<div id="app" v-cloak>
    <el-card shadow="never" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <div>
                <span>审核</span>
            </div>
        </div>
        <div class="table-body">
            <el-alert
                    title="说明：一键打款只有选择未打款的选项才会出现"
                    type="info"
                    style="margin-bottom: 20px;color: red">
            </el-alert>
            <div class="input-item">
                <el-input @keyup.enter.native="search" size="small"
                          placeholder="请输入关键词进行搜索"
                          v-model="keyword"
                          clearable
                          @clear="search">
                    <el-select slot="prepend" v-model="kw_type" placeholder="请选择" size="small"
                               style="width:120px;">
                        <el-option v-for="item in item_type_options"
                                   :key="item.value"
                                   :label="item.label"
                                   :value="item.value">
                        </el-option>
                    </el-select>
                    <el-button slot="append" icon="el-icon-search" @click="search"></el-button>
                </el-input>
            </div>
            <el-table
                    v-loading="listLoading"
                    :data="list"
                    border
                    style="width: 100%"
                    @selection-change="oneClickPayment">
                <el-table-column align='center' type="selection" width="60"></el-table-column>

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
                        <com-ellipsis :line="1">{{scope.row.money}}元</com-ellipsis>
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

                        <el-button circle size="mini" type="text" @click="doDelete(scope.row.id)">
                            <el-tooltip class="item" effect="dark" content="删除" placement="top">
                                <img src="statics/img/mall/del.png" alt="">
                            </el-tooltip>
                        </el-button>

                    </template>
                </el-table-column>

            </el-table>

            <div style="text-align: center;margin-top: 20px;">
                <div v-if="clickPayId.length > 0" style="float: left;">
                    <el-button @click="OnekeyExamine" type="primary">一键打款</el-button>
                </div>
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
                kw_type: 'mobile',
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
                clickPayId:[],
                item_type_options:[
                    {
                        value: 'mobile',
                        label: '手机号'
                    },
                    {
                        value: 'user_id',
                        label: '用户ID'
                    },
                    {
                        value: 'nickname',
                        label: '昵称'
                    },
                ],
            };
        },
        mounted: function () {
            this.getList();
        },
        methods: {
            oneClickPayment (selection) {
                let self = this;
                selection.forEach(function (item) {
                    if (item.status == 0) {
                        self.clickPayId.push(item.id);
                    }
                })
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
                        kw_type: this.kw_type,
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
                self.clickPayId = [];
                self.getList();
            },
            getList() {
                let self = this;
                self.listLoading = true;
                request({
                    params: {
                        r: 'plugin/boss/mall/examine-prize/index',
                        page: self.page,
                        keyword: this.keyword,
                        kw_type: this.kw_type,
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
            OnekeyExamine () {
                this.$confirm('确认批量打款？', '提示', {
                    type: 'warning',
                }).then(e => {
                    request({
                        params: {
                            r: 'plugin/boss/mall/examine-prize/batch-examine',
                        },
                        data:{
                            ids: this.clickPayId
                        },
                        method: 'post'
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

            //删除
            doDelete(id) {
                let self = this;
                self.$confirm('是否删除', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    self.listLoading = true;
                    request({
                        params: {
                            r: 'plugin/boss/mall/examine-prize/do-delete',
                        },
                        method: 'post',
                        data: {
                            id: id
                        }
                    }).then(e => {
                        self.listLoading = false;
                        if (e.data.code === 0) {
                            self.$message.success(e.data.msg);
                            this.getList();
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

</style>
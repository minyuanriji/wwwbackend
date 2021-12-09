<style>
    .table-body {
        padding: 20px;
        background-color: #fff;
    }

    .input-item {
        width: 250px;
        margin: 0 0 20px;
    }

/*    .input-item .el-input__inner {
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
    }*/

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

    .el-alert {
        padding: 0;
        padding-left: 5px;
        padding-bottom: 5px;
    }

    .el-alert--info .el-alert__description {
        color: #606266;
    }

    .el-alert .el-button {
        margin-left: 20px;
    }

    .el-alert__content {
        display: flex;
        align-items: center;
    }

    .table-body .el-alert__title {
        margin-top: 5px;
        font-weight: 400;
    }
    .el-tooltip__popper{max-width: 400px}
</style>
<div id="app" v-cloak>
    <el-card class="box-card" shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <div>
                <span>商户列表</span>
                <div style="float: right;margin-top: -5px">
                    <el-button type="primary" @click="edit" size="small">添加商户</el-button>
                </div>
            </div>
        </div>

        <div class="table-body">
            <el-alert
                    style="margin-bottom:20px;"
                    type="info"
                    title="入驻商户PC端登录网址："
                    :closable="false">
                <template>
                    <span id="target">{{loginRoute}}</span>
                    <el-button v-if="loginRoute" id="copy_btn"
                               data-clipboard-action="copy"
                               data-clipboard-target="#target"
                               size="mini">复制链接
                    </el-button>
                </template>
            </el-alert>
            <div class="input-item">
                <el-input style="width: 400px" v-model="search.keyword" placeholder="请输入搜索内容" clearable size="small"
                          @clear="clearSearch"
                          @change="searchList"
                          @input="triggeredChange">
                    <el-select style="width: 130px" slot="prepend" v-model="search.keyword1">
                        <el-option v-for="item in selectList" :key="item.value"
                                   :label="item.name"
                                   :value="item.value">
                        </el-option>
                    </el-select>
                </el-input>
            </div>
            <el-table
                    v-loading="listLoading"
                    :data="list"
                    border
                    style="width: 100%"
                    @sort-change="sortOrder">
                <el-table-column
                        prop="id"
                        label="ID"
                        width="80">
                </el-table-column>
                <el-table-column
                        :show-overflow-tooltip="true"
                        label="店铺信息" width="200">
                    <template slot-scope="scope">
                        <div flex="cross:center">
                            <com-image width="25" height="25" :src="scope.row.store.cover_url"></com-image>
                            <div style="margin-left: 10px;width: 140px;overflow:hidden;text-overflow: ellipsis;">{{scope.row.store.name}}</div>
<!--                            <com-ellipsis style="margin-left: 10px;" :line="1">{{scope.row.store.name}}</com-ellipsis>-->
                        </div>
                    </template>
                </el-table-column>
                <el-table-column
                        label="用户" width="280">
                    <template slot-scope="scope">
                        <div flex="dir:left cross:center" v-if="scope.row.user">
                            <com-image width="25" height="25" :src="scope.row.user.avatar"></com-image>
                            <div style="margin-left: 10px;width: 115px;overflow:hidden;text-overflow: ellipsis;">{{scope.row.user.nickname}}</div>
<!--                            <com-ellipsis style="margin-left: 10px;" :line="1">{{scope.row.user.nickname}}-->
<!--                            </com-ellipsis>-->
                        </div>
                    </template>
                </el-table-column>
                <el-table-column
                        label="联系人" width="200">
                    <template slot-scope="scope">
                        <div>
                            <com-ellipsis style="margin-left: 10px;" :line="1">{{scope.row.realname}}
                            </com-ellipsis>
                            <com-ellipsis style="margin-left: 10px;" :line="1">电话:{{scope.row.mobile}}
                            </com-ellipsis>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column
                        label="排序"
                        prop="sort"
                        width="100"
                        sortable="custom">
                    <template slot-scope="scope">
                        <div v-if="id != scope.row.id">
                            <el-tooltip class="item" effect="dark" content="排序" placement="top">
                                <span>{{scope.row.sort}}</span>
                            </el-tooltip>
                            <el-button class="edit-sort" type="text" @click="editSort(scope.row)">
                                <img src="statics/img/mall/order/edit.png" alt="">
                            </el-button>
                        </div>
                        <div style="display: flex;align-items: center" v-else>
                            <el-input style="min-width: 70px" type="number" size="mini" class="change" v-model="sort"
                                      autocomplete="off"></el-input>
                            <el-button class="change-quit" type="text" style="color: #F56C6C;padding: 0 5px"
                                       icon="el-icon-error"
                                       circle @click="quit()"></el-button>
                            <el-button class="change-success" type="text"
                                       style="margin-left: 0;color: #67C23A;padding: 0 5px"
                                       icon="el-icon-success" circle @click="change(scope.row)">
                            </el-button>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column
                        label="服务费"
                        prop="transfer_rate"
                        width="100">
                </el-table-column>
                <el-table-column
                        label="入驻时间"
                        prop="review_time"
                        width="200">
                </el-table-column>
                <el-table-column
                        label="开业"
                        width="80">
                    <template slot-scope="scope">
                        <el-switch
                                @change="switchStatus(scope, 'status')"
                                v-model="scope.row.status"
                                active-value="1"
                                inactive-value="0">
                        </el-switch>
                    </template>
                </el-table-column>
                <el-table-column
                        label="好店推荐"
                        width="80">
                    <template slot-scope="scope">
                        <el-switch
                                @change="switchStatus(scope, 'is_recommend')"
                                v-model="scope.row.is_recommend"
                                active-value="1"
                                inactive-value="0">
                        </el-switch>
                    </template>
                </el-table-column>
                <el-table-column
                    fixed="right"
                    width="250"
                    label="操作">
                    <template slot-scope="scope">
                        <el-button @click="edit(scope.row.id)" type="text" circle size="mini">
                            <el-tooltip class="item" effect="dark" content="编辑" placement="top">
                                <img src="statics/img/mall/edit.png" alt="">
                            </el-tooltip>
                        </el-button>
                        <el-button @click="$navigate({r: 'plugin/mch/mall/mch/mall-setting', mch_id: scope.row.id})"
                                   type="text" circle size="mini">
                            <el-tooltip class="item" effect="dark" content="设置" placement="top">
                                <img src="statics/img/plugins/setting.png" alt="">
                            </el-tooltip>
                        </el-button>
                        <el-button @click="updatePasswordDialog(scope.row)" type="text" size="mini" circle>
                            <el-tooltip class="item" effect="dark" content="修改账户信息" placement="top">
                                <img src="statics/img/mall/change.png" alt="">
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

            <div style="margin-top: 20px;text-align: center;">
                <el-pagination
                    hide-on-single-page
                    @current-change="pagination"
                    background
                    layout="prev, pager, next, jumper"
                    :page-count="pageCount">
                </el-pagination>
            </div>
        </div>

        <el-dialog title="修改账户信息" :visible.sync="dialogFormVisible" width="30%" :before-close="handleClose">
            <el-form size="small" @submit.native.prevent="" :model="form" :rules="passwordRules" ref="form">
                <el-form-item label="新账号" prop="account">
                    <el-input v-model="form.account" autocomplete="off"></el-input>
                </el-form-item>
                <el-form-item label="新密码" prop="password">
                    <el-input type="password" v-model="form.password" autocomplete="off"></el-input>
                </el-form-item>
            </el-form>
            <div slot="footer" class="dialog-footer">
                <el-button @click="handleClose()">取 消</el-button>
                <el-button :loading="btnLoading" type="primary" @click="updatePassword('form')">确 定</el-button>
            </div>
        </el-dialog>
    </el-card>
</div>
<script src="https://cdn.jsdelivr.net/clipboard.js/1.5.12/clipboard.min.js"></script>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                list: [],
                listLoading: false,
                page: 1,
                pageCount: 0,
                loginRoute: '',

                search: {
                    keyword: '',
                    keyword1: '',
                    sort_prop: '',
                    sort_type: '',
                },
                dialogFormVisible: false,
                form: {
                    account: '',
                    password: '',
                },
                passwordRules: {
                    account: [
                        {required: true, message: '请输入新账号', trigger: 'change'},
                    ],
                    password: [
                        {required: true, message: '请输入新密码', trigger: 'change'},
                    ],
                },
                btnLoading: false,
                mch_id: 0,
                id: null,
                sort: 0,
                selectList: [
                    {value: 'store_name', name: '店铺名'},
                    {value: 'user_name', name: '用户名'},
                    {value: 'mch_id', name: '商户ID'},
                    {value: 'mobile', name: '联系人手机号'},
                ],
            };
        },
        methods: {
            handleClose () {
                this.dialogFormVisible = false;
                this.form.account = '';
                this.form.password = '';
            },
            triggeredChange (){
                if (this.search.keyword.length>0 && this.search.keyword1.length<=0) {
                    alert('请选择搜索方式');
                    this.search.keyword='';
                }
            },
            clearSearch() {
                this.page = 1;
                this.search.keyword = '';
                this.search.keyword1 = '';
                this.getList();
            },
            sortOrder (e) {
                this.search.sort_prop = e.prop;
                this.search.sort_type = e.order == "descending" ? 'DESC' : 'ASC';
                this.getList();
            },
            editSort(row) {
                this.id = row.id;
                this.sort = row.sort;
            },

            quit() {
                this.id = null;
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
                        r: 'plugin/mch/mall/mch/index',
                        page: self.page,
                        keyword: self.search.keyword,
                        keyword1: self.search.keyword1,
                        sort_prop: self.search.sort_prop,
                        sort_type: self.search.sort_type,
                    },
                    method: 'get',
                }).then(e => {
                    self.listLoading = false;
                    self.list = e.data.data.list;
                    self.pageCount = e.data.data.pagination.page_count;
                    self.loginRoute = e.data.data.url;
                }).catch(e => {
                    console.log(e);
                });
            },
            edit(id) {
                if (id) {
                    navigateTo({
                        r: 'plugin/mch/mall/mch/edit',
                        id: id,
                    });
                } else {
                    navigateTo({
                        r: 'plugin/mch/mall/mch/edit',
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
                            r: 'plugin/mch/mall/mch/destroy',
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
            switchStatus(scope, type) {
                let self = this;
                self.listLoading = true;
                request({
                    params: {
                        r: 'plugin/mch/mall/mch/switch-status',
                    },
                    method: 'post',
                    data: {
                        id: scope.row.id,
                        switch_type: type,
                    }
                }).then(e => {
                    self.listLoading = false;
                    if (e.data.code == 0) {
                        self.$message.success(e.data.msg);
                    } else {
                        self.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    console.log(e);
                });
            },
            searchList() {
                this.page = 1;
                this.getList();
            },
            updatePasswordDialog(row) {
                this.dialogFormVisible = true;
                this.mch_id = row.id;
                if (row.mchAdmin) {
                    this.form.account = row.mchAdmin.username;
                    this.form.password = row.mchAdmin.password;
                }
            },
            updatePassword(formName) {
                this.$refs[formName].validate((valid) => {
                    let self = this;
                    if (valid) {
                        self.btnLoading = true;
                        self.form.id = self.mch_id;
                        request({
                            params: {
                                r: 'plugin/mch/mall/mch/update-password'
                            },
                            method: 'post',
                            data: {
                                form: self.form,
                            }
                        }).then(e => {
                            self.btnLoading = false;
                            if (e.data.code == 0) {
                                self.$message.success(e.data.msg);
                                self.dialogFormVisible = false;
                                this.getList();
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
            change(e) {
                let self = this;
                request({
                    params: {
                        r: 'plugin/mch/mall/mch/edit-sort'
                    },
                    method: 'post',
                    data: {
                        sort: this.sort,
                        id: this.id
                    }
                }).then(e => {
                    if (e.data.code === 0) {
                        this.id = null
                        self.getList();
                    } else {
                        self.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    console.log(e);
                });
            }
        },
        mounted: function () {
            this.getList();
            this.route();
        }
    });

    var clipboard = new Clipboard('#copy_btn');

    var self = this;
    clipboard.on('success', function (e) {
        self.ELEMENT.Message.success('复制成功');
        e.clearSelection();
    });
    clipboard.on('error', function (e) {
        self.ELEMENT.Message.success('复制失败，请手动复制');
    });
</script>

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

    .export-dialog .el-dialog {
        min-width: 350px;
    }

    .export-dialog .el-dialog__body {
        padding: 20px 20px;
    }

    .export-dialog .el-button--submit {
        color: #FFF;
        background-color: #409EFF;
        border-color: #409EFF;
    }
</style>

<div id="app" v-cloak>
    <el-card class="box-card" shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <div>
                <span>商户审核列表</span>
                <div style="float: right;margin-top: -5px">
                    <el-button @click="exportRecord" type="primary" size="small">数据导出</el-button>
                </div>
            </div>
        </div>
        <div class="table-body">
            <div class="input-item">
                <el-input  @keyup.enter.native="searchList" size="small" placeholder="请输入店铺名称" v-model="keyword" clearable @clear='searchList'>
                    <el-button slot="append" icon="el-icon-search" @click="searchList"></el-button>
                </el-input>
            </div>
            <el-tabs v-model="activeName" @tab-click="handleClick">
                <el-tab-pane label="待审核" name="first"></el-tab-pane>
                <el-tab-pane label="通过" name="second"></el-tab-pane>
                <el-tab-pane label="未通过" name="third"></el-tab-pane>
                <el-tab-pane label="特殊折扣申请" name="four"></el-tab-pane>
            </el-tabs>
            <el-table
                    v-loading="listLoading"
                    :data="list"
                    border
                    style="width: 100%"
                    @selection-change="handleSelectionChange">
                <el-table-column
                        prop="id"
                        label="ID"
                        width="60">
                </el-table-column>
                <el-table-column
                        label="店铺信息" width="200">
                    <template slot-scope="scope">
                        <div flex="cross:center">
                            <com-image width="25" height="25" :src="scope.row.store.cover_url"></com-image>
                            <com-ellipsis style="margin-left: 10px;" :line="1">{{scope.row.store.name}}</com-ellipsis>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column label="用户" width="200">
                    <template slot-scope="scope">
                        <div flex="dir:left cross:center" v-if="scope.row.user">
                            <com-image width="25" height="25" :src="scope.row.user.avatar"></com-image>
                            <com-ellipsis style="margin-left: 10px;" :line="1">{{scope.row.user.nickname}}
                            </com-ellipsis>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column label="推荐人" width='200'>
                    <template slot-scope="scope">
                        <div>{{scope.row.parent_nickname}}</div>
                        <div>手机：{{scope.row.parent_mobile}}</DIV>
                        <div>等级：
                            <span v-if="scope.row.parent_role_type == 'branch_office'">分公司</span>
                            <span v-if="scope.row.parent_role_type == 'partner'">合伙人</span>
                            <span v-if="scope.row.parent_role_type == 'store'">店主</span>
                            <span v-if="scope.row.parent_role_type == 'user'">普通用户</span>
                        </DIV>
                    </template>
                </el-table-column>
                <el-table-column label="联系人" width='200'>
                    <template slot-scope="scope">
                        <div>
                            <com-ellipsis style="margin-left: 10px;" :line="1">{{scope.row.realname}}
                            </com-ellipsis>
                            <com-ellipsis style="margin-left: 10px;" :line="1">电话:{{scope.row.mobile}}
                            </com-ellipsis>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column label="特殊申请折扣" width='200' v-if="is_special == '1'">
                    <template slot-scope="scope">
                        <div>折扣：{{scope.row.special_rate}}%</div>
                        <div>说明：{{scope.row.special_rate_remark}}</DIV>
                    </template>
                </el-table-column>

                <el-table-column
                        v-if="activeName == 'second'"
                        label="入驻时间"
                        prop="review_time"
                        width="250">
                </el-table-column>
                <el-table-column
                        v-if="activeName == 'third'"
                        label="审核时间"
                        prop="review_time"
                        width="250">
                </el-table-column>
                <el-table-column
                        v-else
                        label="申请时间"
                        prop="created_at"
                        width="250">
                </el-table-column>
                <el-table-column
                        fixed="right"
                        label="操作">
                    <template slot-scope="scope">
                        <el-button @click="edit(scope.row.id)" type="text" circle size="mini">
                            <el-tooltip class="item" effect="dark" :content="scope.row.review_status == 0 ?'审核' : '详情'"
                                        placement="top">
                                <img src="statics/img/mall/order/detail.png" alt="">
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

            <div flex="dir:right" style="margin-top: 20px;">
                <el-pagination
                    hide-on-single-page
                    @current-change="pagination"
                    background
                    layout="prev, pager, next, jumper"
                    :page-count="pageCount">
                </el-pagination>
            </div>
        </div>


        <el-dialog flex="cross:center" class="export-dialog" :title="exportParams.is_show_download ? '下载' : '提示'" :visible.sync="exportDialogVisible" width="20%">
            <template v-if="!exportParams.is_show_download">
                <div flex="cross:center"><i style="color: #E6A23C;font-size: 20px;margin-right: 5px" class="el-icon-warning"></i>
                    <span>选中{{choose_list.length == 0 ? '全部，共计'+ exportParams.record_count +'条' : choose_list.length + '个'}}记录，是否确认导出</span>
                </div>
                <span slot="footer" class="dialog-footer">
                    <el-button @click="exportDialogVisible = false" size="small">取 消</el-button>
                    <el-button @click="exportRecordData" size="small" type="primary">确定</el-button>
                </span>
            </template>
            <template v-else>
                <el-progress :text-inside="true" :stroke-width="18" :percentage="exportParams.percentage"></el-progress>
                <span slot="footer" class="dialog-footer">
                    <form target="_blank" :action="exportParams.action_url" method="post">
                        <div class="modal-body">
                            <input name="_csrf" type="hidden" id="_csrf" value="<?= Yii::$app->request->csrfToken ?>">
                            <input name="flag" value="EXPORT" type="hidden">
                            <input name="is_download" :value="exportParams.is_download" type="hidden">
                        </div>
                        <div flex="dir:right" style="margin-top: 20px;">
                            <button v-if="exportParams.percentage == 100" type="submit" class="el-button el-button--primary el-button--small">点击下载</button>
                        </div>
                    </form>
                </span>
            </template>
        </el-dialog>

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
                activeName: 'first',
                review_status: 0,
                is_special: 0,
                keyword: null,

                // 导出参数
                choose_list: [],
                exportDialogVisible: false,
                exportParams: {
                    page: 1,
                    is_show_download: false,
                    is_download: 0,
                    percentage: 0,
                    action_url: '<?= Yii::$app->urlManager->createUrl('plugin/mch/mall/mch/export-list') ?>',
                    record_count: 0,//记录总数
                }
            };
        },
        methods: {

            //记录导出
            exportRecord(){
                this.exportDialogVisible = true;
                this.exportParams = {
                    page: 1,
                    is_show_download: false,
                    is_download: 0,
                    percentage: 0,
                    action_url: '<?= Yii::$app->urlManager->createUrl('plugin/mch/mall/mch/export-list') ?>',
                    record_count: 0,//记录总数
                }
            },
            exportRecordData(){
                let self = this;
                self.exportParams.is_show_download = true;
                var review_status;
                var is_special = 0;
                if(self.activeName == "first"){
                    review_status = 0;
                }else if(self.activeName == "second"){
                    review_status = 1;
                }else if(self.activeName == "third"){
                    review_status = 2;
                }else if (self.activeName == "four") {
                    review_status = 0;
                    is_special = 1;
                }

                request({
                    params: {
                        r: 'plugin/mch/mall/mch/export-list',
                    },
                    data: {
                        page          : self.exportParams.page,
                        search        : JSON.stringify(self.search),
                        choose_list   : self.choose_list,
                        review_status : review_status,
                        is_special    : is_special,
                        _csrf         : '<?= Yii::$app->request->csrfToken ?>',
                        is_download   : self.exportParams.is_download
                    },
                    method: 'post'
                }).then(e => {
                    let data = e.data.data;
                    if (e.data.code === 0) {
                        self.exportParams.increase = 100 / data.pagination.page_count;
                        self.exportParams.percentage += self.exportParams.increase;
                        self.exportParams.percentage = parseFloat(self.exportParams.percentage.toFixed(2));
                        if (data.pagination.current_page == data.pagination.page_count) {
                            self.exportParams.percentage = 100;
                            self.exportParams.is_download += data.export_data.is_download;
                        }
                        if (data.pagination.current_page < data.pagination.page_count) {
                            self.exportParams.page += 1;
                            self.exportRecordData();
                        }
                    }else{
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
                        r: 'plugin/mch/mall/mch/review',
                        page: self.page,
                        review_status: self.review_status,
                        is_special: self.is_special,
                        keyword: self.keyword,
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
                        r: 'plugin/mch/mall/mch/edit',
                        id: id,
                        is_review: this.review_status == 0 ? 1 : 0,
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
            handleClick(tab, event) {
                console.log(tab.index);
                if (tab.index == 3) {
                    this.is_special = 1;
                    this.review_status = 0;
                } else {
                    this.is_special = 0;
                    this.review_status = tab.index;
                }
                this.getList();
            },
            // 全选单前页
            handleSelectionChange(val) {
                let self = this;
                self.choose_list = [];
                val.forEach(function (item) {
                    self.choose_list.push(item.id);
                })
            },
        },
        mounted: function () {
            this.getList();
        }
    });
</script>

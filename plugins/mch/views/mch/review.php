

<div id="app" v-cloak>
    <el-card class="box-card" shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <div>
                <span>入驻申请</span>
                <div style="float: right;margin-top: -5px">
                    <el-button @click="exportRecord" type="primary" size="small">数据导出</el-button>
                </div>
            </div>
        </div>
        <div class="table-body" >
            <div class="input-item">
                <el-input  @keyup.enter.native="searchList" size="small" placeholder="申请人/手机号/用户名/ID" v-model="keyword" clearable @clear='searchList'>
                    <el-button slot="append" icon="el-icon-search" @click="searchList"></el-button>
                </el-input>
            </div>
            <el-tabs v-model="activeName" @tab-click="handleClick">
                <el-tab-pane label="待审核" name="verifying"></el-tab-pane>
                <el-tab-pane label="已通过" name="passed"></el-tab-pane>
                <el-tab-pane label="未通过" name="refused"></el-tab-pane>
                <el-tab-pane label="资料填写中" name="applying"></el-tab-pane>
                <el-tab-pane label="特殊折扣申请" name="special_discount"></el-tab-pane>
            </el-tabs>
            <el-table v-loading="listLoading" :data="list" border style="width: 100%" @selection-change="handleSelectionChange">
                <el-table-column prop="id" label="ID" width="70"></el-table-column>
                <el-table-column label="申请人" width="200">
                    <template slot-scope="scope">
                        <div style="font-size:12px;">
                            <div>申请人姓名：{{scope.row.realname}}</DIV>
                            <div>申请人电话：{{scope.row.mobile}}</DIV>
                            <div>推荐人：{{scope.row.parent_nickname}}</div>
                            <div>推荐人手机：{{scope.row.parent_mobile}}</DIV>
                            <div>推荐人等级：
                                <span v-if="scope.row.parent_role_type == 'branch_office'">分公司</span>
                                <span v-if="scope.row.parent_role_type == 'partner'">合伙人</span>
                                <span v-if="scope.row.parent_role_type == 'store'">VIP会员</span>
                                <span v-if="scope.row.parent_role_type == 'user'">普通用户</span>
                            </DIV>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column label="店铺名称" prop="store_name" width="200"></el-table-column>
                <el-table-column align="center" label="状态" width="100">
                    <template slot-scope="scope">
                        <div style="color:#333" v-if="scope.row.status == 'applying'">资料填写中</div>
                        <div style="color:#cc3311" v-if="scope.row.status == 'refused'">未通过</div>
                        <div style="color:green" v-if="scope.row.status == 'passed'">已通过</div>
                        <div style="color:#0040ae" v-if="scope.row.status == 'verifying'">待审核</div>
                    </template>
                </el-table-column>
                <el-table-column label="特殊折扣申请" width="150">
                    <template slot-scope="scope">
                        <div v-if="scope.row.is_special_discount == 1">
                            <div>折扣：{{scope.row.settle_discount}}折</div>
                            <div>说明：{{scope.row.settle_special_rate_remark}}</DIV>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column label="日期" width="240">
                    <template slot-scope="scope">
                        <div>申请时间：{{scope.row.created_at|dateTimeFormat('Y-m-d H:i:s')}}</div>
                        <div>更新时间：{{scope.row.updated_at|dateTimeFormat('Y-m-d H:i:s')}}</div>
                    </template>
                </el-table-column>
                <el-table-column fixed="right" label="操作">
                    <template slot-scope="scope">
                        <el-button @click="applyEdit(scope.row)" size="mini" circle style="margin-left: 10px;">
                            <el-tooltip class="item" effect="dark" content="编辑" placement="top">
                                <img src="statics/img/mall/edit.png" alt="">
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

        <el-dialog title="入驻审核" :visible.sync="apply.dialogVisible">
            <el-form :model="apply.data" :rules="apply.rules" ref="applyForm" label-width="150px"  size="mini">
                <el-tabs>
                    <el-tab-pane label="店铺信息">
                        <el-form-item label="店铺名称：" prop="store_name">
                            <el-input :disabled="apply.data.status == 'verifying' ? false : true" v-model="apply.data.store_name" placeholder="请输入内容" style="width:80%"></el-input>
                        </el-form-item>
                        <el-form-item label="绑定手机：" prop="bind_mobile">
                            <el-input :disabled="apply.data.status == 'verifying' ? false : true" v-model="apply.data.bind_mobile" placeholder="绑定手机" style="width:80%"></el-input>
                        </el-form-item>
                        <el-form-item label="绑定用户：">
                            <div>{{apply.data.nickname}}</div>
                        </el-form-item>
                        <el-form-item label="行业分类：" prop="store_mch_common_cat_id">
                            <el-select :disabled="apply.data.status == 'verifying' ? false : true" v-model="apply.data.store_mch_common_cat_id" placeholder="请选择" style="width:50%">
                                <el-option
                                        v-for="item in cats"
                                        :key="item.id"
                                        :label="item.name"
                                        :value="item.id">
                                </el-option>
                            </el-select>
                        </el-form-item>
                        <el-form-item label="店铺地址：" prop="store_address">
                            <div>{{apply.data.province}} / {{apply.data.city}} / {{apply.data.district}}</div>
                            <el-input :disabled="apply.data.status == 'verifying' ? false : true" v-model="apply.data.store_address" placeholder="详细地址" style="width:80%"></el-input>
                        </el-form-item>
                        <el-form-item label="店铺折扣：" >
                            <el-input :disabled="apply.data.status == 'verifying' ? false : true" v-model="apply.data.settle_discount" placeholder="请输入内容" style="width:150px;">
                                <template slot="append">折</template>
                            </el-input>
                        </el-form-item>
                        <template v-if="apply.data.is_special_discount == 1">
                            <el-form-item label="特殊折扣申请说明：" >
                                <div style="color:gray">{{apply.data.settle_special_rate_remark}}</div>
                            </el-form-item>
                        </template>

                    </el-tab-pane>
                    <el-tab-pane label="结算信息">
                        <el-form-item label="银行：">
                            <div>{{apply.data.settle_bank}}</div>
                        </el-form-item>
                        <el-form-item label="开户人：">
                            <div>{{apply.data.settle_realname}}</div>
                        </el-form-item>
                        <el-form-item label="银行卡号：">
                            <div>{{apply.data.settle_num}}</div>
                        </el-form-item>
                    </el-tab-pane>
                    <el-tab-pane label="申请人信息">
                        <el-form-item label="申请人：">
                            <div>{{apply.data.realname}}</div>
                        </el-form-item>
                        <el-form-item label="申请人手机：">
                            <div>{{apply.data.mobile}}</div>
                        </el-form-item>
                        <el-form-item label="推荐人：">
                            <div>{{apply.data.parent_nickname}}</div>
                        </el-form-item>
                        <el-form-item label="推荐人手机：">
                            <div>{{apply.data.parent_mobile}}</div>
                        </el-form-item>
                        <el-form-item label="推荐人等级：">
                            <div v-if="apply.data.parent_role_type == 'store'">VIP会员</div>
                            <div v-if="apply.data.parent_role_type == 'partner'">合伙人</div>
                            <div v-if="apply.data.parent_role_type == 'branch_office'">分公司</div>
                            <div v-if="apply.data.parent_role_type == 'user'">普通用户</div>
                        </el-form-item>
                    </el-tab-pane>
                    <el-tab-pane label="企业信息">
                        <el-form-item label="营业执照编号：">
                            <div>{{apply.data.license_num}}</div>
                        </el-form-item>
                        <el-form-item label="营业执照名称：">
                            <div>{{apply.data.license_name}}</div>
                        </el-form-item>
                        <el-form-item label="营业执照图片：">
                            <img style="margin-top:10px;" width="50%" :src="apply.data.license_pic"/>
                        </el-form-item>
                    </el-tab-pane>
                    <el-tab-pane label="法人资料">
                        <el-form-item label="法人姓名：">
                            <div>{{apply.data.cor_realname}}</div>
                        </el-form-item>
                        <el-form-item label="证件号码：">
                            <div>{{apply.data.cor_num}}</div>
                        </el-form-item>
                        <el-form-item label="身份证正面：">
                            <div><img width="50%" :src="apply.data.cor_pic1"/></div>
                        </el-form-item>
                        <el-form-item label="身份证反面：">
                            <div><img width="50%" :src="apply.data.cor_pic2"/></div>
                        </el-form-item>
                    </el-tab-pane>
                </el-tabs>
            </el-form>
            <div slot="footer" class="dialog-footer" v-if="apply.data.status == 'verifying'">
                <el-button type="primary" @click="applyDo('passed')">通过</el-button>
                <el-button type="danger" @click="applyDo('refused')">拒绝</el-button>
            </div>
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
                activeName: 'verifying',
                keyword: null,

                // 导出参数
                choose_list: [],
                exportDialogVisible: false,
                exportParams: {
                    page: 1,
                    is_show_download: false,
                    is_download: 0,
                    percentage: 0,
                    action_url: '',
                    record_count: 0,//记录总数
                },

                cats: [],

                apply:{
                    dialogVisible: false,
                    data: {
                        id: 0,
                        status: 'verifying',
                        nickname: '请输入',
                        realname: '请输入',
                        mobile: '请输入',
                        bind_mobile: '请输入',
                        parent_role_type: 'user',
                        parent_nickname: '请输入',
                        parent_mobile: '请输入',
                        is_special_discount: 1,
                        store_name: '请输入',
                        store_mch_common_cat_id: "14",
                        store_address: '请输入',
                        settle_special_rate_remark: '请输入',
                        settle_discount: 1,
                        settle_bank: '请输入',
                        settle_num: '请输入',
                        settle_realname: '请输入',
                        license_num: '请输入',
                        license_name: '请输入',
                        license_pic: '',
                        cor_num: '请输入',
                        cor_pic1: '',
                        cor_pic2: '',
                        cor_realname: '请输入',
                        province: '',
                        city: '',
                        province: ''
                    },
                    rules: {
                        store_name: [
                            {required: true, message: '店铺名称不能为空', trigger: 'change'},
                        ],
                        bind_mobile: [
                            {required: true, message: '绑定手机不能为空', trigger: 'change'},
                        ],
                        store_mch_common_cat_id: [
                            {required: true, message: '行业分类不能为空', trigger: 'change'},
                        ],
                        store_address: [
                            {required: true, message: '店铺地址不能为空', trigger: 'change'},
                        ]
                    },
                }
            };
        },
        methods: {

            applyDo(act) {
                var self = this;
                try {
                    this.$refs['applyForm'].validate((valid) => {

                        if (!valid) return;

                        var apply_data = self.apply.data;
                        apply_data['act'] = act;
                        self.$prompt('请输入备注', '提示', {
                            confirmButtonText: '确定',
                            cancelButtonText: '取消',
                            beforeClose: (action, instance, done) => {
                                if (action === 'confirm') {
                                    instance.confirmButtonLoading = true;
                                    instance.confirmButtonText = '执行中...';
                                    apply_data['remark'] = instance.inputValue;
                                    request({
                                        params: {
                                            r: 'plugin/mch/mall/mch/review-do',
                                        },
                                        method: 'post',
                                        data: apply_data
                                    }).then(e => {
                                        instance.confirmButtonLoading = false;
                                        if (e.data.code === 0) {
                                            self.getList();
                                            self.apply.dialogVisible = false;
                                            self.$message.success(e.data.msg);
                                            done();
                                        } else {
                                            instance.confirmButtonText = '确定';
                                            self.$message.error(e.data.msg);
                                        }
                                    }).catch(e => {
                                        done();
                                        instance.confirmButtonLoading = false;
                                    });
                                }else{
                                    done();
                                }
                            }
                        });
                    });
                }catch (e) {
                    console.log(e);
                }
            },

            applyEdit(row){
                this.apply.data = row;
                this.apply.dialogVisible = true;
            },

            //记录导出
            exportRecord(){
                this.exportDialogVisible = true;
                this.exportParams = {
                    page: 1,
                    is_show_download: false,
                    is_download: 0,
                    percentage: 0,
                    action_url: '<?= Yii::$app->urlManager->createUrl('plugin/mch/mall/mch/export-review-list') ?>',
                    record_count: 0,//记录总数
                }
            },
            exportRecordData(){
                let self = this;
                self.exportParams.is_show_download = true;
                request({
                    params: {
                        r: 'plugin/mch/mall/mch/export-review-list',
                    },
                    data: {
                        page          : self.exportParams.page,
                        choose_list   : self.choose_list,
                        review_status : self.activeName,
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
                        review_status: self.activeName,
                        keyword: self.keyword,
                    },
                    method: 'get',
                }).then(e => {
                    self.listLoading = false;
                    self.list = e.data.data.list;
                    self.cats = e.data.data.cats;
                    self.pageCount = e.data.data.pagination.page_count;
                }).catch(e => {
                    console.log(e);
                });
            },
            handleClick(tab, event) {
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
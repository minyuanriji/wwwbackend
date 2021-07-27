<?php
/**
  * @link:http://www.gdqijianshi.com/
 * copyright: Copyright (c) 2020 广东七件事集团
 * author: zal
 */

?>

<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <div>
                <span>用户管理</span>
                <com-export-dialog style="float: right;margin-top: -5px" :field_list='exportList' :params="searchData"
                                   @selected="exportConfirm">
                </com-export-dialog>
            </div>
        </div>
        <div class="table-body">
            <el-select size="small" v-model="role_type" @change='search' class="select">
                <el-option key="" label="全部用户" value=""></el-option>
                <el-option key="branch_office" label="分公司" value="branch_office"></el-option>
                <el-option key="partner" label="合伙人" value="partner"></el-option>
                <el-option key="store" label="店主" value="store"></el-option>
                <el-option key="user" label="普通用户" value="user"></el-option>
            </el-select>
            <el-select size="small" v-model="platform" @change='search' class="select">
                <el-option key="0" label="全部平台" value="0"></el-option>
                <el-option key="mp-wx" label="微信小程序" value="mp-wx"></el-option>
                <el-option key="wechat" label="微信" value="wechat"></el-option>
                <el-option key="mp-ali" label="支付宝" value="aliapp"></el-option>
                <el-option key="mp-tt" label="抖音/头条" value="ttapp"></el-option>
                <el-option key="mp-bd" label="百度" value="bdapp"></el-option>
            </el-select>
            <div class="input-item" style="width:300px;">
                <el-input @keyup.enter.native="search" size="small" placeholder="请输入ID/昵称/手机号" v-model="keyword"
                          clearable @clear="search">
                    <el-button slot="append" icon="el-icon-search" @click="search"></el-button>
                </el-input>
            </div>

            <el-table class="table-info" :data="form" border style="width: 100%" v-loading="listLoading">
                <el-table-column prop="user_id" label="ID" width="100"></el-table-column>
                <el-table-column label="头像" width="280">
                    <template slot-scope="scope">
                        <com-image mode="aspectFill" style="float: left;margin-right: 8px"
                                   :src="scope.row.avatar_url"></com-image>
                        <div>{{scope.row.nickname}}</div>
                        <img class="platform-img" v-if="scope.row.platform == 'mp-wx'" src="statics/img/mall/wx.png"
                             alt="">
                        <img class="platform-img" v-if="scope.row.platform == 'wechat'" src="statics/img/mall/wx.png"
                             alt="">
                        <img class="platform-img" v-if="scope.row.platform == 'mp-ali'" src="statics/img/mall/ali.png"
                             alt="">
                        <img class="platform-img" v-if="scope.row.platform == 'mp-bd'" src="statics/img/mall/baidu.png"
                             alt="">
                        <img class="platform-img" v-if="scope.row.platform == 'mp-tt'"
                             src="statics/img/mall/toutiao.png" alt="">
                        <el-button @click="openId(scope.$index)" type="success"
                                   style="float:right;padding:5px !important;">显示OpenId
                        </el-button>
                        <div v-if="scope.row.is_open_id">{{scope.row.platform_user_id}}</div>
                    </template>
                </el-table-column>
                <el-table-column prop="mobile" label="手机号" width="120"></el-table-column>
                <el-table-column label="推广" width="260">
                    <template slot-scope="scope">
                        <div style="font-size:12px;">
                            <div>上级名称：[ID:{{scope.row.parent_id}}]{{scope.row.parent_nickname}}</div>
                            <div>上级身份：
                                <span v-if="scope.row.parent_role_type == 'branch_office'">分公司</span>
                                <span v-if="scope.row.parent_role_type == 'partner'">合伙人</span>
                                <span v-if="scope.row.parent_role_type == 'store'">店主</span>
                                <span v-if="scope.row.parent_role_type == 'user'">普通用户</span>
                            </div>
                            <div>上级手机：{{scope.row.parent_mobile}}</div>
                            <div>
                                <el-link @click="childDialog(scope.row)"  type="primary" style="font-size:12px;">
                                    用户推荐：{{scope.row.child_sum}}人（查看）
                                </el-link>
                            </div>
                            <div>
                                <el-link target="_blank" @click="$navigate({r: 'mall/finance/income-log', user_id:scope.row.user_id})" type="primary" style="font-size:12px;">
                                    用户收益：{{scope.row.total_income}}人（查看）
                                </el-link>
                            </div>
                        </div>
                    </template>
                </el-table-column>

                <el-table-column prop="role_type" label="会员类型" width="120">
                    <template slot-scope="scope">
                        <div v-if="scope.row.role_type == 'branch_office'">分公司</div>
                        <div v-if="scope.row.role_type == 'partner'">合伙人</div>
                        <div v-if="scope.row.role_type == 'store'">店主</div>
                        <div v-if="scope.row.role_type == 'user'">普通用户</div>
                    </template>
                </el-table-column>
                <el-table-column prop="order_count" label="订单数">
                    <template slot-scope="scope">
                        <el-button type="text" @click="$navigate({r: 'mall/order/index', user_id:scope.row.user_id})"
                                   v-text="scope.row.order_count"></el-button>
                    </template>
                </el-table-column>
                <el-table-column prop="coupon_count" label="优惠券数量">
                    <template slot-scope="scope">
                        <el-button type="text" @click="$navigate({r: 'mall/user/coupon', user_id:scope.row.user_id})"
                                   v-text="scope.row.coupon_count"></el-button>
                    </template>
                </el-table-column>
                <el-table-column prop="card_count" label="卡券数量">
                    <template slot-scope="scope">
                        <el-button type="text" @click="$navigate({r: 'mall/user/card', user_id:scope.row.user_id})"
                                   v-text="scope.row.card_count"></el-button>
                    </template>
                </el-table-column>
                <el-table-column prop="balance" label="余额">
                    <template slot-scope="scope">
                        <el-button type="text"
                                   @click="$navigate({r: 'mall/user/balance-log', user_id:scope.row.user_id})"
                                   v-text="scope.row.balance"></el-button>
                    </template>
                </el-table-column>
                <el-table-column prop="score" label="动态积分"></el-table-column>
                <el-table-column prop="static_score" label="静态积分">
                    <template slot-scope="scope">
                        <el-button type="text" @click="$navigate({r: 'mall/user/score-log', user_id:scope.row.user_id})"
                                   v-text="scope.row.static_score"></el-button>
                    </template>
                </el-table-column>

                <el-table-column prop="score" label="加入时间">
                    <template slot-scope="scope">
                        {{scope.row.created_at|dateTimeFormat('Y-m-d H:i:s')}}
                    </template>
                </el-table-column>

                <el-table-column label="操作" width="280">
                    <template slot-scope="scope">
                        <el-tooltip class="item" effect="dark" content="编辑" placement="top">
                            <el-button circle type="text" size="mini"
                                       @click="$navigate({r: 'mall/user/edit', id:scope.row.user_id, page: page})">
                                <img src="statics/img/mall/edit.png" alt="">
                            </el-button>
                        </el-tooltip>
                        <el-tooltip class="item" effect="dark" content="充值积分" placement="top">
                            <el-button circle type="text" size="mini" @click="handleIntegral(scope.row)">
                                <img src="statics/img/mall/integral.png" alt="">
                            </el-button>
                        </el-tooltip>
                        <el-tooltip class="item" effect="dark" content="充值余额" placement="top">
                            <el-button circle type="text" size="mini" @click="handleBalance(scope.row)">
                                <img src="statics/img/mall/balance.png" alt="">
                            </el-button>
                        </el-tooltip>
                    </template>
                </el-table-column>
            </el-table>
            <div style="text-align: right;margin: 20px 0;">
                <el-pagination @current-change="pagination" background layout="prev, pager, next"
                               :page-count="pageCount" :current-page="currentPage"></el-pagination>
            </div>
        </div>
        <!-- 充值积分 -->
        <el-dialog title="充值积分" :visible.sync="dialogIntegral" width="30%">
            <el-form :model="integralForm" label-width="80px" :rules="integralFormRules" ref="integralForm">
                <el-form-item label="操作" prop="type">
                    <el-radio v-model="integralForm.type" label="1">充值</el-radio>
                    <el-radio v-model="integralForm.type" label="2">扣除</el-radio>
                </el-form-item>
                <el-form-item label="积分数" prop="num" size="small">
                    <el-input oninput="this.value = this.value.replace(/[^0-9]/g, '');" v-model="integralForm.num"
                              :max="999999999"></el-input>
                </el-form-item>
                <el-form-item label="充值图片" prop="pic_url">
                    <com-attachment :multiple="false" :max="1" @selected="integralPicUrl">
                        <el-button size="mini">选择文件</el-button>
                    </com-attachment>
                    <com-image width="80px" height="80px" mode="aspectFill" :src="integralForm.pic_url"></com-image>
                </el-form-item>
                <el-form-item label="备注" prop="remark" size="small">
                    <el-input v-model="integralForm.remark"></el-input>
                </el-form-item>
            </el-form>
            <div slot="footer" class="dialog-footer">
                <el-button @click="dialogIntegral = false">取消</el-button>
                <el-button :loading="btnLoading" type="primary" @click="integralSubmit">充值</el-button>
            </div>
        </el-dialog>
        <!-- 充值余额 -->
        <el-dialog title="充值余额" :visible.sync="dialogBalance" width="30%">
            <el-form :model="balanceForm" label-width="80px" :rules="balanceFormRules" ref="integralForm">
                <el-form-item label="操作" prop="type">
                    <el-radio v-model="balanceForm.type" label="1">充值</el-radio>
                    <el-radio v-model="balanceForm.type" label="2">扣除</el-radio>
                </el-form-item>
                <el-form-item label="金额" prop="price" size="small">
                    <el-input type="number" v-model="balanceForm.price"></el-input>
                </el-form-item>
                <el-form-item label="充值图片" prop="pic_url">
                    <com-attachment :multiple="false" :max="1" @selected="balancePicUrl">
                        <el-button size="mini">选择文件</el-button>
                    </com-attachment>
                    <com-image width="80px" height="80px" mode="aspectFill" :src="balanceForm.pic_url"></com-image>
                </el-form-item>
                <el-form-item label="备注" prop="remark" size="small">
                    <el-input v-model="balanceForm.remark"></el-input>
                </el-form-item>
            </el-form>
            <div slot="footer" class="dialog-footer">
                <el-button @click="dialogBalance = false">取消</el-button>
                <el-button :loading="btnLoading" type="primary" @click="balanceSubmit">充值</el-button>
            </div>
        </el-dialog>

        <!-- 用户推荐列表 -->
        <el-dialog :title="'用户'+recommandData.nickname+'[ID:'+recommandData.id+']的推荐列表'" :visible.sync="dialogChildren" width="50%">
            <el-select size="small" v-model="recommandData.role_type" @change='childSearch' class="select">
                <el-option key="" label="全部用户" value=""></el-option>
                <el-option key="branch_office" label="分公司" value="branch_office"></el-option>
                <el-option key="partner" label="合伙人" value="partner"></el-option>
                <el-option key="store" label="店主" value="store"></el-option>
                <el-option key="user" label="普通用户" value="user"></el-option>
            </el-select>

            <el-select size="small" v-model="recommandData.team_type" @change='childSearch' class="select">
                <el-option key="" label="全部团队" value=""></el-option>
                <el-option key="direct_push" label="直推" value="direct_push"></el-option>
                <el-option key="Interpulsion" label="间推" value="Interpulsion"></el-option>
            </el-select>

            <el-date-picker size="small" v-model="recommandData.date" type="datetimerange"
                style="float: left"
                value-format="yyyy-MM-dd"
                range-separator="至" start-placeholder="开始日期"
                @change="selectDateTime"
                end-placeholder="结束日期">
            </el-date-picker>

            <div class="input-item" style="width: 200px;margin-left: 10px;">
                <el-input @keyup.enter.native="childSearch" size="small" placeholder="请输入ID/昵称/手机号" v-model="recommandData.keyword"
                          clearable @clear="childSearch">
                    <el-button slot="append" icon="el-icon-search" @click="childSearch"></el-button>
                </el-input>
            </div>

            <el-table v-loading="childDataLoading"  :data="childData" style="width: 100%">
                <el-table-column prop="id" label="ID" width="70"></el-table-column>
                <el-table-column label="头像" >
                    <template slot-scope="scope">
                        <com-image mode="aspectFill" style="float: left;margin-right: 8px"
                                   :src="scope.row.avatar_url"></com-image>
                        <div>{{scope.row.nickname}}</div>
                    </template>
                </el-table-column>
                <el-table-column label="团队类型">
                    <template slot-scope="scope">
                        {{scope.row.team_type}}
                    </template>
                </el-table-column>
                <el-table-column label="等级" width="110">
                    <template slot-scope="scope">
                        <div v-if="scope.row.role_type == 'branch_office'">分公司</div>
                        <div v-if="scope.row.role_type == 'partner'">合伙人</div>
                        <div v-if="scope.row.role_type == 'store'">店主</div>
                        <div v-if="scope.row.role_type == 'user'">普通用户</div>
                    </template>
                </el-table-column>
                <el-table-column prop="mobile" label="手机" width="110"></el-table-column>
                <el-table-column label="日期">
                    <template slot-scope="scope">
                        {{scope.row.created_at|dateTimeFormat('Y-m-d H:i:s')}}
                    </template>
                </el-table-column>
                <el-table-column label="店铺名">
                    <template slot-scope="scope">
                        {{scope.row.store_name}}
                    </template>
                </el-table-column>
            </el-table>
            <div style="text-align: right;margin: 20px 0;">
                <el-pagination @current-change="childPagination" background
                               layout="prev, pager, next"
                               :page-count="recommandData.pageCount"
                               :current-page="recommandData.currentPage">
                </el-pagination>
            </div>
        </el-dialog>

    </el-card>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                searchData: {
                    keyword: '',
                },
                platform: '0',
                member_level: '0',
                role_type: '',
                mall_members: [],
                keyword: '',
                form: [],
                member: [],
                pageCount: 0,
                page: 1,
                member_page: 1,
                currentPage: null,
                listLoading: false,
                btnLoading: false,

                // 导出
                exportList: [],

                //积分
                dialogIntegral: false,
                integralForm: {
                    type: '1',
                    num: '',
                    pic_url: '',
                    remark: '',
                },
                integralFormRules: {
                    type: [
                        {required: true, message: '操作不能为空', trigger: 'blur'},
                    ],
                    num: [
                        {required: true, message: '积分数不能为空', trigger: 'blur'},
                    ],
                },

                //余额
                dialogBalance: false,
                balanceForm: {
                    type: '1',
                    price: '',
                    pic_url: '',
                    remark: '',
                },
                balanceFormRules: {
                    type: [
                        {required: true, message: '操作不能为空', trigger: 'blur'},
                    ],
                    num: [
                        {required: true, message: '金额不能为空', trigger: 'blur'},
                    ],
                },

                //用户推荐
                dialogChildren: false,
                childDataLoading: false,
                recommandData: {
                    id: 0,
                    nickname: '',
                    keyword: '',
                    role_type: '',
                    start_date: '',
                    end_date: '',
                    page: 1,
                    pageCount: 0,
                    currentPage: 0,
                    team_type: '',
                },
                childData: []
            }
        },
        methods: {
            selectDateTime(e) {
                if (e != null) {
                    this.recommandData.start_date = e[0];
                    this.recommandData.end_date = e[1];
                } else {
                    this.recommandData.start_date = '';
                    this.recommandData.end_date = '';
                }
                this.getChildData();
            },
            childDialog(row){
                this.dialogChildren = true;
                if(row.id != this.recommandData.id){
                    this.recommandData.id = row.id;
                    this.recommandData.nickname = row.nickname;
                    this.page = 1;
                    this.keyword = '';
                    this.getChildData();
                }
            },
            childSearch(){
                this.page = 1;
                this.getChildData();
            },
            childPagination(currentPage) {
                this.recommandData.page = currentPage;
                this.getChildData();
            },
            getChildData(){
                var self = this;
                self.childDataLoading = true;
                request({
                    params: {
                        r: 'mall/user/get-child',
                        page: self.recommandData.page,
                        parent_id: self.recommandData.id,
                        keyword: self.recommandData.keyword,
                        role_type: self.recommandData.role_type,
                        start_date: self.recommandData.start_date,
                        end_date: self.recommandData.end_date,
                        team_type: self.recommandData.team_type,
                    },
                }).then(e => {
                    if (e.data.code === 0) {
                        self.childData = e.data.data.list;
                        self.recommandData.pageCount = e.data.data.pagination.page_count;
                        self.recommandData.currentPage = e.data.data.pagination.current_page;
                    } else {
                        self.$message.error(e.data.msg);
                    }
                    self.childDataLoading = false;
                }).catch(e => {
                    self.childDataLoading = false;
                });
            },
            openId(index) {
                let item = this.form;
                item[index].is_open_id = !item[index].is_open_id;
                this.form = JSON.parse(JSON.stringify(this.form));
            },
            exportConfirm() {
                this.searchData.keyword = this.keyword;
            },
            //积分
            integralPicUrl(e) {
                if (e.length) {
                    this.integralForm.pic_url = e[0].url;
                }
            },
            handleIntegral(row) {
                console.log(row);


                this.integralForm = Object.assign(this.integralForm, {user_id: row.user_id});
                this.dialogIntegral = true;
            },
            integralSubmit() {
                this.$refs.integralForm.validate((valid) => {
                    if (valid) {
                        let para = Object.assign({}, this.integralForm);
                        this.btnLoading = true;
                        request({
                            params: {
                                r: 'mall/user/score',
                            },
                            method: 'post',
                            data: para,
                        }).then(e => {
                            if (e.data.code === 0) {
                                location.reload();
                                this.dialogIntegral = false;
                            } else {
                                this.$message.error(e.data.msg);
                            }
                            this.btnLoading = false;
                        }).catch(e => {
                            this.btnLoading = false;
                        });
                    }
                });
            },

            //余额
            balancePicUrl(e) {
                if (e.length) {
                    this.balanceForm.pic_url = e[0].url;
                }
            },
            handleBalance(row) {
                this.balanceForm = Object.assign(this.balanceForm, {user_id: row.user_id});
                this.dialogBalance = true;
            },
            balanceSubmit() {
                this.$refs.integralForm.validate((valid) => {
                    if (valid) {
                        let para = Object.assign({}, this.balanceForm);
                        this.btnLoading = true;
                        request({
                            params: {
                                r: 'mall/user/balance',
                            },
                            method: 'post',
                            data: para,
                        }).then(e => {
                            if (e.data.code === 0) {
                                location.reload();
                                this.dialogBalance = false;
                            } else {
                                this.$message.error(e.data.msg);
                            }
                            this.btnLoading = false;
                        }).catch(e => {
                            this.btnLoading = false;
                        });
                    }
                });
            },
            //
            search() {
                this.page = 1;
                this.getList();
            },

            pagination(currentPage) {
                this.page = currentPage;
                this.getList();
            },
            getList() {
                this.listLoading = true;
                request({
                    params: {
                        r: 'mall/user/index',
                        page: this.page,
                        member_level: this.member_level,
                        role_type: this.role_type,
                        platform: this.platform,
                        keyword: this.keyword,
                    },
                }).then(e => {
                    if (e.data.code === 0) {
                        this.form = e.data.data.list;
                        this.exportList = e.data.data.exportList;
                        this.pageCount = e.data.data.pagination.page_count;
                        this.currentPage = e.data.data.pagination.current_page;
                        this.mall_members = e.data.data.mall_members;
                    } else {
                        this.$message.error(e.data.msg);
                    }
                    this.listLoading = false;
                }).catch(e => {
                    this.listLoading = false;
                });
            },

            getMember() {
                let self = this;
                request({
                    params: {
                        r: 'mall/member-level/index',
                        page: self.member_page
                    },
                    method: 'get',
                }).then(e => {
                    if (e.data.data.list.length > 0) {
                        if (self.member_page == 1) {
                            self.member = e.data.data.list;
                        } else {
                            self.member = self.member.concat(e.data.data.list);
                        }
                        self.member_page++;
                        self.getMember();
                    }
                }).catch(e => {
                    console.log(e);
                });
            },
        },
        mounted: function () {
            this.page = getQuery('page') ? getQuery('page') : 1;
            this.getList();
            this.getMember();
        }
    });
</script>
<style>
    .table-body {
        padding: 20px;
        background-color: #fff;
    }

    .table-info .el-button {
        padding: 0 !important;
        border: 0;
        margin: 0 5px;
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

    .select {
        float: left;
        width: 100px;
        margin-right: 10px;
    }
</style>
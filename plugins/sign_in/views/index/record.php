<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 名片
 * Author: zal
 * Date: 2020-07-10
 * Time: 15:48
 */

?>

<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <span>签到记录</span>
        </div>

        <div class="table-body">
            <el-form size="small" :inline="true" >

                <el-select size="small" v-model="search.level" @change='search' class="select">
                    <el-option key="0" label="全部会员" value="0"></el-option>
                    <el-option v-for="item in member" :key="item.level" :label="item.name" :value="item.level"></el-option>
                </el-select>


                <el-form-item>
                    <el-input style="width: 200px" v-model="search.keyword" placeholder="ID/会员昵称/姓名/手机号"></el-input>
                </el-form-item>
                <el-form-item>
                    <el-button type="primary" plain @click="searchs">搜索</el-button>
                </el-form-item>
            </el-form>
            <el-tabs v-model="activeName" @tab-click="handleClick">
                <el-table :data="list" border v-loading="loading" size="small" style="margin-bottom: 15px;"
                          @selection-change="handleSelectionChange">
                    <el-table-column align='center' type="selection" width="60"></el-table-column>
                    <el-table-column prop="id" label="会员ID" width="60"></el-table-column>
                    <el-table-column prop="nickname"  label="会员">

                        <template slot-scope="scope">
                            <div>{{scope.row.nickname}}</div>
                            <div>
                                <el-image
                                        style="width: 40px; height: 40px"
                                        :src="scope.row.avatar_url"
                                ></el-image>
                            </div>
                        </template>
                    </el-table-column>
                    </el-table-column>
                    <el-table-column label="姓名/手机号" prop="mobile">
                        <template slot-scope="scope">
                            <div>{{scope.row.username}}</div>
                            <div>{{scope.row.mobile}}</div>
                        </template>
                    </el-table-column>
                    <el-table-column prop="member_level" label="会员类型" width="120">
                        <template slot-scope="scope">
                            <div v-if="scope.row.level == item.level" v-for="item in mall_members">{{item.name}}</div>
                            <div v-if="scope.row.level == -1">普通用户</div>
                        </template>
                    </el-table-column>
                    <el-table-column label="最新签到时间" prop="company_name">
                        <template slot-scope="scope">
                            <div>{{scope.row.created}}</div>
                        </template>
                    </el-table-column>
                    <el-table-column label="今日签到状态" prop="sign_in">
                        <template slot-scope="scope">
                            <div>{{scope.row.sign_in}}</div>
                        </template>
                    </el-table-column>
                    <el-table-column label="连续签到状态" prop="continue">
                        <template slot-scope="scope">
                            <div>{{scope.row.continue}}</div>
                        </template>
                    </el-table-column>
                    <el-table-column label="累计奖励" prop="position_name">
                        <template slot-scope="scope">
                            <div>积分：{{scope.row.integral_num}}积分</div>
                            <div>优惠券：{{scope.row.coupon_num}}张</div>
                        </template>
                    </el-table-column>

                    <el-table-column label="操作">
                        <template slot-scope="scope">
                            <el-button type="primary" circle style="margin-left: 10px;margin-top: 10px"
                                       @click.native="toEdit(scope.row.id)">
                                <el-tooltip class="item" effect="dark" content="签到详情" placement="top">
                                    <img src="statics/img/mall/share/detail.png" alt="">签到详情
                                </el-tooltip>
                            </el-button>
                        </template>
                    </el-table-column>
                </el-table>
            </el-tabs>
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
        data: {
            searchData: {
                keyword: '',
                start_time: '',
                end_at: '',
                level:''
            },
            qrimg: '',
            showqr: false,
            avatar: '',
            nickname: '',
            mall_members: [],
            search: {
                keywords: '',
                start_time: '',
                end_at: '',
                level:''

            },
            member_page: 1,
            member:[],
            loading: false,
            activeName: '-1',
            list: [],
            pagination: null,
            dialogChild: false,
            dialogLoading: false,
            select: {
                nickname: '',
                status: 'first',
            },
            dialogContent: false,
            remarksForm: {
                remarks: '',
                id: ''
            },
            remarksLoading: false,
            exportList: [],
            edit: {
                show: false,
            },
            choose_list: []
        },
        mounted() {
            this.loadData();
            this.getMember();
        },

        methods: {
            down() {
                var alink = document.createElement("a");
                alink.href = this.qrimg;
                alink.download = this.nickname;
                alink.click();
            },
            confirmSubmit() {
                this.search.status = this.activeName
            },
            exportConfirm() {
                this.searchData.keyword = this.keyword;
                this.searchData.level = this.level;
                this.searchData.start_time = this.start_time;
                this.searchData.end_at = this.end_at;
            },
            loadData(page) {
                console.log(page,'1111')
                this.loading = true;
                request({
                    params: {
                        r: 'plugin/sign_in/mall/index/record',
                        page: page,
                        keyword: this.search.keyword,
                        level: this.search.level,
                        start_time: this.search.start_time,
                        end_at: this.search.end_at,
                    },
                }).then(e => {
                    this.loading = false;
                    if (e.data.code === 0) {
                        this.list = e.data.data.list;
                        this.pagination = e.data.data.pagination;
                        this.exportList = e.data.data.exportList;
                        this.mall_members = e.data.data.mall_members;
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {
                });
            },
            getMember() {
                let self = this;
                request({
                    params: {
                        r: 'plugin/sign_in/mall/index/level',
                        page: self.member_page
                    },
                    // method: 'get',
                }).then(e => {
                    console.log(e,'eeeeeeeeee')
                    if (e.data.data.list.length > 0) {
                        if (self.member_page == 1) {
                            self.member.push(...e.data.data.list);
                            console.log(self.member,'self.member')
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
            pageChange(page) {
                this.search.page = page;
                this.loadData(page);
                this.getMember();
            },
            handleClick(tab, event) {
                this.search.page = 1;
                this.search.status = this.activeName;
                this.loadData(this.search.page)
            },
            order(id) {
                navigateTo({
                    r: 'mall/distribution/order',
                    id: id
                })
            },
            toEdit(user_id) {
                navigateTo({
                    r: 'plugin/sign_in/mall/index/user',
                    user_id: user_id
                })
            },

            remarks(row) {
                this.dialogContent = true;
                this.remarksForm = {
                    remarks: row.remarks,
                    id: row.id
                }
            },
            editClick() {
                this.edit.show = true;
            },
            editLevel(row) {
                this.level.show = true;
                this.level.distribution = row;
            },
            handleSelectionChange(val) {
                let self = this;
                self.choose_list = [];
                val.forEach(function (item) {
                    self.choose_list.push(item.id);
                })
            },
            searchs() {
                this.page = 1;
                this.loadData(this.page);
            },
        }
    });
</script>
<style>
    .el-tabs__header {
        font-size: 16px;
    }

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

    .batch {
        margin: 0 0 20px;
        display: inline-block;
    }

    .batch .el-button {
        padding: 9px 15px !important;
    }
</style>
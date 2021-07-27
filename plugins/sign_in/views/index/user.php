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
            <el-breadcrumb separator="/">
                <el-breadcrumb-item><span style="color: #409EFF;cursor: pointer"
                                          @click="$navigate({r:'plugin/sign_in/mall/index/record'})">签到记录</span></el-breadcrumb-item>
                <el-breadcrumb-item>签到详情</el-breadcrumb-item>
            </el-breadcrumb>
        </div>

        <div class="table-body">

            <el-tabs v-model="activeName" @tab-click="handleClick">
                <el-table :data="list" border v-loading="loading" size="small" style="margin-bottom: 15px;"
                          @selection-change="handleSelectionChange">
                    <el-table-column prop="id" label="id" width="60"></el-table-column>
                    <el-table-column prop="number"  label="数量">

                        <template slot-scope="scope">
                            <div>{{scope.row.number}}</div>
                        </template>
                    </el-table-column>
                    <el-table-column label="备注" prop="remark">
                        <template slot-scope="scope">
                            <div>{{scope.row.remark}}</div>
                        </template>
                    </el-table-column>
                    <el-table-column label="领取时间" prop="created_at">
                        <template slot-scope="scope">
                            <div>{{scope.row.created_at|dateTimeFormat('Y-m-d H:i:s')}}</div>
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
            user_id:'',
            number: 0,
            member_page: 1,
            activeName: '-1',
            member:[],
            loading: false,
            created_at: '---',
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
                console.log(page,'1111');
                this.user_id = location.href.split('=')[2];
                this.loading = true;
                request({
                    params: {
                        r: 'plugin/sign_in/mall/index/user',
                        page: page,
                        user_id: this.user_id,
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
                    if (e.data.data.list.length > 0) {
                        if (self.member_page == 1) {
                            self.member.push(...e.data.data.list);
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
                console.log(page,'pagessssssss');
                // this.search.page = page;
                this.loadData(page);
                // this.getMember();
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
            levelSuccess() {
                this.loadData();
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
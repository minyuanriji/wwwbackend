<?php
Yii::$app->loadComponentView('com-user-finance-stat');
?>
<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <div>
                <span>商品统计</span>
            </div>
        </div>
        <div class="table-body">
            <el-date-picker size="small" v-model="date" type="datetimerange"
                            style="float: left"
                            value-format="yyyy-MM-dd HH:mm:ss"
                            range-separator="至" start-placeholder="开始日期"
                            @change="selectDateTime"
                            end-placeholder="结束日期">
            </el-date-picker>
            <div class="input-item">
                <el-input @keyup.enter.native="search" size="small" placeholder="请输入商品ID、商品名称搜索" v-model="keyword" clearable
                          @clear="search">
                    <el-button slot="append" icon="el-icon-search" @click="search"></el-button>
                </el-input>
            </div>
            <el-table :data="form" border style="width: 100%" v-loading="listLoading" @sort-change="sortChange">

                <el-table-column prop="id" label="ID" width="80"></el-table-column>

                <el-table-column prop="name" label="商品名称">
                    <template slot-scope="scope">
                        {{scope.row.name}}
                    </template>
                </el-table-column>

                <el-table-column label="总金额">
                    <template slot-scope="scope">
                        {{scope.row.total_price}}
                    </template>
                </el-table-column>

                <el-table-column label="购买次数" width="180">
                    <template slot-scope="scope">
                        {{scope.row.num}}
                    </template>
                </el-table-column>

                <el-table-column prop="refund_num" label="退换次数" width="500px" sortable="false"></el-table-column>

                <!--<el-table-column prop="scope" width="180" label="充值时间">
                    <template slot-scope="scope">
                        {{scope.row.created_at|dateTimeFormat('Y-m-d H:i:s')}}
                    </template>
                </el-table-column>-->

            </el-table>
            <div style="text-align: right;margin: 20px 0;">
                <el-pagination @current-change="pagination" background layout="prev, pager, next"
                               :page-count="pageCount"></el-pagination>
            </div>
        </div>
    </el-card>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                searchData: {
                    keyword: '',
                    date: '',
                    start_date: '',
                    end_date: '',
                },
                date: '',
                keyword: getQuery("user_id"),
                form: [],
                pageCount: 0,
                listLoading: false,
            };
        },
        methods: {
            exportConfirm() {
                this.searchData.keyword = this.keyword;
                this.searchData.date = this.date;
            },
            pagination(currentPage) {
                this.page = currentPage;
                this.getList();
            },
            search() {
                this.page = 1;
                if (this.date == null) {
                    this.searchData.start_date = '';
                    this.searchData.end_date = ''
                }
                this.getList();
            },
            getList() {
                this.listLoading = true;
                request({
                    params: {
                        r: 'mall/data-statistics/goods-statistics',
                        page: this.page,
                        date: this.date,
                        keyword: this.keyword,
                        start_date: this.searchData.start_date,
                        end_date: this.searchData.end_date,
                        sort_prop: this.sort_prop,
                        sort_type: this.sort_type,
                    },
                }).then(e => {
                    if (e.data.code === 0) {
                        let {list, pagination} = e.data.data;
                        this.form = list;
                        this.pageCount = pagination.page_count;
                    } else {
                        this.$message.error(e.data.msg);
                    }
                    this.listLoading = false;
                }).catch(e => {
                    this.listLoading = false;
                });
            },
            selectDateTime(e) {
                if (e != null) {
                    this.searchData.start_date = e[0];
                    this.searchData.end_date = e[1];
                } else {
                    this.searchData.start_date = '';
                    this.searchData.end_date = '';
                }
                this.search();
            },
            // 排序排列
            sortChange(row) {
                if (row.prop) {
                    this.sort_prop = row.prop;
                    this.sort_type = row.order == "descending" ? 'DESC' : 'ASC';
                } else {
                    this.sort_prop = '';
                    this.sort_type = '';
                }
                this.getList();
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
        display: inline-block;
        width: 250px;
        margin: 0 0 20px 20px;
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
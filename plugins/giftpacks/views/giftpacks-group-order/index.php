<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 0 0;position: relative;">
        <el-tabs v-model="activeName" @tab-click="handleClick">
            <el-tab-pane label="全部" name="all"></el-tab-pane>
            <el-tab-pane label="拼团成功" name="success"></el-tab-pane>
            <el-tab-pane label="分享中" name="sharing"></el-tab-pane>
            <el-tab-pane label="已关闭" name="closed"></el-tab-pane>
            <div class="table-body">
                <span style="height: 32px;">创建时间：</span>
                <el-date-picker
                        class="item-box"
                        size="small"
                        @change="changeTime"
                        v-model="search.time"
                        type="datetimerange"
                        value-format="yyyy-MM-dd HH:mm:ss"
                        range-separator="至"
                        start-placeholder="开始日期"
                        end-placeholder="结束日期">
                </el-date-picker>

                <div class="input-item" style="width:300px;margin-bottom:20px;display:inline-block;">
                    <el-input @keyup.enter.native="toSearch" size="small" placeholder="订单ID/订单编号/大礼包名称" v-model="search.keyword" clearable @clear="toSearch">
                        <el-button slot="append" icon="el-icon-search" @click="toSearch"></el-button>
                    </el-input>
                </div>

                <el-table :data="list" size="small" border v-loading="loading" style="margin-bottom: 15px">

                    <el-table-column prop="id" label="ID" width="100"></el-table-column>

                    <el-table-column label="拼团商品" width="300">
                        <template slot-scope="scope">
                            <com-image style="float: left;margin-right: 10px;height: 80px;width: 80px"
                                       :src="scope.row.cover_pic"></com-image>
                            <div style="margin: 10px 0">{{ scope.row.title }}</div>
                        </template>
                    </el-table-column>

                    <el-table-column label="团长" width="300">
                        <template slot-scope="scope">
                            <com-image style="float: left;margin-right: 10px;height: 80px;width: 80px"
                                       :src="scope.row.avatar_url"></com-image>
                            {{scope.row.nickname}}(ID:{{scope.row.user_id}})
                        </template>
                    </el-table-column>

                    <el-table-column label="参团人数" width="130">
                        <template slot-scope="scope">
                            <div>所需人数：<span style="color: rgb(235,86,72)">{{scope.row.need_num}}</span></div>
                            <div>当前人数：<span style="color: rgb(235,86,72)">{{scope.row.user_num}}</span></div>
                        </template>
                    </el-table-column>

                    <el-table-column label="时间" width="150">
                        <template slot-scope="scope">
                            {{ scope.row.created_at|dateTimeFormat('Y-m-d H:i:s') }}
                        </template>
                    </el-table-column>

                    <el-table-column label="操作">
                        <template slot-scope="scope">
                            <el-button @click="toDetail(scope.row.id)" type="text" circle size="mini">
                                <el-tooltip class="item" effect="dark" content="查看拼团订单详情" placement="top">
                                    <img src="statics/img/mall/order/detail.png" alt="">
                                </el-tooltip>
                            </el-button>
                            <!--<el-button @click="destroy(scope.row.id, scope.$index)" circle type="text" size="mini">
                                <el-tooltip class="item" effect="dark" content="删除" placement="top">
                                    <img src="statics/img/mall/del.png" alt="">
                                </el-tooltip>
                            </el-button>-->
                        </template>
                    </el-table-column>
                </el-table>

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
        </el-tabs>
    </el-card>
</div>
<?php echo $this->render("item"); ?>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                passengersData: [],
                search: {
                    keyword: '',
                    status: 'all',
                    start_time: '',
                    end_time: '',
                    time: null,
                },
                loading: false,
                activeName: 'all',
                list: [],
                pagination: null,
                exportList: [],
            };
        },
        mounted() {
            this.loadData();
        },
        methods: {
            // 日期搜索
            changeTime() {
                if (this.search.time) {
                    this.search.start_time = this.search.time[0];
                    this.search.end_time = this.search.time[1];
                } else {
                    this.search.start_time = null;
                    this.search.end_time = null;
                }
                this.loadData();
            },

            loadData(status = 'all', page = 1) {
                this.loading = true;
                request({
                    params: {
                        r: 'plugin/giftpacks/mall/giftpacks-group-order/group-index',
                        status: status,
                        page: page,
                        keyword: this.search.keyword,
                        start_time: this.search.start_time,
                        end_time: this.search.end_time,
                    },
                    method: 'get'
                }).then(e => {
                    this.loading = false;
                    if (e.data.code == 0) {
                        this.list = e.data.data.list;
                        this.pagination = e.data.data.pagination;
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    this.loading = false;
                });
            },

            toSearch() {
                this.page = 1;
                this.loadData(this.activeName);
            },

            showPassengers(row){
                this.passengersData = row.passengers;
                console.log(this.passengersData);
            },

            pageChange(page) {
                this.loadData(this.activeName, page);
            },

            handleClick(tab, event) {
                this.loadData(this.activeName)
            },

            toDetail(id){
                itemApp.show(id);
            },
        }
    })
</script>
<style>
    .item {
        margin-top: 10px;
        margin-right: 40px;
    }
    .el-tabs__header {
        padding: 0 20px;
        height: 56px;
        line-height: 56px;
        background-color: #fff;
    }

    .com-order-user {
        margin-left: 30px;
    }

    .com-order-head .com-order-time {
        color: #909399;
    }

    .export-btn {
        position: absolute;
        top: 10px;
        right: 10px;
        z-index: 2;
    }

    .table-body {
        padding: 20px;
        background-color: #fff;
    }

    .table-body .el-button {
        padding: 0!important;
        border: 0;
        margin: 0 5px;
    }
</style>
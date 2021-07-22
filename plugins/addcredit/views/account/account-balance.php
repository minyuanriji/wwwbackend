<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 0 0;position: relative;">
        <div style="width: 100%;height: 80px;background: white;margin-bottom: 20px;line-height: 80px;padding-left: 20px">账户余额查询</div>
            <div class="table-body">
                <el-table :data="list" size="small" border v-loading="loading" style="margin-bottom: 15px">

                    <el-table-column prop="id" label="ID" width="100"></el-table-column>

                    <el-table-column label="平台名称" width="200">
                        <template slot-scope="scope">
                            <div>{{scope.row.name}}</div>
                        </template>
                    </el-table-column>

                    <el-table-column label="账户余额" width="100">
                        <template slot-scope="scope">
                            <div>{{scope.row.balance}}</div>
                        </template>
                    </el-table-column>

<!--                    <el-table-column label="操作"></el-table-column>-->
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
    </el-card>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                passengersData: [],
                search: {
                    keyword: '',
                    status: 'all',
                    date_start: '',
                    date_end: '',
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
            loadData(page = 1) {
                this.loading = true;
                request({
                    params: {
                        r: 'plugin/addcredit/mall/account/account/account-balance',
                        page: page,
                    },
                    method: 'get'
                }).then(e => {
                    this.loading = false;
                    if (e.data.code == 0) {

                        console.log(e);
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
        }
    })
</script>
<style>
    .el-tabs__header {
        padding: 0 20px;
        height: 56px;
        line-height: 56px;
        background-color: #fff;
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
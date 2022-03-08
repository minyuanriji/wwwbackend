<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header" >
            <span>会员管理</span>
        </div>
        <div class="table-body">
            <el-tabs v-model="activeName" type="card">
                <el-tab-pane label="会员管理" name="first">

                    <el-table :data="list" border v-loading="loading" size="small" style="margin: 15px 0;">
                        <el-table-column prop="create_date" label="日期"></el-table-column>
                        <el-table-column prop="special_id" label="ID"></el-table-column>
                        <el-table-column prop="root_pid" label="推广位"></el-table-column>
                    </el-table>

                </el-tab-pane>
            </el-tabs>
        </div>
    </el-card>
</div>

<script>
    const app = new Vue({
        el: '#app',
        data: {
            loading: false,
            list: [],
            pagination: null,
            search: {

            },
            activeName: 'first',
        },
        mounted() {
            this.loadData();
        },
        computed: {},
        methods: {

            loadData(){
                this.loading = true;
                let params = {
                    r: 'plugin/taobao/mall/publisher/index'
                };
                params = Object.assign(params, this.search);
                params['token'] = getQuery("token");
                let that = this;
                request({
                    params: params,
                    method: 'get',
                }).then(e => {
                    that.loading = false;
                    if (e.data.code == 0) {
                        this.list  = e.data.data.list;
                    } else {
                        that.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    this.loading = false;
                });
            },
        }
    });
</script>
<style>

</style>
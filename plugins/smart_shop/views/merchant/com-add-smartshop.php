<template id="com-add-smartshop">
    <div class="com-add-smartshop">
        <el-button type="primary" @click="showDialog" size="big">添加智慧门店</el-button>
        <el-dialog title="选择智慧门店" :visible.sync="dialogVisible" style="width:100%">
            <el-table v-loading="listLoading"  :data="list" border style="width: 100%">

            </el-table>
        </el-dialog>
    </div>
</template>

<script>
    Vue.component('com-add-smartshop', {
        template: '#com-add-smartshop',
        props: {

        },
        data() {
            return {
                dialogVisible: true,
                list: [],
                listLoading: false,
                page: 1,
                pageCount: 0,
            };
        },
        created() {
            this.getList();
        },
        watch: {},
        methods: {
            showDialog(){
                this.dialogVisible = true;
            },
            getList() {
                let self = this;
                self.listLoading = true;
                request({
                    params: {
                        r: 'plugin/smart_shop/mall/merchant/get-smartshop',
                        page: self.page
                    },
                    method: 'get',
                }).then(e => {
                    if(e.data.code == 0){
                        self.listLoading = false;
                        self.list = e.data.data.list;
                        self.pageCount = e.data.data.pagination.page_count;
                    }else{
                        self.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    console.log(e);
                    self.listLoading = false;
                    self.$message.error("请求失败");
                });
            },
        }
    });
</script>
<style>
    .input-item {
        width: 250px;
        margin: 0 0 20px;
    }
</style>
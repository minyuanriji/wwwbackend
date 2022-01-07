<template id="com-choose-mch">
    <div class="com-choose-mch">
        <el-button type="primary" @click="showDialog" size="big">选择商户</el-button>
        <el-dialog title="选择商户" :visible.sync="dialogVisible" style="width:100%">
            <div class="input-item">
                <el-input style="width: 400px" v-model="search.keyword" placeholder="请输入搜索内容" clearable size="big"
                          @clear="clearSearch"
                          @change="searchList"
                          @input="triggeredChange">
                    <el-select style="width: 130px" slot="prepend" v-model="search.keyword1">
                        <el-option v-for="item in selectList" :key="item.value" :label="item.name" :value="item.value"></el-option>
                    </el-select>
                </el-input>
            </div>
            <el-table v-loading="listLoading" @row-click="chooseRowClick" :data="list" border style="width: 100%">
                <el-table-column label="选择" width="65" align="center">
                    <template slot-scope="scope">
                        &nbsp;&nbsp;
                        <el-radio v-model="radio_id" :label="scope.row.id">&nbsp;</el-radio>
                    </template>
                </el-table-column>
                <el-table-column property="id" label="商户ID" width="150" align="center"></el-table-column>
                <el-table-column label="商户名称" width="350">
                    <template slot-scope="scope">
                        <div flex="cross:center">
                            <com-image width="25" height="25" :src="scope.row.store.cover_url"></com-image>
                            <div style="margin-left: 10px;width: 140px;overflow:hidden;text-overflow: ellipsis;">{{scope.row.store.name}}</div>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column prop="mobile" label="手机" width="150" align="center"></el-table-column>
                <el-table-column label="服务费（%）" prop="transfer_rate" align="center"></el-table-column>
            </el-table>
            <div style="margin-top: 20px;">
                <el-pagination
                        hide-on-single-page
                        @current-change="pagination"
                        background
                        layout="prev, pager, next, jumper"
                        :page-count="pageCount">
                </el-pagination>
            </div>
            <div style="text-align:center;margin-top:30px;">
                <el-button @click="chooseConfirm" type="danger" size="big">确定选择</el-button>
            </div>
        </el-dialog>
    </div>
</template>
<script>
    Vue.component('com-choose-mch', {
        template: '#com-choose-mch',
        props: {

        },
        data() {
            return {
                list: [],
                listLoading: false,
                page: 1,
                pageCount: 0,
                dialogVisible: false,
                search: {
                    keyword: '',
                    keyword1: 'store_name',
                    sort_prop: '',
                    sort_type: '',
                },
                selectList: [
                    {value: 'store_name', name: '店铺名'},
                    {value: 'user_name', name: '用户名'},
                    {value: 'mch_id', name: '商户ID'},
                    {value: 'mobile', name: '联系人手机号'},
                ],
                radio_id: '',
                row: ''
            };
        },
        created() {},
        watch: {},
        methods: {
            chooseConfirm(){
                if(!this.row){
                    this.$message.error("请选择一个商户");
                    return;
                }
                this.$emit('confirm', this.row);
                this.dialogVisible = false;
            },
            chooseRowClick(row, column, event){
                this.radio_id = row.id;
                this.row = row;
            },
            showDialog(){
                this.dialogVisible = true;
                this.getList();
            },
            clearSearch() {
                this.page = 1;
                this.search.keyword = '';
                this.search.keyword1 = '';
                this.getList();
            },
            triggeredChange (){
                if (this.search.keyword.length>0 && this.search.keyword1.length<=0) {
                    alert('请选择搜索方式');
                    this.search.keyword='';
                }
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
                        r: 'plugin/mch/mall/mch/index',
                        page: self.page,
                        keyword: self.search.keyword,
                        keyword1: self.search.keyword1
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
            searchList() {
                this.page = 1;
                this.getList();
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
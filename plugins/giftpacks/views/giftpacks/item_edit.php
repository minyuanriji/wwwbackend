<div id="item_edit_app" v-cloak>

    <el-dialog width="65%" :title="dailogTitle" :visible.sync="dialogFormVisible">

        <template v-if="formData.goods_id == 0">
            <el-card class="box-card">
                <div slot="header" class="clearfix">
                    <span>选择商品</span>
                </div>
                <el-input @keyup.enter.native="loadGoodsData" size="small" placeholder="店名/商品名称/ID" v-model="search.keyword"
                          clearable @clear="searchGoods" style="width:300px;">
                    <el-button slot="append" icon="el-icon-search" @click="searchGoods"></el-button>
                </el-input>
                <el-table @cell-mouse-enter="hover_in" @cell-mouse-leave="hover_out" :data="list" v-loading="loading" style="width: 100%">
                    <el-table-column width="100" prop="goods_id" label="ID"></el-table-column>
                    <el-table-column label="名称">
                        <template slot-scope="scope">
                            <div flex="box:first">
                                <div style="padding-right: 10px;">
                                    <com-image mode="aspectFill" :src="scope.row.cover_pic"></com-image>
                                </div>
                                <div flex="cross:top cross:center">
                                    <div flex="dir:left">
                                        <el-tooltip class="item" effect="dark" placement="top">
                                            <template slot="content">
                                                <div style="width: 320px;">{{scope.row.goods_name}}</div>
                                            </template>
                                            <com-ellipsis :line="2">{{scope.row.goods_name}}</com-ellipsis>
                                        </el-tooltip>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </el-table-column>
                    <el-table-column prop="store_name" label="门店"></el-table-column>
                    <el-table-column prop="goods_price" label="价格"></el-table-column>
                    <el-table-column>
                        <template slot-scope="scope">
                            <el-button @click="chooseGoods(scope.row)" v-if="hover_row != null && hover_row.goods_id==scope.row.goods_id" type="primary" size="small">选择</el-button>
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
            </el-card>
        </template>

        <el-form v-else :rules="rules" ref="formData" label-width="80px" :model="formData" size="small">

            <el-card class="box-card">
                <div slot="header" class="clearfix">
                    <span>已选商品</span>
                    <!--
                    <el-button @click="chooseGoods(null)" style="float: right; padding: 3px 0" type="text">重新选择</el-button>
                    -->
                </div>
                <el-form-item label="门店">
                    <el-input style="width:350px"  v-model="formData.store_name" :disabled="true"></el-input>
                </el-form-item>
                <el-form-item label="价格">
                    <el-input style="width:150px" v-model="formData.goods_price" :disabled="true"></el-input>
                </el-form-item>
                <el-form-item label="标题" prop="name">
                    <el-input style="width:350px"  v-model="formData.name"></el-input>
                </el-form-item>
                <el-form-item label="封面" prop="cover_pic">
                    <com-attachment :multiple="false" :max="1" v-model="formData.cover_pic">
                        <el-tooltip class="item"
                                    effect="dark"
                                    content="建议尺寸:240 * 240"
                                    placement="top">
                            <el-button size="mini">选择文件</el-button>
                        </el-tooltip>
                    </com-attachment>
                    <com-image mode="aspectFill" width='80px' height='80px' :src="formData.cover_pic"></com-image>
                </el-form-item>
            </el-card>

            <div style="padding:20px 20px;">

                <el-form-item label="最大库存" prop="max_stock">
                    <el-input type="number" style="width:150px" v-model="formData.max_stock"></el-input>
                </el-form-item>

                <el-form-item label="不限次数">
                    <el-switch v-model="no_limit" active-text="是" inactive-text="否"></el-switch>
                </el-form-item>
                <el-form-item label="使用次数" v-if="!no_limit">
                    <el-input type="number" style="width:150px" v-model="formData.usable_times"></el-input>
                </el-form-item>

                <el-form-item label="永久有效">
                    <el-switch v-model="no_expire" active-text="是" inactive-text="否"></el-switch>
                </el-form-item>
                <el-form-item label="到期时间" v-if="!no_expire">
                    <el-date-picker v-model="formData.expired_at" type="date" placeholder="选择日期"></el-date-picker>
                </el-form-item>

            </div>


        </el-form>


        <div v-if="formData.goods_id != 0" slot="footer" class="dialog-footer">
            <el-button @click="dialogFormVisible = false">取 消</el-button>
            <el-button :loading="btnLoading" type="primary" @click="save()">确 定</el-button>
        </div>
    </el-dialog>
</div>
<script>
    const itemEditApp = new Vue({
        el: '#item_edit_app',
        data: {
            hover_row: null,
            dailogTitle: '',
            dialogFormVisible: false,
            btnLoading: false,
            no_limit: false,
            no_expire: false,
            formData: {goods_id:0},
            rules: {
                name: [
                    {required: true, message: '商品名称不能为空', trigger: 'change'}
                ],
                cover_pic: [
                    {required: true, message: '封面不能为空', trigger: 'change'}
                ],
                max_stock: [
                    {required: true, message: '最大库存不能为空', trigger: 'change'}
                ]
            },
            search: {
                keyword: '',
                page: 1,
                sort_prop: '',
                sort_type: '',
            },
            list: [],
            loading: false,
            pagination: null,
            savedCallFn : null
        },
        mounted() {
            this.loadGoodsData();
        },
        computed: {

        },
        methods: {
            hover_in(row, column, cell, event){
                this.hover_row = row;
            },
            hover_out(row, column, cell, event){
                this.hover_row = null;
            },
            pageChange(page) {
                this.search.page = page;
                this.loadGoodsData();
            },
            searchGoods() {
                this.search.page = 1;
                this.loadGoodsData();
            },
            loadGoodsData(){
                this.loading = true;
                let params = {
                    r: 'plugin/giftpacks/mall/giftpacks/goods-list'
                };
                params = Object.assign(params, this.search);
                request({
                    params: params,
                    method: 'get',
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
            chooseGoods(row){
                this.formData['name']        = row != null ? row.goods_name : '';
                this.formData['cover_pic']   = row != null ? row.cover_pic : '';
                this.formData['store_id']    = row != null ? row.store_id : 0;
                this.formData['store_name']  = row != null ? row.store_name : '';
                this.formData['goods_id']    = row != null ? row.goods_id : 0;
                this.formData['goods_price'] = row != null ? row.goods_price : 0;
            },
            show(pack_id, row, fn){
                this.dialogFormVisible = true;
                this.savedCallFn = fn;
                if(row != null){
                    this.dailogTitle = row.name;
                    this.formData = row;
                }else{
                    this.formData = {
                        name         : '',
                        cover_pic    : '',
                        pack_id      : pack_id,
                        store_id     : 0,
                        store_name   : '',
                        goods_price  : 0,
                        goods_id     : 0,
                        expired_at   : '',
                        max_stock    : 0,
                        usable_times : 0
                    };
                    this.dailogTitle = "添加商品";
                }
                this.no_limit = parseInt(this.formData.usable_times) > 0 ? false : true;
                this.no_expire = this.formData.expired_at == '' ? true : false;
            },
            hide(){
                this.dialogFormVisible = false;
            },
            save() {
                this.$refs['formData'].validate((valid) => {
                    let self = this;
                    if (valid) {
                        self.btnLoading = true;
                        self.formData.usable_times = !self.no_limit ? self.formData.usable_times : 0;
                        self.formData.expired_at = !self.no_expire ? self.formData.expired_at : 0;
                        request({
                            params: {
                                r: 'plugin/giftpacks/mall/giftpacks/save-item'
                            },
                            method: 'post',
                            data: self.formData
                        }).then(e => {
                            self.btnLoading = false;
                            if (e.data.code == 0) {
                                self.$message.success(e.data.msg);
                                self.hide();
                                self.savedCallFn();
                            } else {
                                self.$message.error(e.data.msg);
                            }
                        }).catch(e => {
                            self.$message.error(e.data.msg);
                            self.btnLoading = false;
                        });
                    } else {
                        console.log('error submit!!');
                        return false;
                    }
                });
            },
        }
    });
</script>
<style>
    .form-body {
        padding: 10px 20px;
        background-color: #fff;
        margin-bottom: 20px;
    }

    .form-button {
        margin: 0;
    }

    .form-button .el-form-item__content {
        margin-left: 0 !important;
    }

    .button-item {
        padding: 9px 25px;
        margin-bottom: 20px;
    }

    .open-img .el-dialog {
        margin-top: 0 !important;
    }

    .click-img {
        width: 100%;
    }

    .el-input-group__append {
        background-color: #fff
    }
</style>

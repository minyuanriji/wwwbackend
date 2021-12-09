<template id="com-new-order">
    <div class="com-new-order">
        <el-dialog title="订单录入" :visible.sync="dialogVisible" :close-on-click-modal="false" @close="close">
            <template v-if="!formData.ali_id || !formData.ali_type">
                <el-card class="box-card">
                    <div slot="header" class="clearfix">
                        <span>选择联盟</span>
                    </div>
                    <div class="input-item" style="width:150px;">
                        <el-select v-model="search.ali_type" placeholder="联盟类型" size="small" style="margin-right:15px;">
                            <el-option label="淘宝联盟" value="ali"></el-option>
                        </el-select>
                    </div>
                    <div class="input-item">
                        <el-input @keyup.enter.native="loadData" size="small" placeholder="请输入搜索内容" v-model="search.keyword"
                                  clearable @clear="toSearch">
                            <el-button slot="append" icon="el-icon-search" @click="toSearch"></el-button>
                        </el-input>
                    </div>
                    <el-table :data="list" v-loading="loading">
                        <el-table-column property="id" label="ID" width="80"></el-table-column>
                        <el-table-column property="ali_type" label="类型" width="110">
                            <template slot-scope="scope">
                                <span v-if="scope.row.ali_type == 'ali'">淘宝联盟</span>
                                <span v-if="scope.row.ali_type == 'jd'">京东联盟</span>
                            </template>
                        </el-table-column>
                        <el-table-column property="name" label="名称" width="200"></el-table-column>
                        <el-table-column label="操作">
                            <template slot-scope="scope">
                                <el-button @click="chooseIt(scope.row)" type="text" circle size="mini">
                                    <el-tooltip class="item" effect="dark" content="选择" placement="top">
                                        <img src="statics/img/mall/choose.png" alt="">
                                    </el-tooltip>
                                </el-button>
                            </template>
                        </el-table-column>
                    </el-table>
                </el-card>
            </template>
            <template v-else>
                <el-card class="box-card" style="margin-bottom:30px;">
                    <div style="display:flex;align-items: center;justify-content: flex-start">
                        <img style="" src="/web/statics/img/mall/tb.jpg" width="60" height="60"/>
                        <div style="padding-left:10px;">
                            <div v-if="formData.ali_type == 'ali'"><b>淘宝联盟</b></div>
                            <div v-if="formData.ali_type == 'jd'"><b>京东联盟</b></div>
                            <div style="color:gray;">{{formData.ali_name}}</div>
                            <div style="color:gray;">ID:{{formData.ali_id}}</div>
                        </div>
                        <div style="text-align:right;flex-grow:1">
                            <el-button @click="chooseClear" type="text" circle size="mini" >
                                <el-tooltip class="item" effect="dark" content="重新选择" placement="top">
                                    <img src="statics/img/mall/nopass.png" alt="">
                                </el-tooltip>
                            </el-button>
                        </div>
                    </div>
                </el-card>
                <el-form :rules="rules" ref="formData" label-width="15%" :model="formData" size="small">
                    <el-form-item label="用户" prop="user_id">
                        <el-input :disabled="searchUserData.loading" placeholder="请输入私域会员ID查询用户" v-model="searchUserData.ali_user_special_id" style="width:350px;">
                            <el-button @click="searchUser({special_id:searchUserData.ali_user_special_id})" slot="append" icon="el-icon-search"></el-button>
                        </el-input>
                        <div v-if="searchUserData.loading" style="color:gray">查询中...</div>
                        <div v-if="!searchUserData.loading && formData.user_id"  style="margin-top:10px;display:flex;align-items: center">
                            <img :src="searchUserData.avatar_url" alt="" style="width:50px;height:50px;">
                            <div style="padding-left:10px;">
                                <div style="line-height:23px;">{{searchUserData.nickname}}</div>
                                <div style="line-height:23px;">ID:{{formData.user_id}}</div>
                            </div>
                        </div>
                        <div v-if="!searchUserData.loading && searchUserData.is_empty" style="color:#cc3311">无法查询到用户</div>
                    </el-form-item>
                    <el-form-item label="联盟订单号" prop="ali_order_sn">
                        <el-input placeholder="请输入" v-model="formData.ali_order_sn" style="width:350px;"></el-input>
                    </el-form-item>
                    <el-form-item label="订单状态" prop="o_status">
                        <el-radio-group v-model="formData.o_status">
                            <el-radio :label="'paid'">已支付</el-radio>
                            <!--
                            <el-radio :label="'finished'">已结束</el-radio>
                            -->
                        </el-radio-group>
                    </el-form-item>
                    <el-form-item label="实付款" prop="pay_price">
                        <el-input placeholder="请输入" type="number" min="0" v-model="formData.pay_price" style="width:220px;">
                            <template slot="append">元</template>
                        </el-input>
                    </el-form-item>
                    <el-form-item label="付款日期" prop="pay_at">
                        <el-date-picker v-model="formData.pay_at" type="datetime" placeholder="选择日期"></el-date-picker>
                    </el-form-item>
                    <el-form-item label="订单生成日期" prop="created_at">
                        <el-date-picker v-model="formData.created_at" type="datetime" placeholder="选择日期"></el-date-picker>
                    </el-form-item>
                    <el-form-item label="联盟商品信息" required>
                        <el-card shadow="always">
                            <el-form-item prop="ali_item_id" label="编号">
                                <el-input placeholder="请输入" v-model="formData.ali_item_id" style="width:350px;"></el-input>
                            </el-form-item>
                            <el-form-item prop="ali_item_name" label="标题">
                                <el-input placeholder="请输入" v-model="formData.ali_item_name" style="width:350px;"></el-input>
                            </el-form-item>
                            <el-form-item prop="ali_item_pic" label="封面">
                                <el-input placeholder="请输入" v-model="formData.ali_item_pic" style="width:350px;"></el-input>
                            </el-form-item>
                            <el-form-item prop="ali_item_price" label="单价">
                                <el-input placeholder="请输入" type="number" min="0" v-model="formData.ali_item_price" style="width:220px;">
                                    <template slot="append">元</template>
                                </el-input>
                            </el-form-item>
                        </el-card>
                    </el-form-item>
                    <el-form-item label="联盟佣金信息" required>
                        <el-card shadow="always">
                            <el-form-item prop="ali_commission_rate" label="比例">
                                <el-input placeholder="请输入" type="number" min="0" v-model="formData.ali_commission_rate" style="width:220px;">
                                    <template slot="append">%</template>
                                </el-input>
                            </el-form-item>
                            <el-form-item prop="ali_commission_price" label="佣金">
                                <el-input placeholder="请输入" type="number" min="0" v-model="formData.ali_commission_price" style="width:220px;">
                                    <template slot="append">元</template>
                                </el-input>
                            </el-form-item>
                        </el-card>
                    </el-form-item>
                    <el-form-item>
                        <el-button @click="saveOrder" type="primary" size="big" style="margin-top:20px;">确认保存</el-button>
                    </el-form-item>
                </el-form>
            </template>
        </el-dialog>
    </div>
</template>

<script>
    function initFormData(){
        return {
            ali_id: '',
            ali_type: '',
            ali_name: '',
            user_id: '',
            o_status: 'paid',
            pay_price: 0.00,
            pay_at: '',
            created_at: '',
            ali_order_sn: '',
            ali_item_id: '',
            ali_item_name: '',
            ali_item_price: 0.00,
            ali_item_pic: '',
            ali_commission_rate: 0,
            ali_commission_price: 0.00
        };
    }

    Vue.component('com-new-order', {
        template: '#com-new-order',
        props: {
            visible:Boolean
        },
        data() {
            return {
                dialogVisible: false,
                searchUserData: {
                    loading: false,
                    is_empty: false,
                    ali_user_special_id: '',
                    avatar_url: '',
                    nickname: ''
                },
                formData: initFormData(),
                rules: {
                    o_status: [
                        {required: true, message: '请选择订单状态', trigger: 'change'},
                    ],
                    pay_price: [
                        {required: true, message: '实付款不能为空', trigger: 'change'},
                    ],
                    pay_at: [
                        {required: true, message: '付款日期不能为空', trigger: 'change'},
                    ],
                    created_at: [
                        {required: true, message: '订单生成日期不能为空', trigger: 'change'},
                    ],
                    ali_order_sn: [
                        {required: true, message: '联盟订单号不能为空', trigger: 'change'},
                    ],
                    ali_item_id: [
                        {required: true, message: '联盟商品编号不能为空', trigger: 'change'},
                    ],
                    ali_item_name: [
                        {required: true, message: '联盟商品名称不能为空', trigger: 'change'},
                    ],
                    ali_item_price: [
                        {required: true, message: '联盟商品单价不能为空', trigger: 'change'},
                    ],
                    ali_item_pic: [
                        {required: true, message: '联盟商品图片不能为空', trigger: 'change'},
                    ],
                    ali_commission_rate: [
                        {required: true, message: '佣金比例不能为空', trigger: 'change'},
                    ],
                    ali_commission_price: [
                        {required: true, message: '佣金不能为空', trigger: 'change'},
                    ]
                },
                loading: false,
                list: [],
                pagination: null,
                search: {
                    keyword: '',
                    page: 1,
                    ali_type: 'ali',
                    sort_prop: '',
                    sort_type: '',
                },
            };
        },
        created() {
            this.dialogVisible = this.visible;
            this.dialogVisible && this.loadData();
        },
        watch: {
            visible(val, oldVal){
                this.dialogVisible = val;
                this.dialogVisible && this.loadData();
            }
        },
        methods: {
            chooseIt(row){
                this.formData.ali_id   = row.id;
                this.formData.ali_type = row.ali_type;
                this.formData.ali_name = row.name;
            },
            chooseClear(){
                this.formData.ali_id   = '';
                this.formData.ali_type = '';
            },
            //通过淘宝联盟私域会员ID查询本地用户信息
            searchUser(param){
                if(!this.searchUserData.ali_user_special_id){
                    this.$message.error("请输入私域会员ID");
                    return;
                }
                this.formData.user_id = '';
                this.searchUserData.loading = true;
                this.searchUserData.is_empty = false;
                let that = this;
                request({
                    params: {
                        r: 'plugin/taolijin/mall/order/search-user'
                    },
                    method: 'post',
                    data: Object.assign(param, {ali_id:this.formData.ali_id})
                }).then(e => {
                    that.searchUserData.loading = false;
                    if (e.data.code == 0) {
                        let user = e.data.data.user;
                        if(!user){
                            that.searchUserData.is_empty = true;
                        }else{
                            that.searchUserData.avatar_url = user.avatar_url;
                            that.searchUserData.nickname = user.nickname;
                            that.formData.user_id = user.id;
                        }
                    } else {
                        that.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    that.searchUserData.loading = false;
                    that.$message.error(e.data.msg);
                });
            },
            loadData() {
                this.loading = true;
                let params = {
                    r: 'plugin/taolijin/mall/ali/list'
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
            close(){
                this.$emit('close');
            },
            toSearch() {
                this.search.page = 1;
                this.loadData();
            },
            pageChange(page) {
                this.search.page = page;
                this.loadData();
            },
            saveOrder(){
                let that = this;
                this.$refs['formData'].validate((valid) => {
                    if (valid) {
                        request({
                            params: {
                                r: 'plugin/taolijin/mall/order/add'
                            },
                            method: 'post',
                            data: that.formData
                        }).then(e => {
                            if (e.data.code == 0) {

                            } else {
                                that.$message.error(e.data.msg);
                            }
                        }).catch(e => {
                            that.$message.error(e.data.msg);
                        });
                    }
                });
            }
        }
    });
</script>


<template id="com-clerk-send">
    <div class="com-send" >
        <!-- 发货 -->
        <el-dialog  title="补货" :visible.sync="dialogVisible" width="35%" @close="closeDialog">
            <el-table v-loading="orderDetailLoading"  ref="multipleTable" :data="orderDetail" tooltip-effect="dark" style="width: 100%" max-height="250" @selection-change="handleSelectionChange">
                <el-table-column label="图片" width="60">
                    <template slot-scope="scope">
                        <com-image width="30" height="30" :src="scope.row.goods_info.goods_attr.cover_pic"></com-image>
                    </template>
                </el-table-column>
                <el-table-column label="名称" show-overflow-tooltip>
                    <template slot-scope="scope">
                        <span>{{scope.row.goods_info.goods_attr.name}}</span>
                    </template>
                </el-table-column>
                <el-table-column prop="goods_info.goods_attr.number" label="数量" width="80" show-overflow-tooltip></el-table-column>

            </el-table>
            <div v-if="!orderDetailLoading" class="title-box">
                <span class="text">物流信息</span>
            </div>
            <el-form v-if="!orderDetailLoading" label-width="130px" class="sendForm" :model="express" :rules="rules" ref="sendForm">
                <el-form-item label="物流选择">
                    <el-radio @change="resetForm('sendForm')" v-model="express.is_express" label="1">快递</el-radio>
                    <el-radio @change="resetForm('sendForm')" v-model="express.is_express" label="2">其它方式</el-radio>
                </el-form-item>
                <el-form-item label="快递公司" prop="express" v-if="express.is_express == 1">
                    <el-autocomplete size="small" v-model="express.express"
                            @select="getCustomer"
                            :fetch-suggestions="querySearch"
                            placeholder="请选择快递公司"></el-autocomplete>
                </el-form-item>
                <el-form-item label="快递单号" prop="express_no" class="express-no" v-if="express.is_express == 1">
                    <el-input placeholder="请输入快递单号" size="small" v-model.trim="express.express_no"
                              autocomplete="off">
                    </el-input>
                </el-form-item>
                <el-form-item v-if="express.is_express == 2" prop="express_content" label="物流内容">
                    <el-input type="textarea" size="small" v-model="express.express_content"
                              autocomplete="off"></el-input>
                </el-form-item>
                <el-form-item style="text-align: right">
                    <el-button size="small" @click="dialogVisible=false">取 消</el-button>
                    <el-button size="small" type="primary" :loading="sendLoading"
                               @click="send_order(express,'sendForm')">确定</el-button>
                </el-form-item>
            </el-form>
        </el-dialog>
    </div>
</template>

<script>
    Vue.component('com-clerk-send', {
        template: '#com-clerk-send',
        props: {
            isShow: {
                type: Boolean,
                default: false,
            },
            isShowPrint: {
                type: Boolean,
                default: true,
            },
            orderDetailIds: Array
        },
        watch: {
            isShow: function (newVal) {
                if (newVal) {
                    this.openExpress();
                } else {
                    this.dialogVisible = false;
                }
            },
        },
        data() {
            return {
                orderDetailLoading: false,
                dialogVisible: false,
                express: {},
                send_type: 1,
                sendLoading: false,
                submitLoading: false,
                rules: {
                    express: [
                        {required: true, message: '快递公司不能为空', trigger: 'change'},
                    ],
                    express_no: [
                        {required: true, message: '快递单号不能为空', trigger: 'change'},
                        {pattern: /^[0-9a-zA-Z]+$/, message: '仅支持数字与英文字母'}
                    ],
                    express_content: [
                        {required: true, message: '物流内容不能为空', trigger: 'change'},
                    ]
                },
                express_list: [],
                multipleSelection: [],
                orderDetail: [],
                expressSingle: {},
            }
        },
        methods: {
            openExpress(){
                var self = this;
                self.dialogVisible = true;
                self.orderDetailLoading = true;
                self.express = {
                    is_express      : '1',
                    express         : '',
                    express_code    : '',
                    express_no      : '',
                    express_content : '',
                };
                request({
                    params: {
                        r: 'mall/order-clerk/send-detail-list',
                        id: self.orderDetailIds.join(","),
                    }
                }).then(e => {
                    self.orderDetailLoading = false;
                    if (e.data.code === 0) {
                        self.orderDetail = e.data.data.details;
                        var ep = e.data.data.express;
                        if(typeof ep == "object"){
                            self.express = {
                                is_express      : ep.send_type == 1 ? "1" : "2",
                                express         : ep.express,
                                express_code    : ep.express_code,
                                express_no      : ep.express_no,
                                express_content : ep.express_content,
                            };
                        }
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    self.orderDetailLoading = false;
                });
                self.getExpress();
            },
            getExpress() {
                request({
                    params: {
                        r: 'mall/express/express-list'
                    },
                }).then(e => {
                    if (e.data.code === 0) {
                        this.express_list = e.data.data.list;
                        for (let i = 0; i < this.express_list.length; i++) {
                            this.express_list[i].value = this.express_list[i].name
                        }
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    console.log(e);
                });
            },
            querySearch(queryString, cb) {
                var express_list = this.express_list;
                var results = queryString ? express_list.filter(this.createFilter(queryString)) : express_list;
                cb(results);
            },
            createFilter(queryString) {
                return (express_list) => {
                    return (express_list.value.toLowerCase().indexOf(queryString.toLowerCase()) === 0);
                };
            },
            getCustomer(e) {
                this.express.express_code = e.code;
            },
            closeDialog() {
                this.$emit('close')
            },
            handleSelectionChange(val) {
                this.multipleSelection = val;
            },
            selectInit(row, index) {	//已发货或不能发货就禁用
                return true;
            },
            resetForm(formName) {
                this.$refs[formName].clearValidate();
            },

            send_order(e, formName) {
                let self = this
                self.$refs[formName].validate((valid) => {
                    if (valid) {
                        self.sendLoading = true;
                        e.send_type = self.express.is_express == 1 ? 1 : 2;
                        e['details_id'] = self.orderDetailIds.join(",");
                        request({
                            params: {
                                r: 'mall/order-clerk/send',
                            },
                            data: e,
                            method: 'post',
                        }).then(e => {
                            self.sendLoading = false;
                            if (e.data.code === 0) {
                                self.dialogVisible = false;
                                self.$emit('submit');
                                self.$message({
                                    message: e.data.msg,
                                    type: 'success'
                                });
                            } else {
                                self.$message.error(e.data.msg);
                            }
                        }).catch(e => {
                            self.sendLoading = false;
                        });
                    }
                });
            },
        },
        created() {

        },
    })
</script>

<style>
    .com-send .title-box {
        margin: 15px 0;
    }

    .com-send .title-box .text {
        background-color: #FEFAEF;
        color: #E6A23C;
        padding: 6px;
    }

    .com-send .get-print {
        width: 100%;
        height: 100%;
    }

    .com-send .el-table__header-wrapper th {
        background-color: #f5f7fa;
    }

    .com-send .el-dialog__body {
        padding: 5px 20px 10px;
    }
</style>



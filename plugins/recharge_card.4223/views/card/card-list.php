<!-- '金豆券充值卡列表' -->
<?php
Yii::$app->loadPluginComponentView('card-batch');
?>

<div id="app" v-cloak>
    <el-container>
        <el-card class="box-card" shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        
            <div slot="header">
                <div>
                    <span>金豆券充值卡列表</span>
                    <!-- <com-export-dialog style="float: right;margin-top: -5px" :field_list='exportList' :params="searchData"
                        @selected="exportConfirm">
                    </com-export-dialog> -->
                    <div style="float: right; margin: -5px 0">
                        <el-button type="primary" @click="exportConfirm" size="small">导出文件</el-button>
                    </div>
                </div>
            </div>
            <div class="table-body">
                <el-select  v-model="status" @change='search' class="select" size="small">
                    <el-option
                        v-for="state in StatusOptions"
                        :key="state.key"
                        :value="state.key"
                        :label="state.display_name"
                    />
                </el-select>
                <div class="batch">
                    <card-batch :choose-list="choose_list" :card-id="card_id" @to-search="getList"></card-batch>
                </div>
				
                <el-tabs @tab-click="handleClick">
                    <el-table
                            v-loading="listLoading"
                            @selection-change="handleSelectionChange"
                            :data="list"
                            border
                            style="width: 100%">
                        <el-table-column fixed align='center' type="selection" width="60"></el-table-column>
                        <el-table-column prop="status" label="状态">
                            <template slot-scope="scope">
                                <div v-if="scope.row.status==0" size="small">未充值</div>
                                <div v-if="scope.row.status==1" size="small">已充值</div>
                                <div v-if="scope.row.status==2" size="small">已过期</div>
                                <div v-if="scope.row.status==-1" size="small">已禁用</div>
                            </template>
                        </el-table-column>
                        <el-table-column prop="serialize_no" label="卡号(序列号)" width="150"></el-table-column>
                        <el-table-column prop="use_code" label="兑换码" width="120"></el-table-column>
                        <el-table-column label="发卡人" prop="user.nickname">
                            <!-- <template slot-scope="scope">
                                <div size="small">{{scope.row.integral_setting.expire==-1?'永久有效':'限时有效'}}</div>
                            </template> -->
                        </el-table-column>
                        <el-table-column label="领取人" prop="picker.nickname">
                        <!-- <template slot-scope="scope">
                                <div size="small">{{scope.row.picker.nickname}}</div>
                            </template> -->
                        </el-table-column>
                        <el-table-column label="金豆券类型" prop="integral_setting" width="100">
                            <template slot-scope="scope">
                                <div size="small">{{scope.row.integral_setting.expire==-1?'永久有效':'限时有效'}}</div>
                            </template>
                        </el-table-column>
                        <el-table-column label="卡券面值" prop="integral_setting">
                            <template slot-scope="scope">
                                <div size="small">{{scope.row.integral_setting.integral_num}}</div>
                            </template>
                        </el-table-column>
                        <el-table-column label="发放周期" prop="integral_setting">
                            <template slot-scope="scope">
                                <div size="small">{{scope.row.integral_setting.period}}{{scope.row.integral_setting.period_unit=="month"?'月':'周'}}</div>
                            </template>
                        </el-table-column>
                        <el-table-column label="过期天数" prop="integral_setting">
                            <template slot-scope="scope">
                                <div size="small" v-if="scope.row.integral_setting.expire!=-1">{{scope.row.integral_setting.expire}}天</div>
                            </template>
                        </el-table-column>
                        <el-table-column prop="expire_time" label="有效期" width="180">
                            <template slot-scope="scope">
                                <div size="small">{{scope.row.expire_time|formatDate}}</div>
                            </template>
                        </el-table-column>
                        <!-- <el-table-column prop="generate_num" label="生成张数"></el-table-column> -->
                        <!-- <el-table-column prop="use_num" label="领取张数"></el-table-column> -->
                        <el-table-column prop="fee" label="手续费">
                            <template slot-scope="scope">
                                <div size="small">{{scope.row.fee}}元</div>
                            </template>
                        </el-table-column>
                        <el-table-column prop="updated_at" label="生成时间" width="180">
                            <template slot-scope="scope">
                                <div size="small">{{scope.row.updated_at|formatDate}}</div>
                            </template>
                        </el-table-column>
                        
                        <el-table-column
                            prop="qr_code_base64,serialize_no"
                            label="二维码">
                            <template slot-scope="scope">
                                <el-popover
                                    placement="right"
                                    :title="'卡号(序列号):'+scope.row.serialize_no"
                                    trigger="hover">
                                    <img :src="scope.row.qr_code_base64" style="max-width:150px"/>
                                    <img slot="reference" :src="scope.row.qr_code_base64" style="max-height:32px;max-width: 100px">
                                </el-popover>
                            </template>
                        </el-table-column>
                        
                        
                        <el-table-column fixed="right" label="操作" width="182">
                        <template slot-scope="scope" >
                                <el-tooltip class="item" effect="dark" content="复制卡号与兑换码" placement="top">
                                    <el-button id="copy_btn" data-clipboard-action="copy" :data-clipboard-text="'卡号：'+scope.row.serialize_no+' 兑换码：'+scope.row.use_code" size="mini">
                                        <i class="el-icon-document-copy"></i>
                                    </el-button>
                                </el-tooltip>
                                <el-tooltip class="item" effect="dark" content="删除" placement="top">
                                    <el-button @click="switchStatus(scope.row.id,'delete',scope.$index)" size="mini">
                                        <i class="el-icon-document-delete"></i>
                                    </el-button>
                                </el-tooltip>
                                <el-tooltip class="item" effect="dark" content="立即禁用" placement="top" v-if="scope.row.status==0">
                                    <el-button @click="switchStatus(scope.row.id,'forbidden',scope.$index)" size="mini" >
                                        <i class="el-icon-unlock"></i>
                                    </el-button>
                                </el-tooltip>
                                <el-tooltip class="item" effect="dark" content="取消禁用" placement="top" v-else="scope.row.status==-1">
                                    <el-button @click="switchStatus(scope.row.id,'forbidden',scope.$index)" size="mini" type="primary">
                                        <i class="el-icon-lock"></i>
                                    </el-button>
                                </el-tooltip>
                            </template>
                        </el-table-column>
                        
                    </el-table>
                </el-tabs>
                
                <div style="text-align: right;margin: 20px 0;">
                    <el-pagination
                            @current-change="pagination"
                            background
                            layout="prev, pager, next"
                            :page-count="pageCount">
                    </el-pagination>
                </div>
            </div>
        </el-card>
    </div>
</el-container>
<script src="https://cdn.jsdelivr.net/clipboard.js/1.5.12/clipboard.min.js"></script>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                card_id : '',   //卡模板ID
                status : 999,
                list: [],
                keyword: '',
                listLoading: false,
                page: 1,
                pageCount: 0,
                StatusOptions:[
                    { key: 999, display_name: '使用状态',value:999 },
                    { key: 0, display_name: '未充值',value:0 },
                    { key: 1, display_name: '已充值',value:1 },
                    { key: 2, display_name: '已过期',value:2 },
                    { key: -1, display_name: '已禁用',value:-1 },
                ],
				choose_list: [],
            };
        },
        filters: {
          // 秒级时间戳转标准时间格式
          formatDate: function (value) {
            let date = new Date(value*1000);
            let y = date.getFullYear();
            let MM = date.getMonth() + 1;
            MM = MM < 10 ? ('0' + MM) : MM;
            let d = date.getDate();
            d = d < 10 ? ('0' + d) : d;
            let h = date.getHours();
            h = h < 10 ? ('0' + h) : h;
            let m = date.getMinutes();
            m = m < 10 ? ('0' + m) : m;
            let s = date.getSeconds();
            s = s < 10 ? ('0' + s) : s;
            return y + '-' + MM + '-' + d + ' ' + h + ':' + m + ':' + s;
          }
        },
        
        
        methods: {
            search() {
                this.page = 1;
                this.getList();
            },

            pagination(currentPage) {
                let self = this;
                self.page = currentPage;
                self.getList();
            },
            // 0.1 获取卡券列表--这里要多加一个status
            getList() {
                let self = this;
                self.listLoading = true;
                request({
                    params: {
                        r: 'plugin/recharge_card/admin/card/card-list',
                        page: self.page,
                        status: this.status,
                        card_id : getQuery('id')
                    },
                    method: 'get',
                }).then(e => {
                    self.listLoading = false;
                    self.list = e.data.data.list;
                    self.pageCount = e.data.data.page_count;
                }).catch(e => {
                    console.log(e);
                });
            },
            // 导出文件
            exportConfirm(){
                navigateTo({
                    r: '/plugin/recharge_card/admin/card/export-card-info',
                    card_id: this.card_id,
                });
            },
            
            // 开关切换是否禁用&删除
            switchStatus(id,option,index) {
                console.log('id:'+id);
                console.log('index:'+index);
                // return
                let self = this;
                let form = {
                    card_detail_id : id,
                    option : option,
                };
                self.listLoading = true;
                request({
                    params: {
                        r: 'plugin/recharge_card/admin/card/card-status',
                    },
                    method: 'post',
                    data: {
                        form: form,
                    }
                }).then(e => {
                    self.listLoading = false;
                    if (e.data.code === 0) {
                        self.$message.success(e.data.msg);
                    } else {
                        self.$message.error(e.data.msg);
                    }
                    self.getList();
                }).catch(e => {
                    console.log(e);
                });
            },
            handleClick(tab, event) {
                this.search.page = 1;
                this.getList()
            },
            handleSelectionChange(val) {
                let self = this;
                self.choose_list = [];
                val.forEach(function (item) {
                    self.choose_list.push(item.id);
                })
               
            },
        },
        mounted: function () {
            if(getQuery('id')){
                this.card_id = getQuery('id');
                let id = getQuery('id');
                console.log('id:'+id);
                
                this.getList();
            }
        },
        created() {
            // this.status = this.StatusOptions[0].display_name
        }
    });
    var clipboard = new Clipboard('#copy_btn');

    var self = this;
    clipboard.on('success', function (e) {
        self.ELEMENT.Message.success('复制成功');
        e.clearSelection();
    });
    clipboard.on('error', function (e) {
        self.ELEMENT.Message.success('复制失败，请手动复制');
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
        margin: 0 0 20px;
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
</style>
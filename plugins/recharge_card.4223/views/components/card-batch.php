<style>
    .card-batch {
    }

    .card-batch .batch-box {
        margin-left: 10px;
    }

    .card-batch .batch-remark {
        margin-top: 5px;
        color: #999999;
        font-size: 14px;
    }

    .card-batch .select-count {
        font-size: 14px;
        margin-left: 10px;
    }

    .card-batch .batch-title {
        font-size: 18px;
    }

    .card-batch .batch-box-left {
        width: 150px;
        border-right: 1px solid #e2e2e2;
        padding: 0 20px;
    }

    .card-batch .batch-box-left div {
        padding: 5px 0;
        margin: 5px 0;
        cursor: pointer;
        -webkit-border-radius: 5px;
        -moz-border-radius: 5px;
        border-radius: 5px;
    }

    .card-batch .batch-div-active {
        background-color: #e2e2e2;
    }

    .card-batch .el-dialog__body {
        padding: 15px 20px;
    }

    .card-batch .batch-box-right {
        padding: 5px 20px;
    }

    .card-batch .express-dialog .el-dialog {
        min-width: 250px;
    }
    .batch-box-right .el-form-item{
        margin-bottom: 0;
    }
</style>


<template id="card-batch">
    <div class="card-batch" flex="dir:left cross:center">
        <el-button type="primary" size="small" @click="batchSetting" style="padding: 9px 15px !important;" v-loading.fullscreen.lock="dialogLoad">批量设置</el-button>
        <el-dialog
            :visible.sync="dialogVisible"
            width="50%">
            <div slot="title">
                <div flex="dir:left">
                    <div class="batch-title">批量修改</div>
                    <div flex="cross:center" class="select-count">{{dialogTitle}}</div>
                </div>
                <div class="batch-remark">注：变更归属人，请谨慎操作。</div>
            </div>
            <div flex="dir:left box:first">
                <div class="batch-box-left" flex="dir:top">
                    <div v-for="(item, index) in baseBatchList"
                         :key='item.key'
                         :class="{'batch-div-active': currentBatch === item.key ? true : false}"
                         @click="tabClick(item.key)"
                         flex="main:center">
                        {{item.name}}
                    </div>
                </div>
                <div class="batch-box-right">
                    <el-form>
                        <el-form-item hidden>
                            <el-input></el-input>
                        </el-form-item>
                        <template>
                            <el-form-item v-if="currentBatch == 'copy'">
                                <el-input v-model="batch_card_name" size="small" style="width:80%;" placeholder="请输入卡券名称">
                                    <i class="el-icon-s-ticket el-input__icon" slot="suffix"></i>
                                </el-input>
                            </el-form-item>
                            <el-form-item v-if="currentBatch == 'copy'">
                                <el-autocomplete size="small" v-model="batch_nickname" value-key="batch_nickname" :fetch-suggestions="querySearchAsync" placeholder="请输入代理商昵称/用户账号/手机号" @select="inviterClick" style="width:80%;">
                                    <i class="el-icon-user el-input__icon" slot="suffix"></i>
                                </el-autocomplete>
                            </el-form-item>
                            <el-form-item v-if="currentBatch == 'have'" v-loading="isHavaList">
                                <el-radio-group v-model="batch_card_id" size="small" @change="toggleRadio">
                                  <el-radio border v-for="(item, index) in havaList" :label="item.id" >{{item.name}} - {{item.user.nickname}}</el-radio>
                                  <span v-if="havaList.length==0" style="font-size:12px;">暂无卡券，请选择“拷贝当前卡券”选择新的归属人</span>
                                </el-radio-group>
                            </el-form-item>
                        </template>
                    </el-form>
                </div>
            </div>
            <div slot="footer">
                <el-button size="small" @click="dialogVisible=false;batch_user_id='';batch_nickname=''">取 消</el-button>
                <el-button size="small" :loading="btnLoading" type="primary" @click="dialogSubmit">确 定
                </el-button>
            </div>
        </el-dialog>
    </div>
</template>

<script>
    Vue.component('card-batch', {
        template: '#card-batch',
        props: {
            // 列表选中的数据
            chooseList: {
                type: Array,
                default: function () {
                    return [];
                }
            },
            cardId: {
                type:String ,
                default: function () {
                    return '';
                }
            },
            batchChangeAgentUrl: {
                type: String,
                default: 'plugin/recharge_card/admin/card/change-agent',
            },
            distributionLevelList: Array,
        },
        data() {
            return {
                isAllChecked: false,
                btnLoading: false,
                dialogVisible: false,
                dialogLoad:false,
                currentBatch: 'copy',
                dialogTitle: '',
                cardInfo:[],
                newBatchList: [],
                baseBatchList: [
                    {
                        name: '拷贝当前卡券',
                        key: 'copy',// 唯一
                    },
                    {
                        name: '选择已有卡券',
                        key: 'have',// 唯一
                    },
                ],
                level: 0,
                batch_new_user_id:'',
                batch_user_id: '',
                batch_nickname:'',
                batch_card_name:'',
                havaList:[],
                isHavaList:false,
                batch_card_id:''
            }
        },
        created(){
                 console.log(this.catdId);
        },
        methods: {
            // 打开批量设置框
            batchSetting() {
                let self = this;
                if (!self.checkChooseList()) {
                    return false;
                }
                self.getCartDetail();
                
            },
            checkChooseList() {
                if (this.isAllChecked) {
                    this.dialogTitle = '已选所有充值卡';
                    return true;
                }
                if (this.chooseList.length > 0) {
                    this.dialogTitle = '已选充值卡' + this.chooseList.length + '个';
                    return true;
                }
                this.$message.warning('请先勾选要设置的充值卡');
                return false;
            },
            batchAction(data) {
                let self = this;
                self.$confirm(data.content, '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    self.btnLoading = true
                    request({
                        params: {
                            r: data.url
                        },
                        data: data.params,
                        method: 'post'
                    }).then(e => {
                        self.btnLoading = false;
                        if (e.data.code === 0) {
                            self.dialogVisible = false;
                            self.$message.success(e.data.msg);
                            self.getList();
                        } else {
                            self.$message.error(e.data.msg);
                        }
                        this.batch_new_user_id = ''
                        this.batch_nickname = ''
                    }).catch(e => {
                        self.$message.error(e.data.msg);
                        self.btnLoading = false;
                    });
                }).catch(() => {
                });
            },
            getCartDetail(){
                let self = this;
                self.dialogLoad = true
                request({
                    params: {
                        r: 'plugin/recharge_card/admin/card/edit',
                        id: self.cardId
                    },
                }).then(e => {
                    if (e.data.code === 0) {
                        let cardInfo = e.data.data.info;
                        if(cardInfo){
                            console.log(cardInfo)
                            self.batch_card_name = cardInfo.name
                            self.cardInfo = cardInfo
                            self.dialogVisible = true;
                            self.dialogLoad = false
                        }
                    } else {
                        self.$message.error(e.data.msg);
                    }
                }).catch(e => {
                });
            },
            tabClick(curr){
                this.currentBatch = curr;
                if(curr == 'have'){
                    this.searchCart()
                }
            },
            toggleRadio(e){
                console.log(e)
                //this.batch_card_id = e;
            },
            getList() {
                this.isAllChecked = false;
                this.$emit('to-search')
            },
            dialogSubmit() {
                let self = this;
                
                let params = {
                    batch_ids: self.chooseList,
                    user_id: self.batch_user_id||'',
                    new_user_id: self.batch_new_user_id||'',
                    card_id: self.batch_card_id||'',
                    current_batch: self.currentBatch || '',
                    card_name: self.batch_card_name || '',
                };
                this.batchAction({
                    url: self.batchChangeAgentUrl,
                    content: '批量变更归属人,是否继续',
                    params: params
                });
                
            },
            //0.1 模糊搜索代理商
            querySearchAsync(queryString, cb) {
                this.keyword = queryString;
                this.searchUser(cb);
            },
            // 0.2 模糊搜索代理商的请求
            searchUser(cb) {
                request({
                    params: {
                        r: 'plugin/recharge_card/admin/card/find-agent',
                        keyword: this.keyword
                    },
                }).then(e => {
                    if (e.data.code === 0) {
                        let list = e.data.data.list;
                        if(list.length>0){
                            list.forEach(function (item, index) {
                                item['batch_nickname']=item.nickname;
                                delete item.user
                            });
                        }
                        cb(e.data.data.list);
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {
                });
            },
            inviterClick(row) {
                this.batch_new_user_id = row.id;
                this.batch_card_id = this.cardInfo.id
                console.log(this.batch_card_name);
                console.log(this.batch_new_user_id);
                console.log(this.batch_user_id);
                console.log(this.batch_nickname);
            },
            // 1.0 模糊搜索卡券
            searchCart() {
                this.isHavaList = true
                request({
                    params: {
                        r: 'plugin/recharge_card/admin/card/find-card',
                    },
                    data: {integral_setting: this.cardInfo.integral_setting,fee:this.cardInfo.fee,expire_time:this.cardInfo.expire_time,id:this.cardInfo.id},
                    method: 'post'
                }).then(e => {
                    if (e.data.code === 0) {
                        let list = e.data.data;
                        if(list.length>0){
                            this.havaList = list
                        }
                        this.isHavaList = false
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {
                });
            },
        },
    })
</script>

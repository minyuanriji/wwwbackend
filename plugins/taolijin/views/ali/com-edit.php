<template id="com-edit">
    <div class="com-edit">
        <div id="iframe_auth_con" v-if="showAuthFrame" style="z-index:9999;position:fixed;left:0px;top:0px;width:100%;height:100%;">
            <div style="width:100%;height:100%;position: relative;">
                <div style="position:absolute;left:0px;top:0px;filter:alpha(Opacity=80);-moz-opacity:0.5;opacity: 0.5;background:white;"></div>
                <iframe src="" style="border:none;background:white;position:absolute;left:3%;top:3%;width:94%;height:94%;"></iframe>
            </div>
        </div>
        <el-dialog :title="dialogTitle" :visible.sync="dialogVisible" :close-on-click-modal="false" @close="close">

            <el-tabs v-model="activeName">
                <el-tab-pane label="基本信息" name="basic">
                    <el-form :rules="rules" ref="formData" label-width="15%" :model="formData" size="small">
                        <el-form-item label="联盟类型" prop="ali_type">
                            <el-select v-model="formData.ali_type" placeholder="请选择" style="width:200px;">
                                <el-option label="淘宝联盟" value="ali"></el-option>
                            </el-select>
                        </el-form-item>
                        <el-form-item label="备注" prop="remark">
                            <el-input type="textarea" :rows="2" placeholder="备注内容" v-model="formData.remark" style="width:300px;"></el-input>
                        </el-form-item>
                        <el-form-item label="排序" prop="sort">
                            <el-input v-model="formData.sort" type="number" min="0" placeholder="排序" style="width:200px;"></el-input>
                        </el-form-item>
                        <el-form-item label="是否启用" prop="is_open">
                            <el-switch v-model="formData.is_open" :active-value="1" :inactive-value="0"></el-switch>
                        </el-form-item>
                        <el-form-item label="自定义参数">
                            <el-table :data="customParamList"  border style="width: 100%">
                                <el-table-column label="名称" width="180" align="center">
                                    <template slot-scope="scope">
                                        <el-input v-model="scope.row.name" type="text"></el-input>
                                    </template>
                                </el-table-column>
                                <el-table-column label="内容" align="center">
                                    <template slot-scope="scope">
                                        <el-input v-model="scope.row.value" ></el-input>
                                    </template>
                                </el-table-column>
                                <el-table-column label="操作" align="center" width="80" >
                                    <template slot-scope="scope">
                                        <el-button @click="removeCustomParam(scope.row)" type="text" circle size="mini">
                                            <el-tooltip class="item" effect="dark" content="删除" placement="top">
                                                <img src="statics/img/mall/del.png" alt="">
                                            </el-tooltip>
                                        </el-button>
                                    </template>
                                </el-table-column>
                            </el-table>
                            <div style="padding-top:20px;">
                                <el-link @click="newCustomParam" icon="el-icon-plus" type="primary">新增自定义参数</el-link>
                            </div>
                        </el-form-item>
                        <!--
                        <el-form-item label="APP KEY" >
                            <el-input v-model="formData.settings_data.app_key" placeholder="" style="width:300px;"></el-input>
                        </el-form-item>
                        <el-form-item label="SECRET KEY" >
                            <el-input v-model="formData.settings_data.secret_key" placeholder="" style="width:300px;"></el-input>
                        </el-form-item>
                        <el-form-item label="妈妈广告位ID" >
                            <el-input v-model="formData.settings_data.adzone_id" placeholder="" style="width:300px;"></el-input>
                        </el-form-item>
                        -->

                    </el-form>

                    <div slot="footer" class="dialog-footer">
                        <el-button @click="close">取 消</el-button>
                        <el-button :loading="btnLoading" type="primary" @click="save">确 定</el-button>
                    </div>
                </el-tab-pane>
                <el-tab-pane v-if="formData.id && formData.ali_type == 'ali'" label="邀请码管理" name="ali_invitecode">

                    <el-card class="box-card">
                        <div slot="header" class="clearfix">
                            <span>邀请码管理</span>
                            <el-button @click="newInviteCode" icon="el-icon-plus" style="float: right; padding: 3px 0;color:red" type="text">
                                新增邀请码
                            </el-button>
                        </div>
                        <el-table :data="aliCodeList" border style="width: 100%">
                            <el-table-column prop="date" label="日期" width="110">
                                <template slot-scope="scope">
                                    {{scope.row.created_at|dateTimeFormat('Y-m-d')}}
                                </template>
                            </el-table-column>
                            <el-table-column label="UID" prop="open_uid"></el-table-column>
                            <el-table-column label="邀请码" prop="code"></el-table-column>
                            <el-table-column label="操作" width="110">
                                <template slot-scope="scope">
                                    <el-button @click="delInviteCode(scope.row)" type="text" circle size="mini">
                                        <el-tooltip class="item" effect="dark" content="删除" placement="top">
                                            <img src="statics/img/mall/del.png" alt="">
                                        </el-tooltip>
                                    </el-button>
                                </template>
                            </el-table-column>
                        </el-table>
                    </el-card>

                </el-tab-pane>
            </el-tabs>

        </el-dialog>
    </div>
</template>

<script>
    function setAuthIframeHeight(){
        var iframe = document.getElementById('iframe_auth_con');
        console.log(iframe, window.height);
        //iframe.height = window.height;
    }
    function initFormData(){
        return {
            id: 0,
            ali_type: "ali",
            remark: "",
            settings_data: {
                app_key:'',
                secret_key: '',
                adzone_id: ''
            },
            is_open: 0,
            sort: 0
        };
    }

    Vue.component('com-edit', {
        template: '#com-edit',
        props: {
            visible: Boolean,
            record: Object
        },
        data() {
            return {
                dialogTitle: "添加账号",
                activeName: "basic",
                dialogVisible: false,
                formData: initFormData(),
                customParamList: [],
                rules: {
                    ali_type: [
                        {required: true, message: '联盟类型不能为空', trigger: 'change'},
                    ],
                    remark: [
                        {required: true, message: '备注不能为空', trigger: 'change'},
                    ],
                    sort: [
                        {required: true, message: '排序不能为空', trigger: 'change'},
                    ],
                },
                btnLoading: false,
                aliCodeList: [],
                showAuthFrame: false
            };
        },
        created() {},
        watch: {
            visible(val, oldVal){
                this.dialogVisible = val;
            },
            record(val, oldVal){
                this.formData = Object.assign(initFormData(), val);
                let key, param;
                this.customParamList = [];
                for(key in this.formData.settings_data){
                    param = {
                        name: key,
                        value: this.formData.settings_data[key]
                    };
                    this.customParamList.push(param);
                }
                if(typeof this.formData['id'] == "undefined" || parseInt(this.formData['id']) <= 0){
                    this.dialogTitle = "添加账号";
                }else{
                    this.dialogTitle = "编辑账号";
                    this.getCode();
                    if(getQuery("act") == "getInviteCode"){
                        this.activeName = "ali_invitecode";
                    }
                }
            }
        },
        methods: {
            removeCustomParam(param){
                let i, newParamList = [];
                for(i=0; i < this.customParamList.length; i++){
                    if(this.customParamList[i].name != param.name){
                        newParamList.push(this.customParamList[i]);
                    }
                }
                this.customParamList = newParamList;
            },
            newCustomParam(){
                this.customParamList.push({
                    name: "",
                    value: ""
                });
            },
            save(){
                let that = this;
                this.$refs['formData'].validate((valid) => {
                    if (valid) {
                        let i, name, newSettingsData = {};
                        for(i=0; i < that.customParamList.length; i++){
                            name = that.customParamList[i].name.trim();
                            if(name.length <= 0 || typeof newSettingsData[name] != "undefined")
                                continue;
                            newSettingsData[name] = that.customParamList[i].value.trim();
                        }
                        that.formData.settings_data = newSettingsData;
                        that.btnLoading = true;
                        request({
                            params: {
                                r: 'plugin/taolijin/mall/ali/edit'
                            },
                            method: 'post',
                            data: that.formData
                        }).then(e => {
                            that.btnLoading = false;
                            if (e.data.code == 0) {
                                that.$message.success(e.data.msg);
                                that.update();
                            } else {
                                that.$message.error(e.data.msg);
                            }
                        }).catch(e => {
                            that.$message.error(e.data.msg);
                            that.btnLoading = false;
                        });
                    }
                });
            },
            getCode() {
                let params = {
                    r: 'plugin/taolijin/mall/ali/invite-code-list',
                    ali_id: this.formData.id
                };
                request({
                    params: params,
                    method: 'get',
                }).then(e => {
                    if (e.data.code == 0) {
                        this.aliCodeList = e.data.data.list;
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {

                });
            },
            newInviteCode(){
                let doUrl = "<?php echo \Yii::$app->getRequest()->getUrl();?>&ali_id="+this.formData.id+"&act=getInviteCode";
                location.href = "?r=plugin/taolijin/auth/ali-auth&ali_id="+this.formData.id+"&do_url="+encodeURIComponent(doUrl);
                /*this.showAuthFrame = true;
                setTimeout(function(){
                    var iframe = document.getElementById('iframe_auth_con');
                    iframe.src = "https://www.taobao.com?rnd=" + Math.ceil(Math.random() * 100);
                }, 500);*/
            },
            delInviteCode(row){
                let that = this;
                that.$confirm('删除该条数据, 是否继续?', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    that.loading = true;
                    request({
                        params: {
                            r: 'plugin/taolijin/mall/ali/delete-invite-code'
                        },
                        method: 'post',
                        data: {id:row.id}
                    }).then(e => {
                        that.loading = false;
                        if (e.data.code == 0) {
                            that.getCode();
                        } else {
                            that.$message.error(e.data.msg);
                        }
                    }).catch(e => {

                    });
                });
            },
            close(){
                this.$emit('close');
            },
            update(){
                this.$emit('update');
            }
        }
    });
</script>
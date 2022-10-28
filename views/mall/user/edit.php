<?php defined('YII_ENV') or exit('Access Denied'); ?>
<style>
    .form-body {
        background-color: #fff;
        padding: 20px 50% 20px 0;
    }

    .button-item {
        margin-top: 12px;
        padding: 9px 25px;
    }

    .tip {
        margin-left: 10px;
        display: inline-block;
        height: 30px;
        line-height: 30px;
        color: #ff4544;
        background-color: #FEF0F0;
        padding: 0 20px;
        border-radius: 5px;
    }
</style>
<section id="app" v-cloak>
    <el-card class="box-card" style="border:0" shadow="never" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header" class="clearfix">
            <el-breadcrumb separator="/">
                <el-breadcrumb-item><span style="color: #409EFF;cursor: pointer"
                                          @click="$navigate({r:'mall/user/index'})">用户管理</span></el-breadcrumb-item>
                <el-breadcrumb-item>用户编辑</el-breadcrumb-item>
            </el-breadcrumb>
            <span></span>
        </div>
        <div class="form-body">
            <el-form :model="form" label-width="120px" :rules="FormRules" ref="form" v-loading="listLoading">
                <el-form-item label="用户">
                    <com-image width="80px" height="80px" mode="aspectFill" :src="form.avatar"></com-image>
                </el-form-item>
                <!--<el-form-item label="会员等级" prop="member_level">
                    <el-select size="small" v-model="form.member_level" placeholder="请选择会员等级">
                        <el-option key="VIP会员" label="VIP会员" :value="-1"></el-option>
                        <el-option
                                v-for="item in mall_members"
                                :key="item.name"
                                :label="item.name"
                                :value="item.level">
                        </el-option>
                    </el-select>
                </el-form-item>-->
                <el-form-item label="会员类型" prop="role_type">
                    <el-select @change="roleTypeChange" size="small" v-model="form.role_type" placeholder="请选择会员类型">
                        <el-option key="user" label="VIP会员" value="user"></el-option>
                        <el-option key="branch_office" label="城市服务商" value="branch_office"></el-option>
                        <el-option key="partner" label="区域服务商" value="partner"></el-option>
                        <el-option key="store" label="VIP代理商" value="store"></el-option>
                    </el-select>
                </el-form-item>

                <el-form-item label="自定义类型显示">
                    <el-input size="small" v-model="form.role_type_label" autocomplete="off" style="width:215px;"></el-input>
                </el-form-item>

                <el-form-item label="上级推荐人" prop="parent_id">
                    <el-autocomplete size="small" v-model="form.parent_name" value-key="nickname"
                                     :fetch-suggestions="querySearchAsync" placeholder="请输入搜索内容"
                                     @select="inviterClick"></el-autocomplete>
                </el-form-item>

                <el-form-item label="锁定上下级关系" prop="is_lianc">
                    <el-switch v-model="form.lock_parent" :active-value="1" :inactive-value="0"></el-switch>
                    <span class="tip">开启可锁定上下级不变</span>
                </el-form-item>

                <el-form-item label="联创区域服务商" prop="is_lianc">
                    <el-switch v-model="form.is_lianc" :active-value="1" :inactive-value="0"></el-switch>
                    <span class="tip">开启可设置商品归属</span>
                </el-form-item>

                <!-- --- -->
                <el-form-item label="加入黑名单" prop="is_blacklist">
                    <el-switch v-model="form.is_blacklist" :active-value="1" :inactive-value="0"></el-switch>
                    <span class="tip">加入黑名单后，用户将无法下单</span>
                </el-form-item>

                <el-form-item label="推广资格" prop="is_inviter">
                    <el-switch v-model="form.is_inviter" :active-value="1" :inactive-value="0"></el-switch>
                    <span class="tip">开启推广资格之后用户拥有绑定下级的权力</span>
                </el-form-item>

                <el-form-item label="审核店铺资格" prop="is_examine">
                    <el-switch v-model="form.is_examine" :active-value="1" :inactive-value="0"></el-switch>
                    <span class="tip">开启审核店铺资格之后用户拥有审核下级门店的权力</span>
                </el-form-item>

                <el-form-item v-if="form.share" label="累计佣金" prop="total_balance">
                    <div>{{form.share.total_balance}}</div>
                </el-form-item>
                <el-form-item v-if="form.share" label="可提现佣金" prop="money">
                    <el-input size="small" v-model="form.money" autocomplete="off"></el-input>
                </el-form-item>
                <el-form-item label="联系方式" prop="contact_way">
                    <el-input size="small" v-model="form.contact_way" autocomplete="off"></el-input>
                </el-form-item>
                <el-form-item label="手机号" prop="mobile">
                    <el-input disabled size="small" v-model="form.mobile" autocomplete="off"></el-input>
                </el-form-item>
                <!--                <el-form-item label="备注" prop="remark">-->
                <!--                    <el-input size="small" v-model="form.remark" autocomplete="off"></el-input>-->
                <!--                </el-form-item>-->
                <el-form-item label="注册时间">
                    <div>{{form.created_at}}</div>
                </el-form-item>
            </el-form>
        </div>
        <el-button style="color: #409EFF;cursor: pointer" @click="$navigate({r:'mall/user/index'})">返回上一页</el-button>
        <el-button class="button-item" :loading="btnLoading" type="primary" @click="onSubmit">提交</el-button>
    </el-card>
</section>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                form: {
                    share: {},
                    role_type: 'user',
                    role_type_label: '',
                    is_lianc: 0
                },
                mall_members: [],
                keyword: '',
                listLoading: false,
                btnLoading: false,
                FormRules: {
                    is_inviter: 0,
                    is_examine: 0,
                    level: [
                        {required: true, message: '等级不能为空', trigger: 'blur'},
                    ],
                },
            };
        },
        methods: {
            roleTypeChange(e){
                if(e == "branch_office"){
                    this.form.role_type_label = "城市服务商";
                }else if(e == "partner"){
                    this.form.role_type_label = "区域服务商";
                }else if(e == "store"){
                    this.form.role_type_label = "VIP代理商";
                }else{
                    this.form.role_type_label = "VIP会员";
                }
            },
            //搜索
            querySearchAsync(queryString, cb) {
                this.keyword = queryString;
                this.searchUser(cb);
            },

            inviterClick(row) {
                this.form.parent_id = row.id;
            },

            searchUser(cb) {
                request({
                    params: {
                        r: 'mall/user/get-can-bind-inviter',
                        keyword: this.keyword,
                        user_id: this.form.id,
                    },
                }).then(e => {
                    if (e.data.code === 0) {
                        cb(e.data.data.list);

                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {
                });
            },

            onSubmit() {
                this.$refs.form.validate((valid) => {
                    if (valid) {
                        this.btnLoading = true;
                        let para = Object.assign({}, this.form);
                        request({
                            params: {
                                r: 'mall/user/edit',
                            },
                            data: para,
                            method: 'post'
                        }).then(e => {
                            this.btnLoading = false;
                            if (e.data.code === 0) {
                                navigateTo({r: 'mall/user/index', page: getQuery('page')});
                            } else {
                                this.$message.error(e.data.msg);
                            }
                        });
                    }
                });
            },

            getList() {
                this.listLoading = true;
                request({
                    params: {
                        r: 'mall/user/edit',
                        id: getQuery('id'),
                    },
                }).then(e => {
                    if (e.data.code === 0) {
                        this.form = e.data.data.list;
                        this.mall_members = e.data.data.mall_members;
                    } else {
                        this.$message.error(e.data.msg);
                    }
                    this.listLoading = false;
                }).catch(e => {
                    this.listLoading = false;
                });
            },
        },
        mounted() {
            this.getList();
        }
    })
</script>

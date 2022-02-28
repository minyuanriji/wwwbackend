<template id="com-edit-order">
    <div class="com-edit-order">
        <el-dialog title="编辑订单" :visible.sync="dialogVisible" :close-on-click-modal="false" @close="close">
            <el-card class="box-card" style="margin-bottom:30px;">
                <div style="display:flex;align-items: center;justify-content: flex-start">
                    <img style="" src="/web/statics/img/mall/tb.jpg" width="60" height="60"/>
                    <div style="padding-left:10px;">
                        <div v-if="formData.ali_type == 'ali'"><b>淘宝联盟</b></div>
                        <div v-if="formData.ali_type == 'jd'"><b>京东联盟</b></div>
                        <div style="color:gray;">{{formData.ali_name}}</div>
                        <div style="color:gray;">ID:{{formData.ali_id}}</div>
                    </div>
                </div>
            </el-card>

            <el-form :rules="rules" ref="formData" label-width="15%" :model="formData" size="small">

                <el-form-item label="用户">
                    <div style="margin-top:10px;display:flex;align-items: center">
                        <img :src="formData.avatar_url" alt="" style="width:50px;height:50px;">
                        <div style="padding-left:10px;">
                            <div style="line-height:23px;">{{formData.nickname}}</div>
                            <div style="line-height:23px;">ID:{{formData.user_id}}</div>
                        </div>
                    </div>
                </el-form-item>
                <el-form-item label="联盟订单号" prop="ali_order_sn">
                    <el-input placeholder="请输入" disabled="true" v-model="formData.ali_order_sn" style="width:350px;"></el-input>
                </el-form-item>
                <el-form-item label="实付款" prop="pay_price">
                    <el-input :disabled="editLoading || editField!='pay_price'" placeholder="请输入" type="number" min="0" v-model="formData.pay_price" style="width:220px;">
                        <template slot="append">元</template>
                    </el-input>
                    <el-link :disabled="editLoading" @click="editField='pay_price'" icon="el-icon-edit" type="primary" v-if="editField!='pay_price'">编辑</el-link>
                    <el-button :loading="editLoading" @click="saveIt" size="small" v-if="editField=='pay_price'" type="danger">保存</el-button>
                </el-form-item>
                <el-form-item label="付款日期" prop="pay_at">
                    <el-date-picker :disabled="editLoading || editField!='pay_at'" v-model="formData.pay_at" type="datetime" placeholder="选择日期"></el-date-picker>
                    <el-link :disabled="editLoading" @click="editField='pay_at'" icon="el-icon-edit" type="primary" v-if="editField!='pay_at'">编辑</el-link>
                    <el-button :loading="editLoading" @click="saveIt" size="small" v-if="editField=='pay_at'" type="danger">保存</el-button>
                </el-form-item>
                <el-form-item label="订单生成日期" prop="ali_created_at">
                    <el-date-picker :disabled="editLoading || editField!='ali_created_at'" v-model="formData.ali_created_at" type="datetime" placeholder="选择日期"></el-date-picker>
                    <el-link :disabled="editLoading" @click="editField='ali_created_at'" icon="el-icon-edit" type="primary" v-if="editField!='ali_created_at'">编辑</el-link>
                    <el-button :loading="editLoading" @click="saveIt" size="small" v-if="editField=='ali_created_at'" type="danger">保存</el-button>
                </el-form-item>
                <el-form-item label="联盟商品信息">
                    <el-card shadow="always">
                        <el-form-item prop="ali_item_id" label="编号">
                            <el-input :disabled="editLoading || editField != 'ali_item_id'" placeholder="请输入" v-model="formData.ali_item_id" style="width:350px;"></el-input>
                            <el-link :disabled="editLoading" @click="editField='ali_item_id'" icon="el-icon-edit" type="primary" v-if="editField!='ali_item_id'">编辑</el-link>
                            <el-button :loading="editLoading" @click="saveIt" size="small" v-if="editField=='ali_item_id'" type="danger">保存</el-button>
                        </el-form-item>
                        <el-form-item prop="ali_item_name" label="标题">
                            <el-input :disabled="editLoading || editField!='ali_item_name'" placeholder="请输入" v-model="formData.ali_item_name" style="width:350px;"></el-input>
                            <el-link :disabled="editLoading" @click="editField='ali_item_name'" icon="el-icon-edit" type="primary" v-if="editField!='ali_item_name'">编辑</el-link>
                            <el-button :loading="editLoading" @click="saveIt" size="small" v-if="editField=='ali_item_name'" type="danger">保存</el-button>
                        </el-form-item>
                        <el-form-item prop="ali_item_pic" label="封面">
                            <el-input :disabled="editLoading || editField!='ali_item_pic'" placeholder="请输入" v-model="formData.ali_item_pic" style="width:350px;"></el-input>
                            <el-link :disabled="editLoading" @click="editField='ali_item_pic'" icon="el-icon-edit" type="primary" v-if="editField!='ali_item_pic'">编辑</el-link>
                            <el-button :loading="editLoading" @click="saveIt" size="small" v-if="editField=='ali_item_pic'" type="danger">保存</el-button>
                        </el-form-item>
                        <el-form-item prop="ali_item_price" label="单价">
                            <el-input :disabled="editLoading || editField!='ali_item_price'" placeholder="请输入" type="number" min="0" v-model="formData.ali_item_price" style="width:220px;">
                                <template slot="append">元</template>
                            </el-input>
                            <el-link :disabled="editLoading" @click="editField='ali_item_price'" icon="el-icon-edit" type="primary" v-if="editField!='ali_item_price'">编辑</el-link>
                            <el-button :loading="editLoading" @click="saveIt" size="small" v-if="editField=='ali_item_price'" type="danger">保存</el-button>
                        </el-form-item>
                    </el-card>
                </el-form-item>
                <el-form-item label="联盟佣金信息">
                    <el-card shadow="always">
                        <el-form-item prop="ali_commission_rate" label="比例">
                            <el-input :disabled="editLoading || editField!='ali_commission_rate'" placeholder="请输入" type="number" min="0" v-model="formData.ali_commission_rate" style="width:220px;">
                                <template slot="append">%</template>
                            </el-input>
                            <el-link :disabled="editLoading" @click="editField='ali_commission_rate'" icon="el-icon-edit" type="primary" v-if="editField!='ali_commission_rate'">编辑</el-link>
                            <el-button :loading="editLoading" @click="saveIt" size="small" v-if="editField=='ali_commission_rate'" type="danger">保存</el-button>
                        </el-form-item>
                        <el-form-item prop="ali_commission_price" label="佣金">
                            <el-input :disabled="editLoading || editField!='ali_commission_price'" placeholder="请输入" type="number" min="0" v-model="formData.ali_commission_price" style="width:220px;">
                                <template slot="append">元</template>
                            </el-input>
                            <el-link :disabled="editLoading" @click="editField='ali_commission_price'" icon="el-icon-edit" type="primary" v-if="editField!='ali_commission_price'">编辑</el-link>
                            <el-button :loading="editLoading" @click="saveIt" size="small" v-if="editField=='ali_commission_price'" type="danger">保存</el-button>
                        </el-form-item>
                    </el-card>
                </el-form-item>
                <el-form-item v-if="formData.status_i.status == 'paid'">
                    <el-button :loading="btnLoading" @click="finishOrder" type="danger" size="big" style="margin-top:20px;">结束订单</el-button>
                </el-form-item>
            </el-form>
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
            status_i: {status: '', text: ''},
            pay_price: 0.00,
            pay_at: '',
            ali_created_at: '',
            ali_order_sn: '',
            ali_item_id: '',
            ali_item_name: '',
            ali_item_price: 0.00,
            ali_item_pic: '',
            ali_commission_rate: 0,
            ali_commission_price: 0.00
        };
    }

    Vue.component('com-edit-order', {
        template: '#com-edit-order',
        props: {
            visible:Boolean,
            orderItem: Object
        },
        data() {
            return {
                dialogVisible: false,
                formData: initFormData(),
                editField: '',
                editLoading: false,
                rules: {},
                btnLoading: false
            };
        },
        created() {

        },
        watch: {
            visible(val, oldVal){
                this.dialogVisible = val;
            },
            orderItem(val, oldVal){
                this.formData = Object.assign(this.formData, val);
            }
        },
        methods: {
            saveIt(){
                this.editLoading = true;
                let data = {field:this.editField, value:this.formData[this.editField]};
                data['order_id'] = this.formData['id'];
                request({
                    params: {
                        r: 'plugin/taolijin/mall/order/edit'
                    },
                    method: 'post',
                    data: data
                }).then(e => {
                    this.editLoading = false;
                    if (e.data.code == 0) {
                        this.editField = '';
                        this.$message.success("修改成功");
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    this.editLoading = false;
                    this.$message.error("请求失败");
                });
            },
            finishOrder(){
                let self = this;
                self.$confirm('确定要结束订单？一旦结束，若有红包、积分、金豆或分佣等奖励信息，将立即发放！', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    self.btnLoading = true;
                    request({
                        params: {
                            r: 'plugin/taolijin/mall/order/do-finish'
                        },
                        method: 'post',
                        data: {
                            order_id: this.formData.id
                        }
                    }).then(e => {
                        self.btnloading = false;
                        if (e.data.code == 0) {
                            self.formData.status_i.status = 'finished';
                            self.formData.status_i.text = '已结束';
                        } else {
                            this.$message.error(e.data.msg);
                        }
                    }).catch(e => {
                        self.btnloading = false;
                        this.$message.error("请求失败");
                    });
                }).catch(() => {

                });
            },
            close(){
                this.$emit('close');
            }
        }
    });
</script>
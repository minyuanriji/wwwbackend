<template id="com-setting">
    <div class="com-setting">
        <el-form size="mini" :data="form" label-width="150px">
            <el-form-item label="是否开启分销" prop="is_distribution" v-if="is_distribution">
                <el-switch
                        v-model="form.is_distribution"
                        :active-value="1"
                        :inactive-value="0">
                </el-switch>
                <div class="red">注：必须在“
                    <el-button type="text" @click="$navigate({r:'mall/distribution/basic'}, true)">分销中心=>基础设置</el-button>
                    ”中开启，才能使用
                </div>
            </el-form-item>
            <el-form-item label="是否开启短信提醒" prop="is_sms" v-if="is_sms">
                <el-switch
                        v-model="form.is_sms"
                        :active-value="1"
                        :inactive-value="0">
                </el-switch>
                <div class="ml-24 red">注：必须在“
                    <el-button type="text" @click="$navigate({r:'mall/setting/sms'}, true)">系统管理=>短信通知</el-button>
                    ”中开启，才能使用
                </div>
            </el-form-item>
            <el-form-item label="是否开启邮件提醒" prop="is_mail" v-if="is_mail">
                <el-switch
                        v-model="form.is_mail"
                        :active-value="1"
                        :inactive-value="0">
                </el-switch>
                <div class="ml-24 red ">注：必须在“
                    <el-button type="text" @click="$navigate({r:'mall/setting/mail'}, true)">系统管理=>邮件通知</el-button>
                    ”中开启，才能使用
                </div>
            </el-form-item>
            <el-form-item label="是否开启打印" prop="is_print" v-if="is_print">
                <el-switch
                        v-model="form.is_print"
                        :active-value="1"
                        :inactive-value="0">
                </el-switch>
                <div class="ml-24 red">注：必须在“
                    <el-button type="text" @click="$navigate({r:'mall/printer/index'}, true)">系统管理=>小票打印</el-button>
                    ”中开启，才能使用
                </div>
            </el-form-item>
            <el-form-item class="switch" label="是否开启区域允许购买" prop="is_territorial_limitation" v-if="is_territorial_limitation">
                <el-switch v-model="form.is_territorial_limitation" :active-value="1"
                           :inactive-value="0"></el-switch>
                <span class="ml-24 red">注：必须在“
                        <el-button type="text" @click="$navigate({r:'mall/area-limit/index'}, true)">
                            系统管理=>区域允许购买
                        </el-button>
                        ”中开启，才能使用
                    </span>
            </el-form-item>
            <el-form-item label="支付方式" prop="payment_type" v-if="is_payment">
                <label slot="label">支付方式
                    <el-tooltip class="item" effect="dark"
                                content="默认支持线上支付；若三个都不勾选，则视为勾选线上支付"
                                placement="top">
                        <i class="el-icon-info"></i>
                    </el-tooltip>
                </label>
                <el-checkbox-group v-model="form.payment_type" size="mini">
                    <el-checkbox label="online_pay" size="mini">线上支付</el-checkbox>
                    <el-checkbox label="huodao" size="mini" v-if="is_surpport_huodao">货到付款</el-checkbox>
                    <el-checkbox label="balance" size="mini">余额支付</el-checkbox>
                </el-checkbox-group>
            </el-form-item>
            <el-form-item label="发货方式" prop="send_type" v-if="is_send_type">
                <label slot="label">发货方式
                    <el-tooltip class="item" effect="dark"
                                content="自提需要设置门店，如果您还未设置门店请保存本页后设置门店"
                                placement="top">
                        <i class="el-icon-info"></i>
                    </el-tooltip>
                </label>
                <div>
                    <el-checkbox-group v-model="form.send_type">
                        <el-checkbox label="express">快递配送</el-checkbox>
                        <el-checkbox label="offline">到店自提</el-checkbox>
                        <el-checkbox label="city" v-if="is_surpport_city">同城配送</el-checkbox>
                    </el-checkbox-group>
                </div>
            </el-form-item>
        </el-form>
    </div>
</template>
<script>
    Vue.component('com-setting', {
        template: '#com-setting',
        props: {
            value: Object,
            is_distribution: {
                type: Boolean,
                default() {
                    return true;
                }
            },
            is_sms: {
                type: Boolean,
                default() {
                    return true;
                }
            },
            is_mail: {
                type: Boolean,
                default() {
                    return true;
                }
            },
            is_print: {
                type: Boolean,
                default() {
                    return true;
                }
            },
            is_territorial_limitation: {
                type: Boolean,
                default() {
                    return true;
                }
            },
            is_payment: {
                type: Boolean,
                default() {
                    return true;
                }
            },
            is_send_type: {
                type: Boolean,
                default() {
                    return true;
                }
            },
            is_surpport_huodao: {
                type: Boolean,
                default() {
                    return true;
                }
            },
            is_surpport_city: {
                type: Boolean,
                default() {
                    return true;
                }
            },
        },
        data() {
            return {
                setting: {
                    is_distribution: 0,
                    is_sms: 0,
                    is_mail: 0,
                    is_print: 0,
                    is_territorial_limitation: 0,
                    send_type: ['express', 'offline'],
                    payment_type: ['online_pay'],
                }
            };
        },
        computed: {
            form() {
                for (let key in this.setting) {
                    if (typeof this.value[key] === 'undefined') {
                        this.value[key] = this.setting[key];
                    }
                }
                return this.value;
            }
        },
    });
</script>

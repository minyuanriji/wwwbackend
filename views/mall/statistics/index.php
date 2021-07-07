<style>
    /* 共同样式 */
    .flex {
        display: flex;
    }

    .flex-y-center {
        align-items: center;
    }

    .flex-x-center {
        justify-content: space-between;
    }

    .flex-x-center {
        align-items: center;
    }

    .common_unit_style {
        background: #F2F3F4;
        border: 1px solid #DCDEE2;
        border-radius: 0px 5px 5px 0px;
        width: 120px;
        height: 40px;
        box-sizing: border-box;
        text-align: center;
    }

    .common_unit_style_left {
        border-right: 0;
        border-left: 1px solid #DCDEE2;
        border-radius: 5px 0px 0px 5px;
    }

    .textarea input.el-input__inner {
        border-radius: 5px 0px 0px 5px;
    }

    .textarea2 input.el-input__inner {
        border-radius: 0;
    }

    .textarea2 {
        flex: 1;
    }

    .textarea.el-input {
        flex: 1;
    }

    .common_box {
        width: 600px;
        height: 40px;
        overflow: hidden;
    }

    /* 共同样式 */

    .title_h3 {
        background: #ffffff;
        padding: 17px 20px;
        width: 100%;
        color: #606266;
        border-radius: 5px;
        font-size: 16px;
    }

    .title_h4 {
        margin-top: 10px;
        padding: 0px 20px;
        background-color: #fff;
        color: #606266;
        font-size: 18px;
        border-radius: 5px 5px 0 0;
    }

    .title_h4 p {
        border-bottom: 2px solid #F3F3F3;
        margin: 0;
        padding: 16px 0;
    }

    .form-body {
        padding: 20px 40% 20px 0;
    }

    .selectBox.el-select {
        width: 400px;
    }

    .tag {
        margin: 0px 20px 15px 0;
    }

    .save_btn {
        margin: 10px 0;
    }
</style>

<div id="app" v-cloak v-loading="loading">
    <div class="title_h3">大数据平台数据设置</div>

    <el-form :model="ruleForm" label-width="172px">
        <div>
            <div class="title_h4">
                <p>大屏设置</p>
            </div>
            <div class="form-body">
                <el-form-item label="设置类型选择" prop="total_price">
                    <div class="flex flex-y-center common_box">
                        <el-switch v-model="ruleForm.set_type" active-color="#03C5FF" active-value='1' inactive-value='0'></el-switch>
                        <p style="margin-left: 10px;color:#606266;">开启虚拟数据</p>
                    </div>
                </el-form-item>
                <el-form-item label="总交易额设置" prop="total_price">
                    <div class="flex flex-y-center common_box">
                        <el-input v-model="ruleForm.total_transactions" class="textarea"></el-input>
                        <div class="common_unit_style">元</div>
                    </div>
                </el-form-item>
                <el-form-item label="今日收益设置" prop="total_price">
                    <div class="flex flex-y-center common_box">
                        <el-input v-model="ruleForm.today_earnings" class="textarea"></el-input>
                        <div class="common_unit_style">元</div>
                    </div>
                </el-form-item>
                <el-form-item label="总用户数量" prop="total_price">
                    <div class="flex flex-y-center common_box">
                        <el-input v-model="ruleForm.user_sum" class="textarea"></el-input>
                        <div class="common_unit_style">人</div>
                    </div>
                </el-form-item>
            </div>
        </div>

        <div>
            <div class="title_h4">
                <p>数据地图区域设置</p>
            </div>
            <div class="form-body">
                <el-form-item label="地区人数设置" prop="total_price">
                    <div class="flex">
                        <el-select v-model="value" class="selectBox" clearable filterable placeholder="请选择">
                            <el-option v-for="(item,index) in options" :key="item.value" :value="item.id" :label="item.name"></el-option>
                        </el-select>

                        <div class="flex" style="margin: 0 25px;">
                            <el-input v-model="people_num" class="textarea"></el-input>
                            <div class="common_unit_style" style="width: 80px;">人</div>
                        </div>

                        <el-button type="primary" @click="addProvince">确认添加</el-button>
                    </div>
                    <div class="flex" style="flex-wrap:wrap;margin-top:20px;">
                        <el-tag closable v-for="(item,index) in ruleForm.province_data" @close="closeTag(index)" :key="index" class="tag">{{item.name}} &nbsp; {{item.num}}</el-tag>
                    </div>
                </el-form-item>
            </div>
        </div>

        <div>
            <div class="title_h4">
                <p>用户数据统计</p>
            </div>
            <div class="form-body">
                <el-form-item label="访客数设置" prop="total_price">
                    <div class="flex flex-y-center common_box">
                        <el-input v-model="ruleForm.visitor_num" class="textarea"></el-input>
                        <div class="common_unit_style">元</div>
                    </div>
                </el-form-item>
                <el-form-item label="浏览量设置" prop="total_price">
                    <div class="flex flex-y-center common_box">
                        <el-input v-model="ruleForm.browse_num" class="textarea"></el-input>
                        <div class="common_unit_style">元</div>
                    </div>
                </el-form-item>
                <el-form-item label="会员等级人数设置" prop="total_price">
                    <div class="flex flex-y-center common_box" v-for="(item,index) in ruleForm.member_level" :key="index" style="margin-bottom:14px">
                        <div class="common_unit_style common_unit_style_left">{{item.name}}</div>
                        <el-input v-model="item.num" class="textarea2"></el-input>
                        <div class="common_unit_style">人</div>
                    </div>
                </el-form-item>
            </div>
        </div>

        <div>
            <div class="title_h4">
                <p>转化率统计</p>
            </div>
            <div class="form-body">
                <el-form-item label="浏览量设置" prop="total_price">
                    <div class="flex flex-y-center common_box">
                        <el-input v-model="ruleForm.conversion_browse_num" class="textarea"></el-input>
                        <div class="common_unit_style">元</div>
                    </div>
                </el-form-item>
                <el-form-item label="访客量设置" prop="total_price">
                    <div class="flex flex-y-center common_box">
                        <el-input v-model="ruleForm.conversion_visitor_num" class="textarea"></el-input>
                        <div class="common_unit_style">元</div>
                    </div>
                </el-form-item>
                <el-form-item label="关注量设置" prop="total_price">
                    <div class="flex flex-y-center common_box">
                        <el-input v-model="ruleForm.follow_num" class="textarea"></el-input>
                        <div class="common_unit_style">人</div>
                    </div>
                </el-form-item>
            </div>
        </div>

        <div>
            <div class="title_h4">
                <p>下单统计设置</p>
            </div>
            <div class="form-body">
                <el-form-item label="访问量设置" prop="total_price">
                    <div class="flex flex-y-center common_box">
                        <el-input v-model="ruleForm.order_visit_num" class="textarea"></el-input>
                        <div class="common_unit_style">元</div>
                    </div>
                </el-form-item>
                <el-form-item label="下单量设置" prop="total_price">
                    <div class="flex flex-y-center common_box">
                        <el-input v-model="ruleForm.order_num" class="textarea"></el-input>
                        <div class="common_unit_style">元</div>
                    </div>
                </el-form-item>
                <el-form-item label="支付人数设置" prop="total_price">
                    <div class="flex flex-y-center common_box">
                        <el-input v-model="ruleForm.pay_num" class="textarea"></el-input>
                        <div class="common_unit_style">人</div>
                    </div>
                </el-form-item>
            </div>
        </div>

        <div class="save_btn">
            <el-button type="primary" @click='save' :loading="loading">保存</el-button>
        </div>
    </el-form>
</div>

<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                loading: false,
                ruleForm: {},
                options: [{
                    value: '选项1',
                    label: '黄金糕'
                }, {
                    value: '选项2',
                    label: '双皮奶'
                }, {
                    value: '选项3',
                    label: '蚵仔煎'
                }, {
                    value: '选项4',
                    label: '龙须面'
                }, {
                    value: '选项5',
                    label: '北京烤鸭'
                }],
                value: '',
                people_num: '',
            }
        },
        mounted() {
            this.getData();
            this.getProvince();
        },
        methods: {
            getData() {
                this.loading = true;
                request({
                    params: {
                        r: 'mall/statistics/index',
                    },
                    method: 'get',
                }).then(e => {
                    this.loading = false;
                    if (e.data.code == 0) {
                        this.ruleForm = e.data.data;
                    }
                })
            },
            getProvince() {
                this.loading = true;
                request({
                    params: {
                        r: 'mall/statistics/get-province',
                    },
                    method: 'post',
                }).then(e => {
                    this.loading = false;
                    if (e.data.code == 0) {
                        this.options = e.data.data;
                        console.log(e.data, 'eee');
                    }
                })
            },
            addProvince() { //添加地区
                var obj = this.options.find((item) => {
                    return item.id == this.value;
                })

                var sameBool = this.ruleForm.province_data.find(item => item.id == this.value);
                if(!sameBool){
                    this.ruleForm.province_data.push({
                        id: this.value,
                        name: obj.name,
                        num: this.people_num
                    })
                }else{
                    this.$message.error('不可添加相同的城市!');
                }
                console.log(this.ruleForm.province_data,'this.ruleForm.province_data');
            },
            closeTag(index) {
                this.ruleForm.province_data.splice(index, 1);
            },
            save() {
                this.loading = true;
                request({
                    params: {
                        r: 'mall/statistics/index',
                    },
                    data: {
                        set_type: parseInt(this.ruleForm.set_type) ? 1 : 0,
                        total_transactions: this.ruleForm.total_transactions,
                        today_earnings: this.ruleForm.today_earnings,
                        user_sum: this.ruleForm.user_sum,
                        visitor_num: this.ruleForm.visitor_num,
                        browse_num: this.ruleForm.browse_num,
                        province_data: JSON.stringify(this.ruleForm.province_data) == '[]' ?'': JSON.stringify(this.ruleForm.province_data),
                        member_level: JSON.stringify(this.ruleForm.member_level),
                        follow_num: this.ruleForm.follow_num,
                        order_visit_num: this.ruleForm.order_visit_num,
                        order_num: this.ruleForm.order_num,
                        pay_num: this.ruleForm.pay_num,
                        conversion_browse_num: this.ruleForm.conversion_browse_num,
                        conversion_visitor_num: this.ruleForm.conversion_visitor_num,
                        add_user: 0,
                    },
                    method: 'post',
                }).then(e => {
                    this.loading = false;
                    if (e.data.code == 0) {
                        this.loading = false;
                        this.$message({
                            message: '保存成功!',
                            type: 'success'
                        });
                    }
                })
            }
        }
    })
</script>
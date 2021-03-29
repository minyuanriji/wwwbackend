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
<div id="app" v-cloak>
    <el-card id="com-goods-distribution" class="box-card" v-loading="cardLoading" shadow="never" style="border:0"
             body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <el-breadcrumb separator="/">
                <el-breadcrumb-item>
                    <span style="color: #409EFF;cursor: pointer" @click="$navigate({r:'plugin/mch/mall/distribution/list'})">商户列表</span>
                </el-breadcrumb-item>
                <el-breadcrumb-item>设置分佣信息</el-breadcrumb-item>
            </el-breadcrumb>
        </div>
        <div class="form-body">
            <el-form :model="ruleForm" :rules="rules" ref="ruleForm" label-width="160px" size="small">
                <el-row>
                    <el-card shadow="never" style="margin-bottom: 20px">
                        <div slot="header">
                            <span>分佣信息</span>
                        </div>
                        <el-col>
                            <el-form-item label="分销佣金类型" prop="share_type">
                                <el-radio v-model="share_type" :label="0">固定金额</el-radio>
                                <el-radio v-model="share_type" :label="1">百分比</el-radio>
                            </el-form-item>
                            <el-form-item label="分销佣金">
                                <template v-if="distributionLevelArray.length == 0">
                                    <el-button type="danger" @click="$navigate({r: 'plugin/distribution/mall/setting/index'})">
                                        请先在分销应用的中开启功能
                                    </el-button>
                                </template>
                                <template v-else>
                                    <div class="box">
                                        <label style="margin-bottom:0;padding:18px 10px;">批量设置</label>
                                        <el-select v-model="selectData" slot="prepend" placeholder="请选择层级">
                                            <el-option v-for="(item, index) in distributionLevelArray" :value="item.value"
                                                       :key="item.id"
                                                       :label="item.label">{{item.label}}
                                            </el-option>
                                        </el-select>
                                        <el-input @keyup.enter.native="enter" type="number" style="width: 150px;"
                                                  v-model="batchShareLevel">
                                            <span slot="append">{{share_type == 1 ? '%' : '元'}}</span>
                                        </el-input>
                                        <el-button type="primary" size="small" @click="batchAttr">设置</el-button>
                                    </div>

                                    <el-table ref="normal" :data="distributionLevelList" border stripe style="margin-top:10px;width: 100%;"
                                              @selection-change="handleSelectionChange">
                                        <el-table-column type="selection" width="70"></el-table-column>
                                        <el-table-column label="等级名称" prop="name"></el-table-column>
                                        <el-table-column :label="item.label" :prop="item.value" :property="item.value"
                                                         v-for="(item, index) in distributionLevelArray" :key="index" width="200">
                                            <template slot-scope="scope">
                                                <el-input type="number" v-model="scope.row[scope.column.property]">
                                                    <span slot="append">{{share_type == 1 ? '%' : '元'}}</span>
                                                </el-input>
                                            </template>
                                        </el-table-column>
                                    </el-table>


                                </template>

                                </template>
                            </el-form-item>
                        </el-col>
                    </el-card>

                </el-row>
            </el-form>
        </div>
        <el-button class="button-item" :loading="btnLoading" type="primary" @click="save('ruleForm')" size="small">保存
        </el-button>
    </el-card>
</div>
<script>
const app = new Vue({
    el: '#app',
    data() {
        return {
            ruleForm: {},
            rules: {},
            share_type: 0,
            distributionLevelArray: [],
            distributionLevelList: [],
            distributionDetails: [],
            batchShareLevel: 0,
            selectList: [],
            selectData: '',
            cardLoading: false,
            btnLoading: false,
        };
    },
    methods: {
        save(){
            let self = this;
            request({
                params: {
                    r: 'plugin/mch/mall/distribution/edit',
                },
                method: 'post',
                data: {
                    mch_id: getQuery('id'),
                    share_type: self.share_type,
                    distribution_level_list: self.distributionLevelList
                }
            }).then(e => {
                self.loading = false;
                if (e.data.code == 0) {
                    self.$message.success(e.data.msg);
                } else {
                    self.$message.error(e.data.msg);
                }
            }).catch(e => {
                console.log(e);
            });
        },
        handleSelectionChange(data) {
            this.selectList = data;
        },
        getDetail() {
            var self = this;
            self.cardLoading = true;
            request({
                params: {
                    r: 'plugin/mch/mall/distribution/edit',
                    id: getQuery('id')
                },
                method: 'get',
                data: {}
            }).then(e => {
                self.cardLoading = false;
                if (e.data.code == 0) {
                    self.share_type = e.data.data.shareType;
                    self.distributionLevelArray = e.data.data.distributionLevelArray;
                    self.distributionLevelList = self.setDistriburtionLevel(e.data.data.distributionLevelList, e.data.data.distributionDetails);
                }
            }).catch(e => {
                console.log(e);
            });
        },
        setDistriburtionLevel(distributionLevelList, list) {
            let newDistributionLevelList = [];
            distributionLevelList.forEach((item) => {
                let newItem = {
                    level: item.level,
                    name: item.name,
                    commission_first: 0,
                    commission_second: 0,
                    commission_third: 0,
                };
                for (let i in list) {
                    if (list[i].level == item.level) {
                        newItem = Object.assign(newItem, list[i]);
                    }
                }
                newDistributionLevelList.push(newItem);
            });

            return JSON.parse(JSON.stringify(newDistributionLevelList));
        },
        batchAttr() {
            if (!this.selectList || this.selectList.length === 0) {
                this.$message.warning('请勾选分销商等级');
                return;
            }
            if (this.selectData === '') {
                this.$message.warning('请选择分销层级');
                return;
            }
            this.distributionLevelList.forEach((item, index) => {
                let flag = false;
                this.selectList.map((item1) => {
                    if (JSON.stringify(item1) === JSON.stringify(item)) {
                        flag = true;
                    }
                });
                if (flag) {
                    item[this.selectData] = this.batchShareLevel
                }
            });
        }
    },
    mounted: function () {
        this.getDetail();
        this.navigateToUrl = 'plugin/mch/mall/distribution/edit';
    }
});
</script>

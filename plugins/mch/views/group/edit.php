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
    <el-card class="box-card" v-loading="cardLoading" shadow="never" style="border:0"
             body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <el-breadcrumb separator="/">
                <el-breadcrumb-item>
                     <span style="color: #409EFF;cursor: pointer"
                           @click="$navigate({r:'plugin/mch/mall/group/list'})">连锁店管理</span>
                </el-breadcrumb-item>
                <el-breadcrumb-item>编辑连锁店</el-breadcrumb-item>
            </el-breadcrumb>
        </div>
        <div class="form-body">
            <el-tabs v-model="activeName">
                <el-tab-pane label="基本信息" name="basic">
                    <el-form :model="ruleForm" :rules="rules" ref="ruleForm" label-width="160px" size="small">

                        <template v-if="!ruleForm.mch_id">
                            <el-form-item label="设置总店">
                                <el-card class="box-card">
                                    <el-input size="big" style="width:300px;" @keyup.enter.native="searchMchList" size="small" placeholder="请输入关键词搜索" v-model="searchMch.keyword" clearable
                                               @clear='searchMchList'>
                                        <el-button @click="searchMchList" slot="append" icon="el-icon-search" ></el-button>
                                    </el-input>
                                    <el-table v-loading="searchMch.listLoading" :data="searchMch.list" border style="margin-top:10px;width: 70%">
                                        <el-table-column prop="id" label="商户ID" width="100"></el-table-column>
                                        <el-table-column label="商户名称" width="180"></el-table-column>
                                        <el-table-column label="操作"></el-table-column>
                                    </el-table>
                                    <div  style="margin-top: 20px;">
                                        <el-pagination
                                                hide-on-single-page
                                                @current-change="pagination"
                                                background
                                                layout="prev, pager, next, jumper"
                                                :page-count="searchMch.pageCount">
                                        </el-pagination>
                                    </div>
                                </el-card>
                            </el-form-item>
                        </template>
                        <template v-else>
                            <el-form-item>
                                <el-button class="button-item" :loading="btnLoading" type="primary" size="small">
                                    保存
                                </el-button>
                            </el-form-item>
                        </template>

                    </el-form>

                </el-tab-pane>
            </el-tabs>
        </div>
    </el-card>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                name: '',
                cover_url: '',
                ruleForm: {
                    mch_id: 0
                },
                rules: {
                    mch_id: [
                        {required: true, message: '总店信息', trigger: 'change'},
                    ]
                },
                btnLoading: false,
                tableLoading: false,
                cardLoading: false,
                activeName: 'basic',
                searchMch: {
                    list: [],
                    listLoading: false,
                    page: 1,
                    pageCount: 0,
                    pagination: null,
                    keyword: ''
                },

            }
        },
        watch: {},
        mounted: function () {
            this.getMchList();
        },
        methods: {
            pagination(currentPage) {
                this.searchMch.page = currentPage;
                this.getMchList();
            },
            searchMchList(){
                this.searchMch.page = 1;
                this.getMchList();
            },
            getMchList() {
                let self = this;
                self.searchMch.listLoading = true;
                request({
                    params: {
                        r: 'plugin/mch/mall/group/search-mch',
                        page: self.searchMch.page,
                        keyword: self.searchMch.keyword
                    },
                    method: 'get',
                }).then(e => {
                    self.searchMch.listLoading = false;
                    self.searchMch.list = e.data.data.list;
                    self.searchMch.pageCount = e.data.data.pagination.page_count;
                }).catch(e => {
                    self.searchMch.listLoading = false;
                });
            }
        }
    });
</script>

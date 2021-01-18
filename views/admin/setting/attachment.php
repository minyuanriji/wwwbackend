<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Author: zal
 * Date: 2020-04-11
 * Time: 09:12
 */
Yii::$app->loadComponentView('com-attachment-edit')
?>
<div id="app" v-cloak>
    <el-card shadow="never" v-loading="loading">
        <com-attachment-edit @save="loadData()" :storage-types="storageTypes">
            <el-button size="small" style="margin-bottom: 20px">添加存储位置</el-button>
        </com-attachment-edit>
        <el-table border :data="list" style="width: 100%">
            <el-table-column label="存储位置">
                <template slot-scope="scope">
                    {{storageTypes[scope.row.type]}}
                </template>
            </el-table-column>
            <el-table-column label="默认存储">
                <template slot-scope="scope">
                    <el-switch :disabled="scope.row.status == 1" @change="handleEnable(scope.row)" active-value="1"
                               inactive-value="0"
                               v-model="scope.row.status"/>
                </template>
            </el-table-column>
            <el-table-column label="操作">
                <template slot-scope="scope">
                    <com-attachment-edit @save="loadData()" :item="scope.row" :storage-types="storageTypes">
                        <el-button size="mini" type="text" circle>
                            <el-tooltip class="item" effect="dark" content="编辑" placement="top">
                                <img src="statics/img/mall/edit.png" alt="">
                            </el-tooltip>
                        </el-button>
                    </com-attachment-edit>
                </template>
            </el-table-column>
        </el-table>
    </el-card>
</div>
<script>
new Vue({
    el: '#app',
    data() {
        return {
            storageTypes: {
            },
            list: [],
            loading: false
        };
    },
    created() {
        this.loadData();
    },
    methods: {
        loadData() {
            this.loading = true;
            this.$request({
                params: {
                    r: 'admin/setting/attachment',
                }
            }).then(e => {
                this.loading = false;
                if (e.data.code === 0) {
                    this.storageTypes = e.data.data.storageTypes;
                    this.list = e.data.data.list;
                } else {
                }
            }).catch(e => {
                this.loading = false;
            });
        },
        handleEnable(item) {
            this.$confirm('确认切换存储位置？切换后默认存储将变更。', '提示').then(e => {
                this.$request({
                    params: {
                        r: 'admin/setting/attachment-enable-storage',
                        id: item.id,
                    },
                }).then(e => {
                    if (e.data.code !== 0) {
                        item.status = 0;
                    } else {
                        this.loadData();
                    }
                }).catch(e => {
                    item.status = 0;
                });
            }).catch(e => {
                item.status = 0;
            });
        },
    },
});
</script>
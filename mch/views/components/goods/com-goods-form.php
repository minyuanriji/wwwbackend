<template id="com-goods-form">
    <div class="com-goods-form">
        <el-tag @close="deleteValue" v-if="value"
                :key="value.name"
                :disable-transitions="true"
                style="margin-right: 10px;"
                closable>
            {{value.name}}
        </el-tag>
        <el-button type="button" size="mini" @click="open">{{title}}</el-button>
        <el-dialog :title="title" :visible.sync="dialog" width="30%">
            <el-card shadow="never" flex="dir:left" style="flex-wrap: wrap" v-loading="loading">
                <el-radio-group v-model="checked">
                    <el-radio style="padding: 10px;" v-for="item in list"
                              :label="item" :key="item.id">{{item.name}}
                    </el-radio>
                </el-radio-group>
            </el-card>
            <div slot="footer" class="dialog-footer">
                <el-button @click="cancel">取 消</el-button>
                <el-button type="primary" @click="confirm">确 定</el-button>
            </div>
        </el-dialog>
    </div>
</template>
<script>
    Vue.component('com-goods-form', {
        template: '#com-goods-form',
        props: {
            value: Array | Object,
            title: String,
            url: String,
        },
        data() {
            return {
                dialog: false,
                list: [],
                checked: {},
                loading: false,
            };
        },
        methods: {
            open() {
                this.dialog = true;
                this.loadData();
            },
            loadData() {
                this.loading = true;
                request({
                    params: {
                        r: this.url
                    }
                }).then(response => {
                    this.loading = false;
                    if (response.data.code == 0) {
                        this.list = response.data.data.list;
                    } else {
                        this.$message.error(response.data.msg);
                    }
                }).catch(response => {
                    console.log(response);
                });
            },
            cancel() {
                this.dialog = false;
                this.checked = {};
            },
            confirm() {
                this.$emit('selected', this.checked);
                this.cancel();
            },
            deleteValue() {
                this.$emit('selected', null);
            }
        }
    });
</script>

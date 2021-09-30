
<template id="com-refund-agree">
    <div class="com-refund-agree">
        <el-dialog title="同意退款操作" :visible.sync="dialogVisible" :close-on-click-modal="false" @close="close">
            <table class="grid-i" style="width:100%;">
                <tr><th>订单详情</td></tr>
            </table>
        </el-dialog>
    </div>
</template>

<script>

    Vue.component('com-refund-agree', {
        template: '#com-refund-agree',
        props: {
            visible: Boolean
        },
        data() {
            return {
                dialogTitle: "添加应用",
                dialogVisible: false,
                rules: {

                }
            };
        },
        watch: {
            visible(val, oldVal){
                this.dialogVisible = val;
            }
        },
        methods: {
            close(){
                this.$emit('close');
            }
        }
    });
</script>
<style>
.grid-i th,.grid-i td{text-align:left;}
</style>
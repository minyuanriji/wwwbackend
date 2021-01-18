<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Author: zal
 * Date: 2020-04-23
 * Time: 10:30
 */
?>
<template id="diy-demo">
    <div>
        <div class="diy-component-preview">{{data.text}}</div>
        <div class="diy-component-edit">
            <el-form label-width="100px" @submit.native.prevent>
                <el-form-item label="文字">
                    <el-input v-model="data.text"></el-input>
                </el-form-item>
            </el-form>
        </div>
    </div>
</template>
<script>
    Vue.component('diy-demo', {
        template: '#diy-demo',
        props: {
            value: Object,
        },
        data() {
            return {
                data: {
                    text: '文字',
                }
            };
        },
        created() {
            if (!this.value) {
                this.$emit('input', JSON.parse(JSON.stringify(this.data)))
            } else {
                this.data = JSON.parse(JSON.stringify(this.value));
            }
        },
        computed: {},
        watch: {
            data: {
                deep: true,
                handler(newVal, oldVal) {
                    this.$emit('input', newVal, oldVal)
                },
            }
        },
        methods: {}
    });
</script>
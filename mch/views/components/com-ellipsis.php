<template id="com-ellipsis">
    <div class="com-ellipsis">
        <div v-line-clamp="line">
            <slot></slot>
        </div>
    </div>
</template>
<script>
Vue.use(VueLineClamp, {
    importCss: true,
});

Vue.component('com-ellipsis', {
    template: '#com-ellipsis',
    props: {
        line: Number,
    },
    computed: {
    },
    methods: {},
});
</script>

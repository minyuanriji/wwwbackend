<?php
echo $this->render('../components/com-order-detail');
?>
<div id="app" v-cloak>
    <com-order-detail></com-order-detail>
</div>

<script>
    new Vue({
        el: '#app',
        data() {
            return {

            };
        },
        created() {
        },
        methods: {

        }
    })
</script>
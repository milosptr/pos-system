<template>
<div class="flex flex-col border rounded drop-shadow px-10 py-6 mt-2">
    <div> {{table.name}} </div>
    <OverviewOrder
        v-for="(order, idx) in table.orders"
        :key="idx"
        :order="order"
    />
</div>
</template>
<script>
import OverviewOrder from './OverviewOrder.vue'

export default {
    name: "OverviewTableOrder",
    components: {
        OverviewOrder
    },
    props: {
        table: {
            type: Object,
            default: null
        }
    }
}
</script>
<style lang="">

</style>

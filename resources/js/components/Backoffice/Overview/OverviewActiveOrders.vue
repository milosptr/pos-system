<template>
<div>
    <OverviewTableOrder
        v-for="(table, idx) in $store.getters.activeTableOrders"
        :key=idx
        :table="table"
    />
</div>
</template>
<script>
import OverviewTableOrder from './OverviewTableOrder.vue'

export default {
    name: 'OverviewActiveOrders',
    components: {
        OverviewTableOrder
    }
}
</script>
<style scoped>

</style>

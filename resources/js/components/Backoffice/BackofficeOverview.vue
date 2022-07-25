<template>
<div class="flex flex-col w-full">
    <OverviewStats />
    <OverviewActiveOrders />
</div>



</template>
<script>

import OverviewStats from './Overview/OverviewStats.vue'
import OverviewActiveOrders from './Overview/OverviewActiveOrders.vue'

export default {
    name: "BackofficeOverview",
    components: {
        OverviewStats,
        OverviewActiveOrders
    },
    mounted() {
        this.$store.dispatch('getStats')
        this.$store.dispatch('getActiveTableOrders')
    }
}
</script>
<style scoped>

</style>

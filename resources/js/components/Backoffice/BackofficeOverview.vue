<template>
<div class="flex flex-col w-full px-10">
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
        this.$store.dispatch('getRevenue')
        this.$store.dispatch('getActiveTableOrders')
    }
}
</script>
<style scoped>

</style>

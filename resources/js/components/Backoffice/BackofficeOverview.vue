<template>
<div class="flex flex-col w-full -mx-0 sm:-mx-6 lg:-mx-8">
    <OverviewStats />
    <OverviewActiveOrders />
    <OverviewSlideoverSidebar />
</div>



</template>
<script>

import OverviewStats from './Overview/OverviewStats.vue'
import OverviewActiveOrders from './Overview/OverviewActiveOrders.vue'
import OverviewSlideoverSidebar from './Overview/OverviewSlideoverSidebar.vue'
import OverviewRevenue from './OverviewRevenue.vue'

export default {
    name: "BackofficeOverview",
    components: {
        OverviewStats,
        OverviewActiveOrders,
        OverviewSlideoverSidebar,
        OverviewRevenue,
    },
    mounted() {
        this.$store.dispatch('getStats')
        this.$store.dispatch('getActiveTableOrders')
    }
}
</script>
<style scoped>

</style>

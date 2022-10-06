<template>
<div class="flex flex-col w-full">
    <OverviewStats />
    <div class="flex flex-col md:flex-col-reverse">
      <OverviewActiveOrders />
      <OverviewScheduler />
    </div>
    <OverviewSlideoverSidebar />
</div>



</template>
<script>

import OverviewStats from './Overview/OverviewStats.vue'
import OverviewActiveOrders from './Overview/OverviewActiveOrders.vue'
import OverviewSlideoverSidebar from './Overview/OverviewSlideoverSidebar.vue'
import OverviewScheduler from './Overview/OverviewScheduler.vue'

export default {
    name: "BackofficeOverview",
    components: {
        OverviewStats,
        OverviewActiveOrders,
        OverviewSlideoverSidebar,
        OverviewScheduler,
    },
    mounted() {
        this.$store.dispatch('getStats')
        this.$store.dispatch('getActiveTableOrders')
    }
}
</script>
<style scoped>

</style>

<template>
<div class="flex flex-col w-full relative">
    <OverviewStats />
    <div class="flex flex-col md:flex-col-reverse">
      <OverviewActiveOrders />
      <OverviewThirdPartyOrders />
      <OverviewScheduler />
    </div>
    <OverviewSlideoverSidebar />
</div>

</template>
<script>
  import OverviewStats from './Overview/OverviewStats.vue'
  import OverviewActiveOrders from './Overview/OverviewActiveOrders.vue'
  import OverviewThirdPartyOrders from './Overview/OverviewThirdPartyOrders.vue'
  import OverviewSlideoverSidebar from './Overview/OverviewSlideoverSidebar.vue'
  import OverviewScheduler from './Overview/OverviewScheduler.vue'

  export default {
      name: "BackofficeOverview",
      components: {
      OverviewStats,
      OverviewActiveOrders,
      OverviewThirdPartyOrders,
      OverviewSlideoverSidebar,
      OverviewScheduler,
  },
      mounted() {
          this.$store.dispatch('getStats')
          this.$store.dispatch('getActiveTableOrders')
          this.$store.dispatch('getThirdPartyOrders')
      }
  }
</script>
<style scoped>

</style>

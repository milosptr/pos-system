<template>
<div>
  <div class="text-xl font-semibold mt-10 mb-5">Active Orders</div>
  <div class="mt-5 grid grid-cols-1 md:grid-cols-3 gap-2">
    <div v-for="(table, idx) in $store.getters.activeTableOrders" :key="idx" class="bg-white shadow px-4 py-5 sm:p-6 hover:bg-orange-50 cursor-pointer">
      <div class="flex flex-col sm:flex-row justify-between items-center font-semibold">
        <div>{{ table.name }}</div>
        <div>{{ $filters.formatPrice(table.total) }} RSD</div>
      </div>
    </div>
  </div>
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

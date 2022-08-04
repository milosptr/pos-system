<template>
<div>
  <div class="flex items-center justify-between">
    <div class="text-xl font-semibold mt-10 mb-5">Active Orders</div>
    <div class="text-lg font-medium mt-10 mb-5 text-indigo-500" @click="showAllActiveOrdersSidebar = true">See All</div>
  </div>
  <div class="mt-5 grid grid-cols-1 md:grid-cols-3 gap-2">
    <div
      v-for="(table, idx) in $store.getters.activeTableOrders"
      :key="idx"
      class="bg-white shadow px-4 py-5 sm:p-6 hover:bg-orange-50 cursor-pointer"
      :class="{'bg-orange-100': activeOrder && activeOrder.id === table.id}"
      @click="$store.commit('setActiveOrder', table)"
    >
      <div
        class="flex flex-col sm:flex-row justify-between items-center font-semibold"
        :class="{'text-indigo-600': table.id === lastActiveTableId}"
      >
        <div>{{ table.name }}</div>
        <div>{{ $filters.formatPrice(table.total) }} RSD</div>
      </div>
    </div>
  </div>
  <OverviewSlideoverAllActiveOrders :show="showAllActiveOrdersSidebar" @close="showAllActiveOrdersSidebar = false" />
</div>
</template>
<script>
import OverviewSlideoverAllActiveOrders from './OverviewSlideoverAllActiveOrders.vue'
import OverviewTableOrder from './OverviewTableOrder.vue'

export default {
    name: 'OverviewActiveOrders',
    components: {
      OverviewTableOrder,
      OverviewSlideoverAllActiveOrders,
    },
    data: () => ({
      showAllActiveOrdersSidebar: false,
    }),
    computed: {
      activeOrder() {
        return this.$store.getters.activeOrder
      },
      lastActiveTableId() {
        let tables = this.$store.getters.activeTableOrders.map((t) => {
          return { id: t.id, order: t.orders.sort((a,b) => {
            return new Date(b.created_at_full) - new Date(a.created_at_full)
          })[0]
          }
        }).sort((a, b) => new Date(b.order.created_at_full) - new Date(a.order.created_at_full))
        return tables[0].id
      },
    },
}
</script>
<style scoped>

</style>

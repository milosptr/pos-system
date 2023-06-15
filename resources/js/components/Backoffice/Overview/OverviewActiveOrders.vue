<template>
  <div>
    <div class="flex items-center justify-between">
      <div class="mt-10 text-xl font-semibold">Aktivni stolovi</div>
      <div
        class="mt-10 text-lg font-medium text-indigo-500"
        @click="showAllActiveOrdersSidebar = true">
        Vidi sve
      </div>
    </div>
    <div class="mt-5 grid grid-cols-1 gap-2 md:grid-cols-3">
      <div
        v-for="(table, idx) in $store.getters.activeTableOrders"
        :key="idx"
        class="cursor-pointer bg-white px-4 py-5 shadow hover:bg-orange-50 sm:p-6"
        :class="{ 'bg-orange-100': activeOrder && activeOrder.id === table.id }"
        @click="$store.commit('setActiveOrder', table)">
        <div
          class="flex flex-col items-center justify-between font-semibold sm:flex-row"
          :class="{ 'text-indigo-600': table.id === lastActiveTableId }">
          <div>{{ table.name }}</div>
          <div>{{ $filters.formatPrice(table.total) }} RSD</div>
        </div>
      </div>
    </div>
    <OverviewSlideoverAllActiveOrders
      :show="showAllActiveOrdersSidebar"
      @close="showAllActiveOrdersSidebar = false" />
  </div>
</template>
<script>
import OverviewSlideoverAllActiveOrders from './OverviewSlideoverAllActiveOrders.vue'
import OverviewTableOrder from './OverviewTableOrder.vue'

export default {
  name: 'OverviewActiveOrders',
  components: {
    OverviewTableOrder,
    OverviewSlideoverAllActiveOrders
  },
  data: () => ({
    showAllActiveOrdersSidebar: false
  }),
  computed: {
    activeOrder() {
      return this.$store.getters.activeOrder
    },
    lastActiveTableId() {
      let tables = this.$store.getters.activeTableOrders
        .map((t) => {
          return {
            id: t.id,
            order: t.orders.sort((a, b) => {
              return new Date(b.created_at_full) - new Date(a.created_at_full)
            })[0]
          }
        })
        .sort((a, b) => new Date(b.order.created_at_full) - new Date(a.order.created_at_full))
      return tables[0].id
    }
  }
}
</script>
<style scoped></style>

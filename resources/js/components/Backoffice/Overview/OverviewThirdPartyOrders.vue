<template>
<div>
  <div class="text-xl font-semibold mt-10">Druge porudzbine</div>
  <div class="mt-5 grid grid-cols-1 md:grid-cols-3 gap-2">
    <div
      v-for="group in thirdPartyOrdersGroupedByTable"
      :key="group.table_id ?? 'ungrouped'"
      class="bg-white shadow px-4 py-5 sm:p-6 hover:bg-orange-50 cursor-pointer"
      :class="{'bg-orange-100': isTableActive(group)}"
      @click="selectTable(group)"
    >
      <div class="flex flex-col sm:flex-row justify-between items-center font-semibold">
        <div class="flex items-center gap-2">
          <span>{{ group.table_name }}</span>
          <span v-if="group.orders.length > 1" class="text-gray-400 text-sm font-normal">({{ group.orders.length }} porudzbina)</span>
        </div>
        <div>{{ $filters.formatPrice(group.active_total) }} RSD</div>
      </div>
      <div class="text-xs text-gray-400 mt-1">
        {{ getLatestOrderTime(group) }}
      </div>
    </div>
  </div>
  <div v-if="!thirdPartyOrdersGroupedByTable.length" class="mt-5 text-gray-500">
    Nema aktivnih porudzbina
  </div>
</div>
</template>

<script>
  export default {
    name: 'OverviewThirdPartyOrders',
    computed: {
      thirdPartyOrdersGroupedByTable() {
        return this.$store.getters.thirdPartyOrdersGroupedByTable
      },
      activeOrder() {
        return this.$store.getters.activeOrder
      },
    },
    methods: {
      isTableActive(group) {
        if (!this.activeOrder) return false
        // Check if any order in this group matches the active order
        return group.orders.some(order => order.id === this.activeOrder.id)
      },
      getLatestOrderTime(group) {
        if (!group.orders.length) return ''
        // Get the most recent order
        const sorted = [...group.orders].sort((a, b) =>
          new Date(b.created_at_full) - new Date(a.created_at_full)
        )
        return sorted[0].created_at
      },
      selectTable(group) {
        // Transform the group's orders to the expected format for the sidebar
        const orders = group.orders.map(order => ({
          id: order.id,
          order: (order.items || []).map(item => ({
            id: item.id,
            name: item.name,
            qty: item.qty,
            price: item.price,
            unit: item.unit,
            modifier: item.modifier,
            print_station_id: item.print_station_id,
            refund: item.active === 0, // Show as refunded/struck through if inactive
          })),
          total: order.active_total ?? order.total,
          created_at: order.created_at,
          created_at_full: order.created_at_full,
          disableRefund: true,
        }))

        // Calculate total from active items only
        const total = group.active_total

        this.$store.commit('setActiveOrder', {
          id: group.table_id ?? 'ungrouped',
          name: group.table_name,
          orders: orders,
          total: total,
        })
      },
    },
  }
</script>

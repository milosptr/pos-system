<template>
<div>
  <div class="text-xl font-semibold mt-10">Druge porudzbine</div>
  <div class="mt-5 grid grid-cols-1 md:grid-cols-3 gap-2">
    <div
      v-for="order in thirdPartyOrders"
      :key="order.id"
      class="bg-white shadow px-4 py-5 sm:p-6 hover:bg-orange-50 cursor-pointer"
      :class="{'bg-orange-100': activeOrder && activeOrder.id === order.id}"
      @click="selectOrder(order)"
    >
      <div class="flex flex-col sm:flex-row justify-between items-center font-semibold">
        <div>{{ order.table_name }}</div>
        <div>{{ $filters.formatPrice(order.total) }} RSD</div>
      </div>
    </div>
  </div>
  <div v-if="!thirdPartyOrders.length" class="mt-5 text-gray-500">
    Nema aktivnih porudzbina
  </div>
</div>
</template>

<script>
  export default {
    name: 'OverviewThirdPartyOrders',
    computed: {
      thirdPartyOrders() {
        return this.$store.getters.thirdPartyOrders
      },
      activeOrder() {
        return this.$store.getters.activeOrder
      },
    },
    methods: {
      selectOrder(order) {
        this.$store.commit('setActiveOrder', {
          id: order.id,
          name: order.table_name,
          orders: [{
            id: order.id,
            order: order.order,
            total: order.total,
            created_at: order.created_at,
            created_at_full: order.created_at_full,
            disableRefund: true,
          }]
        })
      },
    },
  }
</script>

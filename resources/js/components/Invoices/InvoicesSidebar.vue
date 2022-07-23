<template>
  <div v-if="invoice" class="h-screen flex flex-col justify-between">
    <div class="p-4">
      <div class="flex justify-between items-center border-b border-gray-200 pb-1">
        <div class="text-2xl font-bold uppercase">
          Račun broj #{{ invoice?.id }}
        </div>
        <img v-if="invoice.status" :src="$filters.imgUrl('refund.svg')" class="ml-auto mr-2" alt="print" width="32" @click="refund" />
        <img :src="$filters.imgUrl('printer.svg')" alt="print" width="32" />
      </div>
      <div class="OrderSidebar overflow-x-hidden">
        <SingleOrder
          :order="invoice"
          :showOrderLine="false"
          :boxBackground="false"
          :class="{'text-red-500': invoice.status === 0}"
        />
      </div>
    </div>
    <div
      class="p-4 flex items-center justify-between border-t border-gray-200 pt-4"
      :class="{'text-red-500': invoice.status === 0}"
    >
      <div class="text-2xl font-bold uppercase">
        Total
      </div>
      <div class="text-2xl font-bold">
        {{ $filters.formatPrice(invoice?.total) }} RSD
      </div>
    </div>
  </div>
</template>

<script>
import SingleOrder from "../Tables/SingleOrder.vue"

  export default {
    components: {
      SingleOrder
    },
    computed: {
      invoice() {
        return this.$store.getters.activeInvoice
      },
      orders() {
        return this.invoice ? this.invoice.order : []
      }
    },
    methods: {
      refund() {
        this.$store.dispatch('refundInvoice')
      }
    }
  }
</script>

<style scoped>
  .OrderSidebar {
      max-height: 83vh;
      overflow-y: scroll;
    }
</style>

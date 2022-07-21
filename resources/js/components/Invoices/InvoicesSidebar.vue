<template>
  <div v-if="invoice" class="h-screen flex flex-col justify-between">
    <div class="p-4">
      <div class="flex justify-between items-center border-b border-gray-200 pb-1">
        <div class="text-2xl font-bold uppercase">
          Račun broj #{{ invoice?.id }}
        </div>
        <img src="/images/refund.svg" class="ml-auto mr-2" alt="print" width="32" />
        <img src="/images/printer.svg" alt="print" width="32" />
      </div>
      <SingleOrder
        :order="invoice"
        :showOrderLine="false"
      />
    </div>
    <div class="p-4 flex items-center justify-between border-t border-gray-200 pt-4">
      <div class="text-2xl font-bold uppercase">
        Total
      </div>
      <div class="text-2xl font-bold">
        {{ invoice?.total }} RSD
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
    }
  }
</script>

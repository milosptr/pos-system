<template>
  <div v-if="invoice" class="h-screen flex flex-col justify-between">
    <div class="p-4">
      <div class="flex justify-between items-center border-b border-gray-200 pb-1 relative">

        <div class="text-2xl font-bold uppercase">
          Račun broj #{{ invoice?.id }}
        </div>
        <div @click="showTableMenu = true">
          <img :src="$filters.imgUrl('dot-menu.svg')" alt="submenu" width="32">
        </div>
        <InvoiceMenu v-if="showTableMenu" :showRefund="!!invoice.status" @selected="handleInvoiceMenu" @close="showTableMenu = false" />
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
    <RefundReasonModal
      v-if="showRefundReasonModal"
      :preventEvent="true"
      @close="showRefundReasonModal = false"
      @refund="refund"
    />
  </div>
</template>

<script>
  import SingleOrder from "../Tables/SingleOrder.vue"
  import InvoiceMenu from "../Invoices/InvoiceMenu.vue"
  import RefundReasonModal from "../Modals/RefundReasonModal.vue"

  export default {
    components: {
      SingleOrder,
      InvoiceMenu,
      RefundReasonModal
    },
    data: () => ({
      showTableMenu: false,
      showRefundReasonModal: false,
    }),
    computed: {
      invoice() {
        return this.$store.getters.activeInvoice
      },
      orders() {
        return this.invoice ? this.invoice.order : []
      }
    },
    methods: {
      refund(data) {
        this.$store.dispatch('refundInvoice', data)
        this.showRefundReasonModal = false
      },
      handleInvoiceMenu(val) {
        if(val === 'reprint')
          return
        if(val === 'refund')
          this.showRefundReasonModal = true
      }
    }
  }
</script>

<style scoped>
  .OrderSidebar {
      max-height: 79vh;
      overflow-y: scroll;
    }
  @media (max-width: 1024px) {
    img {
      width: 28px;
    }
    .text-2xl {
      font-size: 16px;
    }
  }
</style>

<template>
  <div v-if="order.order.length" class="mb-3 mt-1 relative">
    <div
      v-if="showOrderLine"
      class="bg-gray-500 flex justify-between items-center text-white px-2"
      @click="showSingleOrderMenu = true"
    >
      <div>Porudzbina #{{ index }}</div>
      <div v-if="order.created_at">{{ order.created_at }}</div>
    </div>
    <SingleOrderItem
      v-for="o in orders"
      :key="o.id"
      :order="o"
      :disableRefund="order.disableRefund"
      :class="{'bg-gray-100': boxBackground}"
      class="py-1 px-2 text-xl my-1"
      @refund="refundItem(o)"
    />
    <SingleOrderMenu v-if="showSingleOrderMenu" @selected="handleTableMenu"  @close="showSingleOrderMenu = false" />
    <CacheOutModal v-if="showCacheOutModal" @close="showCacheOutModal = false" @charge="charge" />
  </div>
</template>

<script>
  import CacheOutModal from '../Modals/CacheOutModal.vue'
  import SingleOrderItem from './SingleOrderItem.vue'
  import SingleOrderMenu from './SingleOrderMenu.vue'

  export default {
  components: { SingleOrderItem, SingleOrderMenu, CacheOutModal },
    props: {
      order: {
        type: Object,
        default: () => {}
      },
      index: {
        type: Number,
        default: () => 1
      },
      saved: {
        type: Boolean,
        default: () => true
      },
      showOrderLine: {
        type: Boolean,
        default: () => true
      },
      boxBackground: {
        type: Boolean,
        default: () => true
      },
    },
    data: () => ({
      showSingleOrderMenu: false,
      showCacheOutModal: false,
    }),
    computed: {
      orders() {
        return [...this.order.order].reverse()
      },
      selectedWaiterId() {
        return this.$store.getters.selectedWaiterId
      },
    },
    methods: {
      refundItem(item) {
        this.$store.commit('refundItem', { order: this.order, item})
        this.$store.dispatch('storeRefundItem', this.order)
      },
      charge() {
        let total = this.order.order.reduce((a, val) => a + (val.refund ? 0 : (val.qty * val.price)), 0)
        axios.post('/api/invoices/one/' + this.order.id, { ...this.order, user_id: this.selectedWaiterId, total })
          .then((res) => {
            this.showCacheOutModal = false
            this.$store.dispatch('getTableOrders')
            this.$store.dispatch('getTables')
          })
      },
      handleTableMenu(item) {
        if(item === 'cashout') {
          this.showCacheOutModal = true
        }
        // if(item === 'reprint') {
        //   this.$store.dispatch('')
        // }
        if(item === 'refund') {
          this.showRefundReasonModal = true
        }
      }
    }
  }
</script>

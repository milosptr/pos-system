<template>
  <div class="flex h-screen flex-col justify-between">
    <div class="p-4 pb-0">
      <div class="relative flex items-center justify-between">
        <div class="pb-1 text-2xl font-bold uppercase">
          {{ tableName }}
        </div>
        <div @click="showTableMenu = true">
          <img
            :src="$filters.imgUrl('dot-menu.svg')"
            alt="submenu"
            width="32" />
        </div>
        <TableMenu
          v-if="showTableMenu"
          @selected="handleTableMenu"
          @close="showTableMenu = false" />
      </div>
      <div class="OrderSidebar overflow-x-hidden">
        <SingleOrder
          :order="order"
          :index="orders.length + 1"
          :saved="false" />
        <SingleOrder
          v-for="(o, i) in orders"
          :order="o"
          :key="o.id"
          :index="orders.length - i"
          :saved="true" />
      </div>
    </div>
    <div class="">
      <div class="fs-14rem flex justify-between bg-gray-500 px-4 py-1 font-bold text-white">
        <div>Total</div>
        <div>{{ total }}</div>
      </div>
      <div class="flex">
        <div
          class="w-1/2 bg-primary py-5 text-center text-xl font-bold uppercase text-white"
          @click="cashOut()">
          Naplati
        </div>
        <div
          class="w-1/2 bg-green-600 py-5 text-center text-xl font-bold uppercase text-white"
          @click="storeOrder()">
          Sačuvaj
        </div>
      </div>
    </div>
    <CashOutModal
      v-if="showCashOutModal"
      @close="showCashOutModal = false"
      @charge="charge" />
    <MoveTableModal
      v-if="showMoveTableModal"
      @close="showMoveTableModal = false" />
    <RefundReasonModal
      v-if="showRefundReasonModal"
      @close="showRefundReasonModal = false" />
  </div>
</template>

<script>
import CashOutModal from '../Modals/CashOutModal.vue'
import MoveTableModal from '../Modals/MoveTableModal.vue'
import RefundReasonModal from '../Modals/RefundReasonModal.vue'
import SingleOrder from './SingleOrder.vue'
import TableMenu from './TableMenu.vue'

export default {
  components: { SingleOrder, TableMenu, CashOutModal, MoveTableModal, RefundReasonModal },
  data: () => ({
    showCashOutModal: false,
    showMoveTableModal: false,
    showRefundReasonModal: false,
    showTableMenu: false,
    connectionChecup: null
  }),
  computed: {
    tableName() {
      return this.$store.getters.activeTable?.name
    },
    orders() {
      return this.$store.getters.orders
    },
    order() {
      return { order: this.$store.getters.order, disableRefund: true }
    },
    total() {
      let flatOrders = []
      let total = 0
      this.orders.forEach((o) => flatOrders.push(...o.order))
      flatOrders.push(...this.$store.getters.order)
      total = flatOrders.reduce(function (acc, val) {
        return acc + (val.refund ? 0 : val.price * val.qty)
      }, 0)

      return this.$filters.formatPrice(total)
    }
  },
  mounted() {
    this.checkServerConnection()
    this.$store.dispatch('getInventory')
  },
  unmounted() {
    clearTimeout(this.connectionChecup)
  },
  methods: {
    checkServerConnection() {
      this.$store.dispatch('checkServerConnection')
      this.connectionChecup = setInterval(() => {
        this.$store.dispatch('checkServerConnection')
      }, 6000)
    },
    storeOrder() {
      if (this.order.order.length) {
        this.$store.getters.activeTable.total = parseInt(this.total.replace('.', ''))
        this.$store.dispatch('storeOrder', this.$route.params.id)
      }
      this.$router.push('/')
    },
    cashOut() {
      if (this.order.order.length) {
        this.$store.dispatch('storeOrder', this.$route.params.id).then(() => {
          this.showCashOutModal = true
        })
      }
      if (this.orders.length) {
        this.showCashOutModal = true
      }
    },
    charge(status) {
      this.$store.dispatch('cashOut', { table_id: this.$route.params.id, status })
      this.$router.push('/')
    },
    handleTableMenu(item) {
      if (item === 'move') {
        this.showMoveTableModal = true
      }
      // if(item === 'reprint') {
      //   this.$store.dispatch('')
      // }
      if (item === 'refund') {
        this.showRefundReasonModal = true
      }
    }
  }
}
</script>

<style scoped>
.fs-14rem {
  font-size: 1.4rem;
}
.OrderSidebar {
  max-height: 79vh;
  overflow-y: scroll;
}

/* hide scrollbar but allow scrolling */
.OrderSidebar {
  -ms-overflow-style: none; /* for Internet Explorer, Edge */
  scrollbar-width: none; /* for Firefox */
  overflow-y: scroll;
}

.OrderSidebar::-webkit-scrollbar {
  display: none; /* for Chrome, Safari, and Opera */
}

@media (max-width: 1024px) {
  .OrderSidebar {
    max-height: 74vh;
    overflow-y: scroll;
  }
  img {
    width: 28px;
  }
}
</style>

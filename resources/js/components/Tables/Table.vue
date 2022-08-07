<template>
  <div class="h-screen flex flex-col justify-between">
    <div class="p-4 pb-0">
      <div class="flex items-center justify-between relative">
        <div class="text-2xl font-bold  pb-1 uppercase">
          {{ tableName }}
        </div>
        <div @click="showTableMenu = true">
          <img :src="$filters.imgUrl('dot-menu.svg')" alt="submenu" width="32">
        </div>
        <TableMenu v-if="showTableMenu" @selected="handleTableMenu" @close="showTableMenu = false" />
      </div>
      <div class="OrderSidebar overflow-x-hidden">
        <SingleOrder
          :order="order"
          :index="orders.length + 1"
          :saved="false"
        />
        <SingleOrder
          v-for="(o, i) in orders"
          :order="o"
          :key="o.id"
          :index="orders.length - i"
          :saved="true"
        />
      </div>
    </div>
    <div class="">
      <div class="bg-gray-500 text-white flex justify-between px-4 py-1 fs-14rem font-bold">
        <div>Total</div>
        <div>{{ total }}</div>
      </div>
      <div class="flex">
        <div
          class="bg-primary w-1/2 py-5  text-xl uppercase font-bold text-center text-white"
          @click="cashOut()"
          >
          Naplati
        </div>
        <div
          class="bg-green-600 w-1/2 py-5  text-xl uppercase font-bold text-center text-white"
          @click="storeOrder()"
        >
          Sačuvaj
        </div>
      </div>
    </div>
    <CacheOutModal v-if="showCacheOutModal" @close="showCacheOutModal = false" @charge="charge" />
    <MoveTableModal v-if="showMoveTableModal" @close="showMoveTableModal = false" />
    <RefundReasonModal v-if="showRefundReasonModal" @close="showRefundReasonModal = false" />
  </div>
</template>

<script>
  import CacheOutModal from '../Modals/CacheOutModal.vue'
  import MoveTableModal from '../Modals/MoveTableModal.vue'
  import RefundReasonModal from '../Modals/RefundReasonModal.vue'
  import SingleOrder from './SingleOrder.vue'
  import TableMenu from './TableMenu.vue'

  export default {
    components: { SingleOrder, TableMenu, CacheOutModal, MoveTableModal, RefundReasonModal },
    data: () => ({
     showCacheOutModal: false,
     showMoveTableModal: false,
     showRefundReasonModal: false,
     showTableMenu: false,
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
        total = flatOrders.reduce(function(acc, val) { return acc + (val.refund ? 0 : val.price * val.qty) }, 0)

        return this.$filters.formatPrice(total)
      },
    },
    // watch: {
    //   $route: {
    //     handler(val) {
    //       console.log(val);
    //     }
    //   }
    // },
    mounted() {
      this.$store.dispatch('getCurrentTable', this.$route.params.id)
      // this.$store.dispatch('getInventory')
    },
    methods: {
      storeOrder() {
        if(this.order.order.length) {
          this.$store.dispatch('storeOrder', this.$route.params.id)
        }
        this.$router.push('/')
      },
      cashOut() {
        if(this.order.order.length) {
          this.$store.dispatch('storeOrder', this.$route.params.id)
            .then(() => {
              this.showCacheOutModal = true
            })
        }
        if(this.orders.length) {
          this.showCacheOutModal = true
        }
      },
      charge() {
        this.$store.dispatch('cashOut', { table_id: this.$route.params.id, status: 1 })
        this.$router.push('/')
      },
      handleTableMenu(item) {
        if(item === 'move') {
          this.showMoveTableModal = true
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

<style scoped>
  .fs-14rem {
    font-size: 1.4rem;
  }
  .OrderSidebar {
    max-height: 79vh;
    overflow-y: scroll;
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

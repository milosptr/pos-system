<template>
  <div class="h-screen flex flex-col justify-between">
    <div class="p-4 pb-0">
      <div class="flex items-center justify-between">
        <div class="text-2xl font-bold  pb-1 uppercase">
          Račun #{{ tableNo }}
        </div>
        <div>
          <img src="/images/dot-menu.svg" alt="submenu" width="32">
        </div>
      </div>
      <div class="OrderSidebar">
        <SingleOrder :order="order" :index="orders.length + 1" :saved="false" />
        <SingleOrder
          v-for="(o, i) in orders"
          :order="o"
          :key="o.id"
          :index="orders.length - i"
          :saved="true"
        />
      </div>
    </div>
    <div class="flex gap-2">
      <div
        class="bg-primary w-1/2 py-5 rounded-sm text-lg uppercase font-bold text-center text-white"
        @click="cashOut()"
         >
        Naplati
      </div>
      <div
        class="bg-green-600 w-1/2 py-5 rounded-sm text-lg uppercase font-bold text-center text-white"
        @click="storeOrder()"
      >
        Sačuvaj
      </div>
    </div>
    <CacheOutModal v-if="showCacheOutModal" @close="showCacheOutModal = false" />
  </div>
</template>

<script>
  import CacheOutModal from '../Modals/CacheOutModal.vue'
  import SingleOrder from './SingleOrder.vue'

  export default {
    components: { SingleOrder, CacheOutModal },
    data: () => ({
     showCacheOutModal: false,
    }),
    computed: {
      tableNo() {
        return this.$store.getters.activeTable?.table_number
      },
      orders() {
        return this.$store.getters.orders
      },
      order() {
        return { order: this.$store.getters.order }
      }
    },
    mounted() {
      this.$store.dispatch('getCurrentTable', this.$route.params.id)
    },
    methods: {
      storeOrder() {
        if(this.order.order.length) {
          this.$store.dispatch('storeOrder', this.$route.params.id)
          this.$router.push('/')
        }
      },
      cashOut() {
        if(this.orders.length) {
          this.showCacheOutModal = true
        }
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

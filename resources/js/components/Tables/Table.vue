<template>
  <div class="h-screen flex flex-col justify-between">
    <div class="p-4">
      <div class="text-2xl font-bold border-b border-gray-200 pb-1 uppercase">
        Račun za sto #{{ tableNo }}
      </div>
      <SingleOrder :order="order" :saved="false" />
      <SingleOrder :order="orders" :saved="true" />
    </div>
    <div class="flex gap-2">
      <div class="bg-primary w-1/2 py-5 rounded-sm text-lg uppercase font-bold text-center text-white">
        Naplati
      </div>
      <div class="bg-green-600 w-1/2 py-5 rounded-sm text-lg uppercase font-bold text-center text-white">
        Sačuvaj
      </div>
    </div>
  </div>
</template>

<script>
import SingleOrder from './SingleOrder.vue'
  export default {
  components: { SingleOrder },
    data: () => ({
    }),
    computed: {
      tableNo() {
        return this.$route.params.id
      },
      orders() {
        return this.$store.getters.orders
      },
      order() {
        return { orders: this.$store.getters.order }
      }
    },
    mounted() {
      this.$store.commit('clearOrder')
      axios.get('/api/orders/table/' + this.$route.params.id)
        .then((res) => {
          if(res.data && res.data.order)
            this.order = res.data.order
        })
    },
  }
</script>

<style scoped>
  .OrderSidebar {
    max-height: 80vh;
    overflow-y: scroll;
  }
</style>

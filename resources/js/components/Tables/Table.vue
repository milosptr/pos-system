<template>
  <div class="h-screen flex flex-col justify-between">
    <div class="p-4">
      <div class="text-2xl font-bold border-b border-gray-200 pb-1 uppercase">
        Račun za sto #{{ tableNo }}
      </div>
      <div class="OrderSidebar mt-4">
        <div class="bg-gray-500 flex justify-between items-center text-white px-2">
          <div>Porudzbina #1</div>
          <div>17:00:00</div>
        </div>
        <div
          v-for="order in orders"
          :key="order.id"
          class="py-1 px-2 text-xl bg-gray-100 mt-2">
          <div class="font-semibold ">{{ order.name }}</div>
          <div class="flex justify-between items-center">
            <div>{{ order.qty}} x {{ order.price }},00</div>
            <div class="font-semibold">{{ order.qty * order.price }} RSD</div>
          </div>
        </div>
      </div>
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
  export default {
    data: () => ({
    }),
    computed: {
      tableNo() {
        return this.$route.params.id
      },
      orders() {
        return this.$store.getters.order
      },
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

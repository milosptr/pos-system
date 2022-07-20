<template>
  <div class="">
    <div class="p-4">
      <div class="text-2xl font-bold border-b border-gray-200 pb-1 uppercase">
        Račun za sto #{{ tableNo }}
      </div>
      <div class="mt-4">
        <div class="bg-gray-500 flex justify-between items-center text-white px-2">
          <div>Porudzbina #1</div>
          <div>17:00:00</div>
        </div>
        <div class="flex justify-between items-center py-2 px-2 text-xl">
          <div class="font-semibold">Pileći medaljoni sa pečurkama</div>
          <div class="font-bold">180,00</div>
        </div>
        <div class="flex justify-between items-center py-2 px-2 text-xl">
          <div class="font-semibold">Krušovice toč. 0,5</div>
          <div class="font-bold">180,00</div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
  export default {
    data: () => ({
      order: []
    }),
    computed: {
      tableNo() {
        return this.$route.params.id
      }
    },
    mounted() {
      axios.get('/api/orders/table/' + this.$route.params.id)
        .then((res) => {
          if(res.data && res.data.order)
            this.order = res.data.order
        })
    }
  }
</script>

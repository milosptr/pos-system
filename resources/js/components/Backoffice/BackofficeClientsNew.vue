<template>
  <div class="mt-4">
    <div class="grid w-full grid-cols-1 gap-4 md:grid-cols-3 xl:w-2/3">
      <div class="col-span-2 w-full">
        <label
          for="name"
          class="block text-sm font-medium text-gray-700"
          >Ime</label
        >
        <div class="mt-1">
          <input
            v-model="item.name"
            type="text"
            name="name"
            id="name"
            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            placeholder="Client name" />
        </div>
      </div>
      <div class="w-full">
        <label
          for="discount"
          class="block text-sm font-medium text-gray-700"
          >Popust</label
        >
        <div class="relative mt-1">
          <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
            <span class="text-gray-500 sm:text-sm">%</span>
          </div>
          <input
            v-model="item.discount"
            type="number"
            name="discount"
            id="discount"
            class="block w-full rounded-md border-gray-300 pl-7 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            placeholder="0" />
        </div>
      </div>
    </div>
    <div
      class="mt-12 flex-shrink-0"
      @click="save">
      <button
        type="button"
        class="relative inline-flex items-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
        Sačuvaj klijenta
      </button>
    </div>
  </div>
</template>

<script>
export default {
  data: () => ({
    item: {
      discount: 0
    },
    error: false
  }),
  methods: {
    save() {
      axios.post('/api/backoffice/clients', this.item).then((res) => {
        this.$store.dispatch('getClients', {})
        this.$router.push('/clients')
      })
    }
  }
}
</script>

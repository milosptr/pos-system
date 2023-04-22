<template>
  <div class="mt-4">
    <div class="w-full xl:w-2/3 grid grid-cols-1 md:grid-cols-3 gap-4">
      <div class="w-full col-span-2">
        <label for="name" class="block text-sm font-medium text-gray-700">Ime</label>
        <div class="mt-1">
          <input v-model="item.name" type="text" name="name" id="name" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md" placeholder="Client name" />
        </div>
      </div>
      <div class="w-full">
        <label for="discount" class="block text-sm font-medium text-gray-700">Popust</label>
        <div class="relative mt-1">
          <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
            <span class="text-gray-500 sm:text-sm">%</span>
          </div>
          <input v-model="item.discount" type="number" name="discount" id="discount" class="shadow-sm pl-7 focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md" placeholder="0" />
        </div>
      </div>
    </div>
    <div class="mt-12 flex-shrink-0" @click="save">
      <button type="button" class="relative inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">Sačuvaj klijenta</button>
    </div>
  </div>
</template>

<script>
  export default {
    data: () => ({
      item: {
        discount: 0,
      },
      error: false,
    }),
    methods: {
      save() {
        axios.post('/api/backoffice/clients', this.item)
          .then((res) => {
            this.$store.dispatch('getClients', {})
            this.$router.push('/clients')
          })
      }
    }
  }
</script>

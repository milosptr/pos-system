<template>
  <div class="mt-4">
    <div class="grid w-full grid-cols-1 gap-4 xl:w-1/2">
      <div>
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
            placeholder="Ime kategorije" />
        </div>
      </div>
      <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div>
          <label
            for="location"
            class="block text-sm font-medium text-gray-700"
            >Grupa
            <span
              v-if="error"
              class="text-red-500">
              - obavezno
            </span>
          </label>
          <select
            v-model.number="item.parent_id"
            id="location"
            name="location"
            class="mt-1 block w-full rounded-md border-gray-300 py-2 pl-3 pr-10 text-base focus:border-indigo-500 focus:outline-none focus:ring-indigo-500 sm:text-sm">
            <option
              value="0"
              selected>
              Šank
            </option>
            <option value="1">Kuhinja</option>
          </select>
        </div>
        <div>
          <label
            for="location"
            class="block text-sm font-medium text-gray-700"
            >Red</label
          >
          <select
            v-model.number="item.order"
            id="location"
            name="location"
            class="mt-1 block w-full rounded-md border-gray-300 py-2 pl-3 pr-10 text-base focus:border-indigo-500 focus:outline-none focus:ring-indigo-500 sm:text-sm">
            <option selected>0</option>
            <option
              v-for="(n, idx) in [...Array(25).keys()]"
              :key="idx"
              :value="idx + 1">
              {{ idx + 1 }}
            </option>
          </select>
        </div>
      </div>
    </div>
    <div
      class="mt-12 flex-shrink-0"
      @click="save">
      <button
        type="button"
        class="relative inline-flex items-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
        Sačuvaj kategoriju
      </button>
    </div>
  </div>
</template>

<script>
export default {
  data: () => ({
    categories: [],
    item: {
      order: 0,
      print: 1,
      parent_id: 0
    },
    error: false
  }),
  mounted() {
    axios.get('/api/categories').then((res) => {
      this.categories = res.data.data
    })
  },
  methods: {
    save() {
      axios.post('/api/backoffice/categories', this.item).then((res) => {
        this.$store.dispatch('getCategories', {})
        this.$router.push('/categories')
      })
    }
  }
}
</script>

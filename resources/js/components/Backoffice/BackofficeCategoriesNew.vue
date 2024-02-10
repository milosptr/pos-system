<template>
  <div class="mt-4">
    <div class="w-full xl:w-1/2 grid grid-cols-1 gap-4">
      <div
        class="flex items-center gap-4 cursor-pointer"
        @click="handleIsWarehouse">
        <Switch :enabled="!!warehouse" />
        <div class="font-medium text-gray-700">Kategorija za sirovinu</div>
      </div>
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
            class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"
            placeholder="Ime kategorije" />
        </div>
      </div>
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
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
            class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
            <option
              value="0"
              selected>
              Šank
            </option>
            <option value="1">Kuhinja</option>
          </select>
        </div>
        <div v-if="!warehouse">
          <label
            for="location"
            class="block text-sm font-medium text-gray-700"
            >Red</label
          >
          <select
            v-model.number="item.order"
            id="location"
            name="location"
            class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
            <option selected>0</option>
            <option
              v-for="(_, idx) in [...Array(25).keys()]"
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
        class="relative inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
        Sačuvaj kategoriju
      </button>
    </div>
  </div>
</template>

<script>
import { SwitchLabel } from '@headlessui/vue'
import Switch from '@/js/components/common/Switch.vue'

export default {
  components: { Switch, SwitchLabel },
  data: () => ({
    categories: [],
    warehouse: false,
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
      const url = this.warehouse ? '/api/backoffice/warehouse-categories' : '/api/backoffice/categories'
      axios.post(url, this.item).then(() => {
        this.$store.dispatch('getCategories', {})
        this.$router.push('/categories')
      })
    },
    handleIsWarehouse() {
      this.warehouse = !this.warehouse
    }
  }
}
</script>

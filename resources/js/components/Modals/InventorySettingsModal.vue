<template>
  <div class="fixed w-full h-screen left-0 top-0 z-40 flex items-center justify-center">
    <div
      class="bg-black/20 absolute left-0 top-0 w-full h-screen z-40"
      @click="$emit('close')"></div>
    <div class="bg-white rounded-md w-2/3 h-[calc(100vh-20vh)] z-50">
      <div>
        <div class="sm:hidden">
          <label
            for="tabs"
            class="sr-only"
            >Select a tab</label
          >
          <select
            id="tabs"
            name="tabs"
            class="block w-full rounded-md border-gray-300 py-2 pl-3 pr-10 text-base focus:border-indigo-500 focus:outline-none focus:ring-indigo-500 sm:text-sm">
            <option
              v-for="tab in tabs"
              :key="tab.name"
              :selected="tab.current">
              {{ tab.name }}
            </option>
          </select>
        </div>
        <div class="hidden sm:block">
          <div class="border-b border-gray-200 px-4">
            <nav
              class="-mb-px flex space-x-8"
              aria-label="Tabs">
              <div
                v-for="tab in tabs"
                :key="tab.name"
                @click="changeTab(tab)"
                :class="[
                  tab.current
                    ? 'border-indigo-500 text-indigo-600'
                    : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700',
                  'whitespace-nowrap border-b-2 py-4 px-1 text-sm font-medium'
                ]"
                :aria-current="tab.current ? 'page' : undefined">
                {{ tab.name }}
              </div>
            </nav>
          </div>
        </div>
      </div>
      <!--   Tabs     -->
      <div class="p-4">
        <div class="mb-4">
          Proizvod: <strong>{{ item.name }}</strong>
        </div>
        <div
          v-if="currentTab === 'Magacin'"
          class="grid grid-cols-3 gap-4 items-end">
          <div>
            <div>Sirovina</div>
            <select
              v-model="selectedWarehouse"
              class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md uppercase">
              <option
                v-for="item in warehouse"
                :key="item.id"
                :value="item.id">
                {{ item.name }}
              </option>
            </select>
          </div>
          <div>
            <div>Normativ</div>
            <input
              type="text"
              @input="updateUnit"
              :value="unit"
              class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"
              placeholder="Normativ" />
          </div>
          <div class="flex items-center gap-4">
            <button
              type="button"
              class="relative inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
              @click="saveWarehouse">
              Sačuvaj
            </button>
            <button
              v-if="warehouse_inventory_id"
              type="button"
              class="relative inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
              @click="deleteWarehouse">
              Obriši
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  props: ['item'],
  data: () => ({
    tabs: [{ name: 'Magacin', current: true }],
    warehouse: [],
    warehouse_inventory_id: null,
    selectedWarehouse: null,
    unit: null
  }),
  computed: {
    currentTab() {
      return this.tabs.find((tab) => tab.current).name
    }
  },
  mounted() {
    axios.get('/api/backoffice/warehouse').then((res) => {
      this.warehouse = res.data.data
    })

    axios.get('/api/backoffice/warehouse-inventory/inventory/' + this.item.id).then((res) => {
      if (res.data) {
        this.warehouse_inventory_id = res.data.id
        this.selectedWarehouse = res.data.warehouse_id
        this.unit = res.data.norm
      }
    })
  },
  methods: {
    updateUnit(e) {
      let value = e.target.value
      value = parseFloat(value.replaceAll(',', '.')) || 0
      this.unit = value
    },
    changeTab(tab) {
      this.tabs.forEach((tab) => (tab.current = false))
      tab.current = true
    },
    saveWarehouse() {
      if (!this.selectedWarehouse || !this.unit || !this.item?.id) {
        alert('Morate popuniti sva polja')
        return
      }
      const data = {
        inventory_id: this.item.id,
        warehouse_id: this.selectedWarehouse,
        norm: this.unit
      }
      axios
        .post('/api/backoffice/warehouse-inventory', data)
        .then(() => {
          this.$emit('close')
        })
        .catch((err) => {
          alert('Došlo je do greške')
          console.log(err)
        })
    },
    deleteWarehouse() {
      axios
        .delete('/api/backoffice/warehouse-inventory/' + this.warehouse_inventory_id)
        .then(() => {
          this.$emit('close')
        })
        .catch((err) => {
          alert('Došlo je do greške')
          console.log(err)
        })
    }
  }
}
</script>

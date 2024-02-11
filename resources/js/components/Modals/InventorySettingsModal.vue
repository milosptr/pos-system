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
          class="grid grid-cols-1 gap-4 items-end">
          <div
            v-for="wi in warehouseInventory"
            :key="wi.key"
            class="grid grid-cols-2 gap-4">
            <div :class="{ 'opacity-20': wi?.deleted }">
              <div>Sirovina</div>
              <select
                v-model="wi.warehouse_id"
                :disabled="wi.deleted"
                class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md uppercase">
                <option
                  v-for="item in warehouse"
                  :key="item.id"
                  :value="item.id">
                  {{ item.name }}
                </option>
              </select>
            </div>
            <div class="flex items-end gap-3">
              <div
                class="w-full"
                :class="{ 'opacity-20': wi?.deleted }">
                <div>Normativ</div>
                <input
                  type="text"
                  @input="updateUnit($event, wi)"
                  :value="wi.norm"
                  :disabled="wi.deleted"
                  class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"
                  placeholder="Normativ" />
              </div>
              <div
                v-if="!wi.deleted"
                @click="wi.deleted = true"
                class="text-red-500 px-4 py-1.5 border border-solid border-red-500 rounded-md hover:bg-red-500 hover:text-white">
                Obriši
              </div>
              <div
                v-if="wi.deleted"
                @click="wi.deleted = false"
                class="text-blue-500 px-4 py-1.5 border border-solid border-blue-500 rounded-md hover:bg-blue-500 hover:text-white">
                Povrati
              </div>
            </div>
          </div>
          <div class="flex items-center justify-between gap-4">
            <button
              type="button"
              class="relative inline-flex items-center px-4 py-0.5 border border-transparent shadow-sm text-2xl rounded-md text-gray-500 hover:text-gray-600 bg-gray-200 hover:bg-gray-300 ring-0"
              @click="addNew">
              +
            </button>
            <button
              type="button"
              class="relative inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
              @click="saveWarehouse">
              Sačuvaj izmene
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
    warehouseInventory: [
      {
        key: crypto.randomUUID(),
        warehouse_id: null,
        norm: null,
        deleted: false
      }
    ]
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
        this.warehouseInventory = res.data.map((item) => ({
          key: crypto.randomUUID(),
          id: item.id,
          warehouse_id: item.warehouse_id,
          norm: item.norm,
          deleted: false
        }))
      }
    })
  },
  methods: {
    addNew() {
      this.warehouseInventory.push({
        key: crypto.randomUUID(),
        warehouse_id: null,
        norm: null,
        deleted: false
      })
    },
    updateUnit(e, wi) {
      let value = e.target.value
      value = parseFloat(value.replaceAll(',', '.')) || 0
      this.warehouseInventory = this.warehouseInventory.map((item) => {
        if (item.key === wi.key) {
          item.norm = value
        }
        return item
      })
    },
    changeTab(tab) {
      this.tabs.forEach((tab) => (tab.current = false))
      tab.current = true
    },
    saveWarehouse() {
      const data = this.warehouseInventory.map((item) => ({
        id: item?.id ?? null,
        warehouse_id: item.warehouse_id,
        norm: item.norm,
        deleted: item.deleted,
        inventory_id: this.item.id
      }))

      axios
        .post('/api/backoffice/warehouse-inventory', data)
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

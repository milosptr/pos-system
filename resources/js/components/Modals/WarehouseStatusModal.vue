<template>
  <div class="pb-8 mb-8 border-b border-solid border-gray-300 text-sm">
    <div class="text-xl font-semibold mb-4">Dodaj novo stanje</div>
    <div class="grid grid-cols-4 gap-4 items-end">
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
        <div>Kolicina</div>
        <input
          type="text"
          @input="updateUnit"
          :value="unit"
          class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"
          placeholder="Kolicina" />
      </div>
      <div>
        <div>Za datum</div>
        <input
          type="date"
          v-model="date"
          :max="new Date().toISOString().split('T')[0]"
          class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md" />
      </div>
      <button
        type="button"
        class="relative text-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
        @click="saveWarehouse">
        Uvezi
      </button>
    </div>
  </div>
</template>

<script>
export default {
  data: () => ({
    warehouse: [],
    selectedWarehouse: null,
    unit: 0,
    type: 0,
    date: new Date()
  }),
  mounted() {
    this.getWarehouse()
    this.date = new Date().toISOString().split('T')[0]
  },
  methods: {
    getWarehouse() {
      axios.get('/api/backoffice/warehouse').then((response) => {
        this.warehouse = response.data.data
      })
    },
    updateUnit(e) {
      let value = e.target.value
      value = parseFloat(value.replaceAll(',', '.')) || 0
      this.unit = value
    },
    saveWarehouse() {
      axios
        .post('/api/backoffice/warehouse-status', {
          warehouse_id: this.selectedWarehouse,
          quantity: this.unit,
          type: 0,
          comment: 'Manual entry',
          created_at: this.date
        })
        .then(() => {
          this.$emit('close')
          this.$emit('update')
        })
    }
  }
}
</script>

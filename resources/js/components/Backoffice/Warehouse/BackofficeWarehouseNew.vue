<template>
  <div class="pb-8 mb-8 border-b border-solid border-gray-300 text-sm">
    <h1 class="text-2xl font-bold">Nova Sirovina</h1>
    <div class="grid grid-cols-4 gap-4 mt-4">
      <div class="col-span-2">
        <div>Sirovina</div>
        <input
          type="text"
          v-model="name"
          class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"
          placeholder="Sirovina" />
      </div>
      <div>
        <div>Jedinica mere</div>
        <select
          v-model="unit"
          class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md uppercase">
          <option
            value=""
            disabled
            selected>
            Izaberite jedinicu mere
          </option>
          <option
            v-for="unit in units"
            :key="unit.name"
            :value="unit.name">
            {{ unit.name }}
          </option>
        </select>
      </div>
      <div>
        <div>Kategorija</div>
        <select
          v-model="category"
          class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md uppercase">
          <option
            v-for="cat in categories"
            :key="cat.id"
            :value="cat.id">
            {{ cat.name }}
          </option>
        </select>
      </div>
    </div>
    <div>
      <button
        class="relative inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 mt-5"
        @click="save">
        Sačuvaj
      </button>
    </div>
  </div>
</template>

<script>
export default {
  name: 'BackofficeWarehouseNew',
  data: () => ({
    name: null,
    unit: null,
    category: null,
    categories: [],
    units: [
      {
        name: 'KG'
      },
      {
        name: 'L'
      },
      {
        name: 'KOM.'
      }
    ]
  }),
  mounted() {
    axios.get('/api/categories').then((res) => {
      this.categories = res.data.data
    })
  },
  methods: {
    validate() {
      return this.name && this.unit && this.category
    },
    save() {
      if (this.validate()) {
        axios
          .post('/api/backoffice/warehouse', {
            name: this.name,
            unit: this.unit,
            category: this.category
          })
          .then(() => {
            this.$emit('saved')
          })
          .catch((error) => {
            console.log(error)
          })
      } else {
        alert('Molimo popunite sva polja')
      }
    }
  }
}
</script>

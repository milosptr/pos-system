<template>
  <div class="mt-4">
    <div class="w-full xl:w-1/2 grid grid-cols-1 gap-4">
      <div>
        <label for="name" class="block text-sm font-medium text-gray-700">Ime</label>
        <div class="mt-1">
          <input v-model="item.name" type="text" name="name" id="name" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md" placeholder="Ime proizvoda" />
        </div>
      </div>
      <div>
        <label for="description" class="block text-sm font-medium text-gray-700">Opis artikla</label>
        <div class="mt-1">
          <input v-model="item.description" type="text" name="description" id="description" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md" placeholder="Opis" />
        </div>
      </div>
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label for="location" class="block text-sm font-medium text-gray-700">Kategorija
            <span v-if="error" class="text-red-500">
              - Kategorija je obavezna
            </span>
          </label>
          <select v-model.number="item.category_id" id="location" name="location" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
            <option disabled>Izaberi kategoriju</option>
            <option v-for="(cat, idx) in categories" :key="idx" :value="cat.id">{{ cat.name }}</option>
          </select>
        </div>
        <div>
          <label for="location" class="block text-sm font-medium text-gray-700">
            Status
          </label>
          <select v-model.number="item.active" id="location" name="location" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
            <option disabled>Izaberi status</option>
           <option value="0">Neaktivan</option>
           <option value="1">Aktivan</option>
          </select>
        </div>
      </div>
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label for="price" class="block text-sm font-medium text-gray-700">Cena</label>
          <div class="mt-1">
            <input v-model.number="item.price" type="number" name="price" id="price" class="shadow-sm appearance-none focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md" placeholder="0" />
          </div>
        </div>
        <div>
          <label for="sku" class="block text-sm font-medium text-gray-700">Šifra</label>
          <div class="mt-1">
            <input v-model="item.sku" @input="maskInput" type="text" name="sku" id="sku" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md" placeholder="" />
          </div>
        </div>
      </div>
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div>
          <label for="unit" class="block text-sm font-medium text-gray-700">Mera</label>
          <div class="mt-1">
            <input v-model="item.unit" type="text" name="unit" id="unit" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md" placeholder="KOM." />
          </div>
        </div>
        <div>
          <label for="location" class="block text-sm font-medium text-gray-700">Jedinica</label>
          <select v-model.number="item.sold_by" id="location" name="location" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
            <option value="0" selected>KOM</option>
            <option value="1">POLA PORCIJE</option>
            <option value="2">KG</option>
          </select>
        </div>
        <div>
          <label for="location" class="block text-sm font-medium text-gray-700">Red</label>
          <select v-model.number="item.order" id="location" name="location" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
            <option selected>0</option>
            <option v-for="(n, idx) in [...Array(25).keys()]" :key="idx" :value="idx + 1"> {{ idx + 1 }}</option>
          </select>
        </div>
      </div>
    </div>
    <div class="mt-12 flex-shrink-0" @click="save">
      <button type="button" class="relative inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">Sačuvaj artikal</button>
    </div>
  </div>
</template>

<script>
  export default {
    data: () => ({
      categories: [],
      item: {
        sold_by: 0,
        order: 0,
        active: 1,
        qty: 1,
        unit: 'KOM.'
      },
      error: false,
    }),

    mounted() {
      axios.get('/api/categories')
        .then( (res) => {
            this.categories = res.data.data
        })
    },
    methods: {
      maskInput() {
      const maxLength = 6;
      let inputValue = this.item.sku.toString();
      inputValue = inputValue.substring(inputValue.length - maxLength);

      let maskedValue = "0".repeat(maxLength);
      maskedValue = maskedValue.slice(0, maxLength - inputValue.length) + inputValue;

      this.item.sku = maskedValue;
    },
      save() {
        if(!this.item.category_id) {
          this.error = true
          return
        }
        axios.post('/api/backoffice/inventory', this.item)
          .then((res) => {
            this.$store.dispatch('getInventory', {})
            this.$router.push('/inventory')
          })
      }
    }
  }
</script>

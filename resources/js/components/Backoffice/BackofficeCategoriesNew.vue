<template>
  <div class="mt-4">
    <div class="w-full xl:w-1/2 grid grid-cols-1 gap-4">
      <div>
        <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
        <div class="mt-1">
          <input v-model="item.name" type="text" name="name" id="name" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md" placeholder="Category name" />
        </div>
      </div>
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label for="location" class="block text-sm font-medium text-gray-700">Parent
            <span v-if="error" class="text-red-500">
              - required
            </span>
          </label>
          <select v-model.number="item.parent_id" id="location" name="location" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
            <option value="0" selected>Šank</option>
            <option value="1">Kuhinja</option>
          </select>
        </div>
        <div>
          <label for="location" class="block text-sm font-medium text-gray-700">Order</label>
          <select v-model.number="item.order" id="location" name="location" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
            <option selected>0</option>
            <option v-for="(n, idx) in [...Array(25).keys()]" :key="idx" :value="idx + 1"> {{ idx + 1 }}</option>
          </select>
        </div>
      </div>
    </div>
    <div class="mt-12 flex-shrink-0" @click="save">
      <button type="button" class="relative inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">Save category</button>
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
        parent_id: 0,
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
      save() {
        axios.post('/api/backoffice/categories', this.item)
          .then((res) => {
            this.$store.dispatch('getCategories', {})
            this.$router.push('/categories')
          })
      }
    }
  }
</script>

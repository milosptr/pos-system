<template>
  <div class="flex flex-col">
      <div class="-my-2 -mx-4 sm:-mx-6 lg:-mx-8">
        <div class="inline-block min-w-full py-2 align-middle">
          <div class="shadow-sm ring-1 ring-black ring-opacity-5">
            <table class="min-w-full border-separate" style="border-spacing: 0">
              <thead class="bg-gray-50">
                <tr>
                  <th scope="col" class="sticky top-0 z-10 border-b border-gray-300 bg-gray-50 bg-opacity-75 py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 backdrop-blur backdrop-filter sm:pl-6 lg:pl-8">Id</th>
                  <th scope="col" class="sticky top-0 z-10 hidden border-b border-gray-300 bg-gray-50 bg-opacity-75 px-3 py-3.5 text-left text-sm font-semibold text-gray-900 backdrop-blur backdrop-filter sm:table-cell">Name</th>
                  <th scope="col" class="sticky top-0 z-10 hidden border-b border-gray-300 bg-gray-50 bg-opacity-75 px-3 py-3.5 text-left text-sm font-semibold text-gray-900 backdrop-blur backdrop-filter sm:table-cell">SKU</th>
                  <th scope="col" class="sticky top-0 z-10 hidden border-b border-gray-300 bg-gray-50 bg-opacity-75 px-3 py-3.5 text-left text-sm font-semibold text-gray-900 backdrop-blur backdrop-filter lg:table-cell">Price</th>
                  <th scope="col" class="sticky top-0 z-10 border-b border-gray-300 bg-gray-50 bg-opacity-75 px-3 py-3.5 text-left text-sm font-semibold text-gray-900 backdrop-blur backdrop-filter">Sold By</th>
                  <th scope="col" class="sticky top-0 z-10 border-b border-gray-300 bg-gray-50 bg-opacity-75 px-3 py-3.5 text-left text-sm font-semibold text-gray-900 backdrop-blur backdrop-filter">Category</th>
                </tr>
              </thead>
              <tbody class="bg-white">
                <tr v-for="(item, idx) in inventory" :key="item.id" class="hover:bg-orange-50 cursor-pointer" :class="{'bg-gray-50': idx % 2 === 1 }">
                  <td :class="[idx !== inventory.length - 1 ? 'border-b border-gray-200' : '', 'whitespace-nowrap py-2 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6 lg:pl-8']">{{ item.id }}</td>
                  <td :class="[idx !== inventory.length - 1 ? 'border-b border-gray-200' : '', 'whitespace-nowrap px-3 py-2 text-sm text-gray-500 hidden sm:table-cell']">{{ item.name }}</td>
                  <td :class="[idx !== inventory.length - 1 ? 'border-b border-gray-200' : '', 'whitespace-nowrap px-3 py-2 text-sm text-gray-500 hidden lg:table-cell']">{{ item.sku }}</td>
                  <td :class="[idx !== inventory.length - 1 ? 'border-b border-gray-200' : '', 'whitespace-nowrap px-3 py-2 text-sm text-gray-500']">{{ item.price }} RSD</td>
                  <td :class="[idx !== inventory.length - 1 ? 'border-b border-gray-200' : '', 'whitespace-nowrap px-3 py-2 text-sm text-gray-500']">{{ soldByText(item.sold_by) }}</td>
                  <td :class="[idx !== inventory.length - 1 ? 'border-b border-gray-200' : '', 'whitespace-nowrap px-3 py-2 text-sm text-gray-500']">{{ item.category_name }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
</template>
<script>
export default {
    name: 'BackofficeInventory',
    computed: {
      inventory() {
        return this.$store.getters.inventory
      }
    },
    mounted() {
        this.$store.dispatch('getInventory')
    },
    methods: {
      soldByText(id) {
        if(id === 1)
          return 'PP'
        if(id === 2)
          return 'GR'
        return 'KOM'
      }
    }
}
</script>
<style scoped>

</style>

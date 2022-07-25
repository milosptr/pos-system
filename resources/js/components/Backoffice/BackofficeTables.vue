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
                <th scope="col" class="sticky top-0 z-10 hidden border-b border-gray-300 bg-gray-50 bg-opacity-75 px-3 py-3.5 text-left text-sm font-semibold text-gray-900 backdrop-blur backdrop-filter sm:table-cell">Location</th>
                <th scope="col" class="sticky top-0 z-10 hidden border-b border-gray-300 bg-gray-50 bg-opacity-75 px-3 py-3.5 text-left text-sm font-semibold text-gray-900 backdrop-blur backdrop-filter sm:table-cell">Table Number</th>
                <th scope="col" class="sticky top-0 z-10 hidden border-b border-gray-300 bg-gray-50 bg-opacity-75 px-3 py-3.5 text-left text-sm font-semibold text-gray-900 backdrop-blur backdrop-filter lg:table-cell">Size</th>
                <th scope="col" class="sticky top-0 z-10 border-b border-gray-300 bg-gray-50 bg-opacity-75 px-3 py-3.5 text-left text-sm font-semibold text-gray-900 backdrop-blur backdrop-filter">Rotate</th>
              </tr>
            </thead>
            <tbody class="bg-white">
              <tr v-for="(item, idx) in tables" :key="item.id"  class="hover:bg-orange-50 cursor-pointer" :class="{'bg-gray-50': idx % 2 === 1 }">
                <td :class="[idx !== tables.length - 1 ? 'border-b border-gray-200' : '', 'whitespace-nowrap py-2 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6 lg:pl-8']">{{ item.id }}</td>
                <td :class="[idx !== tables.length - 1 ? 'border-b border-gray-200' : '', 'whitespace-nowrap px-3 py-2 text-sm text-gray-500 hidden sm:table-cell']">{{ item.name }}</td>
                <td :class="[idx !== tables.length - 1 ? 'border-b border-gray-200' : '', 'whitespace-nowrap px-3 py-2 text-sm text-gray-500 hidden sm:table-cell']">{{ item.area ? 'Bašta' : 'Sala' }}</td>
                <td :class="[idx !== tables.length - 1 ? 'border-b border-gray-200' : '', 'whitespace-nowrap px-3 py-2 text-sm text-gray-500 hidden lg:table-cell']">{{ item.table_number }}</td>
                <td :class="[idx !== tables.length - 1 ? 'border-b border-gray-200' : '', 'whitespace-nowrap px-3 py-2 text-sm text-gray-500']">{{ item.size ? 'Big' : 'Small' }}</td>
                <td :class="[idx !== tables.length - 1 ? 'border-b border-gray-200' : '', 'whitespace-nowrap px-3 py-2 text-sm text-gray-500']">{{ item.rotate ? 'YES' : 'NO' }}</td>
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
    name: "BackofficeTables",
    computed: {
      tables() {
        return this.$store.getters.tables
      },
    },
    mounted(){
      this.$store.dispatch('getTables')
    },

}
</script>
<style scoped>

</style>

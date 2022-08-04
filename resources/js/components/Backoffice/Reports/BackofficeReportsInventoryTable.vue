<template>
  <div class="mt-5">
    <div class="shadow-sm ring-1 ring-black ring-opacity-5">
      <table class="min-w-full border-separate" style="border-spacing: 0">
        <thead class="bg-gray-50">
          <tr>
            <th scope="col" class="sticky top-0 z-10 border-b border-gray-300 bg-gray-50 bg-opacity-75 py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 backdrop-blur backdrop-filter sm:pl-6 lg:pl-8">Id</th>
            <th scope="col" class="sticky top-0 z-10 border-b border-gray-300 bg-gray-50 bg-opacity-75 px-3 py-3.5 text-left text-sm font-semibold text-gray-900 backdrop-blur backdrop-filter sm:table-cell">Name</th>
            <th scope="col" class="sticky top-0 z-10 border-b border-gray-300 bg-gray-50 bg-opacity-75 px-3 py-3.5 text-left text-sm font-semibold text-gray-900 backdrop-blur backdrop-filter sm:table-cell">Category</th>
            <th scope="col" class="sticky top-0 z-10 border-b border-gray-300 bg-gray-50 bg-opacity-75 px-3 py-3.5 text-left text-sm font-semibold text-gray-900 backdrop-blur backdrop-filter lg:table-cell">Price</th>
            <th scope="col" class="sticky top-0 z-10 border-b border-gray-300 bg-gray-50 bg-opacity-75 px-3 py-3.5 text-left text-sm font-semibold text-gray-900 backdrop-blur backdrop-filter">
              QTY
              <span v-if="hasInventoryFilter">
                ({{ sumQty }})
              </span>
            </th>
            <th scope="col" class="sticky top-0 z-10 border-b border-gray-300 bg-gray-50 bg-opacity-75 px-3 py-3.5 text-left text-sm font-semibold text-gray-900 backdrop-blur backdrop-filter">Total</th>
          </tr>
        </thead>
        <tbody class="bg-white">
          <tr v-for="(item, idx) in inventory" :key="item.id" class="hover:bg-orange-50 cursor-pointer" :class="[{'bg-gray-50': idx % 2 === 1}]">
            <td :class="[idx !== inventory.length - 1 ? 'border-b border-gray-200' : '', 'whitespace-nowrap py-2 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6 lg:pl-8', {'text-red-500': item.status === 0 }]">{{ item.id }}</td>
            <td :class="[idx !== inventory.length - 1 ? 'border-b border-gray-200' : '', 'whitespace-nowrap px-3 py-2 text-sm text-gray-500 ', {'text-red-500': item.status === 0 }]">{{ item.name }}</td>
            <td :class="[idx !== inventory.length - 1 ? 'border-b border-gray-200' : '', 'whitespace-nowrap px-3 py-2 text-sm text-gray-500', {'text-red-500': item.status === 0 }]">{{ item.category_name }}</td>
            <td :class="[idx !== inventory.length - 1 ? 'border-b border-gray-200' : '', 'whitespace-nowrap px-3 py-2 text-sm text-gray-500 ', {'text-red-500': item.status === 0 }]">{{ $filters.formatPrice(item.price) }} RSD</td>
            <td :class="[idx !== inventory.length - 1 ? 'border-b border-gray-200' : '', 'whitespace-nowrap px-3 py-2 text-sm text-gray-500', {'text-red-500': item.status === 0 }]">{{ item.qty }}</td>
            <td :class="[idx !== inventory.length - 1 ? 'border-b border-gray-200' : '', 'whitespace-nowrap px-3 py-2 text-sm text-gray-500', {'text-red-500': item.status === 0 }]">{{ $filters.formatPrice(item.price * item.qty) }} RSD</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script>
  export default {
    computed: {
      inventory() {
        return this.$store.getters.reports ? this.$store.getters.reports.sales : []
      },
      hasInventoryFilter() {
        return !!this.$store.getters.reportFilters?.inventory
      },
      sumQty() {
        return this.$store.getters.reports.sales.reduce((a, v) => a + (v.status ? v.qty : 0), 0)
      },
    },
  }
</script>

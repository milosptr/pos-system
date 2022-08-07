<template>
  <div class="mt-5">
    <div class="shadow-sm ring-1 ring-black ring-opacity-5">
      <table class="min-w-full border-separate" style="border-spacing: 0">
        <thead class="bg-gray-50">
          <tr>
            <th scope="col" class="sticky top-0 z-10 border-b border-gray-300 bg-gray-50 bg-opacity-75 px-3 py-3.5 text-left text-sm font-semibold text-gray-900 backdrop-blur backdrop-filter sm:table-cell">Date</th>
            <th scope="col" class="sticky top-0 z-10 border-b border-gray-300 bg-gray-50 bg-opacity-75 px-3 py-3.5 text-left text-sm font-semibold text-gray-900 backdrop-blur backdrop-filter sm:table-cell">Total</th>
            <th scope="col" class="sticky top-0 z-10 border-b border-gray-300 bg-gray-50 bg-opacity-75 px-3 py-3.5 text-left text-sm font-semibold text-gray-900 backdrop-blur backdrop-filter sm:table-cell">Refunded</th>
            <th scope="col" class="sticky top-0 z-10 border-b border-gray-300 bg-gray-50 bg-opacity-75 px-3 py-3.5 text-left text-sm font-semibold text-gray-900 backdrop-blur backdrop-filter lg:table-cell">Income</th>
          </tr>
        </thead>
        <tbody class="bg-white">
          <tr v-for="(item, idx) in invoices" :key="item.id" class="hover:bg-orange-50 cursor-pointer" :class="[{'bg-gray-50': idx % 2 === 1}]">
            <td :class="[idx !== invoices.length - 1 ? 'border-b border-gray-200' : '', 'whitespace-nowrap px-3 py-2 text-sm text-gray-500 font-medium']">{{ formatDate(item.date) }}</td>
            <td :class="[idx !== invoices.length - 1 ? 'border-b border-gray-200' : '', 'whitespace-nowrap px-3 py-2 text-sm text-gray-500 ']">{{ $filters.formatPrice(item.total) }} RSD</td>
            <td :class="[idx !== invoices.length - 1 ? 'border-b border-gray-200' : '', 'whitespace-nowrap px-3 py-2 text-sm text-gray-500', {'text-red-500': item.refund > 0 }]">{{ $filters.formatPrice(item.refund) }} RSD</td>
            <td :class="[idx !== invoices.length - 1 ? 'border-b border-gray-200' : '', 'whitespace-nowrap px-3 py-2 text-sm text-gray-900 font-medium']">{{ $filters.formatPrice(item.income) }} RSD</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script>
  export default {
    computed: {
      invoices() {
        return this.$store.getters.reports ? this.$store.getters.reports.invoices : []
      },
    },
    methods: {
      formatDate(date) {
        return dayjs(date).format('DD MMM YYYY')
      }
    }
  }
</script>

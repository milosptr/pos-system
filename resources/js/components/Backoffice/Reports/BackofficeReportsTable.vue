<template>
  <div class="mt-5">
    <div class="shadow-sm ring-1 ring-black ring-opacity-5">
      <table
        class="min-w-full border-separate"
        style="border-spacing: 0">
        <thead class="bg-gray-50">
          <tr>
            <th
              scope="col"
              class="sticky top-0 z-10 border-b border-gray-300 bg-gray-50 bg-opacity-75 px-3 py-3.5 text-left text-sm font-semibold text-gray-900 backdrop-blur backdrop-filter sm:table-cell">
              Datum
            </th>
            <th
              scope="col"
              class="sticky top-0 z-10 border-b border-gray-300 bg-gray-50 bg-opacity-75 px-3 py-3.5 text-left text-sm font-semibold text-gray-900 backdrop-blur backdrop-filter sm:table-cell">
              Ukupno
            </th>
            <th
              scope="col"
              class="sticky top-0 z-10 border-b border-gray-300 bg-gray-50 bg-opacity-75 px-3 py-3.5 text-left text-sm font-semibold text-gray-900 backdrop-blur backdrop-filter sm:table-cell">
              Na račun kuće
            </th>
            <th
              scope="col"
              class="sticky top-0 z-10 border-b border-gray-300 bg-gray-50 bg-opacity-75 px-3 py-3.5 text-left text-sm font-semibold text-gray-900 backdrop-blur backdrop-filter sm:table-cell">
              Stornirano
            </th>
            <th
              scope="col"
              class="sticky top-0 z-10 border-b border-gray-300 bg-gray-50 bg-opacity-75 px-3 py-3.5 text-left text-sm font-semibold text-gray-900 backdrop-blur backdrop-filter lg:table-cell">
              Prihod
            </th>
          </tr>
        </thead>
        <tbody class="bg-white">
          <tr
            v-for="(item, idx) in invoices"
            :key="item.id"
            class="cursor-pointer hover:bg-orange-50"
            :class="[{ 'bg-gray-50': idx % 2 === 1 }]">
            <td
              :class="[
                idx !== invoices.length - 1 ? 'border-b border-gray-200' : '',
                'whitespace-nowrap px-3 py-2 text-sm font-medium text-gray-500'
              ]">
              {{ formatDate(item.date) }}
            </td>
            <td
              :class="[
                idx !== invoices.length - 1 ? 'border-b border-gray-200' : '',
                'whitespace-nowrap px-3 py-2 text-sm text-gray-500 '
              ]">
              {{ $filters.formatPrice(item.total) }} RSD
            </td>
            <td
              :class="[
                idx !== invoices.length - 1 ? 'border-b border-gray-200' : '',
                'whitespace-nowrap px-3 py-2 text-sm text-gray-500 ',
                { 'text-indigo-500': item.onthehouse > 0 }
              ]">
              {{ $filters.formatPrice(item.onthehouse) }} RSD
            </td>
            <td
              :class="[
                idx !== invoices.length - 1 ? 'border-b border-gray-200' : '',
                'whitespace-nowrap px-3 py-2 text-sm text-gray-500',
                { 'text-red-500': item.refund > 0 }
              ]">
              {{ $filters.formatPrice(item.refund) }} RSD
            </td>
            <td
              :class="[
                idx !== invoices.length - 1 ? 'border-b border-gray-200' : '',
                'whitespace-nowrap px-3 py-2 text-sm font-medium text-gray-900'
              ]">
              {{ $filters.formatPrice(item.income) }} RSD
            </td>
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
    }
  },
  methods: {
    formatDate(date) {
      return dayjs(date).format('DD MMM YYYY')
    }
  }
}
</script>

<template>
  <div class="flex flex-col">
      <BackofficeThirdPartyInvoicesFilters />
      <div class="  w-full sm:w-auto overflow-x-scroll lg:overflow-x-auto mt-5">
        <div class="inline-block min-w-full py-2 align-middle">
          <div class="shadow-sm ring-1 ring-black ring-opacity-5">
            <table class="min-w-full border-separate" style="border-spacing: 0">
              <thead class="bg-gray-50">
                <tr>
                  <th scope="col" class="sticky top-0 z-10 border-b border-gray-300 bg-gray-50 bg-opacity-75 py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 backdrop-blur backdrop-filter sm:pl-6 lg:pl-8">Racun ID</th>
                  <th scope="col" class="sticky top-0 z-10 border-b border-gray-300 bg-gray-50 bg-opacity-75 px-3 py-3.5 text-left text-sm font-semibold text-gray-900 backdrop-blur backdrop-filter sm:table-cell">Ukupno</th>
                  <th scope="col" class="sticky top-0 z-10 border-b border-gray-300 bg-gray-50 bg-opacity-75 px-3 py-3.5 text-left text-sm font-semibold text-gray-900 backdrop-blur backdrop-filter sm:table-cell">Sto</th>
                  <th scope="col" class="sticky top-0 z-10 border-b border-gray-300 bg-gray-50 bg-opacity-75 px-3 py-3.5 text-left text-sm font-semibold text-gray-900 backdrop-blur backdrop-filter lg:table-cell">Status</th>
                  <th scope="col" class="sticky top-0 z-10 border-b border-gray-300 bg-gray-50 bg-opacity-75 px-3 py-3.5 text-left text-sm font-semibold text-gray-900 backdrop-blur backdrop-filter">Nacin Placanja</th>
                  <th scope="col" class="sticky top-0 z-10 border-b border-gray-300 bg-gray-50 bg-opacity-75 px-3 py-3.5 text-left text-sm font-semibold text-gray-900 backdrop-blur backdrop-filter">Datum</th>
                </tr>
              </thead>
              <tbody class="bg-white">
                <tr
                  v-for="(item, idx) in invoices"
                  :key="item.id"
                  class="hover:bg-orange-50 cursor-pointer text-gray-500"
                  :class="[{'bg-gray-50': idx % 2 === 1}, {'bg-orange-100': activeOrder && activeOrder.id === item.id}, {'text-red-500': item.status === 0 }, {'text-indigo-500': item.status === 2 }]"
                  @click="$store.commit('setActiveOrder', {...item, orders: [item] })"
                >
                  <td :class="[idx !== invoices.length - 1 ? 'border-b border-gray-200' : '', 'whitespace-nowrap py-2 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6 lg:pl-8',{'text-red-500': item.status === 0 }, {'text-indigo-500': item.status === 2 }]">{{ item.external_invoice_id }}</td>
                  <td :class="[idx !== invoices.length - 1 ? 'border-b border-gray-200' : '', 'whitespace-nowrap px-3 py-2 text-sm ', {'text-orange-500': item.discount }]">
                    <span>{{ $filters.formatPrice(item.total) }} RSD</span>
                    <span v-if="item.discount"> ({{ item.discount }}% discount)</span>
                  </td>
                  <td :class="[idx !== invoices.length - 1 ? 'border-b border-gray-200' : '', 'whitespace-nowrap px-3 py-2 text-sm ']">{{ item.table_name }}</td>
                  <td :class="[idx !== invoices.length - 1 ? 'border-b border-gray-200' : '', 'whitespace-nowrap px-3 py-2 text-sm']">
                    {{ statusText(item.status) }}
                  </td>
                  <td :class="[idx !== invoices.length - 1 ? 'border-b border-gray-200' : '', 'whitespace-nowrap px-3 py-2 text-sm']">{{ paymentTypeText(item.payment_type) }}</td>
                  <td :class="[idx !== invoices.length - 1 ? 'border-b border-gray-200' : '', 'whitespace-nowrap px-3 py-2 text-sm']">{{ item.created_at }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
      <BackofficeThirdPartyInvoicesPagination />
      <OverviewSlideoverSidebar :isInvoice="true" :isThirdPartyInvoice="true" />
    </div>
</template>
<script>
import OverviewSlideoverSidebar from './Overview/OverviewSlideoverSidebar.vue'
import BackofficeThirdPartyInvoicesFilters from "./BackofficeThirdPartyInvoicesFilters.vue"
import BackofficeThirdPartyInvoicesPagination from "./BackofficeThirdPartyInvoicesPagination.vue"
export default {
  components: { OverviewSlideoverSidebar, BackofficeThirdPartyInvoicesFilters, BackofficeThirdPartyInvoicesPagination },
    name: "BackofficeThirdPartyInvoices",
    computed: {
      invoices() {
        return this.$store.getters.thirdPartyInvoices
      },
      activeOrder() {
        return this.$store.getters.activeOrder
      },
    },
    mounted() {
      const key = 'paginate'
      const value = true
      this.$store.commit('setReportFilters', { key, value })
      this.$store.dispatch('getThirdPartyInvoices')
    },
    methods: {
      statusText(status) {
        if(status === 0)
          return 'Stornirano'
        if(status === 2)
          return 'Na racun kuce'
        return 'Naplaceno'
      },
      paymentTypeText(paymentType) {
        if(paymentType === 1)
          return 'Gotovina'
        if(paymentType === 2)
          return 'Kartica'
        if(paymentType === 3)
          return 'Prenos'
        if(paymentType === 4)
          return 'Kasa I'
        return '-'
      },
    },
    unmounted() {
        const key = 'paginate'
       this.$store.commit('setReportFilters', { key })
    }
}
</script>
<style scoped>

</style>

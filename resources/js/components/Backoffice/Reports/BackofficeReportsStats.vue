<template>
  <div>
      <dl class="mt-10 grid grid-cols-1 rounded-lg bg-white overflow-hidden shadow divide-y divide-gray-200 md:grid-cols-3 md:divide-y-0 md:divide-x">
        <div v-if="tabInvoices" class="px-4 py-5 sm:px-6">
          <dt class="text-base font-normal text-gray-900">
            Total
          </dt>
          <dd class="flex flex-col xl:flex-row justify-between items-baseline md:block lg:flex">
            <div
              class="flex flex-col lg:flex-row items-baseline text-2xl font-semibold mb-2 xl:mb-0 text-gray-700 active:opacity-50 cursor-pointer"
              @click="copyToClipBoard(reportsStat.total)"
            >
              {{ $filters.formatPrice(reportsStat?.total) }} RSD
            </div>
          </dd>
        </div>
        <div v-if="tabInvoices" class="px-4 py-5 sm:px-6">
          <dt class="text-base font-normal text-gray-900">
            Refunded
          </dt>
          <dd class="flex flex-col xl:flex-row justify-between items-baseline md:block lg:flex">
            <div
              class="flex flex-col lg:flex-row items-baseline text-2xl font-semibold mb-2 xl:mb-0 text-red-500 active:opacity-50 cursor-pointer"
              @click="copyToClipBoard(reportsStat.refund)"
            >
              {{ $filters.formatPrice(reportsStat?.refund) }} RSD
            </div>
          </dd>
        </div>
        <div class="px-4 py-5 sm:px-6">
          <dt class="text-base font-normal text-gray-900">
            Income
          </dt>
          <dd class="flex flex-col xl:flex-row justify-between items-baseline md:block lg:flex">
            <div
              class="flex flex-col lg:flex-row items-baseline text-2xl font-semibold mb-2 xl:mb-0 text-indigo-500 active:opacity-50 cursor-pointer"
              @click="copyToClipBoard(reportsStat.income)"
            >
              {{ $filters.formatPrice(reportsStat?.income) }} RSD
            </div>
          </dd>
        </div>
      </dl>
  </div>
</template>

<script>
  export default {
    computed: {
      reportsStat() {
        return this.$store.getters.reports?.stats
      },
      tabInvoices() {
        return !this.$store.getters.reportsActiveTab
      },
    },
    methods: {
       copyToClipBoard(text){
        this.$copyText(text.toString())
      },
    }
  }
</script>

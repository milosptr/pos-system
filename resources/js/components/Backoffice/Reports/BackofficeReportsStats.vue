<template>
  <div>
      <dl
        class="mt-10 grid grid-cols-1 rounded-lg bg-white overflow-hidden shadow divide-y divide-gray-200 md:divide-y-0 md:divide-x"
        :class="[tabInvoices && reportsStat?.onthehouse && parseInt(reportsStat.onthehouse) ? 'md:grid-cols-4' : 'md:grid-cols-3']"
      >
        <div v-if="tabInvoices" class="px-4 py-5 sm:px-6">
          <dt class="text-base font-normal text-gray-900">
            Ukupno
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
        <div v-if="tabInvoices && reportsStat?.onthehouse && parseInt(reportsStat.onthehouse)" class="px-4 py-5 sm:px-6">
          <dt class="text-base font-normal text-gray-900">
            Na račun kuće
          </dt>
          <dd class="flex flex-col xl:flex-row justify-between items-baseline md:block lg:flex">
            <div
              class="flex flex-col lg:flex-row items-baseline text-2xl font-semibold mb-2 xl:mb-0 active:opacity-50 cursor-pointer"
              @click="copyToClipBoard(reportsStat.onthehouse)"
            >
              {{ $filters.formatPrice(reportsStat?.onthehouse) }} RSD
            </div>
          </dd>
        </div>
        <div v-if="tabInvoices" class="px-4 py-5 sm:px-6">
          <dt class="text-base font-normal text-gray-900">
            Stornirano
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
            Prihod
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

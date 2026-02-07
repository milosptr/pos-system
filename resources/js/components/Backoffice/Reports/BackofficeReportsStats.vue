<template>
  <div>
      <dl
        class="mt-10 grid grid-cols-1 rounded-lg bg-white overflow-hidden shadow divide-y divide-gray-200 md:divide-y-0 md:divide-x md:grid-cols-6"
      >
        <div v-if="tabInvoices" class="px-4 py-5">
          <dt class="text-base font-normal text-gray-900">
            Gotovina
          </dt>
          <dd class="flex flex-col xl:flex-row justify-between items-baseline md:block lg:flex">
            <div
              class="flex flex-col lg:flex-row items-baseline text-2xl font-semibold mb-2 xl:mb-0 text-gray-700 active:opacity-50 cursor-pointer"
              @click="copyToClipBoard(reportsStat.gotovina)"
            >
            {{ $filters.formatPrice(reportsStat?.gotovina) }} RSD
            </div>
          </dd>
        </div>
        <div v-if="tabInvoices" class="px-4 py-5">
          <dt class="text-base font-normal text-gray-900">
            Kartica
          </dt>
          <dd class="flex flex-col xl:flex-row justify-between items-baseline md:block lg:flex">
            <div
              class="flex flex-col lg:flex-row items-baseline text-2xl font-semibold mb-2 xl:mb-0 text-gray-700 active:opacity-50 cursor-pointer"
              @click="copyToClipBoard(reportsStat.kartica)"
            >
            {{ $filters.formatPrice(reportsStat?.kartica) }} RSD
            </div>
          </dd>
        </div>
        <div v-if="tabInvoices" class="px-4 py-5">
          <dt class="text-base font-normal text-gray-900">
            Prenos na račun
          </dt>
          <dd class="flex flex-col xl:flex-row justify-between items-baseline md:block lg:flex">
            <div
              class="flex flex-col lg:flex-row items-baseline text-2xl font-semibold mb-2 xl:mb-0 text-gray-700 active:opacity-50 cursor-pointer"
              @click="copyToClipBoard(reportsStat.prenos)"
            >
            {{ $filters.formatPrice(reportsStat?.prenos) }} RSD
            </div>
          </dd>
        </div>
        <div v-if="tabInvoices" class="px-4 py-5">
          <dt class="text-base font-normal text-gray-900">
            Kasa I
          </dt>
          <dd class="flex flex-col xl:flex-row justify-between items-baseline md:block lg:flex">
            <div
              class="flex flex-col lg:flex-row items-baseline text-2xl font-semibold mb-2 xl:mb-0 text-gray-700 active:opacity-50 cursor-pointer"
              @click="copyToClipBoard(reportsStat.kasa_i)"
            >
            {{ $filters.formatPrice(reportsStat?.kasa_i) }} RSD
            </div>
          </dd>
        </div>
        <div v-if="tabInvoices" class="px-4 py-5">
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
        <div class="px-4 py-5">
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
      visibleCount() {
        if (!this.tabInvoices) return 1
        let count = 5 // gotovina, kartica, prenos, kasa_i, prihod
        if (this.reportsStat?.onthehouse && parseInt(this.reportsStat.onthehouse)) count++
        return count
      },
    },
    methods: {
       copyToClipBoard(text){
        this.$copyText(text.toString())
      },
    }
  }
</script>

<template>
  <div>
    <dl
      class="mt-10 grid grid-cols-1 divide-y divide-gray-200 overflow-hidden rounded-lg bg-white shadow md:divide-x md:divide-y-0"
      :class="[
        tabInvoices && reportsStat?.onthehouse && parseInt(reportsStat.onthehouse) ? 'md:grid-cols-4' : 'md:grid-cols-3'
      ]">
      <div
        v-if="tabInvoices"
        class="px-4 py-5 sm:px-6">
        <dt class="text-base font-normal text-gray-900">Ukupno</dt>
        <dd class="flex flex-col items-baseline justify-between md:block lg:flex xl:flex-row">
          <div
            class="mb-2 flex cursor-pointer flex-col items-baseline text-2xl font-semibold text-gray-700 active:opacity-50 lg:flex-row xl:mb-0"
            @click="copyToClipBoard(reportsStat.total)">
            {{ $filters.formatPrice(reportsStat?.total) }} RSD
          </div>
        </dd>
      </div>
      <div
        v-if="tabInvoices && reportsStat?.onthehouse && parseInt(reportsStat.onthehouse)"
        class="px-4 py-5 sm:px-6">
        <dt class="text-base font-normal text-gray-900">Na račun kuće</dt>
        <dd class="flex flex-col items-baseline justify-between md:block lg:flex xl:flex-row">
          <div
            class="mb-2 flex cursor-pointer flex-col items-baseline text-2xl font-semibold active:opacity-50 lg:flex-row xl:mb-0"
            @click="copyToClipBoard(reportsStat.onthehouse)">
            {{ $filters.formatPrice(reportsStat?.onthehouse) }} RSD
          </div>
        </dd>
      </div>
      <div
        v-if="tabInvoices"
        class="px-4 py-5 sm:px-6">
        <dt class="text-base font-normal text-gray-900">Stornirano</dt>
        <dd class="flex flex-col items-baseline justify-between md:block lg:flex xl:flex-row">
          <div
            class="mb-2 flex cursor-pointer flex-col items-baseline text-2xl font-semibold text-red-500 active:opacity-50 lg:flex-row xl:mb-0"
            @click="copyToClipBoard(reportsStat.refund)">
            {{ $filters.formatPrice(reportsStat?.refund) }} RSD
          </div>
        </dd>
      </div>
      <div class="px-4 py-5 sm:px-6">
        <dt class="text-base font-normal text-gray-900">Prihod</dt>
        <dd class="flex flex-col items-baseline justify-between md:block lg:flex xl:flex-row">
          <div
            class="mb-2 flex cursor-pointer flex-col items-baseline text-2xl font-semibold text-indigo-500 active:opacity-50 lg:flex-row xl:mb-0"
            @click="copyToClipBoard(reportsStat.income)">
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
    }
  },
  methods: {
    copyToClipBoard(text) {
      this.$copyText(text.toString())
    }
  }
}
</script>

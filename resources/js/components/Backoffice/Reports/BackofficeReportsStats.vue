<template>
  <div>
      <dl class="mt-10 grid grid-cols-1 rounded-lg bg-white overflow-hidden shadow divide-y divide-gray-200 md:grid-cols-3 md:divide-y-0 md:divide-x">
        <div class="px-4 py-5 sm:px-6">
          <dt class="text-base font-normal text-gray-900">
            Total
          </dt>
          <dd class="flex flex-col xl:flex-row justify-between items-baseline md:block lg:flex">
            <div
              class="flex flex-col lg:flex-row items-baseline text-2xl font-semibold mb-2 xl:mb-0 text-gray-700"
            >
              {{ $filters.formatPrice(reportsStat?.total) }} RSD
            </div>
          </dd>
        </div>
        <div class="px-4 py-5 sm:px-6">
          <dt class="text-base font-normal text-gray-900">
            Refunded
          </dt>
          <dd class="flex flex-col xl:flex-row justify-between items-baseline md:block lg:flex">
            <div
              class="flex flex-col lg:flex-row items-baseline text-2xl font-semibold mb-2 xl:mb-0 text-gray-700"
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
              class="flex flex-col lg:flex-row items-baseline text-2xl font-semibold mb-2 xl:mb-0 text-gray-700"
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
    },
  }
</script>

<template>
  <div>
    <div class="grid grid-cols-1 md:grid-cols-3">
      <dl
        class="mt-5 grid grid-cols-1 divide-y divide-gray-200 overflow-hidden bg-white shadow md:divide-x md:divide-y-0">
        <div class="px-4 py-5 sm:p-6">
          <dt class="text-base font-normal text-gray-900">Total + Active Orders</dt>
          <div class="mb-2 flex flex-col items-baseline text-2xl font-semibold text-indigo-600 xl:mb-0">
            {{ $filters.formatPrice($store.getters.totalRevenue) }} RSD
          </div>
        </div>
      </dl>
    </div>
  </div>
</template>

<script>
export default {}
</script>

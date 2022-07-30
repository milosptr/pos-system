<template>
  <div>
    <div class="grid grid-cols-1 md:grid-cols-3">
      <dl class="mt-5 grid grid-cols-1 bg-white overflow-hidden shadow divide-y divide-gray-200 md:divide-y-0 md:divide-x">
        <div class="px-4 py-5 sm:p-6">
          <dt class="text-base font-normal text-gray-900">
            Total + Active Orders
          </dt>
          <div class="flex flex-col items-baseline text-2xl font-semibold text-indigo-600 mb-2 xl:mb-0">
            {{ $filters.formatPrice($store.getters.totalRevenue) }} RSD
          </div>
        </div>
      </dl>
    </div>
  </div>
</template>

<script>
  export default {

  }
</script>

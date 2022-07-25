<template>
    <div v-if="$store.getters.stats" class="mt-6">
        <dl class="mt-5 grid grid-cols-1 rounded-lg bg-white overflow-hidden shadow divide-y divide-gray-200 md:grid-cols-3 md:divide-y-0 md:divide-x">
          <div v-for="item in $store.getters.stats" :key="item.name" class="px-4 py-5 sm:p-6">
            <dt class="text-base font-normal text-gray-900">
              {{ item.name }}
            </dt>
            <dd class="mt-1 flex flex-col xl:flex-row justify-between items-baseline md:block lg:flex">
              <div class="flex flex-col xl:flex-row items-baseline text-2xl font-semibold text-indigo-600 mb-2 xl:mb-0">
                {{ $filters.formatPrice(item.stat) }} RSD
                <span class="xl:ml-2 text-sm font-medium text-gray-500"> from {{ $filters.formatPrice(item.previousStat) }} RSD </span>
              </div>

              <div :class="[item.changeType === 'increase' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800', 'inline-flex items-baseline px-2.5 py-0.5 rounded-full text-sm font-medium md:mt-2 lg:mt-0']">
                <ArrowSmUpIcon v-if="item.changeType === 'increase'" class="-ml-1 mr-0.5 flex-shrink-0 self-center h-5 w-5 text-green-500" aria-hidden="true" />
                <ArrowSmDownIcon v-else class="-ml-1 mr-0.5 flex-shrink-0 self-center h-5 w-5 text-red-500" aria-hidden="true" />
                <span class="sr-only"> {{ item.changeType === 'increase' ? 'Increased' : 'Decreased' }} by </span>
                {{ item.change }}
              </div>
            </dd>
          </div>
        </dl>
    </div>
</template>
<script>
import { ArrowSmDownIcon, ArrowSmUpIcon } from '@heroicons/vue/solid'

export default {
    name: "OverviewStats",
    components: {
      ArrowSmDownIcon, ArrowSmUpIcon
    }
}
</script>
<style scoped>
</style>

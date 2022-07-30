<template>
    <div v-if="$store.getters.stats" class="">
        <dl class="mt-5 grid grid-cols-1 rounded-lg bg-white overflow-hidden shadow divide-y divide-gray-200 md:grid-cols-4 md:divide-y-0 md:divide-x">
          <div v-for="item in $store.getters.stats" :key="item.name" class="px-4 py-5 sm:p-6">
            <dt class="text-base font-normal text-gray-900">
              {{ item.name === 'Total' ? (item.name + ' Charged') : item.name }}
            </dt>
            <dd class="mt-2 flex flex-col xl:flex-row justify-between items-baseline md:block lg:flex">
              <div
                class="flex flex-col lg:flex-row items-baseline text-2xl font-semibold mb-2 xl:mb-0"
                :class="[item.primary ? 'text-indigo-600' : 'text-gray-700']"
              >
                {{ $filters.formatPrice(item.stat) }} RSD
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

<template>
    <div v-if="$store.getters.stats" class="">
        <dl
          class="mt-5 grid grid-cols-1 rounded-lg bg-white overflow-hidden shadow divide-y divide-gray-200 md:divide-y-0 md:divide-x"
          :class="gridCols"
        >
          <div v-for="item in filteredStats" :key="item.name" class="px-4 py-5 sm:p-6">
            <dt class="text-base font-normal text-gray-900">
              {{ item.name }}
            </dt>
            <dd class="mt-2 flex flex-col xl:flex-row justify-between items-baseline md:block lg:flex">
              <div
                class="flex flex-col lg:flex-row items-baseline text-2xl font-semibold mb-2 xl:mb-0 cursor-pointer active:opacity-50"
                :class="[item.primary ? 'text-indigo-600' : 'text-gray-700']"
                @click="copyToClipBoard(item.stat)"
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
    },
    computed: {
      filteredStats() {
        return this.$store.getters.stats.filter((s) => (s.stat !== 0 || s.name !== 'On the house'))
      },
      gridCols() {
        const len = this.filteredStats.length
        if(len === 5)
          return 'md:grid-cols-5'
        return 'md:grid-cols-4'
      },
    },
    methods: {
      copyToClipBoard(text){
        this.$copyText(text.toString())
      },
    }
}
</script>
<style scoped>
</style>

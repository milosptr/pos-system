<template>
  <div
    v-if="$store.getters.stats"
    class="">
    <dl
      class="mt-5 grid grid-cols-1 divide-y divide-gray-200 overflow-hidden rounded-lg bg-white shadow md:divide-x md:divide-y-0"
      :class="gridCols">
      <div
        v-for="item in filteredStats"
        :key="item.name"
        class="px-4 py-5 sm:p-6">
        <dt class="text-base font-normal text-gray-900">
          {{ item.name }}
        </dt>
        <dd class="mt-2 flex flex-col items-baseline justify-between md:block lg:flex xl:flex-row">
          <div
            class="mb-2 flex cursor-pointer flex-col items-baseline text-2xl font-semibold active:opacity-50 lg:flex-row xl:mb-0"
            :class="[item.primary ? 'text-indigo-600' : 'text-gray-700']"
            @click="copyToClipBoard(item.stat)">
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
  name: 'OverviewStats',
  components: {
    ArrowSmDownIcon,
    ArrowSmUpIcon
  },
  computed: {
    filteredStats() {
      return this.$store.getters.stats.filter((s) => s.stat !== 0 || s.name !== 'Na račun kuće')
    },
    gridCols() {
      const len = this.filteredStats.length
      if (len === 5) return 'md:grid-cols-5'
      return 'md:grid-cols-4'
    }
  },
  methods: {
    copyToClipBoard(text) {
      this.$copyText(text.toString())
    }
  }
}
</script>
<style scoped></style>

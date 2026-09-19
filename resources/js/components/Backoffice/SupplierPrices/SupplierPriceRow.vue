<template>
  <tr class="align-top border-b border-gray-200 last:border-0 hover:bg-gray-50">
    <td class="py-2 px-4 text-sm text-gray-900">{{ article.name }}</td>
    <td class="py-2 px-4 text-xs leading-6 text-gray-400">{{ article.unit }}</td>
    <td class="py-2 px-4">
      <div class="flex items-baseline gap-2">
        <span class="text-sm font-semibold text-gray-900">{{ price(latest.unit_price) }}</span>
        <span v-if="changeText" class="text-xs font-medium" :class="direction === 'up' ? 'text-red-600' : 'text-green-700'">{{ changeText }}</span>
      </div>
      <div class="text-xs text-gray-400">{{ price(latest.unit_price_gross) }}</div>
      <div class="mt-1 text-xs text-gray-400">{{ $filters.formatDate(latest.date) }}</div>
    </td>
    <td class="py-2 px-4">
      <div v-if="earlier.length" class="flex flex-wrap gap-x-6 gap-y-3">
        <div v-for="entry in earlier" :key="entry.date + entry.invoice_number" :title="entry.invoice_number">
          <div class="text-sm text-gray-600">{{ price(entry.unit_price) }}</div>
          <div class="text-xs text-gray-400">{{ price(entry.unit_price_gross) }}</div>
          <div class="mt-1 text-xs text-gray-400">{{ $filters.formatDate(entry.date) }}</div>
        </div>
      </div>
      <div v-else class="text-xs leading-6 text-gray-400">Nema ranijih cena</div>
    </td>
  </tr>
</template>

<script>
  export default {
    props: {
      article: {
        type: Object,
        required: true,
      },
    },
    computed: {
      latest() {
        return this.article.entries[0]
      },
      earlier() {
        return this.article.entries.slice(1)
      },
      direction() {
        if (!this.article.changed) {
          return null
        }
        return this.latest.unit_price > this.article.entries[1].unit_price ? 'up' : 'down'
      },
      // A price that rose from zero has a direction but no percentage, so the
      // arrow stands on its own.
      changeText() {
        if (!this.direction) {
          return null
        }
        const arrow = this.direction === 'up' ? '↑' : '↓'
        return this.article.change === null ? arrow : `${arrow} ${Math.abs(this.article.change).toFixed(1)}%`
      },
    },
    methods: {
      price(value) {
        return value ? this.$filters.formatPrice(value, true) : '-'
      },
    },
  }
</script>

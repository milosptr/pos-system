<template>
  <tr class="cursor-pointer hover:bg-gray-50" :class="[expanded && 'bg-gray-50']" @click="expanded = !expanded">
    <td class="py-3 px-4 border-b border-gray-200">
      <div class="text-sm leading-5 text-gray-900">{{ article.name }}</div>
      <div class="text-xs leading-5 text-gray-400">{{ article.unit }}</div>
    </td>
    <td class="py-3 px-4 border-b border-gray-200 whitespace-nowrap">
      <div class="text-sm font-semibold leading-5 text-gray-900">{{ price(latest.unit_price) }}</div>
      <div class="text-xs leading-5 text-gray-400">{{ $filters.formatDate(latest.date) }}</div>
    </td>
    <td class="py-3 px-4 border-b border-gray-200 whitespace-nowrap">
      <span v-if="changeText" class="rounded-md py-1 px-2 text-xs font-medium ring-1 ring-inset" :class="direction === 'up' ? 'text-red-700 bg-red-50 ring-red-600/10' : 'text-green-700 bg-green-50 ring-green-600/20'">
        {{ changeText }}
      </span>
      <span v-else class="text-xs text-gray-400">{{ earlier.length ? 'Ista cena' : 'Prva cena' }}</span>
    </td>
    <td class="py-3 px-4 border-b border-gray-200">
      <div v-if="earlier.length" class="flex flex-wrap items-end gap-x-2 gap-y-1">
        <template v-for="entry in earlier" :key="entry.date + entry.invoice_number">
          <div>
            <div class="text-xs leading-4 text-gray-400">{{ shortDate(entry.date) }}</div>
            <div class="text-sm leading-5 text-gray-600">{{ price(entry.unit_price) }}</div>
          </div>
          <span class="text-sm leading-5 text-gray-300">→</span>
        </template>
        <span class="text-xs leading-5 text-gray-400">sada</span>
      </div>
      <span v-else class="text-sm text-gray-300">—</span>
    </td>
    <td class="py-3 px-4 border-b border-gray-200">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5 text-gray-400 transition-transform" :class="[expanded && 'rotate-180']">
        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
      </svg>
    </td>
  </tr>
  <tr v-if="expanded" class="bg-gray-50 border-b border-gray-200">
    <td colspan="5" class="px-4 pb-4">
      <table class="w-full text-left text-xs">
        <thead>
          <tr class="text-gray-400 border-b border-gray-200">
            <th class="font-medium py-1 pr-3">Datum</th>
            <th class="font-medium py-1 pr-3 text-right">Cena bez PDV</th>
            <th class="font-medium py-1 pr-3 text-right">Cena sa PDV</th>
            <th class="font-medium py-1 pr-3 text-right">Promena</th>
            <th class="font-medium py-1">Račun</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="row in rows" :key="row.date + row.invoice_number" class="text-gray-900 border-b border-gray-100 last:border-0">
            <td class="py-1 pr-3">{{ $filters.formatDate(row.date) }}</td>
            <td class="py-1 pr-3 text-right">{{ price(row.unit_price) }}</td>
            <td class="py-1 pr-3 text-right text-gray-400">{{ price(row.unit_price_gross) }}</td>
            <td class="py-1 pr-3 text-right" :class="row.step > 0 ? 'text-red-600' : 'text-green-700'">{{ row.stepText }}</td>
            <td class="py-1 text-gray-400">{{ row.invoice_number }}</td>
          </tr>
        </tbody>
      </table>
    </td>
  </tr>
</template>

<script>
  const SHORT_DATE_FORMAT = 'DD.MM.YY.'

  export default {
    props: {
      article: {
        type: Object,
        required: true,
      },
    },
    data: () => ({
      expanded: false,
    }),
    computed: {
      latest() {
        return this.article.entries[0]
      },
      // Oldest first, the way the eye reads a sequence.
      earlier() {
        return this.article.entries.slice(1).reverse()
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
      rows() {
        return this.article.entries.map((entry, index) => {
          const older = this.article.entries[index + 1]
          const step = older ? entry.unit_price - older.unit_price : null
          return {
            ...entry,
            step,
            stepText: this.stepText(step, older),
          }
        })
      },
    },
    methods: {
      price(value) {
        return value ? this.$filters.formatPrice(value, true) : '-'
      },
      shortDate(value) {
        return dayjs(value).format(SHORT_DATE_FORMAT)
      },
      stepText(step, older) {
        if (step === null || step === 0) {
          return step === null ? '-' : 'Ista cena'
        }
        const percent = older.unit_price > 0 ? ` (${Math.abs((step / older.unit_price) * 100).toFixed(1)}%)` : ''
        return `${step > 0 ? '+' : '−'}${this.$filters.formatPrice(Math.abs(step), true)}${percent}`
      },
    },
  }
</script>

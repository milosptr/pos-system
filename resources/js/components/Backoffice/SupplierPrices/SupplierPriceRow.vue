<template>
  <tr class="cursor-pointer hover:bg-gray-50" :class="[expanded && 'bg-gray-50']" @click="expanded = !expanded">
    <td class="py-3 px-4 border-b border-gray-200">
      <div class="text-sm leading-5 text-gray-900">{{ article.name }}</div>
      <div class="text-xs leading-5 text-gray-400">{{ article.unit }}</div>
    </td>
    <td class="py-3 px-4 border-b border-gray-200">
      <div class="flex justify-end items-start">
        <template v-for="(entry, index) in trail" :key="entry.date + entry.invoice_number">
          <span v-if="index > 0" class="w-4 text-center text-sm leading-5 text-gray-300">›</span>
          <div class="w-20 text-right tabular-nums">
            <div class="text-sm leading-5" :class="index === trail.length - 1 ? 'font-semibold text-gray-900' : 'text-gray-500'">{{ price(entry.unit_price) }}</div>
            <div class="text-xs leading-4 text-gray-400">{{ shortDate(entry.date) }}</div>
          </div>
        </template>
      </div>
    </td>
    <td class="py-3 px-4 border-b border-gray-200 text-right whitespace-nowrap">
      <span v-if="changeText" class="rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset" :class="badgeClass">{{ changeText }}</span>
      <span v-else-if="trail.length > 1" class="text-xs text-gray-400">0,0%</span>
      <div v-if="stepText" class="mt-1 text-xs text-gray-400 tabular-nums">{{ stepText }}</div>
    </td>
    <td class="py-3 px-4 border-b border-gray-200">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5 text-gray-400 transition-transform" :class="[expanded && 'rotate-180']">
        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
      </svg>
    </td>
  </tr>
  <tr v-if="expanded" class="bg-gray-50 border-b border-gray-200">
    <td colspan="4" class="px-4 pb-4">
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
            <td class="py-1 pr-3 text-right tabular-nums">{{ price(row.unit_price) }}</td>
            <td class="py-1 pr-3 text-right tabular-nums text-gray-400">{{ price(row.unit_price_gross) }}</td>
            <td class="py-1 pr-3 text-right tabular-nums" :class="row.step > 0 ? 'text-red-600' : 'text-green-700'">{{ row.stepText }}</td>
            <td class="py-1 text-gray-400">{{ row.invoice_number }}</td>
          </tr>
        </tbody>
      </table>
    </td>
  </tr>
</template>

<script>
  const SHORT_DATE_FORMAT = 'DD.MM.YY.'

  // A price that moves a few percent is worth a glance, one that moves ten is
  // worth acting on, so the badge carries the weight instead of the reader.
  const NOTABLE_CHANGE_PERCENT = 3
  const BIG_CHANGE_PERCENT = 10

  const BADGE_CLASSES = {
    up: [
      'text-red-700 bg-red-50 ring-red-600/10',
      'text-red-800 bg-red-100 ring-red-600/20',
      'text-white bg-red-600 ring-red-600',
    ],
    down: [
      'text-green-700 bg-green-50 ring-green-600/20',
      'text-green-800 bg-green-100 ring-green-600/20',
      'text-white bg-green-600 ring-green-600',
    ],
  }

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
      // Oldest first, so the sequence reads the way time runs and the newest
      // price lands in the same place on every row.
      trail() {
        return [...this.article.entries].reverse()
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
        return this.article.change === null ? arrow : `${arrow} ${this.percent(this.article.change)}`
      },
      badgeClass() {
        const size = Math.abs(this.article.change || 0)
        const tier = size >= BIG_CHANGE_PERCENT ? 2 : (size >= NOTABLE_CHANGE_PERCENT ? 1 : 0)
        return BADGE_CLASSES[this.direction][tier]
      },
      stepText() {
        if (!this.direction || this.article.change === null) {
          return null
        }
        const step = this.latest.unit_price - this.article.entries[1].unit_price
        return `${step > 0 ? '+' : '−'}${this.$filters.formatPrice(Math.abs(step), true)}`
      },
      rows() {
        return this.article.entries.map((entry, index) => {
          const older = this.article.entries[index + 1]
          const step = older ? entry.unit_price - older.unit_price : null
          return {
            ...entry,
            step,
            stepText: this.stepLabel(step, older),
          }
        })
      },
    },
    methods: {
      price(value) {
        return value ? this.$filters.formatPrice(value, true) : '-'
      },
      percent(value) {
        return `${Math.abs(value).toFixed(1).replace('.', ',')}%`
      },
      shortDate(value) {
        return dayjs(value).format(SHORT_DATE_FORMAT)
      },
      stepLabel(step, older) {
        if (step === null || step === 0) {
          return step === null ? '-' : 'Ista cena'
        }
        const percent = older.unit_price > 0 ? ` (${this.percent((step / older.unit_price) * 100)})` : ''
        return `${step > 0 ? '+' : '−'}${this.$filters.formatPrice(Math.abs(step), true)}${percent}`
      },
    },
  }
</script>

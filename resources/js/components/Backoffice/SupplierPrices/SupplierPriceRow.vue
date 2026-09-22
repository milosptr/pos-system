<template>
  <tr class="cursor-pointer hover:bg-gray-50" :class="[expanded && 'bg-gray-50']" @click="expanded = !expanded">
    <td class="py-2 px-4 overflow-hidden" :title="article.name">
      <div class="flex items-baseline gap-1.5">
        <span class="text-sm text-gray-900 truncate">{{ article.name }}</span>
        <span class="text-xs text-gray-400 flex-none">{{ article.unit }}</span>
      </div>
      <div v-if="showSupplier" class="text-xs text-gray-400 truncate">{{ article.supplier }}</div>
    </td>
    <td class="py-2 pr-4 border-r border-gray-300">
      <SupplierPriceChart :points="chartPoints" />
    </td>
    <td class="py-2 px-4 text-right text-sm font-semibold text-gray-900 tabular-nums whitespace-nowrap">
      {{ price(current.unit_price_gross) }}
    </td>
    <td class="py-2 pr-4 text-right whitespace-nowrap">
      <span v-if="changeText" class="rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset" :class="badgeClass">{{ changeText }}</span>
      <span v-else class="text-sm text-gray-300">—</span>
    </td>
    <td class="py-2 pr-4">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-4 w-4 text-gray-400 transition-transform" :class="[expanded && 'rotate-180']">
        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
      </svg>
    </td>
  </tr>
  <tr v-if="expanded" class="bg-gray-100">
    <td colspan="5" class="px-4 pb-3">
      <table class="w-full table-fixed text-left text-xs">
        <thead>
          <tr class="text-gray-500 border-b border-gray-300">
            <th class="font-medium py-1.5 pr-6 w-1/5">Datum</th>
            <th class="font-medium py-1.5 pr-6 w-1/5 text-right">Cena bez PDV</th>
            <th class="font-medium py-1.5 pr-6 w-1/5 text-right">Cena sa PDV</th>
            <th class="font-medium py-1.5 pr-6 w-1/5 text-right">Promena</th>
            <th class="font-medium py-1.5 w-1/5 text-right">Račun</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="row in rows" :key="row.date + row.invoice_number" class="border-b border-gray-200 last:border-0" :class="row.step ? 'text-gray-900' : 'text-gray-400'">
            <td class="py-1.5 pr-6">{{ $filters.formatDate(row.date) }}</td>
            <td class="py-1.5 pr-6 text-right tabular-nums text-gray-400">{{ price(row.unit_price) }}</td>
            <td class="py-1.5 pr-6 text-right tabular-nums">{{ price(row.unit_price_gross) }}</td>
            <td class="py-1.5 pr-6 text-right tabular-nums" :class="stepClass(row.step)">{{ row.stepText }}</td>
            <td class="py-1.5 text-right text-gray-400">{{ row.invoice_number }}</td>
          </tr>
        </tbody>
      </table>
      <div v-if="article.observations > article.entries.length" class="mt-2 text-xs text-gray-400">
        Prikazano poslednjih {{ article.entries.length }} od {{ article.observations }} računa.
      </div>
    </td>
  </tr>
</template>

<script>
  import SupplierPriceChart from './SupplierPriceChart.vue'
  import { changeDirection, DIRECTION_UP } from './priceChange'

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
    components: {
      SupplierPriceChart,
    },
    props: {
      article: {
        type: Object,
        required: true,
      },
      showSupplier: {
        type: Boolean,
        default: false,
      },
    },
    data: () => ({
      expanded: false,
    }),
    computed: {
      current() {
        return this.article.entries[0]
      },
      chartPoints() {
        return [...this.article.entries].reverse().map((entry) => ({
          price: entry.unit_price_gross,
          date: entry.date,
          invoice: entry.invoice_number,
        }))
      },
      direction() {
        return changeDirection(this.article)
      },
      changeText() {
        if (!this.direction) {
          return null
        }
        const arrow = this.direction === DIRECTION_UP ? '↑' : '↓'
        return this.article.change === null ? arrow : `${arrow} ${this.percent(this.article.change)}`
      },
      badgeClass() {
        const size = Math.abs(this.article.change || 0)
        const tier = size >= BIG_CHANGE_PERCENT ? 2 : (size >= NOTABLE_CHANGE_PERCENT ? 1 : 0)
        return BADGE_CLASSES[this.direction][tier]
      },
      rows() {
        return this.article.entries.map((entry, index) => {
          const older = this.article.entries[index + 1]
          const step = older ? entry.unit_price_gross - older.unit_price_gross : null
          return { ...entry, step, stepText: this.stepLabel(step, older) }
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
      stepClass(step) {
        if (!step) {
          return ''
        }
        return step > 0 ? 'text-red-600' : 'text-green-700'
      },
      stepLabel(step, older) {
        if (step === null) {
          return '-'
        }
        if (step === 0) {
          return 'ista cena'
        }
        const percent = older.unit_price_gross > 0 ? ` (${this.percent((step / older.unit_price_gross) * 100)})` : ''
        return `${step > 0 ? '+' : '−'}${this.$filters.formatPrice(Math.abs(step), true)}${percent}`
      },
    },
  }
</script>

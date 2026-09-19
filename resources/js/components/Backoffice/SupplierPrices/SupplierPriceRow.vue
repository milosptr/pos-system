<template>
  <tr class="cursor-pointer hover:bg-gray-50" :class="[expanded && 'bg-gray-50']" @click="expanded = !expanded">
    <td class="py-3 px-4 border-b border-gray-200">
      <div class="text-sm leading-5 text-gray-900">{{ article.name }}</div>
      <div class="text-xs leading-5 text-gray-400">{{ article.unit }}</div>
    </td>
    <td class="py-3 px-4 border-b border-gray-200">
      <div class="text-sm font-semibold leading-5 text-gray-900">{{ price(latest.unit_price) }}</div>
      <div class="text-xs leading-5 text-gray-400">{{ $filters.formatDate(latest.date) }}</div>
    </td>
    <td class="py-3 px-4 border-b border-gray-200">
      <span v-if="changeText" class="rounded-md py-1 px-2 text-xs font-medium ring-1 ring-inset" :class="direction === 'up' ? 'text-red-700 bg-red-50 ring-red-600/10' : 'text-green-700 bg-green-50 ring-green-600/20'">
        {{ changeText }}
      </span>
      <span v-else class="text-xs text-gray-400">Bez promene</span>
    </td>
    <td class="py-3 px-4 border-b border-gray-200">
      <div class="flex items-center gap-3">
        <svg v-if="points" :width="SPARK_WIDTH" :height="SPARK_HEIGHT" :viewBox="`0 0 ${SPARK_WIDTH} ${SPARK_HEIGHT}`" fill="none" class="flex-none">
          <polyline :points="points" :stroke="trendColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
          <circle :cx="lastPoint.x" :cy="lastPoint.y" r="2.5" :fill="trendColor" />
        </svg>
        <span class="text-xs text-gray-400">{{ trendLabel }}</span>
      </div>
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
  const SPARK_WIDTH = 72
  const SPARK_HEIGHT = 22
  const SPARK_PADDING = 3

  const TREND_COLORS = {
    up: '#dc2626',
    down: '#15803d',
    flat: '#9ca3af',
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
      SPARK_WIDTH,
      SPARK_HEIGHT,
    }),
    computed: {
      latest() {
        return this.article.entries[0]
      },
      // Oldest first, the way a trend is read.
      trend() {
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
        return this.article.change === null ? arrow : `${arrow} ${Math.abs(this.article.change).toFixed(1)}%`
      },
      overallDirection() {
        const values = this.trend.map((entry) => entry.unit_price)
        if (values.length < 2 || values[0] === values[values.length - 1]) {
          return 'flat'
        }
        return values[values.length - 1] > values[0] ? 'up' : 'down'
      },
      trendColor() {
        return TREND_COLORS[this.overallDirection]
      },
      trendLabel() {
        if (this.trend.length < 2) {
          return 'Prva cena'
        }
        return `${this.trend.length} cene od ${this.$filters.formatDate(this.trend[0].date)}`
      },
      coordinates() {
        const values = this.trend.map((entry) => entry.unit_price)
        if (values.length < 2) {
          return null
        }
        const min = Math.min(...values)
        const span = Math.max(...values) - min || 1
        const step = SPARK_WIDTH / (values.length - 1)
        const height = SPARK_HEIGHT - SPARK_PADDING * 2
        return values.map((value, index) => ({
          x: index * step,
          y: SPARK_PADDING + height - ((value - min) / span) * height,
        }))
      },
      points() {
        return this.coordinates ? this.coordinates.map(({ x, y }) => `${x},${y}`).join(' ') : null
      },
      lastPoint() {
        return this.coordinates[this.coordinates.length - 1]
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
      stepText(step, older) {
        if (step === null || step === 0) {
          return step === null ? '-' : 'Bez promene'
        }
        const percent = older.unit_price > 0 ? ` (${Math.abs((step / older.unit_price) * 100).toFixed(1)}%)` : ''
        return `${step > 0 ? '+' : '−'}${this.$filters.formatPrice(Math.abs(step), true)}${percent}`
      },
    },
  }
</script>

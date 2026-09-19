<template>
  <tr class="cursor-pointer hover:bg-gray-50" :class="[expanded && 'bg-gray-50']" @click="expanded = !expanded">
    <td class="py-2 px-4">
      <div class="flex items-baseline gap-1.5">
        <span class="text-sm text-gray-900 truncate">{{ article.name }}</span>
        <span class="text-xs text-gray-400 flex-none">{{ article.unit }}</span>
      </div>
    </td>
    <td class="py-2 pr-4 border-r border-gray-300">
      <div class="relative h-7">
        <div v-for="tick in ticks" :key="tick.at" class="absolute inset-y-0 w-px bg-gray-100" :style="{ left: tick.at + '%' }" />
        <template v-for="segment in segments" :key="segment.from">
          <div v-if="segment.label" class="absolute top-0 h-4 text-xs leading-4 tabular-nums text-gray-500 whitespace-nowrap" :style="{ left: segment.left + '%' }">
            {{ segment.label }}
          </div>
          <div class="absolute bottom-1 h-1.5 rounded-full" :class="segment.tone" :style="{ left: segment.left + '%', width: segment.width + '%' }" :title="segment.tooltip" />
        </template>
      </div>
    </td>
    <td class="py-2 px-4 text-right text-sm font-semibold text-gray-900 tabular-nums whitespace-nowrap">
      {{ price(current.unit_price) }}
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
  <tr v-if="expanded" class="bg-gray-50">
    <td colspan="5" class="px-4 pb-3">
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
          <tr v-for="row in rows" :key="row.date + row.invoice_number" class="border-b border-gray-100 last:border-0" :class="row.step ? 'text-gray-900' : 'text-gray-400'">
            <td class="py-1 pr-3">{{ $filters.formatDate(row.date) }}</td>
            <td class="py-1 pr-3 text-right tabular-nums">{{ price(row.unit_price) }}</td>
            <td class="py-1 pr-3 text-right tabular-nums text-gray-400">{{ price(row.unit_price_gross) }}</td>
            <td class="py-1 pr-3 text-right tabular-nums" :class="stepClass(row.step)">{{ row.stepText }}</td>
            <td class="py-1 text-gray-400">{{ row.invoice_number }}</td>
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
  // How much of the row a price has to hold before its number fits beside the
  // bar; anything shorter lives in the tooltip.
  const LABEL_MIN_PERCENT = 13

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

  const SEGMENT_TONES = {
    first: 'bg-gray-200',
    up: 'bg-red-200',
    down: 'bg-emerald-200',
  }

  const CURRENT_TONES = {
    first: 'bg-gray-400',
    up: 'bg-red-400',
    down: 'bg-emerald-400',
  }

  const MIN_SEGMENT_PERCENT = 0.4

  export default {
    props: {
      article: {
        type: Object,
        required: true,
      },
      window: {
        type: Object,
        required: true,
      },
      ticks: {
        type: Array,
        required: true,
      },
    },
    data: () => ({
      expanded: false,
    }),
    computed: {
      current() {
        return this.article.entries[0]
      },
      // A price holds until the next one takes over, not until the last
      // invoice that happened to charge it, so a level seen on one invoice
      // still covers the months it was in force.
      spans() {
        return this.article.levels.map((level, index) => {
          const next = this.article.levels[index + 1]
          return { level, from: this.time(level.from), to: next ? this.time(next.from) : this.window.to }
        })
      },
      // A span that began before the window is clipped to the left edge
      // rather than dropped, so the bar never starts in mid air.
      visibleSpans() {
        return this.spans.filter((span) => span.to >= this.window.from && span.from <= this.window.to)
      },
      segments() {
        return this.visibleSpans.map(({ level, from, to }, index) => {
          const left = this.position(Math.max(from, this.window.from))
          const right = this.position(Math.min(to, this.window.to))
          const width = Math.max(right - left, MIN_SEGMENT_PERCENT)
          const tone = level.change === null ? 'first' : (level.change > 0 ? 'up' : 'down')
          const isCurrent = index === this.visibleSpans.length - 1

          return {
            from: level.from,
            left,
            width,
            tone: isCurrent ? CURRENT_TONES[tone] : SEGMENT_TONES[tone],
            label: width >= LABEL_MIN_PERCENT ? this.price(level.unit_price) : null,
            tooltip: this.tooltip(level),
          }
        })
      },
      direction() {
        if (this.article.total_change === null) {
          return this.article.changed ? 'up' : null
        }
        return this.article.total_change > 0 ? 'up' : 'down'
      },
      // The whole climb, not the last step: an article that crept up five
      // times is the one worth seeing, and every single step looks small.
      changeText() {
        if (!this.direction) {
          return null
        }
        const arrow = this.direction === 'up' ? '↑' : '↓'
        return this.article.total_change === null ? arrow : `${arrow} ${this.percent(this.article.total_change)}`
      },
      badgeClass() {
        const size = Math.abs(this.article.total_change || 0)
        const tier = size >= BIG_CHANGE_PERCENT ? 2 : (size >= NOTABLE_CHANGE_PERCENT ? 1 : 0)
        return BADGE_CLASSES[this.direction][tier]
      },
      rows() {
        return this.article.entries.map((entry, index) => {
          const older = this.article.entries[index + 1]
          const step = older ? entry.unit_price - older.unit_price : null
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
      time(date) {
        return dayjs(date).valueOf()
      },
      position(at) {
        return ((at - this.window.from) / (this.window.to - this.window.from)) * 100
      },
      stepClass(step) {
        if (!step) {
          return ''
        }
        return step > 0 ? 'text-red-600' : 'text-green-700'
      },
      tooltip(level) {
        const parts = [this.price(level.unit_price), `od ${this.$filters.formatDate(level.from)}`]
        if (level.invoices > 1) {
          parts.push(`${level.invoices} računa`)
        }
        if (level.change !== null) {
          parts.push(`${level.change > 0 ? '+' : '−'}${this.percent(level.change)}`)
        }
        return parts.join(' · ')
      },
      stepLabel(step, older) {
        if (step === null) {
          return '-'
        }
        if (step === 0) {
          return 'ista cena'
        }
        const percent = older.unit_price > 0 ? ` (${this.percent((step / older.unit_price) * 100)})` : ''
        return `${step > 0 ? '+' : '−'}${this.$filters.formatPrice(Math.abs(step), true)}${percent}`
      },
    },
  }
</script>

<template>
  <div ref="host" class="relative w-full min-w-[300px] max-w-full" :style="{ height: `${HEIGHT}px` }" @mousemove="track" @mouseleave="release">
    <svg v-if="width && visible.length" :width="width" :height="HEIGHT">
      <defs>
        <linearGradient v-for="tone in TONES" :id="`spg-${id}-${tone}`" :key="`gradient-${tone}`" gradientUnits="userSpaceOnUse" x1="0" :y1="topY" x2="0" :y2="HEIGHT">
          <stop offset="0" :stop-color="COLORS[tone]" :stop-opacity="OPACITY[tone]" />
          <stop offset="1" :stop-color="COLORS[tone]" stop-opacity="0" />
        </linearGradient>
        <clipPath v-for="tone in TONES" :id="`spc-${id}-${tone}`" :key="`clip-${tone}`">
          <rect v-for="band in bands[tone]" :key="band.x" :x="band.x" y="0" :width="band.width" :height="HEIGHT" />
        </clipPath>
      </defs>
      <path v-for="tone in filledTones" :key="`area-${tone}`" :d="areaPath" :fill="`url(#spg-${id}-${tone})`" :clip-path="`url(#spc-${id}-${tone})`" />
      <path v-for="tone in filledTones" :key="`line-${tone}`" :d="linePath" fill="none" :stroke="COLORS[tone]" :stroke-width="LINE_WIDTH" stroke-linecap="round" stroke-linejoin="round" :clip-path="`url(#spc-${id}-${tone})`" />
      <circle v-for="dot in dots" :key="dot.x" :cx="dot.x" :cy="dot.y" :r="POINT_RADIUS" :fill="DOT_FILL" :stroke="dot.color" :stroke-width="LINE_WIDTH" />
      <circle v-if="hovered" :cx="hovered.x" :cy="hovered.y" :r="HOVER_RADIUS" :fill="DOT_FILL" :stroke="hovered.color" :stroke-width="HOVER_LINE_WIDTH" />
    </svg>
    <Teleport to="body">
      <div v-if="hovered" class="pointer-events-none fixed z-50 whitespace-nowrap rounded-md bg-gray-900 px-2 py-1 text-xs text-white shadow-lg" :style="tooltipStyle">
        <div class="font-semibold tabular-nums">{{ $filters.formatPrice(hovered.point.price, true) }}</div>
        <div class="text-gray-300">{{ $filters.formatDate(hovered.point.date) }}{{ hovered.point.invoice ? ` · ${hovered.point.invoice}` : '' }}</div>
      </div>
    </Teleport>
  </div>
</template>

<script>
  import { reactive } from 'vue'

  const HEIGHT = 48
  const VERTICAL_PADDING = 6
  const POINT_RADIUS = 2.2
  const HOVER_RADIUS = 3.5
  const EDGE_INSET = HOVER_RADIUS + 1

  const LINE_WIDTH = 1.5
  const HOVER_LINE_WIDTH = 2
  const DOT_FILL = '#fff'

  const VISIBLE_PRICES = 20

  const TOOLTIP_GAP = 12

  const TONE_UP = 'up'
  const TONE_DOWN = 'down'
  const TONE_FLAT = 'flat'

  const TONES = [TONE_FLAT, TONE_DOWN, TONE_UP]

  const COLORS = {
    [TONE_UP]: '#f43f5e',
    [TONE_DOWN]: '#10b981',
    [TONE_FLAT]: '#9ca3af',
  }

  const OPACITY = {
    [TONE_UP]: 0.32,
    [TONE_DOWN]: 0.32,
    [TONE_FLAT]: 0.16,
  }

  // Two fills that meet on the same x leave a hairline of background between
  // them, so every band is grown half a pixel and the later one covers it.
  const BAND_BLEED = 0.5

  let chartCount = 0

  // One row at a time owns the tooltip: a mouseleave that never arrives, which
  // happens when the pointer jumps between rows, would otherwise leave the
  // tooltip of every row it touched on screen.
  const pointed = reactive({ chart: null })

  export default {
    props: {
      points: {
        type: Array,
        required: true,
      },
    },
    data: () => ({
      id: ++chartCount,
      width: 0,
      hoveredIndex: null,
      hostBox: null,
      HEIGHT,
      TONES,
      COLORS,
      OPACITY,
      POINT_RADIUS,
      HOVER_RADIUS,
      LINE_WIDTH,
      HOVER_LINE_WIDTH,
      DOT_FILL,
    }),
    computed: {
      visible() {
        return this.points.slice(-VISIBLE_PRICES)
      },
      // An invoice that repeats the price keeps the colour of the last real
      // change: without it a steady article comes out striped grey, and the
      // rise that put it there stops being visible.
      tones() {
        const tones = []
        for (let index = 1; index < this.visible.length; index++) {
          const step = this.visible[index].price - this.visible[index - 1].price
          const carried = tones[index - 2] || TONE_FLAT
          tones.push(step > 0 ? TONE_UP : (step < 0 ? TONE_DOWN : carried))
        }
        return tones
      },
      coordinates() {
        const prices = this.visible.map((point) => point.price)
        const max = Math.max(...prices)
        const span = max - Math.min(...prices)
        const usable = HEIGHT - VERTICAL_PADDING * 2
        const count = this.visible.length
        const reach = this.width - EDGE_INSET * 2

        return this.visible.map((point, index) => ({
          x: count === 1 ? this.width - EDGE_INSET : EDGE_INSET + (index * reach) / (count - 1),
          y: span === 0 ? HEIGHT / 2 : VERTICAL_PADDING + ((max - point.price) / span) * usable,
        }))
      },
      linePath() {
        const points = this.coordinates
        return `M${points[0].x},${points[0].y}${this.segments.join('')}`
      },
      areaPath() {
        const points = this.coordinates
        const last = points[points.length - 1]
        return `${this.linePath}L${last.x},${HEIGHT}L${points[0].x},${HEIGHT}Z`
      },
      // A plain spline overshoots between two invoices and draws a price the
      // supplier never charged, so the tangents are clamped to the data.
      segments() {
        const points = this.coordinates
        const count = points.length

        if (count < 2) {
          return []
        }
        if (count === 2) {
          return [`L${points[1].x},${points[1].y}`]
        }

        const widths = []
        const slopes = []
        for (let index = 0; index < count - 1; index++) {
          widths[index] = points[index + 1].x - points[index].x
          slopes[index] = (points[index + 1].y - points[index].y) / widths[index]
        }

        const tangents = [slopes[0]]
        for (let index = 1; index < count - 1; index++) {
          if (slopes[index - 1] * slopes[index] <= 0) {
            tangents[index] = 0
          } else {
            const first = 2 * widths[index] + widths[index - 1]
            const second = widths[index] + 2 * widths[index - 1]
            tangents[index] = (first + second) / (first / slopes[index - 1] + second / slopes[index])
          }
        }
        tangents[count - 1] = slopes[count - 2]

        return points.slice(0, -1).map((point, index) => {
          const next = points[index + 1]
          const reach = widths[index] / 3
          return `C${point.x + reach},${point.y + tangents[index] * reach} ${next.x - reach},${next.y - tangents[index + 1] * reach} ${next.x},${next.y}`
        })
      },
      bands() {
        const points = this.coordinates
        return TONES.reduce((bands, tone) => ({
          ...bands,
          [tone]: this.tones
            .map((value, index) => ({
              tone: value,
              x: Math.max(points[index].x - BAND_BLEED, 0),
              width: points[index + 1].x - points[index].x + BAND_BLEED * 2,
            }))
            .filter((band) => band.tone === tone),
        }), {})
      },
      filledTones() {
        return TONES.filter((tone) => this.bands[tone].length)
      },
      topY() {
        return Math.min(...this.coordinates.map((point) => point.y))
      },
      dots() {
        return this.coordinates.map((point, index) => ({
          ...point,
          color: index === 0 ? COLORS[TONE_FLAT] : COLORS[this.tones[index - 1]],
        }))
      },
      hovered() {
        if (this.hoveredIndex === null || pointed.chart !== this.id) {
          return null
        }
        return {
          ...this.dots[this.hoveredIndex],
          point: this.visible[this.hoveredIndex],
        }
      },
      tooltipStyle() {
        if (!this.hostBox) {
          return null
        }
        return {
          left: `${this.hostBox.left + this.hovered.x}px`,
          top: `${this.hostBox.top + this.hovered.y - TOOLTIP_GAP}px`,
          transform: 'translate(-50%, -100%)',
        }
      },
    },
    mounted() {
      this.observer = new ResizeObserver(([entry]) => {
        this.width = entry.contentRect.width
      })
      this.observer.observe(this.$refs.host)
    },
    beforeUnmount() {
      this.observer.disconnect()
      this.release()
    },
    methods: {
      track(event) {
        if (!this.width || !this.visible.length) {
          return
        }

        pointed.chart = this.id
        this.hostBox = this.$refs.host.getBoundingClientRect()
        const at = event.clientX - this.hostBox.left

        this.hoveredIndex = this.coordinates.reduce((nearest, point, index) => (
          Math.abs(point.x - at) < Math.abs(this.coordinates[nearest].x - at) ? index : nearest
        ), 0)
      },
      release() {
        if (pointed.chart === this.id) {
          pointed.chart = null
        }
        this.hoveredIndex = null
      },
    },
  }
</script>

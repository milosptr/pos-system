<template>
  <div
    class="bg-gray-900 rounded-lg shadow-md overflow-hidden"
    :class="mode === 'ready' ? 'opacity-60' : ''"
  >
    <!-- Header -->
    <div
      class="relative px-4 py-3 flex items-center justify-between overflow-hidden"
      :class="headerClass"
      @click="onHeaderClick"
    >
      <!-- Fill animation -->
      <div
        v-if="counting"
        class="absolute inset-0 bg-white/20"
        :style="{ width: fillWidth, transition: 'width 3s linear' }"
      ></div>
      <span class="relative text-white font-bold text-lg">{{ order.table_name }}</span>
      <template v-if="mode === 'active'">
        <button
          v-if="counting"
          @click.stop="cancelCountdown"
          class="relative text-white text-sm font-medium bg-white/20 hover:bg-white/30 px-3 py-1 rounded transition-colors"
        >
          Odustani
        </button>
        <span v-else class="relative text-gray-400 text-sm font-medium">{{ elapsed }}</span>
      </template>
      <template v-else>
        <span class="text-gray-400 text-sm font-medium">{{ readyTime }}</span>
      </template>
    </div>

    <!-- Items -->
    <ul class="divide-y divide-gray-800 px-4 py-2">
      <li
        v-for="item in order.items"
        :key="item.id"
        class="py-2 cursor-pointer select-none"
        @click="toggleDone(item)"
      >
        <div class="flex items-center justify-between">
          <div
            class="font-semibold text-base"
            :class="itemClass(item)"
          >
            {{ item.qty }}x {{ item.name }}
          </div>
          <span v-if="item.is_done && !item.storno" class="text-green-500 text-sm ml-2">&#10003;</span>
        </div>
        <div v-if="item.modifier" class="text-sm mt-0.5 pl-4" :class="item.storno ? 'text-red-400 line-through' : item.is_done ? 'text-gray-600' : 'text-gray-400'">
          {{ item.modifier }}
        </div>
      </li>
    </ul>
  </div>
</template>

<script>
export default {
  props: {
    order: {
      type: Object,
      required: true,
    },
    mode: {
      type: String,
      required: true,
    },
    now: {
      type: Object,
      required: true,
    },
  },
  data() {
    return {
      counting: false,
      fillWidth: '0%',
      countdownTimer: null,
    }
  },
  computed: {
    headerClass() {
      if (this.mode === 'ready') return 'bg-gray-800'
      return this.counting ? 'bg-gray-800 cursor-pointer' : 'bg-gray-800 cursor-pointer hover:bg-gray-700'
    },
    elapsed() {
      const created = dayjs(this.order.created_at)
      const diffMinutes = this.now.diff(created, 'minute')
      if (diffMinutes >= 60) {
        const hours = Math.floor(diffMinutes / 60)
        const mins = diffMinutes % 60
        return `${hours}h ${mins}m`
      }
      return `${diffMinutes} min`
    },
    readyTime() {
      if (!this.order.ready_at) return ''
      return dayjs(this.order.ready_at).format('HH:mm')
    },
  },
  beforeUnmount() {
    this.clearCountdown()
  },
  methods: {
    toggleDone(item) {
      if (this.mode !== 'active') return
      this.$store.dispatch('toggleItemDone', { orderId: this.order.id, itemId: item.id })
    },
    itemClass(item) {
      if (item.storno) return 'text-red-500 line-through'
      if (item.is_done) return 'text-gray-600'
      return 'text-gray-100'
    },
    onHeaderClick() {
      if (this.mode !== 'active' || this.counting) return
      this.startCountdown()
    },
    startCountdown() {
      this.counting = true
      // Trigger fill animation on next frame
      requestAnimationFrame(() => {
        this.fillWidth = '100%'
      })
      this.countdownTimer = setTimeout(() => {
        this.$store.dispatch('markReady', this.order.id)
        this.resetState()
      }, 3000)
    },
    cancelCountdown() {
      this.clearCountdown()
      this.resetState()
    },
    clearCountdown() {
      if (this.countdownTimer) {
        clearTimeout(this.countdownTimer)
        this.countdownTimer = null
      }
    },
    resetState() {
      this.counting = false
      this.fillWidth = '0%'
    },
  },
}
</script>

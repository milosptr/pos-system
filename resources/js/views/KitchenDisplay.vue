<template>
  <div class="h-screen flex flex-col bg-gray-950">
    <!-- Header -->
    <header class="bg-gray-900 border-b border-gray-800 px-6 py-4 flex md:grid md:grid-cols-3 items-center justify-between flex-shrink-0">
      <div class="flex space-x-2">
        <button
          @click="setTab('active')"
          :class="activeTab === 'active' ? 'bg-white text-gray-900' : 'bg-gray-800 text-gray-400 hover:bg-gray-700'"
          class="px-5 py-2 rounded-lg font-semibold text-sm transition-colors"
        >
          Aktivne
          <span
            v-if="activeOrders.length"
            class="ml-2 inline-flex items-center justify-center px-2 py-0.5 rounded-full text-xs font-bold"
            :class="activeTab === 'active' ? 'bg-gray-900/20 text-gray-900' : 'bg-gray-700 text-gray-300'"
          >
            {{ activeOrders.length }}
          </span>
        </button>
        <button
          @click="setTab('ready')"
          :class="activeTab === 'ready' ? 'bg-white text-gray-900' : 'bg-gray-800 text-gray-400 hover:bg-gray-700'"
          class="px-5 py-2 rounded-lg font-semibold text-sm transition-colors"
        >
          Izdate
          <span
            v-if="readyOrders.length"
            class="ml-2 inline-flex items-center justify-center px-2 py-0.5 rounded-full text-xs font-bold"
            :class="activeTab === 'ready' ? 'bg-gray-900/20 text-gray-900' : 'bg-gray-700 text-gray-300'"
          >
            {{ readyOrders.length }}
          </span>
        </button>
      </div>
      <h1 class="hidden md:block text-white text-lg font-bold tracking-wide text-center uppercase">{{ activeTab === 'active' ? 'Aktivne porudžbine' : 'Izdate porudžbine' }}</h1>
      <span @click="goToCheckin" class="text-white text-lg font-semibold text-right cursor-pointer hover:text-gray-300 transition-colors">{{ currentTime }}</span>
    </header>

    <!-- Content -->
    <main class="flex-1 overflow-x-auto overflow-y-hidden p-6">
      <div v-if="currentOrders.length" class="h-full" style="column-width: 18rem; column-fill: auto; column-gap: 1rem;">
        <div v-for="order in currentOrders" :key="order.id" class="mb-4" style="break-inside: avoid;">
          <KitchenOrderCard
            :order="order"
            :mode="activeTab"
            :now="now"
          />
        </div>
      </div>
      <div v-else class="flex items-center justify-center h-full">
        <p class="text-gray-600 text-xl font-medium">
          {{ activeTab === 'active' ? 'Nema aktivnih porudzbina' : 'Nema izdatih porudzbina' }}
        </p>
      </div>
    </main>
  </div>
</template>

<script>
import Pusher from 'pusher-js'
import KitchenOrderCard from '../components/Kitchen/KitchenOrderCard.vue'

export default {
  components: { KitchenOrderCard },
  data() {
    return {
      pusher: null,
      pollInterval: null,
      timerInterval: null,
      readySince: null,
      now: dayjs(),
      knownOrderIds: new Set(),
      initialLoadDone: false,
      notificationSound: new Audio('/sounds/glass-tone.mp3'),
    }
  },
  computed: {
    activeTab() {
      return this.$store.getters.activeTab
    },
    activeOrders() {
      return this.$store.getters.activeOrders
    },
    readyOrders() {
      return this.$store.getters.readyOrders
    },
    currentOrders() {
      return this.activeTab === 'active' ? this.activeOrders : this.readyOrders
    },
    currentTime() {
      return this.now.format('HH:mm')
    },
  },
  watch: {
    activeOrders(orders) {
      const currentIds = new Set(orders.map(o => o.id))

      if (!this.initialLoadDone) {
        this.knownOrderIds = currentIds
        this.initialLoadDone = true
        return
      }

      const hasNewOrder = orders.some(o => !this.knownOrderIds.has(o.id))
      if (hasNewOrder) {
        this.notificationSound.currentTime = 0
        this.notificationSound.play().catch(() => {})
      }

      this.knownOrderIds = currentIds
    },
  },
  mounted() {
    this.$store.dispatch('fetchOrders')
    this.initPusher()
    this.pollInterval = setInterval(() => {
      this.$store.dispatch('fetchOrders')
    }, 30000)
    this.timerInterval = setInterval(() => {
      this.now = dayjs()
      if (this.readySince && Date.now() - this.readySince >= 20000) {
        this.readySince = null
        this.$store.commit('setActiveTab', 'active')
      }
    }, 1000)
  },
  beforeUnmount() {
    if (this.pollInterval) clearInterval(this.pollInterval)
    if (this.timerInterval) clearInterval(this.timerInterval)
    if (this.pusher) this.pusher.disconnect()
  },
  methods: {
    setTab(tab) {
      this.$store.commit('setActiveTab', tab)
      this.readySince = tab === 'ready' ? Date.now() : null
    },
    goToCheckin() {
      const sendBack = window.location.href
      window.location.href = `http://192.168.200.30:81/checkin?sendBack=${encodeURIComponent(sendBack)}`
    },
    initPusher() {
      this.pusher = new Pusher(import.meta.env.VITE_PUSHER_APP_KEY, {
        cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
      })
      this.pusher.subscribe('broadcasting')
      this.pusher.bind('kitchen-update', () => {
        this.$store.dispatch('fetchOrders')
      })
    },
  },
}
</script>

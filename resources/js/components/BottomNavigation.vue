<template>
  <div
    class="fixed bottom-0 left-0 z-10 w-full"
    style="background: rgb(32, 73, 101)">
    <div class="flex items-center justify-between">
      <div class="my-3 flex w-72 gap-5 px-6">
        <div
          v-for="area in areas"
          :key="area.id"
          class="w-1/2 rounded-md p-2"
          style="background: rgb(65, 154, 174)"
          @click="selectActiveArea(area.id)">
          <div class="text-center text-xl font-medium uppercase text-white">
            {{ area.name }}
          </div>
          <div
            class="h-10 w-full"
            :style="'background-image: url(' + area.pattern + '); background-size: 80%;'"></div>
        </div>
      </div>
      <div class="flex gap-5">
        <div
          class="flex items-center gap-3 rounded-md bg-primary px-6 py-5 text-xl text-white"
          @click="showTransactionsModal = true">
          <img
            :src="$filters.imgUrl('coins.svg')"
            alt="icon"
            width="28" />
          <div class="w-full font-medium uppercase tracking-wide">Promet</div>
        </div>
        <div
          class="flex items-center gap-3 rounded-md bg-primary px-6 py-5 text-xl text-white"
          @click="showInvoicesModal = true">
          <img
            :src="$filters.imgUrl('receipt.svg')"
            alt="icon"
            width="28" />
          <div class="w-full font-medium uppercase tracking-wide">Računi</div>
        </div>
      </div>
      <div class="flex items-center">
        <div class="flex w-56 flex-col text-center text-base font-bold text-white">
          <div>Radni dan</div>
          <div class="font-roboto-mono">{{ timestamp }}</div>
        </div>
        <div
          class="NotificationIcon relative flex items-center justify-center rounded-md pr-8"
          @click="showTasksModal = true">
          <div
            v-if="tasks.length"
            class="absolute right-0 top-0 mr-6 mt-1 flex h-6 w-6 items-center justify-center rounded-full bg-red-500">
            <div
              class="text-sm font-bold leading-none text-white"
              :class="{ 'text-xs': tasks.length > 9 }">
              {{ tasks.length }}
            </div>
          </div>
          <BellIcon class="animateBell h-10 w-10 text-white" />
        </div>
      </div>
    </div>
    <InvoicesModal
      v-if="showInvoicesModal"
      @close="showInvoicesModal = false" />
    <TransactionsModal
      v-if="showTransactionsModal"
      @close="showTransactionsModal = false" />
    <TasksModal
      v-if="showTasksModal"
      @close="showTasksModal = false" />
  </div>
</template>

<script>
import TransactionsModal from './Modals/TransactionsModal.vue'
import InvoicesModal from './Modals/InvoicesModal.vue'
import TasksModal from './Modals/TasksModal.vue'
import { BellIcon } from '@heroicons/vue/outline'
export default {
  components: { TransactionsModal, InvoicesModal, BellIcon, TasksModal },
  data: () => ({
    timestamp: null,
    showTransactionsModal: false,
    showInvoicesModal: false,
    showTasksModal: false,
    tabs: [
      { id: 0, name: 'Promet', url: '/transactions', icon: '/images/coins.svg' },
      { id: 1, name: 'Računi', url: '/invoices', icon: '/images/receipt.svg' }
    ]
  }),
  computed: {
    activeArea() {
      return this.$store.getters.getActiveArea
    },
    areas() {
      return this.$store.getters.getAreas
    },
    tasks() {
      return this.$store.getters.tasks.filter((t) => !t.done)
    }
  },
  mounted() {
    this.timestamp = this.getDate()
    setInterval(() => {
      this.timestamp = this.getDate()
    }, 1000)
  },
  methods: {
    selectActiveArea(id) {
      this.$store.dispatch(
        'storeActiveArea',
        this.areas.find((a) => a.id === id)
      )
      this.$router.push('/')
    },
    getDate() {
      const now = new Date()
      const date = now.getHours() >= 0 && now.getHours() <= 4 ? now.getDate() - 1 : now.getDate()
      const fullDate = date + '.' + (now.getMonth() + 1) + '.' + now.getFullYear()
      const hours = now.getHours() < 10 ? '0' + now.getHours() : now.getHours()
      const minutes = now.getMinutes() < 10 ? '0' + now.getMinutes() : now.getMinutes()
      const seconds = now.getSeconds() < 10 ? '0' + now.getSeconds() : now.getSeconds()
      const time = hours + ':' + minutes + ':' + seconds
      const timestamp = fullDate + ' ' + time
      return timestamp.toString().replaceAll('0', 'O')
    }
  }
}
</script>

<style scoped>
.NotificationIcon {
  height: 65px;
}
.animateDot {
  animation: pulse 3s linear infinite;
}
.font-roboto-mono {
  font-family: 'Roboto Mono', monospace;
}

@keyframes pulse {
  0% {
    transform: scale(1.1);
  }
  50% {
    transform: scale(0.8);
  }
  100% {
    transform: scale(1.1);
  }
}
</style>

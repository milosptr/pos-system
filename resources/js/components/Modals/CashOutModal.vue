<template>
  <Modal>
    <div
      v-if="showDiscount"
      class="flex h-full flex-col justify-between">
      <div>
        <div v-if="!showDiscountModal">
          <div class="mb-6 text-center text-3xl font-semibold uppercase">Dodavanje popusta</div>
          <EnterPin @success="openDiscountModal" />
        </div>
        <div v-else>
          <div class="mb-6 text-center text-3xl font-semibold uppercase">Klijenti</div>
          <div class="grid max-h-[300px] grid-cols-2 gap-4 overflow-scroll">
            <div
              v-for="client in clients"
              :key="client.id"
              class="flex items-center justify-center border-2 border-gray-200 bg-gray-200 px-2 py-3 text-center text-xl font-semibold"
              @click="applyDiscount(client.discount)">
              {{ client.name }} - {{ client.discount }}%
            </div>
          </div>
        </div>
      </div>
      <div
        class="rounded-sm bg-gray-200 py-5 text-center text-2xl font-bold uppercase text-red-500"
        :class="{ 'mx-10': !showDiscountModal }"
        @click="toggleShowDiscount">
        Odustani
      </div>
    </div>
    <div
      v-else
      class="flex h-full flex-col justify-between">
      <div class="">
        <div class="mb-6 text-center text-3xl font-semibold uppercase">Naplata</div>
        <div class="grid grid-cols-2 gap-3">
          <div
            class="my-1 border-2 border-gray-200 px-2 py-6 text-center text-2xl font-semibold"
            :class="[isOnTheHouse ? 'bg-primary text-white' : 'bg-gray-200']"
            @click="isOnTheHouse = true">
            Reprezentacija
          </div>
          <div
            v-if="discount"
            class="my-1 border-2 border-gray-200 bg-gray-200 px-2 py-6 text-center text-2xl font-semibold">
            Popust: {{ discount }}%
          </div>
          <div
            v-else
            class="my-1 border-2 border-gray-200 bg-gray-200 px-2 py-6 text-center text-2xl font-semibold"
            @click="toggleShowDiscount">
            Popust
          </div>
        </div>
        <div
          v-if="discount"
          class="my-1 border-2 border-gray-200 bg-gray-200 px-2 py-6 text-center text-2xl font-semibold">
          Cena sa popustom: {{ $filters.formatPrice(totalWithDiscount) }} RSD
        </div>
        <div
          v-if="showError"
          class="mt-2 text-2xl font-medium text-red-500">
          Izaberite konobara da biste naplatili
        </div>
      </div>
      <div class="flex flex-col gap-2">
        <div
          v-if="discount"
          class="rounded-sm bg-gray-200 py-5 text-center text-2xl font-bold uppercase text-red-500"
          @click="toggleShowDiscount">
          Ukloni popust
        </div>
        <div
          v-if="isOnTheHouse"
          class="rounded-sm bg-gray-200 py-5 text-center text-2xl font-bold uppercase text-red-500"
          @click="isOnTheHouse = false">
          Ukloni reprezentaciju
        </div>
        <div class="flex gap-2">
          <div
            class="w-1/2 rounded-sm bg-red-500 py-5 text-center text-2xl font-bold uppercase text-white"
            @click="$emit('close')">
            Odustani
          </div>
          <div
            class="w-1/2 rounded-sm bg-green-500 py-5 text-center text-2xl font-bold uppercase text-white"
            @click="cashOut">
            Naplati
          </div>
        </div>
      </div>
    </div>
  </Modal>
</template>

<script>
import Modal from './Modal.vue'
import EnterPin from '../EnterPin.vue'

export default {
  components: {
    Modal,
    EnterPin
  },
  computed: {
    waiters() {
      return this.$store.getters.waiters
    },
    selectedWaiterId() {
      return this.$store.getters.selectedWaiterId
    },
    discount() {
      return this.$store.getters.discount
    },
    total() {
      return this.$store.getters.activeTable.total
    },
    totalWithDiscount() {
      if (this.discount) return this.total - (this.total * this.discount) / 100
      return this.total
    }
  },
  data: () => ({
    showError: false,
    showDiscount: false,
    showDiscountModal: false,
    isOnTheHouse: false,
    clients: []
  }),
  mounted() {
    axios.get('/api/backoffice/clients').then((res) => {
      this.clients = res.data
    })
  },
  methods: {
    openDiscountModal() {
      this.showDiscountModal = true
    },
    applyDiscount(discount = 0) {
      this.$store.commit('setDiscount', discount)
      this.showDiscount = false
      this.showDiscountModal = false
    },
    toggleShowDiscount() {
      if (this.discount) {
        this.$store.commit('setDiscount', 0)
        return
      }
      this.showDiscount = !this.showDiscount
    },
    selectedWaiter(id) {
      this.$store.commit('setSelectedWaiterId', id)
      this.showError = false
    },
    cashOut() {
      if (this.selectedWaiterId === null) {
        this.showError = true
        return
      }
      const status = this.isOnTheHouse ? 2 : 1
      this.$emit('charge', status)
    }
  }
}
</script>

<style scoped>
.overflow-scroll {
  -ms-overflow-style: none; /* Internet Explorer 10+ */
  scrollbar-width: none; /* Firefox */
}
.overflow-scroll::-webkit-scrollbar {
  display: none; /* Safari and Chrome */
}
</style>

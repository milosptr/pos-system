<template>
  <Modal>
    <div v-if="showDiscount" class="flex flex-col justify-between h-full">
      <div>
        <div class="text-center text-3xl font-semibold mb-6 uppercase">Dodavanje popusta</div>
        <EnterPin @success="applyDiscount" />
      </div>
      <div
        class="bg-gray-200 py-5 rounded-sm text-2xl uppercase font-bold text-center text-red-500 mx-10"
        @click="toggleShowDiscount"
      >
        Odustani
      </div>
    </div>
    <div v-else class="flex flex-col justify-between h-full">
      <div class="">
        <div class="text-center text-3xl font-semibold mb-6 uppercase">Naplata</div>
        <div class="grid grid-cols-2 gap-3">
          <div
            class="py-6 px-2 text-2xl my-1 text-center font-semibold border-2 border-gray-200 bg-gray-200"
          >
            Konobar: Srdjan
          </div>
          <div
            v-if="discount"
            class="hidden py-6 px-2 text-2xl my-1 text-center font-semibold border-2 border-gray-200 bg-gray-200"
          >
            Popust: {{ discount }}%
          </div>
          <div
            v-else
            class="hidden py-6 px-2 text-2xl my-1 text-center font-semibold border-2 border-gray-200 bg-gray-200"
            @click="toggleShowDiscount"
          >
            Popust
          </div>
        </div>
        <div
          v-if="discount"
          class="py-6 px-2 text-2xl my-1 text-center font-semibold border-2 border-gray-200 bg-gray-200"
        >
          Cena sa popustom: {{ $filters.formatPrice(totalWithDiscount) }} RSD
        </div>
        <div v-if="showError" class="mt-2 text-2xl text-red-500 font-medium">Izaberite konobara da biste naplatili</div>
      </div>
      <div class="flex flex-col gap-2">
        <div
          v-if="discount"
          class="bg-gray-200 py-5 rounded-sm text-2xl uppercase font-bold text-center text-red-500"
          @click="toggleShowDiscount"
        >
         Ukloni popust
        </div>
        <div class="flex gap-2">
          <div
            class="bg-red-500 w-1/2 py-5 rounded-sm text-2xl uppercase font-bold text-center text-white"
            @click="$emit('close')"
          >
            Odustani
          </div>
          <div
            class="bg-green-500 w-1/2 py-5 rounded-sm text-2xl uppercase font-bold text-center text-white"
            @click="cacheOut"
          >
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
      EnterPin,
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
        if(this.discount)
          return this.total - (this.total * this.discount / 100)
        return this.total
      },
    },
    data: () => ({
      showError: false,
      showDiscount: false,
    }),
    methods: {
      applyDiscount() {
        this.$store.commit('setDiscount', 15)
        this.showDiscount = false
      },
      toggleShowDiscount() {
        if(this.discount) {
          this.$store.commit('setDiscount', 0)
          return
        }
        this.showDiscount = !this.showDiscount
      },
      selectedWaiter(id) {
        this.$store.commit('setSelectedWaiterId', id)
        this.showError = false
      },
      cacheOut() {
        if(this.selectedWaiterId === null) {
          this.showError = true
          return
        }
        this.$emit('charge')
      }
    }
  }
</script>

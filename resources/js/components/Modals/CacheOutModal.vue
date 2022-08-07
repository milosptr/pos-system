<template>
  <Modal>
    <div class="flex flex-col justify-between h-full">
      <div class="">
        <div class="text-center text-3xl font-semibold mb-6 uppercase">Izaberite konobara</div>
        <div class="grid grid-cols-3 gap-3">
          <div
            class="py-10 px-2 text-2xl my-1 text-center font-semibold border-2 border-primary bg-primary text-white"
          >
            Srdjan
          </div>
          <!-- <div
            v-for="waiter in waiters"
            :key="waiter.id"
            class="py-10 px-2 text-2xl my-1 text-center font-semibold border-2"
            :class="[waiter.id === selectedWaiterId ? 'border-primary bg-primary text-white' : 'bg-gray-200 border-transparent']"
            @click="selectedWaiter(waiter.id)"
          >
            {{ waiter.name }}
          </div> -->
        </div>
        <div v-if="showError" class="mt-2 text-2xl text-red-500 font-medium">Izaberite konobara da biste naplatili</div>
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
  </Modal>
</template>

<script>
  import Modal from './Modal.vue'

  export default {
    components: {
      Modal,
    },
    computed: {
      waiters() {
        return this.$store.getters.waiters
      },
      selectedWaiterId() {
        return this.$store.getters.selectedWaiterId
      },
    },
    data: () => ({
      showError: false,
    }),
    methods: {
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

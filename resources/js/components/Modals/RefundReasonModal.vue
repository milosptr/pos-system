<template>
  <Modal>
     <div class="flex flex-col justify-between h-full">
      <div class="">
        <div class="text-center text-3xl font-semibold mb-6 uppercase">Izaberite konobara</div>
        <div class="grid grid-cols-3 gap-3">
          <div
            v-for="waiter in waiters"
            :key="waiter.id"
            class="py-10 px-2 text-2xl my-1 text-center font-semibold border-2"
            :class="[waiter.id === selectedWaiterId ? 'border-primary bg-primary text-white' : 'bg-gray-200 border-transparent']"
            @click="selectedWaiter(waiter.id)"
          >
            {{ waiter.name }}
          </div>
        </div>
        <div class="text-center text-3xl font-semibold mb-6 uppercase mt-10">Izaberite razlog storniranja</div>
        <div class="TablesListHeight grid grid-cols-2 gap-3">
          <div
            v-for="reason in reasons"
            :key="reason.id"
            class="py-6 px-2 text-xl text-center font-semibold border-2"
            :class="[reason.id === selectedReasonId ? 'border-primary bg-primary text-white' : 'bg-gray-200 border-transparent']"
            @click="selectReason(reason.id)"
          >
            {{ reason.name }}
          </div>
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
          @click="refund"
        >
          Storniraj
        </div>
      </div>
     </div>
  </Modal>
</template>

<script>
  import Modal from "./Modal.vue"

  export default {
    components: { Modal },
    data: () => ({
      reasons: [],
      selectedReasonId: null,
      showError: false,
    }),
    computed: {
      waiters() {
        return this.$store.getters.waiters
      },
      selectedWaiterId() {
        return this.$store.getters.selectedWaiterId
      },
    },
    mounted() {
      axios.get('/api/refund-reasons')
        .then((res) => {
          this.reasons = res.data
          this.selectedReasonId = res.data[0].id
        })
    },
    methods: {
      selectReason(id) {
        this.selectedReasonId = id
      },
      selectedWaiter(id) {
        this.$store.commit('setSelectedWaiterId', id)
        this.showError = false
      },
      refund() {
        if(this.selectedWaiterId === null) {
          this.showError = true
          return
        }
        this.$store.dispatch('cashOut', { table_id: this.$route.params.id, status: 0, user_id: this.selectedWaiterId, wrefund_reason_id: this.selectedReasonId  })
        this.$router.push('/')
      },
    }

  }
</script>

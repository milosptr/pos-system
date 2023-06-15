<template>
  <Modal>
    <div class="flex h-full flex-col justify-between">
      <div class="">
        <div
          v-if="!preventEvent"
          class="mb-6 text-center text-2xl font-semibold uppercase">
          Izaberite konobara
        </div>
        <div
          v-if="!preventEvent"
          class="grid grid-cols-3 gap-3">
          <div
            v-for="waiter in waiters"
            :key="waiter.id"
            class="my-1 border-2 px-2 py-5 text-center text-2xl font-semibold xl:py-10"
            :class="[
              waiter.id === selectedWaiterId ? 'border-primary bg-primary text-white' : 'border-transparent bg-gray-200'
            ]"
            @click="selectedWaiter(waiter.id)">
            {{ waiter.name }}
          </div>
        </div>
        <div class="mb-6 mt-10 text-center text-2xl font-semibold uppercase">Izaberite razlog storniranja</div>
        <div class="TablesListHeight grid grid-cols-2 gap-3">
          <div
            v-for="reason in reasons"
            :key="reason.id"
            class="border-2 px-2 py-5 text-center text-xl font-semibold xl:py-6"
            :class="[
              reason.id === selectedReasonId ? 'border-primary bg-primary text-white' : 'border-transparent bg-gray-200'
            ]"
            @click="selectReason(reason.id)">
            {{ reason.name }}
          </div>
        </div>
        <div
          v-if="showError"
          class="mt-2 text-2xl font-medium text-red-500">
          Izaberite konobara da biste naplatili
        </div>
      </div>
      <div class="flex gap-2">
        <div
          class="w-1/2 rounded-sm bg-red-500 py-5 text-center text-2xl font-bold uppercase text-white"
          @click="$emit('close')">
          Odustani
        </div>
        <div
          class="w-1/2 rounded-sm bg-green-500 py-5 text-center text-2xl font-bold uppercase text-white"
          @click="refund">
          Storniraj
        </div>
      </div>
    </div>
  </Modal>
</template>

<script>
import Modal from './Modal.vue'

export default {
  props: {
    preventEvent: {
      type: Boolean,
      default: () => false
    }
  },
  components: { Modal },
  data: () => ({
    reasons: [],
    selectedReasonId: null,
    showError: false
  }),
  computed: {
    waiters() {
      return this.$store.getters.waiters
    },
    selectedWaiterId() {
      return this.$store.getters.selectedWaiterId
    }
  },
  mounted() {
    axios.get('/api/refund-reasons').then((res) => {
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
      if (this.preventEvent) {
        this.$emit('refund', { status: 0, refund_reason_id: this.selectedReasonId })
        return
      }
      if (this.selectedWaiterId === null) {
        this.showError = true
        return
      }

      this.$store.dispatch('cashOut', {
        table_id: this.$route.params.id,
        status: 0,
        user_id: this.selectedWaiterId,
        refund_reason_id: this.selectedReasonId
      })
      this.$router.push('/')
    }
  }
}
</script>

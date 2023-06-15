<template>
  <Modal>
    <div class="flex h-full flex-col justify-between">
      <div class="">
        <div class="mb-6 text-center text-3xl font-semibold uppercase">Izaberite novi sto</div>
        <div class="TablesListHeight grid grid-cols-4 gap-3">
          <div
            v-for="table in tables"
            :key="table.id"
            class="border-2 px-2 py-4 text-center text-lg font-semibold"
            :class="[
              table.id === selectedTableId ? 'border-primary bg-primary text-white' : 'border-transparent bg-gray-200'
            ]"
            @click="selectTable(table.id)">
            {{ table.name }}
          </div>
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
          @click="move">
          Prenesi
        </div>
      </div>
    </div>
  </Modal>
</template>

<script>
import Modal from './Modal.vue'

export default {
  components: {
    Modal
  },
  data: () => ({
    tables: [],
    selectedTableId: null
  }),
  computed: {
    activeTable() {
      return this.$store.getters.activeTable
    }
  },
  mounted() {
    axios.get('/api/tables/available').then((res) => {
      this.tables = res.data.data
    })
  },
  methods: {
    selectTable(id) {
      this.selectedTableId = id
    },
    move() {
      this.$store.dispatch('moveOrdersToTable', { from: this.activeTable.id, to: this.selectedTableId }).then(() => {
        this.$router.push('/')
      })
    }
  }
}
</script>

<style scoped>
.TablesListHeight {
  max-height: 55vh;
  overflow-y: scroll;
}
</style>

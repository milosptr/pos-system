<template>
  <Modal>
    <div class="flex flex-col justify-between h-full">
      <div class="">
        <div class="text-center text-3xl font-semibold mb-6 uppercase">Izaberite novi sto</div>
        <div class="TablesListHeight grid grid-cols-4 gap-3">
          <div
            v-for="table in tables"
            :key="table.id"
            class="py-4 px-2 text-lg text-center font-semibold border-2"
            :class="[table.id === selectedTableId ? 'border-primary bg-primary text-white' : 'bg-gray-200 border-transparent']"
            @click="selectTable(table.id)"
          >
            {{ table.name }}
          </div>
        </div>
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
          @click="move"
        >
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
      Modal,
    },
    data: () => ({
      tables: [],
      selectedTableId: null,
    }),
    computed: {
      activeTable() {
        return this.$store.getters.activeTable
      }
    },
    mounted() {
      axios.get('/api/tables/available')
        .then((res) => {
          this.tables = res.data.data
        })
    },
    methods: {
      selectTable(id) {
        this.selectedTableId = id
      },
      move() {
        this.$store.dispatch('moveOrdersToTable', { from: this.activeTable.id, to: this.selectedTableId})
          .then(() => {
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

<template>
  <div>
    <BackofficeWarehouseNew @saved="getWharehouse" />
    <div class="border border-solid border-gray-200 bg-white">
      <div
        class="grid grid-cols-4 sm:grid-cols-5 gap-4 font-semibold py-1 border-b border-solid border-gray-300 bg-gray-200">
        <div class="px-4">Ime sirovine</div>
        <div class="px-4 hidden sm:block">Jedinica</div>
        <div class="px-4">Kuhinja/Šank</div>
        <div class="px-4">Kategorija</div>
        <div class="px-4"></div>
      </div>
      <div
        v-for="(item, index) in warehouse"
        :key="item.id"
        class="grid grid-cols-4 sm:grid-cols-5 gap-4 py-1"
        :class="{ 'bg-gray-100': index % 2 }"
        draggable="true"
        @dragstart="dragStart(index)"
        @dragover.prevent
        @drop="drop(index)">
        <div class="px-4 cursor-grab">{{ item.name }}</div>
        <div class="px-4 hidden sm:block">{{ item.unit }}</div>
        <div class="px-4">{{ item.category_parent }}</div>
        <div class="px-4">{{ item.category?.name }}</div>
        <div class="px-4 flex justify-end gap-3">
          <ReloadIcon
            class="w-4 h-4 cursor-pointer text-blue-500"
            @click="resetModal = item" />
          <TrashIcon
            class="w-5 h-5 cursor-pointer text-red-500"
            @click="deleteModal = item" />
        </div>
      </div>
    </div>
    <DeleteModal
      :show="!!resetModal"
      :title="resetModalTitle"
      :body="resetModalBody"
      @close="resetModal = null"
      @delete="resetWarehouseItem" />
    <DeleteModal
      :show="!!deleteModal"
      :title="deleteModalTitle"
      :body="deleteModalBody"
      @close="deleteModal = null"
      @delete="deleteWarehouseItem" />
  </div>
</template>

<script>
import { TrashIcon } from '@heroicons/vue/outline'
import DeleteModal from '@/js/components/Modals/DeleteModal.vue'
import BackofficeWarehouseNew from '@/js/components/Backoffice/Warehouse/BackofficeWarehouseNew.vue'
import ReloadIcon from '@/icons/ReloadIcon.vue'
export default {
  name: 'WarehouseItems',
  components: {
    BackofficeWarehouseNew,
    DeleteModal,
    TrashIcon,
    ReloadIcon
  },
  data: () => ({
    warehouse: [],
    deleteModal: null,
    resetModal: null
  }),
  mounted() {
    this.getWharehouse()
  },
  computed: {
    deleteModalTitle() {
      return 'Brisanje sirovine'
    },
    deleteModalBody() {
      return 'Da li ste sigurni da želite da obrišete ovu sirovinu?'
    },
    resetModalTitle() {
      return `Resetovanje sirovine ${this.resetModal?.name}`
    },
    resetModalBody() {
      return 'Da li ste sigurni da želite da resetujete ovu sirovinu?'
    }
  },
  methods: {
    dragStart(index) {
      this.draggedIndex = index
    },
    resetWarehouseItem() {
      axios.patch(`/api/backoffice/warehouse/${this.resetModal.id}/reset`).then(() => {
        this.getWharehouse()
        this.resetModal = null
        alert('Sirovina je resetovana')
      })
    },
    drop(dropIndex) {
      if (window.innerWidth < 768) return
      const itemToMove = this.warehouse.splice(this.draggedIndex, 1)[0]
      this.warehouse.splice(dropIndex, 0, itemToMove)
      this.warehouse = this.warehouse.map((item, index) => {
        return {
          ...item,
          order: index
        }
      })
      axios.patch(
        '/api/backoffice/warehouse/order',
        this.warehouse.map((item) => ({
          id: item.id,
          order: item.order
        }))
      )
    },
    getWharehouse() {
      axios
        .get('/api/backoffice/warehouse')
        .then((response) => {
          this.warehouse = response.data.data
        })
        .catch((error) => {
          console.log(error)
        })
    },
    deleteWarehouseItem() {
      axios
        .delete(`/api/backoffice/warehouse/${this.deleteModal.id}`)
        .then(() => {
          this.warehouse = this.warehouse.filter((item) => item.id !== this.deleteModal.id)
          this.deleteModal = null
          this.getWharehouse()
        })
        .catch((error) => {
          console.log(error)
        })
    }
  }
}
</script>

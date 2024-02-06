<template>
  <div>
    <BackofficeWarehouseNew @saved="getWharehouse" />
    <div class="border border-solid border-gray-200 bg-white">
      <div class="grid grid-cols-5 gap-4 font-semibold py-1 border-b border-solid border-gray-300 bg-gray-200">
        <div class="px-4">Ime sirovine</div>
        <div class="px-4">Jedinica</div>
        <div class="px-4">Kuhinja/Šank</div>
        <div class="px-4">Kategorija</div>
        <div class="px-4"></div>
      </div>
      <div
        v-for="(item, index) in warehouse"
        :key="item.id"
        class="grid grid-cols-5 gap-4 py-1"
        :class="{ 'bg-gray-100': index % 2 }">
        <div class="px-4">{{ item.name }}</div>
        <div class="px-4">{{ item.unit }}</div>
        <div class="px-4">{{ item.category_parent }}</div>
        <div class="px-4">{{ item.category?.name }}</div>
        <div class="px-4 flex justify-end">
          <TrashIcon
            class="w-5 h-5 cursor-pointer text-red-500"
            @click="deleteModal = item" />
        </div>
      </div>
    </div>
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
export default {
  name: 'WarehouseItems',
  components: {
    BackofficeWarehouseNew,
    DeleteModal,
    TrashIcon
  },
  data: () => ({
    warehouse: [],
    deleteModal: null
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
    }
  },
  methods: {
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

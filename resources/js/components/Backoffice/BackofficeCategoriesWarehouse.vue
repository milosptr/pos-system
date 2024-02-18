<template>
  <div>
    <table
      class="min-w-full border-separate"
      style="border-spacing: 0">
      <thead class="bg-gray-50">
        <tr class="">
          <th
            class="sticky top-0 z-10 border-b border-gray-300 bg-gray-50 bg-opacity-75 px-3 py-3.5 text-left text-sm font-semibold text-gray-900 backdrop-blur backdrop-filter sm:table-cell">
            ID
          </th>
          <th
            class="sticky top-0 z-10 border-b border-gray-300 bg-gray-50 bg-opacity-75 px-3 py-3.5 text-left text-sm font-semibold text-gray-900 backdrop-blur backdrop-filter sm:table-cell">
            Ime
          </th>
          <th
            class="sticky top-0 z-10 border-b border-gray-300 bg-gray-50 bg-opacity-75 px-3 py-3.5 text-left text-sm font-semibold text-gray-900 backdrop-blur backdrop-filter sm:table-cell">
            Grupa
          </th>
          <th
            class="sticky top-0 z-10 border-b border-gray-300 bg-gray-50 bg-opacity-75 px-3 py-3.5 text-left text-sm font-semibold text-gray-900 backdrop-blur backdrop-filter sm:table-cell"></th>
        </tr>
      </thead>
      <tbody class="bg-white">
        <tr
          v-for="(category, idx) in categories"
          :key="category.id"
          :class="{ 'bg-gray-50': idx % 2 === 1 }"
          draggable="true"
          @dragstart="dragStart(idx)"
          @dragover.prevent
          @drop="drop(idx)">
          <td class="px-3 text-sm py-1.5">{{ idx + 1 }}</td>
          <td class="px-3 text-sm py-1.5 cursor-grab">{{ category.name }}</td>
          <td class="px-3 text-sm py-1.5">{{ category.parent_id === 0 ? 'Šank' : 'Kuhinja' }}</td>
          <td class="px-3 text-sm py-1.5 text-right">
            <div
              class="text-red-500 cursor-pointer"
              @click="() => handleDelete(category)">
              Delete
            </div>
          </td>
        </tr>
      </tbody>
    </table>
    <DeleteModal
      :show="showDeleteModal"
      :title="'Delete - ' + itemToDelete?.name"
      @close="showDeleteModal = false"
      @delete="deleteItem" />
  </div>
</template>

<script>
import DeleteModal from '@/js/components/Modals/DeleteModal.vue'

export default {
  name: 'BackofficeCategoriesWarehouse',
  components: { DeleteModal },
  data: () => ({
    name: null,
    unit: null,
    category: null,
    categories: [],
    itemToDelete: null,
    showDeleteModal: false
  }),
  mounted() {
    this.getCategories()
  },
  methods: {
    dragStart(index) {
      this.draggedIndex = index
    },
    drop(dropIndex) {
      if (window.innerWidth < 768) return
      const itemToMove = this.categories.splice(this.draggedIndex, 1)[0]
      this.categories.splice(dropIndex, 0, itemToMove)
      this.categories = this.categories.map((item, index) => {
        return {
          ...item,
          order: index
        }
      })
      axios.patch(
        '/api/backoffice/warehouse-categories/order',
        this.categories.map((item) => ({
          id: item.id,
          order: item.order
        }))
      )
    },
    getCategories() {
      axios.get('/api/backoffice/warehouse-categories').then((res) => {
        this.categories = res.data
      })
    },
    handleDelete(item) {
      this.itemToDelete = item
      this.showDeleteModal = true
    },
    deleteItem() {
      axios.delete('/api/backoffice/warehouse-categories/' + this.itemToDelete.id).then(() => {
        this.getCategories()
        this.showDeleteModal = false
        this.itemToDelete = null
      })
    }
  }
}
</script>

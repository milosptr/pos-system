<template>
  <tr
    class="cursor-pointer hover:bg-orange-50"
    :class="{ 'bg-blue-200 hover:bg-blue-200': isChanged }">
    <td
      :class="[
        idx !== clients.length - 1 ? 'border-b border-gray-200' : '',
        'whitespace-nowrap py-2 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6 lg:pl-8'
      ]">
      {{ idx + 1 }}
    </td>
    <td
      :class="[
        idx !== clients.length - 1 ? 'border-b border-gray-200' : '',
        'whitespace-nowrap px-3 py-2 text-sm text-gray-500 '
      ]">
      <input
        v-model="item.name"
        type="text"
        class="min-w-input-name m-0 w-full appearance-none border-none bg-transparent p-0" />
    </td>
    <td
      :class="[
        idx !== clients.length - 1 ? 'border-b border-gray-200' : '',
        'whitespace-nowrap px-3 py-2 text-sm text-gray-500'
      ]">
      <input
        v-model="item.discount"
        type="text"
        class="m-0 w-16 appearance-none border-none bg-transparent p-0" />
    </td>
    <td
      class="text-right"
      :class="[
        idx !== clients.length - 1 ? 'border-b border-gray-200' : '',
        'whitespace-nowrap px-3 py-2 text-sm text-gray-500'
      ]">
      <div class="flex justify-end gap-4">
        <div
          class="text-blue-500"
          @click="updateItem">
          Save
        </div>
        <div
          class="text-red-500"
          @click="showDeleteModal = true">
          Delete
        </div>
      </div>
    </td>
    <DeleteModal
      :show="showDeleteModal"
      :title="'Delete - ' + item.name"
      @close="showDeleteModal = false"
      @delete="deleteItem" />
  </tr>
</template>

<script>
import Switch from '../common/Switch.vue'
import DeleteModal from '../Modals/DeleteModal.vue'

export default {
  props: {
    idx: {
      type: Number,
      default: () => 0
    },
    item: {
      type: Object,
      default: () => {}
    },
    clients: {
      type: Object,
      default: () => {}
    }
  },
  components: {
    Switch,
    DeleteModal
  },
  data: () => ({
    defaultItem: null,
    showDeleteModal: false
  }),
  computed: {
    isChanged() {
      return !_.isEqual(this.defaultItem, this.item)
    }
  },
  mounted() {
    this.defaultItem = { ...this.item }
  },
  methods: {
    updateItem() {
      axios.post('/api/backoffice/clients/' + this.item.id, this.item).then((res) => {
        this.defaultItem = { ...this.item }
      })
    },
    deleteItem() {
      axios.delete('/api/backoffice/clients/' + this.item.id).then(() => {
        this.$store.dispatch('getClients', {})
      })
    }
  }
}
</script>

<style scoped>
.min-w-input-name {
  min-width: 150px;
}
</style>

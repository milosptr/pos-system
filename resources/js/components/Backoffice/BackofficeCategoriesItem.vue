<template>
  <tr
    class="cursor-pointer hover:bg-orange-50"
    :class="{ 'bg-blue-200 hover:bg-blue-200': isChanged }">
    <td
      :class="[
        idx !== categories.length - 1 ? 'border-b border-gray-200' : '',
        'whitespace-nowrap py-2 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6 lg:pl-8'
      ]">
      {{ item.id }}
    </td>
    <td
      :class="[
        idx !== categories.length - 1 ? 'border-b border-gray-200' : '',
        'whitespace-nowrap px-3 py-2 text-sm text-gray-500 '
      ]">
      <input
        v-model="item.name"
        type="text"
        class="min-w-input-name m-0 w-full appearance-none border-none bg-transparent p-0" />
    </td>
    <td
      :class="[
        idx !== categories.length - 1 ? 'border-b border-gray-200' : '',
        'whitespace-nowrap px-3 py-2 text-sm text-gray-500 '
      ]">
      {{ item.parent_id ? 'Kuhinja' : 'Šank' }}
    </td>
    <td
      :class="[
        idx !== categories.length - 1 ? 'border-b border-gray-200' : '',
        'whitespace-nowrap px-3 py-2 text-sm text-gray-500'
      ]">
      <input
        v-model="item.order"
        type="number"
        class="m-0 w-16 appearance-none border-none bg-transparent p-0" />
    </td>
    <td
      :class="[
        idx !== categories.length - 1 ? 'border-b border-gray-200' : '',
        'whitespace-nowrap px-3 py-2 text-sm text-gray-500'
      ]">
      <Switch
        :enabled="!!item.print"
        @click="emitFun" />
    </td>
    <td
      :class="[
        idx !== categories.length - 1 ? 'border-b border-gray-200' : '',
        'whitespace-nowrap px-3 py-2 text-sm text-gray-500'
      ]">
      <input
        v-model="item.color"
        type="color"
        name=""
        id="" />
    </td>
    <td
      class="text-right"
      :class="[
        idx !== categories.length - 1 ? 'border-b border-gray-200' : '',
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
    categories: {
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
      axios.put('/api/backoffice/categories/' + this.item.id, this.item).then((res) => {
        this.defaultItem = { ...this.item }
      })
    },
    deleteItem() {
      axios.delete('/api/backoffice/categories/' + this.item.id).then(() => {
        this.$store.dispatch('getCategories', {})
      })
    },
    emitFun(e, b) {
      this.item.print = !this.item.print ? 1 : 0
    },
    selectAll(e) {
      e.target.select()
    }
  }
}
</script>

<style scoped>
.min-w-input-name {
  min-width: 150px;
}
</style>

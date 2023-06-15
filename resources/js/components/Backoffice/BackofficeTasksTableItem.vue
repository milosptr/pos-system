<template>
  <tr class="cursor-pointer hover:bg-orange-50">
    <td
      :class="[
        idx !== tasks.length - 1 ? 'border-b border-gray-200' : '',
        'whitespace-nowrap px-3 py-2 text-sm font-medium text-gray-900'
      ]">
      {{ item.id }}
    </td>
    <td
      :class="[
        idx !== tasks.length - 1 ? 'border-b border-gray-200' : '',
        'whitespace-nowrap px-3 py-2 text-sm text-gray-900'
      ]">
      {{ item.company }}
    </td>
    <td
      :class="[
        idx !== tasks.length - 1 ? 'border-b border-gray-200' : '',
        'whitespace-nowrap px-3 py-2 text-sm text-gray-900'
      ]">
      {{ item.date }}
    </td>
    <td
      :class="[
        idx !== tasks.length - 1 ? 'border-b border-gray-200' : '',
        'whitespace-nowrap px-3 py-2 text-sm text-gray-900'
      ]">
      {{ item.price }} {{ item.price ? 'RSD' : '' }}
    </td>
    <td
      :class="[
        idx !== tasks.length - 1 ? 'border-b border-gray-200' : '',
        'whitespace-pre-wrap px-3 py-2 text-sm text-gray-900'
      ]">
      {{ item.message }}
    </td>
    <td
      :class="[
        idx !== tasks.length - 1 ? 'border-b border-gray-200' : '',
        'whitespace-nowrap px-3 py-2 text-center text-sm font-medium text-gray-900'
      ]">
      <div
        class="mx-auto h-3.5 w-3.5 rounded-full"
        :class="[item.done ? 'bg-green-500' : 'bg-red-500']"></div>
    </td>
    <td
      :class="[
        idx !== tasks.length - 1 ? 'border-b border-gray-200' : '',
        'whitespace-nowrap px-3 py-2 text-sm text-gray-500'
      ]">
      <div
        class="text-red-500"
        @click="showDeleteModal = true">
        Delete
      </div>
    </td>
    <DeleteModal
      :show="showDeleteModal"
      :title="'Delete - task ' + item.id"
      @close="showDeleteModal = false"
      @delete="deleteItem" />
  </tr>
</template>

<script>
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
    tasks: {
      type: Object,
      default: () => {}
    }
  },
  components: { DeleteModal },
  data: () => ({
    showDeleteModal: false
  }),
  mounted() {},
  methods: {
    deleteItem() {
      axios.delete('/api/tasks/' + this.item.id).then((res) => {
        this.$store.dispatch('getTasks')
      })
    }
  }
}
</script>

<style scoped>
.min-w-input-name {
  min-width: 100px;
}
</style>

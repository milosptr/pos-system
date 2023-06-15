<template>
  <Modal
    :superWide="true"
    bodyClass="overflow-scroll">
    <div class="mb-3 text-xl font-semibold">Refundacije</div>
    <table
      class="min-w-full border-separate"
      style="border-spacing: 0">
      <thead class="bg-gray-50">
        <tr>
          <th
            scope="col"
            class="border-b border-gray-300 bg-gray-50 bg-opacity-75 px-3 py-2 text-left text-sm font-semibold text-gray-900 backdrop-blur backdrop-filter sm:table-cell">
            Datum
          </th>
          <th
            scope="col"
            class="border-b border-gray-300 bg-gray-50 bg-opacity-75 px-3 py-2 text-left text-sm font-semibold text-gray-900 backdrop-blur backdrop-filter sm:table-cell">
            Firma
          </th>
          <th
            scope="col"
            class="border-b border-gray-300 bg-gray-50 bg-opacity-75 px-3 py-2 text-left text-sm font-semibold text-gray-900 backdrop-blur backdrop-filter sm:table-cell">
            Cena
          </th>
          <th
            scope="col"
            class="w-32 border-b border-gray-300 bg-gray-50 bg-opacity-75 px-3 py-2 text-right text-sm font-semibold text-gray-900 backdrop-blur backdrop-filter">
            Uradjeno
          </th>
        </tr>
      </thead>
      <tbody>
        <tr
          v-for="(task, idx) in tasks"
          :key="idx"
          class="cursor-pointer text-lg hover:bg-orange-50"
          :class="{ 'bg-gray-50': idx % 2 === 1, 'text-green-600': task.done }">
          <td class="whitespace-nowrap border-b border-gray-200 px-3 py-2 font-medium">{{ task.date }}</td>
          <td class="whitespace-nowrap border-b border-gray-200 px-3 py-2 font-medium">{{ task.company }}</td>
          <td class="whitespace-nowrap border-b border-gray-200 px-3 py-2 font-medium">
            {{ $filters.formatPrice(task.price) }} RSD
          </td>
          <td class="w-32 whitespace-nowrap border-b border-gray-200 px-3 py-2 text-right font-medium">
            <input
              :id="`task-${tasks.id}`"
              :name="`task-${tasks.id}`"
              type="checkbox"
              :checked="task.done"
              class="h-5 w-5 rounded border-2 border-red-500 text-green-600 outline-none ring-0 focus:ring-0 focus:ring-green-500"
              @click="finishTask(task)" />
          </td>
        </tr>
      </tbody>
    </table>

    <div class="mb-3 mt-6 text-xl font-semibold">Ostale poruke</div>
    <div class="flex flex-col gap-2">
      <div
        v-for="(message, idx) in messages"
        :key="idx"
        class="flex items-start justify-between rounded-md bg-gray-100 p-4 text-lg">
        <div
          class="mr-2 mt-0.5"
          :class="{ 'text-green-600': message.done }">
          {{ message.message }}
        </div>
        <div class="">
          <input
            :id="`task-${message.id}`"
            :name="`task-${message.id}`"
            type="checkbox"
            :checked="message.done"
            class="h-5 w-5 rounded border-2 border-red-500 text-green-600 outline-none ring-0 focus:ring-0 focus:ring-green-500"
            @click="finishTask(message)" />
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
  computed: {
    tasks() {
      return this.$store.getters.tasks.filter((t) => t.type === 1)
    },
    messages() {
      return this.$store.getters.tasks.filter((t) => t.type === 2)
    }
  },
  methods: {
    finishTask(task) {
      axios.post('/api/tasks/finish/' + task.id, { done: !task.done })
    }
  }
}
</script>

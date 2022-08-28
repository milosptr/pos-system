<template>
  <Modal :superWide="true" bodyClass="overflow-scroll">
     <div class="text-xl font-semibold mb-3">Refundacije</div>
     <table class="min-w-full border-separate" style="border-spacing: 0">
        <thead class="bg-gray-50">
          <tr>
            <th scope="col" class="border-b border-gray-300 bg-gray-50 bg-opacity-75 px-3 py-2 text-left text-sm font-semibold text-gray-900 backdrop-blur backdrop-filter sm:table-cell">Datum</th>
            <th scope="col" class="border-b border-gray-300 bg-gray-50 bg-opacity-75 px-3 py-2 text-left text-sm font-semibold text-gray-900 backdrop-blur backdrop-filter sm:table-cell">Firma</th>
            <th scope="col" class="border-b border-gray-300 bg-gray-50 bg-opacity-75 px-3 py-2 text-left text-sm font-semibold text-gray-900 backdrop-blur backdrop-filter sm:table-cell">Cena</th>
            <th scope="col" class="w-32 border-b border-gray-300 bg-gray-50 bg-opacity-75 px-3 py-2 text-sm font-semibold text-gray-900 backdrop-blur backdrop-filter text-right">Uradjeno</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(task, idx) in tasks" :key="idx" class="hover:bg-orange-50 cursor-pointer text-lg" :class="{'bg-gray-50': idx % 2 === 1, 'text-green-600': task.done }">
            <td class="border-b border-gray-200 whitespace-nowrap py-2 px-3 font-medium">{{ task.date }}</td>
            <td class="border-b border-gray-200 whitespace-nowrap py-2 px-3 font-medium">{{ task.company }}</td>
            <td class="border-b border-gray-200 whitespace-nowrap py-2 px-3 font-medium">{{ $filters.formatPrice(task.price) }} RSD</td>
            <td class="w-32 border-b border-gray-200 whitespace-nowrap py-2 px-3 font-medium text-right">
              <input :id="`task-${tasks.id}`" :name="`task-${tasks.id}`" type="checkbox" :checked="task.done" class="focus:ring-green-500 h-5 w-5 text-green-600 border-red-500 rounded outline-none ring-0 focus:ring-0" @click="finishTask(task)" />
            </td>
          </tr>
        </tbody>
     </table>

     <div class="text-xl font-semibold mt-6 mb-3">Ostale poruke</div>
     <div class="flex flex-col gap-2">
      <div v-for="(message, idx) in messages" :key="idx" class="text-lg rounded-md bg-gray-100 p-4 flex items-start justify-between">
        <div class="mr-2 mt-0.5" :class="{'text-green-600': message.done}">{{ message.message }}</div>
        <div class="">
          <input :id="`task-${message.id}`" :name="`task-${message.id}`" type="checkbox" :checked="message.done" class="focus:ring-green-500 h-5 w-5 text-green-600 border-red-500 rounded outline-none ring-0 focus:ring-0" @click="finishTask(message)" />
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
    computed: {
      tasks() {
        return this.$store.getters.tasks.filter((t) => t.type === 1)
      },
      messages() {
        return this.$store.getters.tasks.filter((t) => t.type === 2)
      },
    },
    methods: {
      finishTask(task) {
        axios.post('/api/tasks/finish/' + task.id, { done: !task.done })
      }
    }
  }
</script>

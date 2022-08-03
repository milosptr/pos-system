<template>
  <div class="mt-4">
    <Notification :show="saved" @close="saved = false" />
    <div class="w-full grid grid-cols-1 gap-4 xl:w-2/3">
      <div class="grid grid-cols-1 gap-4">
        <div>
          <label for="company" class="block text-sm font-medium text-gray-700">Company</label>
          <div class="mt-1">
            <input v-model="task.company" type="text" name="company" id="company" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md" placeholder="Company name" @input="changeType(TYPE_TASK)" />
          </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label for="date" class="block text-sm font-medium text-gray-700">Date</label>
            <div class="mt-1">
              <input v-model="task.date" type="date" name="date" id="date" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md" placeholder="Choose date" />
            </div>
          </div>
          <div>
            <label for="price" class="block text-sm font-medium text-gray-700">Price</label>
            <div class="mt-1">
              <input v-model="task.price" type="number" name="price" id="price" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md" placeholder="Price" @input="changeType(TYPE_TASK)" />
            </div>
          </div>
        </div>
        <div>
          <label for="comment" class="block text-sm font-medium text-gray-700">Add your comment</label>
          <div class="mt-1">
            <textarea v-model="task.message" rows="4" name="comment" id="comment" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md" @input="changeType(TYPE_MESSAGE)" />
          </div>
        </div>
      </div>
    </div>
    <div class="mt-10 flex-shrink-0">
      <button  @click="save" type="button" class="relative inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">Save task</button>
    </div>
    <div class="border-t border-gray-300 w-full my-6"></div>
    <BackofficeTasksTable />
  </div>
</template>
<script>
import { CheckCircleIcon, XIcon } from '@heroicons/vue/solid'
import Notification from '../common/Notification.vue'
import BackofficeTasksTable from './BackofficeTasksTable.vue'


export default {
    name: 'BackofficeMyAccount',
    components: {CheckCircleIcon, XIcon, Notification, BackofficeTasksTable},
    data: () => ({
      task: {
        type: 1,
      },
      TYPE_TASK: 1,
      TYPE_MESSAGE: 2,
      saved: false,
    }),
    computed: {},
    mounted() {
      this.$store.dispatch('getTasks')
    },
    methods: {
      changeType(type) {
        if(type === this.TYPE_TASK) {
          this.task.message = null
          this.task.type = this.TYPE_TASK
        }

        if(type === this.TYPE_MESSAGE) {
          this.task.company = null
          this.task.date = null
          this.task.price = null
          this.task.type = this.TYPE_MESSAGE
        }
      },
      save() {
        axios.post('/api/tasks', this.task)
          .then((res) => {
            this.$store.dispatch('getTasks')
            this.saved = true
            this.task = {}
            setTimeout(() => {
              this.saved = false
            }, 1000);
          })
      }
    }
}
</script>
<style scoped>

</style>

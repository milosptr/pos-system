<template>
  <div class="shadow sm:rounded-md sm:overflow-hidden">
    <div class="bg-white py-6 px-4 sm:p-6">
      <Notification :show="saved" @close="saved = false" />
      <h2 id="payment-details-heading" class="text-lg leading-6 font-medium text-gray-900">Personal information</h2>
      <div class="w-full xl:w-2/3 grid grid-cols-1 gap-4 mt-5">
        <div>
          <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
          <div class="mt-1">
            <input v-model="user.name" type="text" name="name" id="name" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md" placeholder="Product name" />
          </div>
        </div>
        <div>
          <label for="username" class="block text-sm font-medium text-gray-700">Username</label>
          <div class="mt-1">
            <input v-model="user.username" type="text" name="username" id="username" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md" placeholder="Product name" />
          </div>
        </div>
        <div>
          <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
          <div class="mt-1">
            <input v-model="user.password" type="password" name="password" id="password" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md" placeholder="New password" />
          </div>
        </div>
      </div>
      <div class="mt-12 flex-shrink-0" @click="update">
        <button type="button" class="relative inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">Update</button>
      </div>
    </div>
  </div>
</template>
<script>
import { CheckCircleIcon, XIcon } from '@heroicons/vue/solid'
import Notification from '../common/Notification.vue'

export default {
    name: 'BackofficeMyAccount',
    components: {CheckCircleIcon, XIcon, Notification},
    data: () => ({
      user: {},
      saved: false,
    }),
    computed: {},
    mounted() {
      axios.get('/api/backoffice/users/' + document.getElementById('backoffice').dataset.key)
        .then((res) => {
          this.user = res.data.data
        })
    },
    methods: {
      update() {
        axios.post('/api/backoffice/users/' + this.user.id, this.user)
          .then((res) => {
            this.user = res.data.data
            this.saved = true
          })
      }
    }
}
</script>
<style scoped>

</style>

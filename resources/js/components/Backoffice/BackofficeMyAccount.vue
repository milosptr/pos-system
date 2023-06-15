<template>
  <div class="shadow sm:overflow-hidden sm:rounded-md">
    <div class="bg-white px-4 py-6 sm:p-6">
      <Notification
        :show="saved"
        @close="saved = false" />
      <h2
        id="payment-details-heading"
        class="text-lg font-medium leading-6 text-gray-900">
        Moj profil
      </h2>
      <div class="mt-5 grid w-full grid-cols-1 gap-4 xl:w-2/3">
        <div>
          <label
            for="name"
            class="block text-sm font-medium text-gray-700"
            >Ime</label
          >
          <div class="mt-1">
            <input
              v-model="user.name"
              type="text"
              name="name"
              id="name"
              class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
              placeholder="Product name" />
          </div>
        </div>
        <div>
          <label
            for="username"
            class="block text-sm font-medium text-gray-700"
            >Korisničko ime</label
          >
          <div class="mt-1">
            <input
              v-model="user.username"
              type="text"
              name="username"
              id="username"
              class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
              placeholder="Product name" />
          </div>
        </div>
        <div>
          <label
            for="password"
            class="block text-sm font-medium text-gray-700"
            >Lozinka</label
          >
          <div class="mt-1">
            <input
              v-model="user.password"
              type="password"
              name="password"
              id="password"
              class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
              placeholder="New password" />
          </div>
        </div>
      </div>
      <div
        class="mt-12 flex-shrink-0"
        @click="update">
        <button
          type="button"
          class="relative inline-flex items-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
          Sačuvaj promenu
        </button>
      </div>
    </div>
  </div>
</template>
<script>
import { CheckCircleIcon, XIcon } from '@heroicons/vue/solid'
import Notification from '../common/Notification.vue'

export default {
  name: 'BackofficeMyAccount',
  components: { CheckCircleIcon, XIcon, Notification },
  data: () => ({
    user: {},
    saved: false
  }),
  computed: {},
  mounted() {
    axios.get('/api/backoffice/users/' + document.getElementById('backoffice').dataset.key).then((res) => {
      this.user = res.data.data
    })
  },
  methods: {
    update() {
      axios.post('/api/backoffice/users/' + this.user.id, this.user).then((res) => {
        this.user = res.data.data
        this.saved = true
      })
    }
  }
}
</script>
<style scoped></style>

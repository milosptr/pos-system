<template>
  <div class="bg-white shadow sm:rounded-lg">
    <div class="px-4 py-5 sm:p-6">
      <h3 class="text-lg leading-6 font-medium text-gray-900 mb-6">Prijava - Podešavanja</h3>
      <div class="space-y-4">
        <div class="flex items-center justify-between">
          <span class="text-sm text-gray-700">Ikonica za uvoz fajlova</span>
          <Switch
            :class="settings.checkin_show_upload_icon === '1' ? 'bg-indigo-600' : 'bg-gray-200'"
            class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
            @click="toggle('checkin_show_upload_icon')">
            <span
              :class="settings.checkin_show_upload_icon === '1' ? 'translate-x-5' : 'translate-x-0'"
              class="pointer-events-none relative inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out" />
          </Switch>
        </div>
        <div class="flex items-center justify-between">
          <span class="text-sm text-gray-700">Ikonica za pregled računa</span>
          <Switch
            :class="settings.checkin_show_invoice_icon === '1' ? 'bg-indigo-600' : 'bg-gray-200'"
            class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
            @click="toggle('checkin_show_invoice_icon')">
            <span
              :class="settings.checkin_show_invoice_icon === '1' ? 'translate-x-5' : 'translate-x-0'"
              class="pointer-events-none relative inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out" />
          </Switch>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
  import { Switch } from '@headlessui/vue'

  export default {
    components: { Switch },
    data() {
      return {
        settings: {
          checkin_show_upload_icon: '1',
          checkin_show_invoice_icon: '1',
        }
      }
    },
    mounted() {
      axios.get('/api/backoffice/settings').then((res) => {
        this.settings = { ...this.settings, ...res.data }
      })
    },
    methods: {
      toggle(key) {
        const newValue = this.settings[key] === '1' ? '0' : '1'
        this.settings[key] = newValue
        axios.put('/api/backoffice/settings', { key, value: newValue })
      }
    }
  }
</script>

<template>
  <router-view></router-view>
  <portal-target name="portal" />
  <ServerErrorModal />
</template>

<script>
  import pusher from 'pusher-js'
  import ServerErrorModal from './components/Modals/ServerErrorModal.vue'
  import { attachResync } from './realtime'
  export default {
    components: {ServerErrorModal},
    data: () => ({
      pusher: null,
      teardownResync: null,
    }),
    mounted() {
      this.$store.dispatch('setDefaultActiveArea')
      this.$store.dispatch('loadEPOS')
      this.$store.dispatch('setEpsonDevice')
      this.pusherInit()
    },
    beforeUnmount() {
      if (this.teardownResync) this.teardownResync()
      if (this.pusher) this.pusher.disconnect()
    },
    methods: {
      pusherInit()
      {
        const pusher = new Pusher(import.meta.env.VITE_PUSHER_APP_KEY, { 'cluster' : import.meta.env.VITE_PUSHER_APP_CLUSTER })
        pusher.subscribe('broadcasting')
        pusher.bind('tables-update', (data) => {
            this.$store.dispatch('getTables')
        })
        pusher.bind('notifications', (data) => {
            this.$store.dispatch('getTasks')
        })
        pusher.bind('menu-update', (data) => {
            this.$store.dispatch('getInventory')
            this.$store.dispatch('getCategories')
        })
        // Recover events missed while the socket was down (sleep / background / wifi drop).
        this.pusher = pusher
        this.teardownResync = attachResync(pusher, () => {
          this.$store.dispatch('getTables')
          this.$store.dispatch('getTasks')
          this.$store.dispatch('getInventory')
          this.$store.dispatch('getCategories')
        })
        addEventListener("beforeunload", (event) => {
          pusher.disconnect()
        })
      },
    }
  }
</script>

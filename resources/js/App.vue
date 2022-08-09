<template>
  <router-view></router-view>
  <portal-target name="portal" />
  <ServerErrorModal />
</template>

<script>
  import pusher from 'pusher-js'
  import ServerErrorModal from './components/Modals/ServerErrorModal.vue'
  export default {
    components: {ServerErrorModal},
    mounted() {
      this.$store.dispatch('setDefaultActiveArea')
      this.$store.dispatch('loadEPOS')
      this.$store.dispatch('setEpsonDevice')
      this.pusherInit()
      this.checkServerConnection()
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
        addEventListener("beforeunload", (event) => {
          pusher.disconnect()
        })
      },
      checkServerConnection() {
        this.$store.dispatch('checkServerConnection')
        setInterval(() => {
          this.$store.dispatch('checkServerConnection')
        }, 10000);
      }
    }
  }
</script>

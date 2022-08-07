<template>
  <router-view></router-view>
  <portal-target name="portal" />
</template>

<script>
  import pusher from 'pusher-js'

  export default {
    mounted() {
      this.$store.dispatch('setDefaultActiveArea')
      this.$store.dispatch('loadEPOS')
      this.$store.dispatch('setEpsonDevice')
      this.pusherInit()
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
      }
    }
  }
</script>

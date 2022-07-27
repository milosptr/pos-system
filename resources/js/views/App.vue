<template>
  <div class="relative w-full h-screen bg-repeat" :style="'background-image: url(' + activeArea?.pattern + '); height: 100vh; background-size: 35%;'">
    <Tables />
    <BottomNavigation />
    <PrintingNotification v-show="$store.getters.printingNotification" />
  </div>
</template>


<script>
  import BottomNavigation from '../components/BottomNavigation.vue'
  import Tables from '../components/Tables/Tables.vue'
  import PrintingNotification from '../components/PrintingNotification.vue'
  import pusher from 'pusher-js'

  export default {
    components: {
      Tables,
      BottomNavigation,
      PrintingNotification,
    },
    computed: {
      activeArea() {
        return this.$store.getters.getActiveArea
      }
    },
    created()
    {
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
      }
    }
  }
</script>

<template>
  <div
    class="relative h-screen w-full bg-repeat"
    :style="'background-image: url(' + activeArea?.pattern + '); height: 100vh; background-size: 35%;'">
    <Tables />
    <BottomNavigation />
    <PrintingNotification v-show="$store.getters.printingNotification" />
  </div>
</template>

<script>
import BottomNavigation from '../components/BottomNavigation.vue'
import Tables from '../components/Tables/Tables.vue'
import PrintingNotification from '../components/PrintingNotification.vue'

export default {
  components: {
    Tables,
    BottomNavigation,
    PrintingNotification
  },
  computed: {
    activeArea() {
      return this.$store.getters.getActiveArea
    }
  }
}
</script>

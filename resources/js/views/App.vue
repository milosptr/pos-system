<template>
  <div class="relative w-full h-screen bg-repeat" :style="'background-image: url(' + activeArea?.pattern + '); height: 100vh; background-size: 35%;'">
    <Tables />
    <BottomNavigation />
  </div>
</template>


<script>
  import BottomNavigation from '../components/BottomNavigation.vue'
  import Tables from '../components/Tables/Tables.vue'

  export default {
    components: {
      Tables,
      BottomNavigation,
    },
    computed: {
      activeArea() {
        return this.$store.getters.getActiveArea
      }
    },
  }
</script>

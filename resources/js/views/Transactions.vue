<template>
  <div class="relative w-full h-screen">
    Transactions
    <BottomNavigation />
  </div>
</template>

<script>
import BottomNavigation from '../components/BottomNavigation.vue'
  export default {
  components: { BottomNavigation },
    data: () => ({
      transactions: []
    }),
    mounted() {

    }
  }
</script>

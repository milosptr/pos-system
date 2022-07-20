<template>
  <div class="relative w-full h-screen">
    Invoices
    <BottomNavigation />
  </div>
</template>

<script>
import BottomNavigation from '../components/BottomNavigation.vue'
  export default {
  components: { BottomNavigation },
    data: () => ({
      invoices: []
    }),
    mounted() {

    }
  }
</script>

<template>
  <div class="relative h-screen w-full">
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
  mounted() {}
}
</script>

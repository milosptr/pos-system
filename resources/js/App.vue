<template>
  <router-view></router-view>
</template>

<script>
  export default {
    mounted() {
      this.$store.dispatch('setDefaultActiveArea')
      this.$store.dispatch('loadEPOS')
    }
  }
</script>

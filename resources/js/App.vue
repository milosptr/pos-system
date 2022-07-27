<template>
  <router-view></router-view>
  <portal-target name="portal" />
</template>

<script>
  export default {
    mounted() {
      this.$store.dispatch('setDefaultActiveArea')
      this.$store.dispatch('loadEPOS')
      this.$store.commit('setEpsonDevice')
    }
  }
</script>

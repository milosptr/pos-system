<template>
  <div class="flex h-screen">
    <div class="SidebarWidth border-r border-gray-300">
      <router-view name="sidebar" />
    </div>
    <div class="OverviewWidth bg-gray-100">
      <router-view />
    </div>
  </div>
</template>

<script>
  export default {

  }
</script>

<style scoped>
  .OverviewWidth {
    width: 72%;
  }
  .SidebarWidth {
    width: 28%;
  }
</style>

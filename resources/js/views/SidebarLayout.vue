<template>
  <div class="flex h-screen">
    <div class="OverviewWidth bg-gray-50">
      <router-view />
    </div>
    <div class="SidebarWidth border-l border-gray-200">
      <router-view name="sidebar" />
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

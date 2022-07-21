<template>
  <div class="flex h-screen relative">
    <div class="SidebarWidth border-r border-gray-300 fixed left-0 top-0 h-screen">
      <router-view name="sidebar" />
    </div>
    <div class="OverviewWidth bg-gray-100 ml-auto">
      <router-view name="main" />
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

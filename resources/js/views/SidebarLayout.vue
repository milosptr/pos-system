<template>
  <div class="relative flex h-screen">
    <div class="SidebarWidth fixed left-0 top-0 h-screen border-r border-gray-300">
      <router-view name="sidebar" />
    </div>
    <div class="OverviewWidth ml-auto bg-gray-100">
      <router-view name="main" />
    </div>
  </div>
</template>

<script>
export default {}
</script>

<style scoped>
.OverviewWidth {
  width: 72%;
}
.SidebarWidth {
  width: 28%;
}
</style>

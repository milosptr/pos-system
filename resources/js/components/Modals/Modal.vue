<template>
  <portal to="portal">
    <div class="fixed top-0 left-0 w-full h-screen flex items-center justify-center z-30">
      <div class="bg-black opacity-50 absolute left-0 top-0 w-full h-screen z-30" @click="$emit('close')"></div>
      <div class="bg-white w-1/2 h-5/6 rounded-sm p-6 z-40 relative">
        <div class="absolute right-0 top-0 p-3">
          <img src="/images/close.svg" alt="close" width="36" @click="$emit('close')" />
        </div>
        <div class="mt-5">
          <slot></slot>
        </div>
      </div>
    </div>
  </portal>
</template>

<script>
  export default {

  }
</script>

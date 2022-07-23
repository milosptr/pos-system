<template>
  <portal to="portal">
    <div class="fixed top-0 left-0 w-full h-screen flex items-center justify-center z-30">
      <div class="bg-black opacity-50 absolute left-0 top-0 w-full h-screen z-30" @click="$emit('close')"></div>
      <div
        class="ModalHeight bg-white rounded-sm p-6 z-40 relative"
        :class="[ numpad ? 'NumpadWidth' : 'w-1/2' ]"
      >
        <div class="absolute right-0 top-0 p-3">
          <img :src="$filters.imgUrl('close.svg')" alt="close" width="36" @click="$emit('close')" />
        </div>
        <div class="pt-5 h-full">
          <slot></slot>
        </div>
      </div>
    </div>
  </portal>
</template>

<script>
  export default {
    props: {
      numpad: {
        type: Boolean,
        default: () => false,
      }
    }
  }
</script>

<style scoped>
  .ModalHeight {
    height: 95%;
  }
  .NumpadWidth {
    width: 42%;
  }
</style>

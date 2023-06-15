<template>
  <portal to="portal">
    <div class="fixed left-0 top-0 z-30 flex h-screen w-full items-center justify-center">
      <div
        class="absolute left-0 top-0 z-30 h-screen w-full bg-black opacity-50"
        @click="$emit('close')"></div>
      <div
        class="ModalHeight relative z-40 rounded-sm bg-white p-6"
        :class="[numpad ? 'NumpadWidth' : 'w-1/2', { 'w-2/3': superWide }]">
        <!-- <div class="absolute right-0 top-0 p-3">
          <img :src="$filters.imgUrl('close.svg')" alt="close" width="36" @click="$emit('close')" />
        </div> -->
        <div
          class="h-full pt-5"
          :class="bodyClass">
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
      default: () => false
    },
    superWide: {
      type: Boolean,
      default: () => false
    },
    bodyClass: {
      type: String,
      default: () => ''
    }
  },
  mounted() {
    document.body.style.overflow = 'hidden'
  },
  beforeUnmount() {
    document.body.style.overflow = ''
  }
}
</script>

<style scoped>
.overflow-scroll {
  -ms-overflow-style: none; /* for Internet Explorer, Edge */
  scrollbar-width: none; /* for Firefox */
  overflow-y: scroll;
}

.overflow-scroll::-webkit-scrollbar {
  display: none; /* for Chrome, Safari, and Opera */
}
.ModalHeight {
  height: 90%;
}
.NumpadWidth {
  width: 42%;
}
@media (min-width: 1600px) {
  .ModalHeight {
    height: 85%;
  }
  .NumpadWidth {
    width: 32%;
  }
}
@media (max-width: 450px) {
  .NumpadWidth {
    width: 92%;
  }
}
</style>

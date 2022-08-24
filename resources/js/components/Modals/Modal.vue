<template>
  <portal to="portal">
    <div class="fixed top-0 left-0 w-full h-screen flex items-center justify-center z-30">
      <div class="bg-black opacity-50 absolute left-0 top-0 w-full h-screen z-30" @click="$emit('close')"></div>
      <div
        class="ModalHeight bg-white rounded-sm p-6 z-40 relative"
        :class="[ numpad ? 'NumpadWidth' : 'w-1/2', { 'w-2/3': superWide } ]"
      >
        <!-- <div class="absolute right-0 top-0 p-3">
          <img :src="$filters.imgUrl('close.svg')" alt="close" width="36" @click="$emit('close')" />
        </div> -->
        <div class="pt-5 h-full" :class="bodyClass">
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
      },
      superWide: {
        type: Boolean,
        default: () => false,
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
    },
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
  @media(max-width: 450px) {
    .NumpadWidth {
      width: 92%;
    }
  }
</style>

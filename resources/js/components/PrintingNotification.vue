<template>
  <!-- Global notification live region, render this permanently at the end of the document -->
  <div
    aria-live="assertive"
    class="pointer-events-none fixed inset-0 z-50 flex items-end px-4 py-6 sm:p-6">
    <div class="flex w-full flex-col items-center space-y-4 sm:items-end">
      <!-- Notification panel, dynamically insert this into the live region when it needs to be displayed -->
      <transition
        enter-active-class="transform ease-out duration-300 transition"
        enter-from-class="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
        enter-to-class="translate-y-0 opacity-100 sm:translate-x-0"
        leave-active-class="transition ease-in duration-100"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0">
        <div
          class="pointer-events-auto w-full max-w-sm overflow-hidden rounded-lg bg-white shadow-lg ring-1 ring-black ring-opacity-5">
          <div class="p-4">
            <div class="flex items-center">
              <div class="flex w-0 flex-1 items-center justify-between">
                <svg
                  v-if="$store.getters.printingAttempts <= 1 && !timeoutExceeded"
                  xmlns="http://www.w3.org/2000/svg"
                  class="RotateAnimation"
                  width="24"
                  height="24"
                  fill="#000000"
                  viewBox="0 0 256 256">
                  <rect
                    width="256"
                    height="256"
                    fill="none"></rect>
                  <path
                    d="M168,40.7a96,96,0,1,1-80,0"
                    fill="none"
                    stroke="#000000"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="12"></path>
                </svg>
                <p
                  v-if="$store.getters.printingAttempts <= 1 && !timeoutExceeded"
                  class="ml-3 w-0 flex-1 text-sm font-medium text-gray-900">
                  Štampanje {{ printingTypeText }}
                </p>
                <p
                  v-else
                  class="ml-3 w-0 flex-1 text-sm font-semibold text-red-500">
                  Problem sa štampanjem
                </p>
              </div>
              <div class="ml-4 flex flex-shrink-0">
                <button
                  type="button"
                  @click="$store.commit('setPrintingNotification', false)"
                  class="inline-flex rounded-md bg-white text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                  <span class="sr-only">Close</span>
                  <XIcon
                    class="h-5 w-5"
                    aria-hidden="true" />
                </button>
              </div>
            </div>
          </div>
        </div>
      </transition>
    </div>
  </div>
</template>

<script>
import { XIcon } from '@heroicons/vue/solid'

export default {
  components: {
    XIcon
  },
  data: () => ({
    timeoutExceeded: false
  }),
  computed: {
    printingTypeText() {
      if (this.$store.getters.printingType === 'invoice') return ' računa'
      return ' porudžbine'
    }
  },
  mounted() {
    setTimeout(() => {
      this.timeoutExceeded = true
    }, 30000)
  }
}
</script>

<style scoped>
.RotateAnimation {
  animation: rotate 1s linear infinite;
}

@keyframes rotate {
  0% {
    transform: rotate(0);
  }
  100% {
    transform: rotate(360deg);
  }
}
</style>

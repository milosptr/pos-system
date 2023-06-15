<template>
  <div
    v-click-outside="() => $emit('close')"
    class="absolute right-0 top-0 z-10 mr-1 rounded-sm border border-gray-200 bg-white text-lg font-semibold uppercase shadow-lg">
    <div
      class="border-b border-gray-200 px-5 py-3"
      @click="handleClick('reprint')">
      Ponovno štampanje
    </div>
    <div
      v-if="showRefund"
      class="px-5 py-3"
      @click="handleClick('refund')">
      Storniraj račun
    </div>
  </div>
</template>

<script>
export default {
  props: {
    showRefund: {
      type: Boolean,
      default: () => true
    }
  },
  methods: {
    handleClick(type) {
      this.$emit('selected', type)
      this.$emit('close')
    }
  }
}
</script>

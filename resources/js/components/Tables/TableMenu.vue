<template>
  <div
    v-click-outside="() => $emit('close')"
    class="absolute top-0 right-0 mr-1 bg-white shadow-lg border border-gray-200 z-10 rounded-sm text-lg uppercase font-semibold">
    <div class="py-3 px-5 border-b border-gray-200" @click="handleClick('move')">
      Prenesi zaduženje
    </div>
    <div class="py-3 px-5" @click="handleClick('refund')">
      Storniraj račun
    </div>
  </div>
</template>

<script>
  export default {
    methods: {
      handleClick(type) {
        this.$emit('selected', type)
        this.$emit('close')
      }
    }
  }
</script>

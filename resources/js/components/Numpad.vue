<template>
  <div class="">
    <div
      v-if="!hideSubtitle"
      class="mb-6 text-center text-2xl font-semibold uppercase">
      Unesite Kolicinu
      <div v-if="subtitle.length">za {{ subtitle }}</div>
    </div>
    <div class="NumpadWidth relative mx-auto w-full">
      <div
        class="col-span-2 flex w-full items-center justify-center rounded-sm border border-gray-300 bg-gray-50 py-2"
        :class="{ 'error border-red-500': status === false }">
        <div class="MinValueHeight w-full pl-5 text-5xl font-semibold leading-none">{{ value.join('') }}</div>
        <div
          class="BackspaceHeight absolute right-0 top-0 flex items-center justify-center pr-3"
          @click="clear">
          <img
            :src="$filters.imgUrl('backspace.svg')"
            alt="obrisi"
            width="64" />
        </div>
      </div>
    </div>
    <div class="NumpadWidth mx-auto mt-10 grid grid-cols-3 gap-3">
      <div
        v-for="key in keys"
        :key="key.key"
        class="SingleKey flex w-full items-center justify-center rounded-sm border border-gray-300"
        :class="[key.background, key.color, { KeyButton: key.isButton }]"
        @click="enterKey(key.key)">
        <div class="text-center text-4xl font-semibold leading-none">
          {{ key.key }}
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    subtitle: {
      type: String,
      default: () => ''
    },
    hideSubtitle: {
      type: Boolean,
      default: () => false
    }
  },
  data: () => ({
    value: [''],
    valueIndex: 0,
    status: null,
    keys: [
      { key: 1, background: 'bg-gray-300', color: 'text-gray-900' },
      { key: 2, background: 'bg-gray-300', color: 'text-gray-900' },
      { key: 3, background: 'bg-gray-300', color: 'text-gray-900' },
      { key: 4, background: 'bg-gray-300', color: 'text-gray-900' },
      { key: 5, background: 'bg-gray-300', color: 'text-gray-900' },
      { key: 6, background: 'bg-gray-300', color: 'text-gray-900' },
      { key: 7, background: 'bg-gray-300', color: 'text-gray-900' },
      { key: 8, background: 'bg-gray-300', color: 'text-gray-900' },
      { key: 9, background: 'bg-gray-300', color: 'text-gray-900' },
      { key: '.', background: 'bg-gray-300', color: 'text-gray-900' },
      { key: 0, background: 'bg-gray-300', color: 'text-gray-900' },
      { key: 'Enter', background: 'bg-green-600', color: 'text-white', isButton: true }
    ]
  }),
  methods: {
    clear() {
      this.value = ['']
      this.$emit('input', this.value)
    },
    enterKey(key) {
      this.status = null
      if (key === 'Enter') {
        this.$emit('input', this.value.join(''))
      }

      if (key !== 'Enter') {
        this.value[this.valueIndex] = key
        this.valueIndex++
      }
    }
  }
}
</script>

<style scoped>
.error {
  animation: ErrorShake 0.2s ease-in-out;
}

.NumpadWidth {
  width: 90%;
}

.BackspaceHeight {
  height: 66px;
}

.MinValueHeight {
  min-height: 48px;
}

.SingleKey {
  height: 70px;
}

.KeyButton div {
  font-size: 26px;
}

@keyframes ErrorShake {
  0% {
    transform: translateX(0);
  }
  25% {
    transform: translateX(-10px);
  }
  50% {
    transform: translateX(0);
  }
  75% {
    transform: translateX(10px);
  }
  100% {
    transform: translateX(0);
  }
}
</style>

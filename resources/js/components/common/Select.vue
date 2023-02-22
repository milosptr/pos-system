<template>
  <Listbox as="div" v-model="selected" ref="selectCategory">
    <div class="relative">
      <ListboxButton class="bg-white relative w-full border border-gray-300 rounded-md shadow-sm pl-3 pr-10 py-2 text-left cursor-default focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
        <span class="select-component-text block truncate w-32 sm:w-48">{{ selected ? selected.name : 'Select' }}</span>
        <span class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
          <SelectorIcon class="h-5 w-5 text-gray-400" aria-hidden="true" />
        </span>
      </ListboxButton>

      <transition leave-active-class="transition ease-in duration-100" leave-from-class="opacity-100" leave-to-class="opacity-0">
        <ListboxOptions class="absolute z-50 mt-1 w-full bg-white shadow-lg max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm">
          <ListboxOption as="template" v-for="item in list" :key="item.id" :value="item" v-slot="{ active, selected }" @click="selectItem(item)">
            <li :class="[active ? 'text-white bg-indigo-600' : 'text-gray-900', 'cursor-default select-none relative py-2 pl-8 pr-4']">
              <span :class="[selected ? 'font-semibold' : 'font-normal', 'block truncate']">
                {{ item.name }}
              </span>

              <span v-if="selected" :class="[active ? 'text-white' : 'text-indigo-600', 'absolute inset-y-0 left-0 flex items-center pl-1.5']">
                <CheckIcon class="h-5 w-5" aria-hidden="true" />
              </span>
            </li>
          </ListboxOption>
        </ListboxOptions>
      </transition>
    </div>
  </Listbox>
</template>

<script>
import { Listbox, ListboxButton, ListboxLabel, ListboxOption, ListboxOptions } from '@headlessui/vue'
import { CheckIcon, SelectorIcon } from '@heroicons/vue/solid'

export default {
  components: {Listbox, ListboxButton, ListboxLabel, ListboxOption, ListboxOptions, CheckIcon, SelectorIcon},
  props: {
    list: {
      type: Array,
      default: () => [],
    },
    preselected: {
      type: Number,
      default: () => null
    }
  },
  data: () => ({
    selected: null,
  }),
  mounted() {
    if(this.preselected !== null) {
      this.selected = this.list.find((l) => l.id === this.preselected)
    }
  },
  methods: {
    selectItem(selected) {
      this.selected = selected
      this.$emit('select', selected)
    }
  }
}

</script>

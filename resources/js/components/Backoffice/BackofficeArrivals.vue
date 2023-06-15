<template>
  <div class="-mt-10">
    <div class="mb-10 flex items-center justify-end gap-4">
      <div
        class="cursor-pointer rounded-md bg-gray-200 px-4 py-2"
        :class="[{ 'bg-indigo-500 text-white': page === 1 }]"
        @click="chnagePage(1)">
        Pregled
      </div>
      <div
        class="cursor-pointer rounded-md bg-gray-200 px-4 py-2"
        :class="[{ 'bg-indigo-500 text-white': page === 2 }]"
        @click="chnagePage(2)">
        Individualni
      </div>
    </div>
    <div>
      <BackofficeArrivalsOverview v-show="page === 1" />
      <BackofficeArrivalsIndividual v-show="page === 2" />
    </div>
  </div>
</template>

<script>
import BackofficeArrivalsIndividual from './BackofficeArrivalsIndividual.vue'
import BackofficeArrivalsOverview from './BackofficeArrivalsOverview.vue'

export default {
  components: { BackofficeArrivalsOverview, BackofficeArrivalsIndividual },
  data: () => ({
    page: 1
  }),
  methods: {
    chnagePage(page) {
      this.page = page
    }
  }
}
</script>

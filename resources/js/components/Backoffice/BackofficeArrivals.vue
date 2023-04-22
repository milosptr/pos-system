<template>
  <div class="-mt-10">
    <div class="flex justify-end items-center gap-4 mb-10">
      <div class="bg-gray-200 px-4 py-2 rounded-md cursor-pointer" :class="[{'bg-indigo-500 text-white': page === 1}]" @click="chnagePage(1)">Pregled</div>
      <div class="bg-gray-200 px-4 py-2 rounded-md cursor-pointer" :class="[{'bg-indigo-500 text-white': page === 2}]" @click="chnagePage(2)">Individualni</div>
    </div>
    <div>
      <BackofficeArrivalsOverview v-show="page === 1" />
      <BackofficeArrivalsIndividual v-show="page === 2" />
    </div>
  </div>
</template>

<script>
import BackofficeArrivalsIndividual from './BackofficeArrivalsIndividual.vue';
import BackofficeArrivalsOverview from './BackofficeArrivalsOverview.vue';

export default {
  components: { BackofficeArrivalsOverview, BackofficeArrivalsIndividual },
  data: () => ({
    page: 1,
  }),
  methods: {
    chnagePage(page) {
      this.page = page;
    },
  }
}
</script>

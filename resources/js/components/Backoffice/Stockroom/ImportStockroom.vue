<template>
  <div>
    <div class="flex justify-end mb-4">
      <div for="magimport"
      @click="showModal = true"
      class="inline-block gap-3 px-3 py-2 cursor-pointer border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
      <img src="/images/importpackage.svg" width="20" />
    </div>
    </div>
    <div v-if="showModal" class="fixed left-0 top-0 z-[100] w-full h-screen flex justify-center items-center">
      <div class="absolute left-0 top-0 w-full h-screen bg-black z-[99] opacity-50" @click="showModal = false"></div>
      <div class="relative z-[100] w-1/2 h-1/2 bg-white p-10 rounded-lg">
        <div class="text-xl font-semibold">Uvezi magacin</div>
        <div>
          <div class="flex items-center gap-5 mt-8">
            <input v-model="date" type="date" name="date" class="rounded-md border-gray-400">
            <input v-on:change="handleFileUpload" type="file" name="file" />
            <div @click="handleSubmit" class="bg-indigo-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
              Uvezi
            </div>
          </div>
        </div>
      </div>
    </div>
    <ImportSuccessFlash v-if="success" />
  </div>
</template>

<script>
import axios from 'axios';
import ImportSuccessFlash from './ImportSuccessFlash.vue';

export default {
  name: "ImportStockroom",
  data: () => ({
    showModal: false,
    success: false,
    date: null,
    file: null,
  }),
  mounted() {
    axios.get('/api/working-day')
      .then((res) => {
        this.date = new Date(res.data[0]).toISOString().slice(0, 10)
      })
  },
  methods: {
    handleFileUpload(event) {
      const file = event.target.files[0];
      this.file = file;
    },
    handleSubmit() {
      const formData = new FormData();
      formData.append("date", this.date);
      formData.append("file", this.file);
      fetch("/api/sales/import", {
        method: "POST",
        body: formData,
      })
        .then((data) => {
          this.success = true
          this.showModal = false
          this.$emit('imported')
        });
    },
  },
  components: { ImportSuccessFlash }
};
</script>

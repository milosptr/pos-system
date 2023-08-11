<template>
  <div class="mt-5">
    <ImportStockroom @imported="fetchSalesImports()" />
    <div class="shadow-sm ring-1 ring-black ring-opacity-5 overflow-x-scroll md:overflow-visible">
      <table class="min-w-full border-separate" style="border-spacing: 0">
        <thead class="bg-gray-50">
          <tr>
            <th scope="col"
              class="sticky top-0 z-10 border-b border-gray-300 bg-gray-50 bg-opacity-75 px-3 py-3.5 text-left text-sm font-semibold text-gray-900 backdrop-blur backdrop-filter sm:table-cell">
              ID</th>
            <th scope="col"
              class="sticky top-0 z-10 border-b border-gray-300 bg-gray-50 bg-opacity-75 px-3 py-3.5 text-left text-sm font-semibold text-gray-900 backdrop-blur backdrop-filter sm:table-cell">
              Filename</th>
            <th scope="col"
              class="sticky top-0 z-10 border-b border-gray-300 bg-gray-50 bg-opacity-75 px-3 py-3.5 text-left text-sm font-semibold text-gray-900 backdrop-blur backdrop-filter">
              For date</th>
            <th scope="col"
              class="sticky top-0 z-10 border-b border-gray-300 bg-gray-50 bg-opacity-75 px-3 py-3.5 text-left text-sm font-semibold text-gray-900 backdrop-blur backdrop-filter">
              Import date</th>
            <th scope="col"
              class="sticky top-0 z-10 border-b border-gray-300 bg-gray-50 bg-opacity-75 px-3 py-3.5 text-left text-sm font-semibold text-gray-900 backdrop-blur backdrop-filter">
            </th>
          </tr>
        </thead>
        <tbody class="bg-white">
          <tr v-for="(item, idx) in imports" :key="item.id" class="hover:bg-orange-50 cursor-pointer"
            :class="[{ 'bg-gray-50': idx % 2 === 1 }]">
            <td
              :class="[idx !== imports.length - 1 ? 'border-b border-gray-200' : '', 'whitespace-nowrap px-3 py-2 text-sm text-gray-900']">
              {{ item.id }}</td>
            <td
              :class="[idx !== imports.length - 1 ? 'border-b border-gray-200' : '', 'whitespace-nowrap px-3 py-2 text-sm text-gray-500']">
              {{ item.filename }}</td>
              <td
              :class="[idx !== imports.length - 1 ? 'border-b border-gray-200' : '', 'whitespace-nowrap px-3 py-2 text-sm text-gray-500']">
              {{ item.sales && item.sales.length ? formatDate(item.sales[0].created_at) : '' }}</td>
              <td
              :class="[idx !== imports.length - 1 ? 'border-b border-gray-200' : '', 'whitespace-nowrap px-3 py-2 text-sm text-gray-500']">
              {{ formatDateTime(item.created_at) }}</td>
            <td
              :class="[idx !== imports.length - 1 ? 'border-b border-gray-200' : '', 'whitespace-nowrap px-3 py-2 text-sm text-right']">
              <div class="text-red-500 hover:underline" @click="handleDelete(item)">Delete</div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <Pagination v-if="pagination.lastPage !== 1" :currentPage="pagination.page" :perPage="pagination.perPage" :total="pagination.total" @change="changePage" />
    <div v-if="showDeleteModal" class="fixed left-0 top-0 z-[100] w-full h-screen flex justify-center items-center">
      <div class="absolute left-0 top-0 w-full h-screen bg-black z-[99] opacity-50" @click="showDeleteModal = false"></div>
      <div class="relative z-[100] w-[90%] md:w-1/2 md:h-1/2 bg-white p-10 rounded-lg">
        <div class="text-xl font-semibold">Obriši uvoz</div>
        <div class="mt-8">
          <div class="text-base">Da li ste sigurni da želite da obrišete uvoz <strong>{{ importToDelete?.filename }}</strong> dana <strong>{{ formatDate(importToDelete.created_at) }}</strong>?</div>
          <div class="text-base">Ukoliko obrišete uvoz, nećete moći da ga povratite.</div>
        </div>
        <div>
          <div class="flex items-center justify-end gap-4 mt-8">
            <div class="bg-gray-400 hover:bg-gray-500 text-white font-bold py-2 px-6 rounded cursor-pointer" @click="showDeleteModal = false"> Cancel </div>
            <div class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-6 rounded cursor-pointer" @click="handleDeleteImport"> Delete </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import Pagination from '../../common/Pagination.vue';
import ImportStockroom from '../Stockroom/ImportStockroom.vue';
const options = {
  day: '2-digit',
  month: '2-digit',
  year: 'numeric',
  hour: '2-digit',
  minute: '2-digit',
  second: '2-digit',
  hour12: false,
};
export default {
  data: () => ({
    imports: [],
    importToDelete: null,
    showDeleteModal: false,
    pagination: {
      page: 1,
      perPage: 50,
      total: 0,
      lastPage: 1
    }
  }),
  components: {
    ImportStockroom,
    Pagination
},
  mounted() {
    this.fetchSalesImports()
  },
  methods: {
    handleDelete(item) {
      this.importToDelete = item
      this.showDeleteModal = true
    },
    handleDeleteImport() {
      axios.delete(`/api/sales/imports/${this.importToDelete.id}`)
        .then(() => {
          this.fetchSalesImports()
          this.showDeleteModal = false
        })
    },
    changePage(page) {
      window.scrollTo({top: 0, behavior: 'smooth'});
      this.fetchSalesImports(page)
    },
    fetchSalesImports(page = 1) {
      axios.get(`/api/sales/imports?page=${page}`)
        .then(({ data }) => {
          this.imports = data.data
          this.pagination = {
            page: data.current_page,
            perPage: data.per_page,
            total: data.total,
            lastPage: data.last_page
          }
        })
    },
    formatDate(dateString) {
      const date = new Date(dateString);
      const formatter = new Intl.DateTimeFormat('sr-RS', {  day: '2-digit', month: '2-digit', year: 'numeric'});
      return formatter.format(date);
    },
    formatDateTime(dateString) {
      const date = new Date(dateString);
      const formatter = new Intl.DateTimeFormat('sr-RS', options);
      return formatter.format(date);
    },
  }
}
</script>

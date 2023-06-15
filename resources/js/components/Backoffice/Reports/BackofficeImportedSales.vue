<template>
  <div class="mt-5">
    <ImportStockroom @imported="fetchSalesImports()" />
    <div class="overflow-x-scroll shadow-sm ring-1 ring-black ring-opacity-5 md:overflow-visible">
      <table
        class="min-w-full border-separate"
        style="border-spacing: 0">
        <thead class="bg-gray-50">
          <tr>
            <th
              scope="col"
              class="sticky top-0 z-10 border-b border-gray-300 bg-gray-50 bg-opacity-75 px-3 py-3.5 text-left text-sm font-semibold text-gray-900 backdrop-blur backdrop-filter sm:table-cell">
              ID
            </th>
            <th
              scope="col"
              class="sticky top-0 z-10 border-b border-gray-300 bg-gray-50 bg-opacity-75 px-3 py-3.5 text-left text-sm font-semibold text-gray-900 backdrop-blur backdrop-filter sm:table-cell">
              Filename
            </th>
            <th
              scope="col"
              class="sticky top-0 z-10 border-b border-gray-300 bg-gray-50 bg-opacity-75 px-3 py-3.5 text-left text-sm font-semibold text-gray-900 backdrop-blur backdrop-filter sm:table-cell">
              Content
            </th>
            <th
              scope="col"
              class="sticky top-0 z-10 border-b border-gray-300 bg-gray-50 bg-opacity-75 px-3 py-3.5 text-left text-sm font-semibold text-gray-900 backdrop-blur backdrop-filter">
              For date
            </th>
            <th
              scope="col"
              class="sticky top-0 z-10 border-b border-gray-300 bg-gray-50 bg-opacity-75 px-3 py-3.5 text-left text-sm font-semibold text-gray-900 backdrop-blur backdrop-filter">
              Import date
            </th>
            <th
              scope="col"
              class="sticky top-0 z-10 border-b border-gray-300 bg-gray-50 bg-opacity-75 px-3 py-3.5 text-left text-sm font-semibold text-gray-900 backdrop-blur backdrop-filter"></th>
          </tr>
        </thead>
        <tbody class="bg-white">
          <tr
            v-for="(item, idx) in imports"
            :key="item.id"
            class="cursor-pointer hover:bg-orange-50"
            :class="[{ 'bg-gray-50': idx % 2 === 1 }]">
            <td
              :class="[
                idx !== imports.length - 1 ? 'border-b border-gray-200' : '',
                'whitespace-nowrap px-3 py-2 text-sm text-gray-900'
              ]">
              {{ item.id }}
            </td>
            <td
              :class="[
                idx !== imports.length - 1 ? 'border-b border-gray-200' : '',
                'whitespace-nowrap px-3 py-2 text-sm text-gray-500'
              ]">
              {{ item.filename }}
            </td>
            <td
              :class="[
                idx !== imports.length - 1 ? 'border-b border-gray-200' : '',
                'relative whitespace-nowrap px-3 py-2 text-right text-sm'
              ]">
              <div class="group relative">
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  viewBox="0 0 14 14"
                  height="16"
                  width="16">
                  <g>
                    <path
                      d="M9.5,1.5H11a1,1,0,0,1,1,1v10a1,1,0,0,1-1,1H3a1,1,0,0,1-1-1V2.5a1,1,0,0,1,1-1H4.5"
                      fill="none"
                      stroke="#6b7280"
                      stroke-linecap="round"
                      stroke-linejoin="round"></path>
                    <rect
                      x="4.5"
                      y="0.5"
                      width="5"
                      height="2.5"
                      rx="1"
                      fill="none"
                      stroke="#6b7280"
                      stroke-linecap="round"
                      stroke-linejoin="round"></rect>
                    <line
                      x1="4.5"
                      y1="5.5"
                      x2="9.5"
                      y2="5.5"
                      fill="none"
                      stroke="#6b7280"
                      stroke-linecap="round"
                      stroke-linejoin="round"></line>
                    <line
                      x1="4.5"
                      y1="8"
                      x2="9.5"
                      y2="8"
                      fill="none"
                      stroke="#6b7280"
                      stroke-linecap="round"
                      stroke-linejoin="round"></line>
                    <line
                      x1="4.5"
                      y1="10.5"
                      x2="9.5"
                      y2="10.5"
                      fill="none"
                      stroke="#6b7280"
                      stroke-linecap="round"
                      stroke-linejoin="round"></line>
                  </g>
                </svg>
                <div
                  class="absolute left-0 top-4 z-10 hidden w-[300px] border border-gray-300 bg-white p-1 text-left shadow-md group-hover:block">
                  <div
                    v-for="(sale, idx) in item.sales"
                    class="flex justify-between gap-4 text-gray-700">
                    <div class="truncate">{{ sale.name }}</div>
                    <div>{{ sale.total }}.00</div>
                  </div>
                </div>
              </div>
            </td>
            <td
              :class="[
                idx !== imports.length - 1 ? 'border-b border-gray-200' : '',
                'whitespace-nowrap px-3 py-2 text-sm text-gray-500'
              ]">
              {{ item.sales && item.sales.length ? formatDate(item.sales[0].created_at) : '' }}
            </td>
            <td
              :class="[
                idx !== imports.length - 1 ? 'border-b border-gray-200' : '',
                'whitespace-nowrap px-3 py-2 text-sm text-gray-500'
              ]">
              {{ formatDateTime(item.created_at) }}
            </td>
            <td
              :class="[
                idx !== imports.length - 1 ? 'border-b border-gray-200' : '',
                'whitespace-nowrap px-3 py-2 text-right text-sm'
              ]">
              <div
                class="text-red-500 hover:underline"
                @click="handleDelete(item)">
                Delete
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <div
      v-if="showDeleteModal"
      class="fixed left-0 top-0 z-[100] flex h-screen w-full items-center justify-center">
      <div
        class="absolute left-0 top-0 z-[99] h-screen w-full bg-black opacity-50"
        @click="showDeleteModal = false"></div>
      <div class="relative z-[100] w-[90%] rounded-lg bg-white p-10 md:h-1/2 md:w-1/2">
        <div class="text-xl font-semibold">Obriši uvoz</div>
        <div class="mt-8">
          <div class="text-base">
            Da li ste sigurni da želite da obrišete uvoz <strong>{{ importToDelete?.filename }}</strong> dana
            <strong>{{ formatDate(importToDelete.created_at) }}</strong
            >?
          </div>
          <div class="text-base">Ukoliko obrišete uvoz, nećete moći da ga povratite.</div>
        </div>
        <div>
          <div class="mt-8 flex items-center justify-end gap-4">
            <div
              class="cursor-pointer rounded bg-gray-400 px-6 py-2 font-bold text-white hover:bg-gray-500"
              @click="showDeleteModal = false">
              Cancel
            </div>
            <div
              class="cursor-pointer rounded bg-red-600 px-6 py-2 font-bold text-white hover:bg-red-700"
              @click="handleDeleteImport">
              Delete
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import ImportStockroom from '../Stockroom/ImportStockroom.vue'
const options = {
  day: '2-digit',
  month: '2-digit',
  year: 'numeric',
  hour: '2-digit',
  minute: '2-digit',
  second: '2-digit',
  hour12: false
}
export default {
  data: () => ({
    imports: [],
    importToDelete: null,
    showDeleteModal: false
  }),
  components: {
    ImportStockroom
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
      axios.delete(`/api/sales/imports/${this.importToDelete.id}`).then(() => {
        this.fetchSalesImports()
        this.showDeleteModal = false
      })
    },
    fetchSalesImports() {
      axios.get('/api/sales/imports').then(({ data }) => {
        this.imports = data
      })
    },
    formatDate(dateString) {
      const date = new Date(dateString)
      const formatter = new Intl.DateTimeFormat('sr-RS', { day: '2-digit', month: '2-digit', year: 'numeric' })
      return formatter.format(date)
    },
    formatDateTime(dateString) {
      const date = new Date(dateString)
      const formatter = new Intl.DateTimeFormat('sr-RS', options)
      return formatter.format(date)
    }
  }
}
</script>

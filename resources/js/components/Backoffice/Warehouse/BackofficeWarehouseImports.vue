<template>
  <div>
    <WarehouseStatusModal @update="getWarehouseStatus" />
    <div class="">
      <div class="text-right flex items-center gap-4">
        <ChevronLeftIcon
          class="w-6 cursor-pointer"
          @click="previousDate()" />
        <input
          type="date"
          v-model="date"
          :max="maxDate"
          @change="updateDate"
          class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-48 sm:text-sm border-gray-300 rounded-md" />
        <ChevronRightIcon
          class="w-6 cursor-pointer"
          @click="nextDate()" />
      </div>
      <div class="mt-6 border border-solid border-gray-200 bg-white">
        <div class="grid grid-cols-4 font-semibold text-sm py-1 border-b border-solid border-gray-300 bg-gray-200">
          <div class="px-4">Datum</div>
          <div class="px-4">Sirovina</div>
          <div class="px-4">Kolicina</div>
          <div class="px-4 text-right pr-9"></div>
        </div>
        <div
          v-for="(item, index) in warehouse"
          :key="item.id"
          class="grid grid-cols-4 items-center text-sm py-1"
          :class="{ 'bg-gray-100': index % 2 === 1 }">
          <div class="px-4 py-1">{{ dayjs(item.created_at).format('DD.MM.YYYY HH:mm') }}</div>
          <div class="px-4">{{ item.warehouse.name }}</div>
          <div
            v-if="editImport && editImport.id === item.id"
            class="flex items-center gap-3">
            <input
              type="number"
              v-model="editImport.quantity"
              class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-24 sm:text-sm border-gray-300 rounded-md py-0.5" />
            <button
              type="button"
              class="relative text-center px-4 py-0.5 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
              @click="editImportQuantity">
              Sačuvaj
            </button>
            <button
              type="button"
              class="relative text-center px-4 py-0.5 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
              @click="editImport = null">
              Odustani
            </button>
          </div>
          <div
            v-else
            class="px-4 flex items-center gap-3">
            <div>{{ item.quantity }}</div>
            <div>
              <PencilIcon
                class="w-3.5 h-3.5 text-blue-600 cursor-pointer"
                @click="editImport = { ...item }" />
            </div>
          </div>
          <div class="px-4 flex justify-end items-center gap-2 text-red-500">
            <TrashIcon
              class="w-5 h-5 cursor-pointer"
              @click="deleteModal = item" />
          </div>
        </div>
      </div>
    </div>
    <DeleteModal
      :show="!!deleteModal"
      :title="deleteModalTitle"
      :body="deleteModalBody"
      @close="deleteModal = null"
      @delete="deleteImport" />
  </div>
</template>

<script>
import dayjs from 'dayjs'
import {
  ChevronLeftIcon,
  ChevronRightIcon,
  ArrowUpIcon,
  ArrowDownIcon,
  TrashIcon,
  PencilIcon
} from '@heroicons/vue/outline'
import WarehouseStatusModal from '@/js/components/Modals/WarehouseStatusModal.vue'
import DeleteModal from '@/js/components/Modals/DeleteModal.vue'
export default {
  name: 'BackofficeWarehouseImports',
  data: () => ({
    warehouse: [],
    date: dayjs().format('YYYY-MM-DD'),
    deleteModal: null,
    editImport: null
  }),
  components: {
    DeleteModal,
    WarehouseStatusModal,
    ChevronLeftIcon,
    ChevronRightIcon,
    ArrowUpIcon,
    ArrowDownIcon,
    TrashIcon,
    PencilIcon
  },
  mounted() {
    this.getWarehouseStatus()
  },
  computed: {
    maxDate() {
      return dayjs().format('YYYY-MM-DD')
    },
    deleteModalTitle() {
      return 'Brisanje ulaza'
    },
    deleteModalBody() {
      return `Da li ste sigurni da želite da obrišete sirovinu ${this.deleteModal?.warehouse?.name} - kolicina ${this.deleteModal?.quantity} unos za datum ${dayjs(this.deleteModal?.created_at).format('DD.MM.YYYY')}?`
    }
  },
  methods: {
    dayjs,
    getWarehouseStatus() {
      axios
        .get('/api/backoffice/warehouse-status/imports?date=' + dayjs(this.date).format('YYYY-MM-DD'))
        .then((response) => {
          this.warehouse = response.data?.data ?? []
        })
    },
    updateDate() {
      this.getWarehouseStatus()
    },
    previousDate() {
      this.date = dayjs(this.date).subtract(1, 'day').format('YYYY-MM-DD')
      this.getWarehouseStatus()
    },
    nextDate() {
      this.date = dayjs(this.date).add(1, 'day').format('YYYY-MM-DD')
      if (dayjs(this.date).isAfter(dayjs().format('YYYY-MM-DD'))) {
        this.date = dayjs().format('YYYY-MM-DD')
        return
      }
      this.getWarehouseStatus()
    },
    deleteImport() {
      axios.delete('/api/backoffice/warehouse-status/imports/' + this.deleteModal.id).then(() => {
        this.deleteModal = null
        this.getWarehouseStatus()
      })
    },
    editImportQuantity() {
      axios
        .put('/api/backoffice/warehouse-status/imports/' + this.editImport.id, {
          quantity: this.editImport.quantity
        })
        .then(() => {
          this.editImport = null
          this.getWarehouseStatus()
        })
    }
  }
}
</script>

<template>
  <tr
    class="hover:bg-orange-50 cursor-pointer"
    :class="{ 'bg-blue-200 hover:bg-blue-200': isChanged, 'bg-red-300': isRemoving }">
    <td
      :class="[
        idx !== inventory.length - 1 ? 'border-b border-gray-200' : '',
        'whitespace-nowrap py-2 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6 lg:pl-8'
      ]">
      {{ idx + 1 }}
    </td>
    <td
      :class="[
        idx !== inventory.length - 1 ? 'border-b border-gray-200' : '',
        'whitespace-nowrap px-3 py-2 text-sm text-gray-500 '
      ]">
      <input
        v-model="item.name"
        type="text"
        class="appearance-none min-w-input-name w-full p-0 m-0 border-none bg-transparent" />
    </td>
    <td
      :class="[
        idx !== inventory.length - 1 ? 'border-b border-gray-200' : '',
        'whitespace-nowrap px-3 py-2 text-sm text-gray-500'
      ]">
      <div class="flex items-center">
        <input
          v-model="item.price"
          type="number"
          class="appearance-none w-20 p-0 m-0 border-none bg-transparent text-right"
          @click="selectAll" />
        <div>RSD</div>
        <ClockIcon
          v-if="item.pricing.length"
          class="w-4 h-4 ml-2"
          @click="showPricingSidebar" />
      </div>
    </td>
    <td
      :class="[
        idx !== inventory.length - 1 ? 'border-b border-gray-200' : '',
        'whitespace-nowrap px-3 py-2 text-sm text-gray-500'
      ]">
      {{ soldByText(item.sold_by) }}
    </td>
    <td
      :class="[
        idx !== inventory.length - 1 ? 'border-b border-gray-200' : '',
        'whitespace-nowrap px-3 py-2 text-sm text-gray-500'
      ]">
      <input
        v-model="item.order"
        type="number"
        class="appearance-none w-10 p-0 m-0 border-none bg-transparent" />
    </td>
    <td
      :class="[
        idx !== inventory.length - 1 ? 'border-b border-gray-200' : '',
        'whitespace-nowrap px-3 py-2 text-sm text-gray-500'
      ]">
      {{ item.category_name }}
    </td>
    <td
      :class="[
        idx !== inventory.length - 1 ? 'border-b border-gray-200' : '',
        'whitespace-nowrap px-3 py-2 text-sm text-gray-500'
      ]">
      <Switch
        :enabled="!!item.active"
        @click="updateStatus" />
    </td>
    <td
      :class="[
        idx !== inventory.length - 1 ? 'border-b border-gray-200' : '',
        'whitespace-nowrap px-3 py-2 text-sm text-gray-500'
      ]">
      <input
        v-model="item.color"
        type="color"
        name=""
        id=""
        class="w-6 rounded-full overflow-hidden" />
    </td>
    <td
      :class="[
        idx !== inventory.length - 1 ? 'border-b border-gray-200' : '',
        'whitespace-nowrap px-3 py-2 text-sm text-gray-500'
      ]">
      <input
        v-model="item.sku"
        type="text"
        class="appearance-none w-16 p-0 m-0 border-none bg-transparent text-sm" />
    </td>
    <td
      :class="[
        idx !== inventory.length - 1 ? 'border-b border-gray-200' : '',
        'whitespace-nowrap px-3 py-2 text-sm text-gray-500'
      ]">
      <div class="flex gap-4">
        <div
          class="text-blue-500 text-xl leading-none"
          @click="showSettingsModal = true">
          ⚙
        </div>
        <div
          class="text-blue-500"
          @click="updateItem">
          Save
        </div>
        <div
          class="text-red-500"
          @click="showDeleteModal = true">
          Delete
        </div>
      </div>
    </td>
    <EditPricesModal
      v-if="showEditPricesModal"
      :item="item"
      :price="item.price"
      @close="showEditPricesModal = false"
      @save="saveNewPrices" />
    <DeleteModal
      :show="showDeleteModal"
      :title="'Delete - ' + item.name"
      @close="showDeleteModal = false"
      @delete="deleteItem" />
    <InventorySettingsModal
      v-if="showSettingsModal"
      :item="item"
      @close="showSettingsModal = false" />
  </tr>
</template>

<script>
import Switch from '../common/Switch.vue'
import DeleteModal from '../Modals/DeleteModal.vue'
import EditPricesModal from '../Modals/EditPricesModal.vue'
import { ClockIcon } from '@heroicons/vue/outline'
import InventorySettingsModal from '../Modals/InventorySettingsModal.vue'

export default {
  props: {
    idx: {
      type: Number,
      default: () => 0
    },
    item: {
      type: Object,
      default: () => {}
    },
    inventory: {
      type: Object,
      default: () => {}
    }
  },
  components: { InventorySettingsModal, Switch, DeleteModal, EditPricesModal, ClockIcon },
  data: () => ({
    defaultItem: null,
    isRemoving: false,
    showDeleteModal: false,
    showEditPricesModal: false,
    showSettingsModal: false
  }),
  computed: {
    isChanged() {
      return !_.isEqual(this.defaultItem, this.item)
    }
  },
  mounted() {
    this.defaultItem = { ...this.item }
  },
  methods: {
    updateItem() {
      axios.put('/api/backoffice/inventory/' + this.item.id, this.item).then(() => {
        this.defaultItem = { ...this.item }
      })
    },
    saveNewPrices(prices) {
      this.item.price = prices.retail_price
      this.updateItem()
      axios.post('/api/backoffice/inventory-pricing', prices).then((res) => {
        this.$store.commit('setActiveInventoryPricing', res.data.data)
        this.$store.dispatch('getInventory').then(() => {
          this.defaultItem = { ...this.item }
        })
      })
      this.showEditPricesModal = false
    },
    editPrices() {
      this.showEditPricesModal = true
    },
    deleteItem() {
      this.isRemoving = true
      axios.delete('/api/backoffice/inventory/' + this.item.id).then(() => {
        this.$store.dispatch('getInventory', {})
      })
    },
    showPricingSidebar() {
      this.$store.commit('setActiveInventoryPricing', this.item)
    },
    updateStatus() {
      this.item.active = !this.item.active ? 1 : 0
    },
    selectAll(e) {
      e.target.select()
    },
    soldByText(id) {
      if (id === 1) return 'PP'
      if (id === 2) return 'KG'
      return 'KOM'
    }
  }
}
</script>

<style scoped>
.min-w-input-name {
  min-width: 300px;
}
</style>

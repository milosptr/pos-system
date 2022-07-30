<template>
  <tr class="hover:bg-orange-50 cursor-pointer" :class="{'bg-blue-200 hover:bg-blue-200': isChanged, 'bg-red-300': isRemoving}">
    <td :class="[idx !== inventory.length - 1 ? 'border-b border-gray-200' : '', 'whitespace-nowrap py-2 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6 lg:pl-8']">{{ idx + 1 }}</td>
    <td :class="[idx !== inventory.length - 1 ? 'border-b border-gray-200' : '', 'whitespace-nowrap px-3 py-2 text-sm text-gray-500 ']">
      <input v-model="item.name" type="text" class="appearance-none min-w-input-name w-full p-0 m-0 border-none bg-transparent" />
    </td>
    <td :class="[idx !== inventory.length - 1 ? 'border-b border-gray-200' : '', 'whitespace-nowrap px-3 py-2 text-sm text-gray-500']">
      <input v-model="item.price" type="number" class="appearance-none w-20 p-0 m-0 border-none bg-transparent text-right" @click="selectAll" />
     RSD</td>
    <td :class="[idx !== inventory.length - 1 ? 'border-b border-gray-200' : '', 'whitespace-nowrap px-3 py-2 text-sm text-gray-500']">{{ soldByText(item.sold_by) }}</td>
    <td :class="[idx !== inventory.length - 1 ? 'border-b border-gray-200' : '', 'whitespace-nowrap px-3 py-2 text-sm text-gray-500']">
      <input v-model="item.order" type="number" class="appearance-none w-16 p-0 m-0 border-none bg-transparent" />
    </td>
    <td :class="[idx !== inventory.length - 1 ? 'border-b border-gray-200' : '', 'whitespace-nowrap px-3 py-2 text-sm text-gray-500']">{{ item.category_name }}</td>
    <td :class="[idx !== inventory.length - 1 ? 'border-b border-gray-200' : '', 'whitespace-nowrap px-3 py-2 text-sm text-gray-500']">
      <Switch :enabled="!!item.active" @click="updateStatus" />
    </td>
    <td :class="[idx !== inventory.length - 1 ? 'border-b border-gray-200' : '', 'whitespace-nowrap px-3 py-2 text-sm text-gray-500']">
      <input v-model="item.color" type="color" name="" id="">
    </td>
    <td :class="[idx !== inventory.length - 1 ? 'border-b border-gray-200' : '', 'whitespace-nowrap px-3 py-2 text-sm text-gray-500']">
      <div class="flex gap-4">
        <div class="text-blue-500" @click="updateItem">Save</div>
        <div class="text-red-500" @click="deleteItem">Delete</div>
      </div>
    </td>
  </tr>
</template>

<script>
  import Switch from '../common/Switch.vue'
  export default {
    props: {
      idx: {
        type: Number,
        default: () => 0,
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
    components: { Switch },
    data: () => ({
      defaultItem: null,
      isRemoving: false,
    }),
    computed: {
      isChanged() {
        return !_.isEqual(this.defaultItem, this.item)
      },
    },
    mounted() {
      this.defaultItem = { ...this.item }
    },
    methods: {
      updateItem() {
        axios.put('/api/backoffice/inventory/' + this.item.id, this.item)
          .then((res) => {
            this.defaultItem = { ...this.item }
          })
      },
      deleteItem() {
        this.isRemoving = true
        axios.delete('/api/backoffice/inventory/' + this.item.id)
          .then(() => {
            this.$store.dispatch('getInventory', {})
          })
      },
      updateStatus(e) {
        this.item.active = !this.item.active ? 1 : 0
      },
      selectAll(e) {
        e.target.select()
      },
      soldByText(id) {
        if(id === 1)
          return 'PP'
        if(id === 2)
          return 'KG'
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

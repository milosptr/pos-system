<template>
  <tr class="hover:bg-orange-50 cursor-pointer" :class="{'bg-blue-200 hover:bg-blue-200': isChanged}">
    <td :class="[idx !== inventory.length - 1 ? 'border-b border-gray-200' : '', 'whitespace-nowrap py-2 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6 lg:pl-8']">{{ idx + 1 }}</td>
    <td :class="[idx !== inventory.length - 1 ? 'border-b border-gray-200' : '', 'whitespace-nowrap px-3 py-2 text-sm text-gray-500 hidden sm:table-cell']">
      <input v-model="item.name" type="text" class="appearance-none w-full p-0 m-0 border-none bg-transparent" />
    </td>
    <td :class="[idx !== inventory.length - 1 ? 'border-b border-gray-200' : '', 'whitespace-nowrap px-3 py-2 text-sm text-gray-500']">
      <input v-model="item.price" type="number" class="appearance-none w-20 p-0 m-0 border-none bg-transparent text-right" />
     RSD</td>
    <td :class="[idx !== inventory.length - 1 ? 'border-b border-gray-200' : '', 'whitespace-nowrap px-3 py-2 text-sm text-gray-500']">{{ soldByText(item.sold_by) }}</td>
    <td :class="[idx !== inventory.length - 1 ? 'border-b border-gray-200' : '', 'whitespace-nowrap px-3 py-2 text-sm text-gray-500']">
      <input v-model="item.order" type="number" class="appearance-none w-16 p-0 m-0 border-none bg-transparent" />
    </td>
    <td :class="[idx !== inventory.length - 1 ? 'border-b border-gray-200' : '', 'whitespace-nowrap px-3 py-2 text-sm text-gray-500']">{{ item.category_name }}</td>
    <td :class="[idx !== inventory.length - 1 ? 'border-b border-gray-200' : '', 'whitespace-nowrap px-3 py-2 text-sm text-gray-500']">
      <div class="text-blue-500" @click="updateItem">Save</div>
    </td>
  </tr>
</template>

<script>
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
    data: () => ({
      defaultItem: null,
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

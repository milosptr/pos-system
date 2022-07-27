<template>
  <tr class="hover:bg-orange-50 cursor-pointer" :class="{'bg-blue-200 hover:bg-blue-200': isChanged}">
    <td :class="[idx !== tables.length - 1 ? 'border-b border-gray-200' : '', 'whitespace-nowrap py-2 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6 lg:pl-8']">{{ item.id }}</td>
    <td :class="[idx !== tables.length - 1 ? 'border-b border-gray-200' : '', 'whitespace-nowrap px-3 py-2 text-sm text-gray-500 hidden sm:table-cell']">
      <input v-model="item.name" type="text" class="appearance-none w-full p-0 m-0 border-none bg-transparent" />
    </td>
    <td :class="[idx !== tables.length - 1 ? 'border-b border-gray-200' : '', 'whitespace-nowrap px-3 py-2 text-sm text-gray-500 hidden sm:table-cell']">{{ item.area ? 'Bašta' : 'Sala' }}</td>
    <td :class="[idx !== tables.length - 1 ? 'border-b border-gray-200' : '', 'whitespace-nowrap px-3 py-2 text-sm text-gray-500 hidden lg:table-cell']">
      <input v-model="item.table_number" type="number" class="appearance-none w-20 p-0 m-0 border-none bg-transparent" />
    </td>
    <td :class="[idx !== tables.length - 1 ? 'border-b border-gray-200' : '', 'whitespace-nowrap px-3 py-2 text-sm text-gray-500']">
      <div class="flex items-center gap-3">
        <div>Small</div>
        <Switch :enabled="item.size" @click="updateSize" />
        <div>Big</div>
      </div>
    </td>
    <td :class="[idx !== tables.length - 1 ? 'border-b border-gray-200' : '', 'whitespace-nowrap px-3 py-2 text-sm text-gray-500']">
      <Switch :enabled="item.rotate" @click="updateRotation" />
    </td>
    <td :class="[idx !== tables.length - 1 ? 'border-b border-gray-200' : '', 'whitespace-nowrap px-3 py-2 text-sm text-gray-500']">
      <div class="text-blue-500" @click="updateItem">Save</div>
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
      tables: {
        type: Object,
        default: () => {}
      }
    },
    components: {
      Switch,
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
        axios.put('/api/backoffice/tables/' + this.item.id, this.item)
          .then((res) => {
            this.defaultItem = { ...this.item }
          })
      },
      updateSize(e) {
        this.item.size = !this.item.size ? 1 : 0
      },
      updateRotation(e) {
        this.item.rotate = !this.item.rotate ? 1 : 0
      },
      selectAll(e) {
        e.target.select()
      },
    }
  }
</script>

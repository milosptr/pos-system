<template>
  <table
    v-if="backups.length"
    class="w-full">
    <thead class="bg-gray-300">
      <tr class="text-left">
        <th class="px-4 py-1">id</th>
        <th class="px-4 py-1">name</th>
        <th class="px-4 py-1">path</th>
        <th class="px-4 py-1">size</th>
        <th class="px-4 py-1">is_uploaded</th>
        <th class="px-4 py-1">is_deleted</th>
        <th class="px-4 py-1">created_at</th>
      </tr>
    </thead>
    <tbody>
      <tr
        v-for="backup in backups"
        :key="backup.id"
        class="border border-solid border-gray-300"
        :class="[backup.is_uploaded ? 'bg-green-200' : 'bg-red-200']">
        <td class="px-4 py-1">{{ backup.id }}</td>
        <td class="px-4 py-1">{{ backup.filename }}</td>
        <td class="px-4 py-1">{{ backup.path }}</td>
        <td class="px-4 py-1">{{ backup.size }}</td>
        <td class="px-4 py-1">{{ backup.is_uploaded ? 'Yes' : 'No' }}</td>
        <td class="px-4 py-1">{{ backup.is_deleted ? 'Yes' : 'No' }}</td>
        <td class="px-4 py-1">{{ castDate(backup.created_at) }}</td>
      </tr>
    </tbody>
  </table>
  <div v-if="backups.length === 0">No backups found in the database.</div>
</template>

<script>
import Table from '@/js/components/Tables/Table.vue'

export default {
  name: 'BackofficeBackups',
  components: { Table },
  data: () => ({
    backups: []
  }),
  mounted() {
    axios('/api/backoffice/backups')
      .then((response) => {
        this.backups = response.data.backups
      })
      .catch((error) => {
        console.error(error)
      })
  },
  methods: {
    castDate(date) {
      return new Date(date).toLocaleString('sr-RS')
    }
  }
}
</script>

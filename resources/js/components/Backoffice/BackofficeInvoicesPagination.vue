<template>
  <div
    v-if="pagination.meta"
    class="mt-3 flex items-center justify-between px-1 py-3"
    aria-label="Pagination">
    <div class="hidden sm:block">
      <p class="text-sm text-gray-700">
        Showing
        {{ ' ' }}
        <span class="font-medium">{{ from }}</span>
        {{ ' ' }}
        to
        {{ ' ' }}
        <span class="font-medium">{{ to }}</span>
        {{ ' ' }}
        of
        {{ ' ' }}
        <span class="font-medium">{{ pagination.meta.total }}</span>
        {{ ' ' }}
        results
      </p>
    </div>
    <div class="flex flex-1 justify-between sm:justify-end">
      <div
        v-if="pagination.links.prev"
        class="relative inline-flex cursor-pointer items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
        @click="prevPage">
        Previous
      </div>
      <div
        v-if="pagination.links.next"
        class="relative ml-3 inline-flex cursor-pointer items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
        :class="{ 'ml-auto': !pagination.links.prev }"
        @click="nextPage">
        Next
      </div>
    </div>
  </div>
</template>

<script>
export default {
  computed: {
    pagination() {
      return this.$store.getters.pagination
    },
    from() {
      return this.pagination.meta.per_page * this.pagination.meta.current_page - this.pagination.meta.per_page + 1
    },
    to() {
      const to = this.pagination.meta.per_page * this.pagination.meta.current_page
      return to > this.pagination.meta.total ? this.pagination.meta.total : to
    }
  },
  methods: {
    prevPage() {
      const key = 'page'
      const value = this.pagination.meta.current_page - 1
      this.$store.commit('setReportFilters', { key, value })
      this.$store.dispatch('getInvoices')
      this.scrollToTop()
    },
    nextPage() {
      const key = 'page'
      const value = this.pagination.meta.current_page + 1
      this.$store.commit('setReportFilters', { key, value })
      this.$store.dispatch('getInvoices')
      this.scrollToTop()
    },
    scrollToTop() {
      window.scroll({
        top: 0,
        left: 0,
        behavior: 'smooth'
      })
    }
  }
}
</script>

<template>
  <div v-if="pagination.meta" class="px-1 py-3 flex items-center justify-between mt-3" aria-label="Pagination">
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
    <div class="flex-1 flex justify-between sm:justify-end">
      <div
        v-if="pagination.links.prev"
        class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 cursor-pointer"
        @click="prevPage"
      >
        Previous
      </div>
      <div
        v-if="pagination.links.next"
        class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 cursor-pointer"
        :class="{'ml-auto': !pagination.links.prev}"
        @click="nextPage"
      >
        Next
      </div>
    </div>
  </div>
</template>

<script>
  export default {
    computed: {
      pagination() {
        return this.$store.getters.thirdPartyPagination
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
        this.$store.dispatch('getThirdPartyInvoices')
        this.scrollToTop()
      },
      nextPage() {
        const key = 'page'
        const value = this.pagination.meta.current_page + 1
        this.$store.commit('setReportFilters', { key, value })
        this.$store.dispatch('getThirdPartyInvoices')
        this.scrollToTop()
      },
      scrollToTop() {
        window.scroll({
          top: 0,
          left: 0,
          behavior: 'smooth'
        })
      },
    }
  }
</script>

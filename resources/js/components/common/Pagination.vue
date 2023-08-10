<template>
  <nav class="flex items-center justify-between border-t border-gray-200 py-3 px-2"
    aria-label="Pagination">
    <div class="hidden sm:block">
      <p class="text-sm text-gray-700">
        Stranica
        <span class="font-medium">{{ currentPage }}</span>
        od
        <span class="font-medium">{{ pages }}</span>
      </p>
    </div>
    <div class="flex flex-1 justify-between sm:justify-end">
      <a @click="previousPage" :class="{'opacity-50': isFirstPage}"
        class="relative inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus-visible:outline-offset-0">Prethodna</a>
      <a @click="nextPage"
        class="relative ml-3 inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus-visible:outline-offset-0"
        :class="{'opacity-50': isLastPage}">Sledeća</a>
    </div>
  </nav>
</template>

<script>
  export default {
    name: "Pagination",
    props: {
      total: {
        type: Number,
        required: true,
      },
      perPage: {
        type: Number,
        required: true,
      },
      currentPage: {
        type: Number,
        required: true,
      },
    },
    computed: {
      pages() {
        return Math.ceil(this.total / this.perPage);
      },
      isFirstPage() {
        return this.currentPage === 1;
      },
      isLastPage() {
        return this.currentPage === this.pages;
      },
    },
    methods: {
      previousPage() {
        if (this.currentPage > 1) {
          this.$emit("change", this.currentPage - 1);
        }
      },
      nextPage() {
        if (this.currentPage < this.pages) {
          this.$emit("change", this.currentPage + 1);
        }
      },
    },
  }
</script>

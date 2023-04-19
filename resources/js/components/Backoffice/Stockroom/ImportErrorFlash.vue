<template>
  <div id="importMagFlash" aria-live="assertive"
    class="pointer-events-none fixed inset-x-0 bottom-0 flex items-end px-4 py-6 sm:items-start sm:p-6">
    <div class="flex w-full flex-col items-center space-y-4 sm:items-end">
      <div
        class="pointer-events-auto w-full max-w-sm overflow-hidden rounded-lg bg-white shadow-lg ring-1 ring-black ring-opacity-5">
        <div class="p-4">
          <div class="flex items-start">
            <div class="flex-shrink-0">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 14 14" class="h-6 w-6 text-green-400"><g><line x1="7" y1="5" x2="7" y2="8" fill="none" stroke="#f22b2b" stroke-linecap="round" stroke-linejoin="round"></line><circle cx="7" cy="11" r="0.5" fill="none" stroke="#f22b2b" stroke-linecap="round" stroke-linejoin="round"></circle><path d="M7.89,1.05a1,1,0,0,0-1.78,0l-5.5,11A1,1,0,0,0,1.5,13.5h11a1,1,0,0,0,.89-1.45Z" fill="none" stroke="#f22b2b" stroke-linecap="round" stroke-linejoin="round"></path></g></svg>
            </div>
            <div class="ml-3 w-0 flex-1 pt-0.5">
              <p class="text-sm font-medium text-gray-900">Došlo je do greške!</p>
              <p class="text-sm font-medium text-gray-500 mt-0.5">{{ error }}</p>
            </div>
            <div class="ml-4 flex flex-shrink-0">
              <button type="button" @click="closeFlash"
                class="inline-flex rounded-md bg-white text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                <span class="sr-only">Close</span>
                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                  <path
                    d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" />
                </svg>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: "ImportErrorFlash",
  props: {
    error: {
      type: String,
      required: true,
    },
  },
  methods: {
    closeFlash() {
      document.getElementById("importMagFlash").remove();
    },
  },
}
</script>

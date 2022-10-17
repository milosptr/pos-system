<template>
  <div>
    <div class="text-xl font-semibold mt-10">Employees</div>
    <div class="mt-5 bg-white shadow rounded-lg">
      <div class="grid grid-cols-1 lg:grid-cols-3 xl:grid-cols-6 divide-y lg:divide-y-0 divide-x divide-gray-200">
        <div
          v-for="(shift, index) in shifts"
          :key="'s-' + shift"
          class="flex flex-col gap-2 p-4 sm:p-6 sm:pt-2"
        >
          <div class="text-center font-semibold text-lg mb-3 border-b border-gray-200">
            {{ shift }}
          </div>
         <div class="w-full h-full flex flex-col justify-center items-center gap-2">
           <div
              v-for="schedule in getFilteredEmployees(index+1, 1)"
              :key="schedule.id"
              class="relative w-full rounded-xl py-1 px-1 lg:px-2 text-center select-none tracking-wide text-sm text-white"
              :style="'background: ' + schedule.employee.color"
            >
              {{ schedule.employee.name }}
              <span v-if="schedule.time"> ({{ schedule.time }})</span>
            </div>
         </div>
        </div>
        <div
          v-for="(shift, index) in shifts"
          :key="'s-' + shift"
          class="flex flex-col gap-2 p-4 sm:p-6 sm:pt-2"
        >
          <div class="text-center font-semibold text-lg mb-3 border-b border-gray-200">
            {{ shift }}
          </div>
           <div class="w-full h-full flex flex-col justify-center items-center gap-2">
            <div
              v-for="schedule in getFilteredEmployees(index+1, 0)"
              :key="schedule.id"
              class="relative w-full rounded-xl py-1 px-1 lg:px-2 text-center select-none tracking-wide text-sm text-white"
              :style="'background: ' + schedule.employee.color"
            >
              {{ schedule.employee.name }}
              <span v-if="schedule.time"> ({{ schedule.time }})</span>
            </div>
           </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
  export default {
    data: () => ({
      shifts: ['I', 'M', 'II'],
      schedules: [],
    }),
    mounted() {
      fetch("http://192.168.200.30:81/public/today")
        .then(response => response.json())
        .then(result => { this.schedules = result.data })
        .catch(error => console.log('error', error))
    },
    methods: {
      getFilteredEmployees(shift, occupation) {
        return this.schedules.filter((s) => s.shift === shift && s.occupation === occupation)
      }
    }
  }
</script>

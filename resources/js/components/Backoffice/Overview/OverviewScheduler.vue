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
         <div class="w-full h-full flex flex-col justify-center items-center gap-2.5">
           <div
              v-for="schedule in getFilteredEmployees(index+1, 1)"
              :key="schedule.id"
              class="relative w-full rounded-xl py-1 px-1 lg:px-2 text-center select-none tracking-wide text-sm text-white"
              :style="`background: ${schedule.employee.color}; order: ${schedule.order}`"
            >
              {{ schedule.employee.name }}
              <span v-if="schedule.time"> ({{ schedule.time }})</span>
              <div v-if="schedule.employee.lastCheckin" class="flex justify-center gap-2">
                <div v-if="schedule.employee.lastCheckin.check_in">{{ formatDate(schedule.employee.lastCheckin.check_in) }}</div>
                <div> - </div>
                <div v-if="schedule.employee.lastCheckin.check_out">{{ formatDate(schedule.employee.lastCheckin.check_out) }}</div>
              </div>
              <div
                v-if="!(!!schedule.employee?.lastCheckin?.check_out || schedule.employee.lastCheckin === null)"
                class="absolute -top-2.5 -right-1 w-[16px] h-[16px] rounded-full bg-[#0BDA51] border-2 border-solid border-white"
              />
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
           <div class="w-full h-full flex flex-col justify-center items-center gap-2.5">
            <div
              v-for="schedule in getFilteredEmployees(index+1, 0)"
              :key="schedule.id"
              class="relative w-full rounded-xl py-1 px-1 lg:px-2 text-center select-none tracking-wide text-sm text-white"
              :style="`background: ${schedule.employee.color}; order: ${schedule.order}`"
            >
              {{ schedule.employee.name }}
              <span v-if="schedule.time"> ({{ schedule.time }})</span>
              <div v-if="schedule.employee.lastCheckin" class="flex justify-center gap-2">
                <div v-if="schedule.employee.lastCheckin.check_in">{{ formatDate(schedule.employee.lastCheckin.check_in) }}</div>
                <div> - </div>
                <div v-if="schedule.employee.lastCheckin.check_out">{{ formatDate(schedule.employee.lastCheckin.check_out) }}</div>
              </div>
              <div
                v-if="!(!!schedule.employee?.lastCheckin?.check_out || schedule.employee.lastCheckin === null)"
                class="absolute -top-2.5 -right-1 w-[16px] h-[16px] rounded-full bg-[#0BDA51] border-2 border-solid border-white"
              />
            </div>
           </div>
        </div>
      </div>
      <div v-if="nonScheduled.length" class="pt-4 border-t">
        <div class="text-lg font-semibold px-4 sm:px-6">Prijavljeni van rasporeda</div>
        <div class="grid grid-cols-6 gap-x-10 px-4 sm:px-6 py-4">
          <div
              v-for="schedule in nonScheduled"
              :key="schedule.id"
              class="relative w-full rounded-xl py-1 px-1 lg:px-2 text-center select-none tracking-wide text-sm text-white"
              :style="`background: ${schedule.color};`"
            >
              {{ schedule.name }}
              <div v-if="schedule.lastCheckin" class="flex justify-center gap-2">
                <div v-if="schedule.lastCheckin.check_in">{{ formatDate(schedule.lastCheckin.check_in) }}</div>
                <div> - </div>
                <div v-if="schedule.lastCheckin.check_out">{{ formatDate(schedule.lastCheckin.check_out) }}</div>
              </div>
              <div
                v-if="!(!!schedule?.lastCheckin?.check_out || schedule.lastCheckin === null)"
                class="absolute -top-2.5 -right-1 w-[16px] h-[16px] rounded-full bg-[#0BDA51] border-2 border-solid border-white"
              />
            </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
  const dateFormatOptions = {
    hour: 'numeric',
    minute: 'numeric',
    hourCycle: 'h23',
  }

  export default {
    data: () => ({
      shifts: ['I', 'M', 'II'],
      schedules: [],
      nonScheduled: [],
    }),
    mounted() {
      this.pusherInit()
      this.fetchSchedules()
      this.fetchNonScheduledEmployees()
    },
    methods: {
      pusherInit() {
        const pusher = new Pusher(import.meta.env.VITE_PUSHER_APP_KEY, { 'cluster' : import.meta.env.VITE_PUSHER_APP_CLUSTER })
        console.log(pusher)
        pusher.subscribe('broadcasting')
        pusher.bind('employee-checkin', (data) => {
          console.log('fetching new info')
          this.fetchSchedules()
        })
      },
      fetchSchedules() {
        // fetch("http://192.168.200.30:81/public/today")
        fetch("http://scheduler.test/public/today")
          .then(response => response.json())
          .then(result => { this.schedules = result.data })
          .catch(error => console.log('error', error))
      },
      fetchNonScheduledEmployees() {
        fetch("http://scheduler.test/public/checkinsToday")
          .then(response => response.json())
          .then(result => { this.nonScheduled = result.data })
          .catch(error => console.log('error', error))
      },
      getFilteredEmployees(shift, occupation) {
        return this.schedules.filter((s) => s.shift === shift && s.occupation === occupation)
      },
      formatDate(date) {
        if(date) {
          return new Intl.DateTimeFormat('default', dateFormatOptions).format(new Date(date))
        }
        return ''
      }
    }
  }
</script>

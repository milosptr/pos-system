<template>
  <div class="flex justify-end gap-4">
    <div class="w-64 relative">
      <litepie-datepicker
        use-range
        separator=" to "
        :formatter="formatter"
        :shortcuts="customShortcuts"
        :auto-apply="false"
        v-model="dateValue"
        />
    </div>
  </div>
</template>

<script>
  const customShortcuts = () => {
    return [
      {
        label: 'Today',
        atClick: () => {
          const date = new Date();
          return [
            new Date(date.setDate(date.getDate())),
            date
          ];
        }
      },
      {
        label: 'Tomorrow',
        atClick: () => {
          const date = new Date();
          return [
            new Date(date.setDate(date.getDate() + 1)),
            date
          ];
        }
      },
      {
        label: 'Yesterday',
        atClick: () => {
          const date = new Date();
          return [
            new Date(date.setDate(date.getDate() - 1)),
            date
          ];
        }
      },
      {
        label: 'This week',
        atClick: () => {
          const date = new Date();
          return [
            new Date(date.setDate(date.getDate() - 1)),
            date
          ];
        }
      },
      {
        label: 'Last week',
        atClick: () => {
          const date = new Date();
          return [
            new Date(date.setDate(date.getDate() - 1)),
            date
          ];
        }
      },
      {
        label: 'This Month',
        atClick: () => {
          const date = new Date();
          return [
            new Date(date.setFullYear(date.getFullYear() - 1)),
            new Date()
          ];
        }
      },
      {
        label: 'Last Month',
        atClick: () => {
          const date = new Date();
          return [
            new Date(date.setFullYear(date.getFullYear() - 1)),
            new Date()
          ];
        }
      },
      {
        label: 'Last 6 Months',
        atClick: () => {
          const date = new Date();
          return [
            new Date(date.setFullYear(date.getFullYear() - 1)),
            new Date()
          ];
        }
      },
      {
        label: 'This Year',
        atClick: () => {
          const date = new Date();
          return [
            new Date(date.setFullYear(date.getFullYear() - 1)),
            new Date()
          ];
        }
      },
    ];
  }

  export default {
    data: () => ({
      dateValue: '',
      formatter: {
        date: 'YYYY-MM-DD',
        month: 'MMM'
      },
      customShortcuts,
    }),
    computed: {},
    mounted() {
      console.log(this.$dayjs(new Date()));
    }
  }
</script>

<template>
  <div>
    <TransitionRoot
      as="template"
      :show="sidebarOpen">
      <Dialog
        as="div"
        class="relative z-40 md:hidden"
        @close="sidebarOpen = false">
        <TransitionChild
          as="template"
          enter="transition-opacity ease-linear duration-300"
          enter-from="opacity-0"
          enter-to="opacity-100"
          leave="transition-opacity ease-linear duration-300"
          leave-from="opacity-100"
          leave-to="opacity-0">
          <div class="fixed inset-0 bg-gray-600 bg-opacity-75" />
        </TransitionChild>

        <div class="fixed inset-y-0 right-0 flex z-40">
          <div class="flex-shrink-0 w-14">
            <!-- Force sidebar to shrink to fit close icon -->
          </div>
          <TransitionChild
            as="template"
            enter="transition ease-in-out duration-300 transform"
            enter-from="translate-x-full"
            enter-to="translate-x-0"
            leave="transition ease-in-out duration-300 transform"
            leave-from="translate-x-0"
            leave-to="translate-x-full">
            <DialogPanel class="relative flex-1 flex flex-col max-w-xs w-full bg-gray-800">
              <TransitionChild
                as="template"
                enter="ease-in-out duration-300"
                enter-from="opacity-0"
                enter-to="opacity-100"
                leave="ease-in-out duration-300"
                leave-from="opacity-100"
                leave-to="opacity-0">
                <div class="absolute top-0 left-0 -ml-12 pt-2">
                  <button
                    type="button"
                    class="ml-1 flex items-center justify-center h-10 w-10 rounded-full focus:outline-none focus:ring-2 focus:ring-inset focus:ring-white"
                    @click="sidebarOpen = false">
                    <span class="sr-only">Close sidebar</span>
                    <XIcon
                      class="h-6 w-6 text-white"
                      aria-hidden="true" />
                  </button>
                </div>
              </TransitionChild>
              <div class="flex-1 h-0 pt-5 pb-4 overflow-y-auto">
                <div class="flex-shrink-0 flex items-center px-4">
                  <svg
                    class="h-8 w-auto"
                    viewBox="0 0 1695 278"
                    fill="none"
                    xmlns="http://www.w3.org/2000/svg">
                    <path
                      d="M552.901 0.865021C544.186 1.98957 532.94 4.42608 524.787 7.05002C515.885 9.86138 499.204 17.5458 494.893 20.8257L492.644 22.5125L501.359 38.8184C506.139 47.8148 510.075 55.218 510.168 55.3117C510.262 55.4054 512.605 53.9997 515.416 52.1255C540.812 35.0699 581.389 32.5397 610.628 46.1279C635.18 57.6545 654.485 79.958 662.544 106.01C670.041 130.281 668.354 160.738 658.233 183.603C651.955 197.848 637.054 215.653 624.216 224.275C605.567 236.832 582.795 242.267 558.43 240.112C524.319 237.207 494.518 216.59 480.18 186.321C478.681 183.229 477.369 180.511 477.275 180.324C477.088 180.042 443.445 197.66 442.415 198.597C442.227 198.691 443.914 202.158 446.069 206.188C462.469 236.738 491.145 260.541 523.85 270.943C540.062 276.097 547.653 277.128 568.364 277.128C584.107 277.128 588.699 276.753 596.946 274.973C616.813 270.756 634.524 263.165 649.237 252.576C678.756 231.49 697.311 203.564 704.902 168.703C706.682 160.457 706.964 156.333 706.964 138.997C706.964 121.66 706.682 117.537 704.902 109.29C698.811 81.1762 687.003 60.1847 665.637 39.5681C638.741 13.6099 609.784 1.61472 572.112 0.771309C563.397 0.583885 554.682 0.677597 552.901 0.865021Z"
                      fill="white" />
                    <path
                      d="M826.072 1.80213C798.145 3.86379 776.967 10.0488 761.223 20.732C752.508 26.6358 742.855 37.6938 738.451 46.5965C732.641 58.4042 731.422 64.2144 731.422 79.958C731.516 92.328 731.704 94.2022 734.046 100.856C739.388 116.225 748.853 126.345 766.002 134.78C784.651 143.963 798.52 147.712 841.347 154.928C856.997 157.552 873.396 160.738 877.894 162.05C894.575 166.829 907.695 174.982 911.912 183.229C916.223 191.663 916.129 206.001 911.724 215.466C903.197 233.552 878.644 243.017 840.316 243.017C789.055 243.017 764.69 228.304 764.69 197.379V190.538H745.011H725.331V193.537C725.331 200.191 727.487 213.872 729.642 220.526C732.734 230.272 737.701 238.331 745.854 246.953C760.942 262.884 780.621 271.787 810.141 276.004C815.295 276.753 827.477 277.222 841.066 277.222C860.183 277.128 865.431 276.847 875.739 274.973C909.475 269.069 932.528 254.825 945.367 231.771C955.301 214.06 956.706 183.416 948.366 166.642C942.837 155.302 932.341 145.369 918.847 138.341C904.04 130.75 893.169 127.845 849.031 119.786C815.107 113.601 801.988 110.321 791.586 105.448C776.217 98.2318 770.032 89.6103 769.938 75.6472C769.938 69.2748 770.313 67.3068 772.468 62.8087C781.558 44.2537 812.39 33.5705 848.094 36.7567C864.494 38.1624 872.272 39.9429 882.486 44.9097C896.918 51.7506 903.946 60.4659 904.977 72.5547L905.539 78.0837H925.032H944.524L943.961 72.6484C940.681 35.1636 912.38 10.0488 866.18 3.67637C855.591 2.27069 834.037 1.23985 826.072 1.80213Z"
                      fill="white" />
                    <path
                      d="M1125.07 1.80213C1097.15 3.86379 1075.97 10.0488 1060.22 20.732C1051.51 26.6358 1041.86 37.6938 1037.45 46.5965C1031.64 58.4042 1030.42 64.2144 1030.42 79.958C1030.52 92.328 1030.7 94.2022 1033.05 100.856C1038.39 116.225 1047.85 126.345 1065 134.78C1083.65 143.963 1097.52 147.712 1140.35 154.928C1156 157.552 1172.4 160.738 1176.89 162.05C1193.58 166.829 1206.69 174.982 1210.91 183.229C1215.22 191.663 1215.13 206.001 1210.72 215.466C1202.2 233.552 1177.64 243.017 1139.32 243.017C1088.06 243.017 1063.69 228.304 1063.69 197.379V190.538H1044.01H1024.33V193.537C1024.33 200.191 1026.49 213.872 1028.64 220.526C1031.73 230.272 1036.7 238.331 1044.85 246.953C1059.94 262.884 1079.62 271.787 1109.14 276.004C1114.29 276.753 1126.48 277.222 1140.07 277.222C1159.18 277.128 1164.43 276.847 1174.74 274.973C1208.48 269.069 1231.53 254.825 1244.37 231.771C1254.3 214.06 1255.71 183.416 1247.37 166.642C1241.84 155.302 1231.34 145.369 1217.85 138.341C1203.04 130.75 1192.17 127.845 1148.03 119.786C1114.11 113.601 1100.99 110.321 1090.59 105.448C1075.22 98.2318 1069.03 89.6103 1068.94 75.6472C1068.94 69.2748 1069.31 67.3068 1071.47 62.8087C1080.56 44.2537 1111.39 33.5705 1147.09 36.7567C1163.49 38.1624 1171.27 39.9429 1181.49 44.9097C1195.92 51.7506 1202.95 60.4659 1203.98 72.5547L1204.54 78.0837H1224.03H1243.52L1242.96 72.6484C1239.68 35.1636 1211.38 10.0488 1165.18 3.67637C1154.59 2.27069 1133.04 1.23985 1125.07 1.80213Z"
                      fill="white" />
                    <path
                      d="M1531.74 1.80213C1503.81 3.86379 1482.64 10.0488 1466.89 20.732C1458.18 26.6358 1448.52 37.6938 1444.12 46.5965C1438.31 58.4042 1437.09 64.2144 1437.09 79.958C1437.18 92.328 1437.37 94.2022 1439.72 100.856C1445.06 116.225 1454.52 126.345 1471.67 134.78C1490.32 143.963 1504.19 147.712 1547.02 154.928C1562.67 157.552 1579.07 160.738 1583.56 162.05C1600.24 166.829 1613.36 174.982 1617.58 183.229C1621.89 191.663 1621.8 206.001 1617.39 215.466C1608.87 233.552 1584.31 243.017 1545.98 243.017C1494.72 243.017 1470.36 228.304 1470.36 197.379V190.538H1450.68H1431V193.537C1431 200.191 1433.16 213.872 1435.31 220.526C1438.4 230.272 1443.37 238.331 1451.52 246.953C1466.61 262.884 1486.29 271.787 1515.81 276.004C1520.96 276.753 1533.15 277.222 1546.73 277.222C1565.85 277.128 1571.1 276.847 1581.41 274.973C1615.14 269.069 1638.2 254.825 1651.04 231.771C1660.97 214.06 1662.38 183.416 1654.03 166.642C1648.51 155.302 1638.01 145.369 1624.52 138.341C1609.71 130.75 1598.84 127.845 1554.7 119.786C1520.78 113.601 1507.66 110.321 1497.25 105.448C1481.89 98.2318 1475.7 89.6103 1475.61 75.6472C1475.61 69.2748 1475.98 67.3068 1478.14 62.8087C1487.23 44.2537 1518.06 33.5705 1553.76 36.7567C1570.16 38.1624 1577.94 39.9429 1588.16 44.9097C1602.59 51.7506 1609.62 60.4659 1610.65 72.5547L1611.21 78.0837H1630.7H1650.19L1649.63 72.6484C1646.35 35.1636 1618.05 10.0488 1571.85 3.67637C1561.26 2.27069 1539.71 1.23985 1531.74 1.80213Z"
                      fill="white" />
                    <path
                      fill-rule="evenodd"
                      clip-rule="evenodd"
                      d="M1235.59 106L1281.35 181.097V278H1320.65V181.891L1431 0H1391.7L1302.39 146.546L1277.79 106H1235.59Z"
                      fill="white" />
                    <path
                      d="M251.148 138.528V273.005H270.828H290.507V224.275V175.544H343.923C389.186 175.544 398.557 175.357 404.649 174.045C431.357 168.516 453.754 150.617 465.374 125.315C471.09 113.132 472.59 105.635 472.59 90.2663C472.59 74.1478 470.903 66.4635 464.343 53.2501C453.379 30.7592 432.575 14.0784 407.085 7.33116C396.964 4.61351 381.502 4.05124 316.091 4.05124H251.148V138.528ZM397.245 42.9417C410.834 46.2217 424.328 56.1551 430.326 67.4006C435.386 76.6781 437.167 90.9223 434.73 101.512C431.825 114.069 421.329 127.47 409.615 133.28C398.838 138.715 396.402 138.997 340.924 138.997H290.507V90.2663V41.5361H340.924C382.626 41.5361 392.279 41.8172 397.245 42.9417Z"
                      fill="white" />
                    <path
                      d="M0 138.997V273.005H107.769H215.538V255.2V237.394H127.448H39.3591V196.629V155.865H113.86H188.361V138.059V120.254H113.86H39.3591V80.4265V40.5989H127.448H215.538V22.7936V4.98834H107.769H0V138.997Z"
                      fill="white" />
                  </svg>
                </div>
                <nav class="mt-5 px-2 space-y-1">
                  <router-link
                    v-for="item in navigation"
                    :key="item.name"
                    :to="{ name: item.href }"
                    :class="[
                      currentRoute === item.href
                        ? 'bg-gray-900 text-white'
                        : 'text-gray-300 hover:bg-gray-700 hover:text-white',
                      'group flex items-center px-2 py-2 text-base font-medium rounded-md'
                    ]"
                    @click="sidebarOpen = false">
                    <component
                      :is="item.icon"
                      :class="[
                        currentRoute === item.href ? 'text-gray-300' : 'text-gray-400 group-hover:text-gray-300',
                        'mr-4 flex-shrink-0 h-6 w-6'
                      ]"
                      aria-hidden="true" />
                    {{ item.name }}
                  </router-link>
                </nav>
              </div>
              <div class="flex-shrink-0 flex bg-gray-700 p-4">
                <div class="flex-shrink-0 group block">
                  <div class="flex items-center">
                    <div>
                      <img
                        class="inline-block h-10 w-10 rounded-full"
                        src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80"
                        alt="" />
                    </div>
                    <div class="ml-3">
                      <p class="text-base font-medium text-white">Tom Cook</p>
                      <a
                        href="/logout"
                        class="text-xs font-medium text-gray-300 group-hover:text-gray-200"
                        >Logout</a
                      >
                    </div>
                  </div>
                </div>
              </div>
            </DialogPanel>
          </TransitionChild>
        </div>
      </Dialog>
    </TransitionRoot>

    <!-- Static sidebar for desktop -->
    <div class="hidden md:flex md:w-64 md:flex-col md:fixed md:inset-y-0">
      <!-- Sidebar component, swap this element with another sidebar if you like -->
      <div class="flex-1 flex flex-col min-h-0 bg-gray-800">
        <div class="flex-1 flex flex-col pt-5 pb-4 overflow-y-auto">
          <div class="flex items-center flex-shrink-0 px-4">
            <svg
              class="h-6 w-auto"
              viewBox="0 0 1695 278"
              fill="none"
              xmlns="http://www.w3.org/2000/svg">
              <path
                d="M552.901 0.865021C544.186 1.98957 532.94 4.42608 524.787 7.05002C515.885 9.86138 499.204 17.5458 494.893 20.8257L492.644 22.5125L501.359 38.8184C506.139 47.8148 510.075 55.218 510.168 55.3117C510.262 55.4054 512.605 53.9997 515.416 52.1255C540.812 35.0699 581.389 32.5397 610.628 46.1279C635.18 57.6545 654.485 79.958 662.544 106.01C670.041 130.281 668.354 160.738 658.233 183.603C651.955 197.848 637.054 215.653 624.216 224.275C605.567 236.832 582.795 242.267 558.43 240.112C524.319 237.207 494.518 216.59 480.18 186.321C478.681 183.229 477.369 180.511 477.275 180.324C477.088 180.042 443.445 197.66 442.415 198.597C442.227 198.691 443.914 202.158 446.069 206.188C462.469 236.738 491.145 260.541 523.85 270.943C540.062 276.097 547.653 277.128 568.364 277.128C584.107 277.128 588.699 276.753 596.946 274.973C616.813 270.756 634.524 263.165 649.237 252.576C678.756 231.49 697.311 203.564 704.902 168.703C706.682 160.457 706.964 156.333 706.964 138.997C706.964 121.66 706.682 117.537 704.902 109.29C698.811 81.1762 687.003 60.1847 665.637 39.5681C638.741 13.6099 609.784 1.61472 572.112 0.771309C563.397 0.583885 554.682 0.677597 552.901 0.865021Z"
                fill="white" />
              <path
                d="M826.072 1.80213C798.145 3.86379 776.967 10.0488 761.223 20.732C752.508 26.6358 742.855 37.6938 738.451 46.5965C732.641 58.4042 731.422 64.2144 731.422 79.958C731.516 92.328 731.704 94.2022 734.046 100.856C739.388 116.225 748.853 126.345 766.002 134.78C784.651 143.963 798.52 147.712 841.347 154.928C856.997 157.552 873.396 160.738 877.894 162.05C894.575 166.829 907.695 174.982 911.912 183.229C916.223 191.663 916.129 206.001 911.724 215.466C903.197 233.552 878.644 243.017 840.316 243.017C789.055 243.017 764.69 228.304 764.69 197.379V190.538H745.011H725.331V193.537C725.331 200.191 727.487 213.872 729.642 220.526C732.734 230.272 737.701 238.331 745.854 246.953C760.942 262.884 780.621 271.787 810.141 276.004C815.295 276.753 827.477 277.222 841.066 277.222C860.183 277.128 865.431 276.847 875.739 274.973C909.475 269.069 932.528 254.825 945.367 231.771C955.301 214.06 956.706 183.416 948.366 166.642C942.837 155.302 932.341 145.369 918.847 138.341C904.04 130.75 893.169 127.845 849.031 119.786C815.107 113.601 801.988 110.321 791.586 105.448C776.217 98.2318 770.032 89.6103 769.938 75.6472C769.938 69.2748 770.313 67.3068 772.468 62.8087C781.558 44.2537 812.39 33.5705 848.094 36.7567C864.494 38.1624 872.272 39.9429 882.486 44.9097C896.918 51.7506 903.946 60.4659 904.977 72.5547L905.539 78.0837H925.032H944.524L943.961 72.6484C940.681 35.1636 912.38 10.0488 866.18 3.67637C855.591 2.27069 834.037 1.23985 826.072 1.80213Z"
                fill="white" />
              <path
                d="M1125.07 1.80213C1097.15 3.86379 1075.97 10.0488 1060.22 20.732C1051.51 26.6358 1041.86 37.6938 1037.45 46.5965C1031.64 58.4042 1030.42 64.2144 1030.42 79.958C1030.52 92.328 1030.7 94.2022 1033.05 100.856C1038.39 116.225 1047.85 126.345 1065 134.78C1083.65 143.963 1097.52 147.712 1140.35 154.928C1156 157.552 1172.4 160.738 1176.89 162.05C1193.58 166.829 1206.69 174.982 1210.91 183.229C1215.22 191.663 1215.13 206.001 1210.72 215.466C1202.2 233.552 1177.64 243.017 1139.32 243.017C1088.06 243.017 1063.69 228.304 1063.69 197.379V190.538H1044.01H1024.33V193.537C1024.33 200.191 1026.49 213.872 1028.64 220.526C1031.73 230.272 1036.7 238.331 1044.85 246.953C1059.94 262.884 1079.62 271.787 1109.14 276.004C1114.29 276.753 1126.48 277.222 1140.07 277.222C1159.18 277.128 1164.43 276.847 1174.74 274.973C1208.48 269.069 1231.53 254.825 1244.37 231.771C1254.3 214.06 1255.71 183.416 1247.37 166.642C1241.84 155.302 1231.34 145.369 1217.85 138.341C1203.04 130.75 1192.17 127.845 1148.03 119.786C1114.11 113.601 1100.99 110.321 1090.59 105.448C1075.22 98.2318 1069.03 89.6103 1068.94 75.6472C1068.94 69.2748 1069.31 67.3068 1071.47 62.8087C1080.56 44.2537 1111.39 33.5705 1147.09 36.7567C1163.49 38.1624 1171.27 39.9429 1181.49 44.9097C1195.92 51.7506 1202.95 60.4659 1203.98 72.5547L1204.54 78.0837H1224.03H1243.52L1242.96 72.6484C1239.68 35.1636 1211.38 10.0488 1165.18 3.67637C1154.59 2.27069 1133.04 1.23985 1125.07 1.80213Z"
                fill="white" />
              <path
                d="M1531.74 1.80213C1503.81 3.86379 1482.64 10.0488 1466.89 20.732C1458.18 26.6358 1448.52 37.6938 1444.12 46.5965C1438.31 58.4042 1437.09 64.2144 1437.09 79.958C1437.18 92.328 1437.37 94.2022 1439.72 100.856C1445.06 116.225 1454.52 126.345 1471.67 134.78C1490.32 143.963 1504.19 147.712 1547.02 154.928C1562.67 157.552 1579.07 160.738 1583.56 162.05C1600.24 166.829 1613.36 174.982 1617.58 183.229C1621.89 191.663 1621.8 206.001 1617.39 215.466C1608.87 233.552 1584.31 243.017 1545.98 243.017C1494.72 243.017 1470.36 228.304 1470.36 197.379V190.538H1450.68H1431V193.537C1431 200.191 1433.16 213.872 1435.31 220.526C1438.4 230.272 1443.37 238.331 1451.52 246.953C1466.61 262.884 1486.29 271.787 1515.81 276.004C1520.96 276.753 1533.15 277.222 1546.73 277.222C1565.85 277.128 1571.1 276.847 1581.41 274.973C1615.14 269.069 1638.2 254.825 1651.04 231.771C1660.97 214.06 1662.38 183.416 1654.03 166.642C1648.51 155.302 1638.01 145.369 1624.52 138.341C1609.71 130.75 1598.84 127.845 1554.7 119.786C1520.78 113.601 1507.66 110.321 1497.25 105.448C1481.89 98.2318 1475.7 89.6103 1475.61 75.6472C1475.61 69.2748 1475.98 67.3068 1478.14 62.8087C1487.23 44.2537 1518.06 33.5705 1553.76 36.7567C1570.16 38.1624 1577.94 39.9429 1588.16 44.9097C1602.59 51.7506 1609.62 60.4659 1610.65 72.5547L1611.21 78.0837H1630.7H1650.19L1649.63 72.6484C1646.35 35.1636 1618.05 10.0488 1571.85 3.67637C1561.26 2.27069 1539.71 1.23985 1531.74 1.80213Z"
                fill="white" />
              <path
                fill-rule="evenodd"
                clip-rule="evenodd"
                d="M1235.59 106L1281.35 181.097V278H1320.65V181.891L1431 0H1391.7L1302.39 146.546L1277.79 106H1235.59Z"
                fill="white" />
              <path
                d="M251.148 138.528V273.005H270.828H290.507V224.275V175.544H343.923C389.186 175.544 398.557 175.357 404.649 174.045C431.357 168.516 453.754 150.617 465.374 125.315C471.09 113.132 472.59 105.635 472.59 90.2663C472.59 74.1478 470.903 66.4635 464.343 53.2501C453.379 30.7592 432.575 14.0784 407.085 7.33116C396.964 4.61351 381.502 4.05124 316.091 4.05124H251.148V138.528ZM397.245 42.9417C410.834 46.2217 424.328 56.1551 430.326 67.4006C435.386 76.6781 437.167 90.9223 434.73 101.512C431.825 114.069 421.329 127.47 409.615 133.28C398.838 138.715 396.402 138.997 340.924 138.997H290.507V90.2663V41.5361H340.924C382.626 41.5361 392.279 41.8172 397.245 42.9417Z"
                fill="white" />
              <path
                d="M0 138.997V273.005H107.769H215.538V255.2V237.394H127.448H39.3591V196.629V155.865H113.86H188.361V138.059V120.254H113.86H39.3591V80.4265V40.5989H127.448H215.538V22.7936V4.98834H107.769H0V138.997Z"
                fill="white" />
            </svg>
          </div>
          <nav class="mt-5 flex-1 px-2 space-y-1">
            <router-link
              v-for="item in navigation"
              :key="item.name"
              :to="{ name: item.href }"
              :class="[
                currentRoute === item.href
                  ? 'bg-gray-900 text-white'
                  : 'text-gray-300 hover:bg-gray-700 hover:text-white',
                'group flex items-center px-2 py-2 text-sm font-medium rounded-md'
              ]">
              <component
                :is="item.icon"
                :class="[
                  currentRoute === item.href ? 'text-gray-300' : 'text-gray-400 group-hover:text-gray-300',
                  'mr-3 flex-shrink-0 h-6 w-6'
                ]"
                aria-hidden="true" />
              {{ item.name }}
            </router-link>
          </nav>
        </div>
        <div class="flex-shrink-0">
          <div class="mb-5 flex-1 px-2 space-y-2">
            <a
              v-for="item in bottomNavigation"
              :key="item.name"
              :href="item.href"
              :target="item.blank ? '_blank' : '_self'"
              class="text-gray-300 bg-gray-700 hover:bg-slate-900 hover:text-white group flex items-center px-2 py-2 text-sm font-medium rounded-md">
              <component
                :is="item.icon"
                class="text-gray-300 group-hover:text-gray-300 mr-3 flex-shrink-0 h-6 w-6"
                aria-hidden="true" />
              {{ item.name }}
            </a>
          </div>
          <div class="flex bg-gray-700 p-4">
            <div class="flex-shrink-0 w-full group block">
              <div class="flex items-center">
                <div>
                  <img
                    class="inline-block h-9 w-9 rounded-full"
                    src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80"
                    alt="" />
                </div>
                <div class="ml-3 flex flex-col">
                  <p class="text-sm font-medium text-white">Tom Cook</p>
                  <a
                    href="/logout"
                    class="text-xs font-medium text-gray-300 group-hover:text-gray-200"
                    >Logout</a
                  >
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="md:pl-64 flex flex-col flex-1">
      <div class="sticky top-0 z-20 md:hidden pl-1 pt-1 sm:pl-3 sm:pt-3 bg-gray-800 text-right">
        <div class="flex items-center justify-between">
          <svg
            class="h-5 w-auto"
            viewBox="0 0 1695 278"
            fill="none"
            xmlns="http://www.w3.org/2000/svg">
            <path
              d="M552.901 0.865021C544.186 1.98957 532.94 4.42608 524.787 7.05002C515.885 9.86138 499.204 17.5458 494.893 20.8257L492.644 22.5125L501.359 38.8184C506.139 47.8148 510.075 55.218 510.168 55.3117C510.262 55.4054 512.605 53.9997 515.416 52.1255C540.812 35.0699 581.389 32.5397 610.628 46.1279C635.18 57.6545 654.485 79.958 662.544 106.01C670.041 130.281 668.354 160.738 658.233 183.603C651.955 197.848 637.054 215.653 624.216 224.275C605.567 236.832 582.795 242.267 558.43 240.112C524.319 237.207 494.518 216.59 480.18 186.321C478.681 183.229 477.369 180.511 477.275 180.324C477.088 180.042 443.445 197.66 442.415 198.597C442.227 198.691 443.914 202.158 446.069 206.188C462.469 236.738 491.145 260.541 523.85 270.943C540.062 276.097 547.653 277.128 568.364 277.128C584.107 277.128 588.699 276.753 596.946 274.973C616.813 270.756 634.524 263.165 649.237 252.576C678.756 231.49 697.311 203.564 704.902 168.703C706.682 160.457 706.964 156.333 706.964 138.997C706.964 121.66 706.682 117.537 704.902 109.29C698.811 81.1762 687.003 60.1847 665.637 39.5681C638.741 13.6099 609.784 1.61472 572.112 0.771309C563.397 0.583885 554.682 0.677597 552.901 0.865021Z"
              fill="white" />
            <path
              d="M826.072 1.80213C798.145 3.86379 776.967 10.0488 761.223 20.732C752.508 26.6358 742.855 37.6938 738.451 46.5965C732.641 58.4042 731.422 64.2144 731.422 79.958C731.516 92.328 731.704 94.2022 734.046 100.856C739.388 116.225 748.853 126.345 766.002 134.78C784.651 143.963 798.52 147.712 841.347 154.928C856.997 157.552 873.396 160.738 877.894 162.05C894.575 166.829 907.695 174.982 911.912 183.229C916.223 191.663 916.129 206.001 911.724 215.466C903.197 233.552 878.644 243.017 840.316 243.017C789.055 243.017 764.69 228.304 764.69 197.379V190.538H745.011H725.331V193.537C725.331 200.191 727.487 213.872 729.642 220.526C732.734 230.272 737.701 238.331 745.854 246.953C760.942 262.884 780.621 271.787 810.141 276.004C815.295 276.753 827.477 277.222 841.066 277.222C860.183 277.128 865.431 276.847 875.739 274.973C909.475 269.069 932.528 254.825 945.367 231.771C955.301 214.06 956.706 183.416 948.366 166.642C942.837 155.302 932.341 145.369 918.847 138.341C904.04 130.75 893.169 127.845 849.031 119.786C815.107 113.601 801.988 110.321 791.586 105.448C776.217 98.2318 770.032 89.6103 769.938 75.6472C769.938 69.2748 770.313 67.3068 772.468 62.8087C781.558 44.2537 812.39 33.5705 848.094 36.7567C864.494 38.1624 872.272 39.9429 882.486 44.9097C896.918 51.7506 903.946 60.4659 904.977 72.5547L905.539 78.0837H925.032H944.524L943.961 72.6484C940.681 35.1636 912.38 10.0488 866.18 3.67637C855.591 2.27069 834.037 1.23985 826.072 1.80213Z"
              fill="white" />
            <path
              d="M1125.07 1.80213C1097.15 3.86379 1075.97 10.0488 1060.22 20.732C1051.51 26.6358 1041.86 37.6938 1037.45 46.5965C1031.64 58.4042 1030.42 64.2144 1030.42 79.958C1030.52 92.328 1030.7 94.2022 1033.05 100.856C1038.39 116.225 1047.85 126.345 1065 134.78C1083.65 143.963 1097.52 147.712 1140.35 154.928C1156 157.552 1172.4 160.738 1176.89 162.05C1193.58 166.829 1206.69 174.982 1210.91 183.229C1215.22 191.663 1215.13 206.001 1210.72 215.466C1202.2 233.552 1177.64 243.017 1139.32 243.017C1088.06 243.017 1063.69 228.304 1063.69 197.379V190.538H1044.01H1024.33V193.537C1024.33 200.191 1026.49 213.872 1028.64 220.526C1031.73 230.272 1036.7 238.331 1044.85 246.953C1059.94 262.884 1079.62 271.787 1109.14 276.004C1114.29 276.753 1126.48 277.222 1140.07 277.222C1159.18 277.128 1164.43 276.847 1174.74 274.973C1208.48 269.069 1231.53 254.825 1244.37 231.771C1254.3 214.06 1255.71 183.416 1247.37 166.642C1241.84 155.302 1231.34 145.369 1217.85 138.341C1203.04 130.75 1192.17 127.845 1148.03 119.786C1114.11 113.601 1100.99 110.321 1090.59 105.448C1075.22 98.2318 1069.03 89.6103 1068.94 75.6472C1068.94 69.2748 1069.31 67.3068 1071.47 62.8087C1080.56 44.2537 1111.39 33.5705 1147.09 36.7567C1163.49 38.1624 1171.27 39.9429 1181.49 44.9097C1195.92 51.7506 1202.95 60.4659 1203.98 72.5547L1204.54 78.0837H1224.03H1243.52L1242.96 72.6484C1239.68 35.1636 1211.38 10.0488 1165.18 3.67637C1154.59 2.27069 1133.04 1.23985 1125.07 1.80213Z"
              fill="white" />
            <path
              d="M1531.74 1.80213C1503.81 3.86379 1482.64 10.0488 1466.89 20.732C1458.18 26.6358 1448.52 37.6938 1444.12 46.5965C1438.31 58.4042 1437.09 64.2144 1437.09 79.958C1437.18 92.328 1437.37 94.2022 1439.72 100.856C1445.06 116.225 1454.52 126.345 1471.67 134.78C1490.32 143.963 1504.19 147.712 1547.02 154.928C1562.67 157.552 1579.07 160.738 1583.56 162.05C1600.24 166.829 1613.36 174.982 1617.58 183.229C1621.89 191.663 1621.8 206.001 1617.39 215.466C1608.87 233.552 1584.31 243.017 1545.98 243.017C1494.72 243.017 1470.36 228.304 1470.36 197.379V190.538H1450.68H1431V193.537C1431 200.191 1433.16 213.872 1435.31 220.526C1438.4 230.272 1443.37 238.331 1451.52 246.953C1466.61 262.884 1486.29 271.787 1515.81 276.004C1520.96 276.753 1533.15 277.222 1546.73 277.222C1565.85 277.128 1571.1 276.847 1581.41 274.973C1615.14 269.069 1638.2 254.825 1651.04 231.771C1660.97 214.06 1662.38 183.416 1654.03 166.642C1648.51 155.302 1638.01 145.369 1624.52 138.341C1609.71 130.75 1598.84 127.845 1554.7 119.786C1520.78 113.601 1507.66 110.321 1497.25 105.448C1481.89 98.2318 1475.7 89.6103 1475.61 75.6472C1475.61 69.2748 1475.98 67.3068 1478.14 62.8087C1487.23 44.2537 1518.06 33.5705 1553.76 36.7567C1570.16 38.1624 1577.94 39.9429 1588.16 44.9097C1602.59 51.7506 1609.62 60.4659 1610.65 72.5547L1611.21 78.0837H1630.7H1650.19L1649.63 72.6484C1646.35 35.1636 1618.05 10.0488 1571.85 3.67637C1561.26 2.27069 1539.71 1.23985 1531.74 1.80213Z"
              fill="white" />
            <path
              fill-rule="evenodd"
              clip-rule="evenodd"
              d="M1235.59 106L1281.35 181.097V278H1320.65V181.891L1431 0H1391.7L1302.39 146.546L1277.79 106H1235.59Z"
              fill="white" />
            <path
              d="M251.148 138.528V273.005H270.828H290.507V224.275V175.544H343.923C389.186 175.544 398.557 175.357 404.649 174.045C431.357 168.516 453.754 150.617 465.374 125.315C471.09 113.132 472.59 105.635 472.59 90.2663C472.59 74.1478 470.903 66.4635 464.343 53.2501C453.379 30.7592 432.575 14.0784 407.085 7.33116C396.964 4.61351 381.502 4.05124 316.091 4.05124H251.148V138.528ZM397.245 42.9417C410.834 46.2217 424.328 56.1551 430.326 67.4006C435.386 76.6781 437.167 90.9223 434.73 101.512C431.825 114.069 421.329 127.47 409.615 133.28C398.838 138.715 396.402 138.997 340.924 138.997H290.507V90.2663V41.5361H340.924C382.626 41.5361 392.279 41.8172 397.245 42.9417Z"
              fill="white" />
            <path
              d="M0 138.997V273.005H107.769H215.538V255.2V237.394H127.448H39.3591V196.629V155.865H113.86H188.361V138.059V120.254H113.86H39.3591V80.4265V40.5989H127.448H215.538V22.7936V4.98834H107.769H0V138.997Z"
              fill="white" />
          </svg>
          <div class="flex items-center">
            <div
              class="-mt-0.5 h-12 w-12 inline-flex items-center justify-center rounded-md text-gray-500 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500"
              @click="reloadPage">
              <RefreshIcon
                class="h-6 w-6 text-white"
                aria-hidden="true" />
            </div>
            <button
              type="button"
              class="-ml-0.5 -mt-0.5 h-12 w-12 inline-flex items-center justify-center rounded-md text-gray-500 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500"
              @click="sidebarOpen = true">
              <span class="sr-only">Open sidebar</span>
              <MenuIcon
                class="h-6 w-6 text-white"
                aria-hidden="true" />
            </button>
          </div>
        </div>
      </div>
      <main class="flex-1">
        <div class="py-6">
          <div class="max-w-[90%] flex justify-between items-center mx-4 sm:mx-auto">
            <h1 class="text-2xl font-semibold text-gray-900 capitalize">{{ pageTitle }}</h1>
            <div
              v-if="showButton"
              class="ml-4 mt-2 flex-shrink-0"
              @click="handleAddButton">
              <button
                type="button"
                class="relative inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                {{ buttonName }}
              </button>
            </div>
          </div>
          <div class="max-w-[90%] mx-auto">
            <!-- Replace with your content -->
            <div class="py-4">
              <router-view />
            </div>
            <!-- /End replace -->
          </div>
        </div>
      </main>
    </div>
  </div>
</template>

<script>
import pusher from 'pusher-js'
import { Dialog, DialogPanel, TransitionChild, TransitionRoot } from '@headlessui/vue'
import {
  CubeIcon,
  CogIcon,
  ViewGridIcon,
  FolderIcon,
  HomeIcon,
  MenuIcon,
  XIcon,
  ClipboardListIcon,
  ChartPieIcon,
  CalendarIcon,
  ViewBoardsIcon,
  DesktopComputerIcon,
  BellIcon,
  RefreshIcon,
  WifiIcon,
  UserGroupIcon,
  ClockIcon,
  DocumentTextIcon,
  ArchiveIcon
} from '@heroicons/vue/outline'

const navigation = [
  { name: 'Početna', href: 'overview', icon: HomeIcon, current: true },
  { name: 'Računi', href: 'invoices', icon: ClipboardListIcon, current: false },
  { name: 'Pregled prometa', href: 'reports', icon: ChartPieIcon, current: false },
  { name: 'Artikli', href: 'inventory', icon: CubeIcon, current: false },
  { name: 'Kategorije', href: 'categories', icon: FolderIcon, current: false },
  // { name: 'Tables', href: 'tables', icon: ViewGridIcon, current: false },
  { name: 'Obaveštenja', href: 'tasks', icon: BellIcon, current: false },
  { name: 'Klijenti', href: 'clients', icon: UserGroupIcon, current: false },
  { name: 'Fakture', href: 'bank-invoices', icon: DocumentTextIcon, current: false },
  { name: 'Dolasci', href: 'arrivals', icon: ClockIcon, current: false },
  { name: 'Magacin', href: 'warehouse', icon: ArchiveIcon, current: false },
  // { name: 'Connections Log', href: 'connection-logs', icon: WifiIcon, current: false },
  { name: 'Podešavanja', href: 'settings', icon: CogIcon, current: false }
]

export default {
  components: {
    Dialog,
    DialogPanel,
    TransitionChild,
    TransitionRoot,
    XIcon,
    ArchiveIcon,
    MenuIcon,
    ViewBoardsIcon,
    RefreshIcon,
    DocumentTextIcon
  },
  data: () => ({
    navigation,
    sidebarOpen: false,
    bottomNavigation: [
      { name: 'Konobari aplikacija', href: '/', icon: DesktopComputerIcon, blank: false },
      { name: 'Raspored aplikacija', href: 'http://192.168.200.30:81/dashboard', icon: CalendarIcon, blank: true }
    ]
  }),
  computed: {
    currentRoute() {
      return this.$route.name
    },
    currentPath() {
      return this.$route.path
    },
    pageTitle() {
      return this.navigation.find((item) => item.href === this.currentRoute)?.name ?? ''
    },
    showButton() {
      const paths = ['/inventory', '/categories', '/clients']
      return paths.includes(this.$route.path)
    },
    buttonName() {
      if (this.$route.name === 'inventory') return 'Dodaj artikal'
      if (this.$route.name === 'clients') return 'Dodaj klijenta'
      if (this.$route.name === 'categories') return 'Dodaj kategoriju'
      return 'Dodaj ' + this.$route.name
    }
  },
  mounted() {
    this.pusherInit()
  },
  methods: {
    pusherInit() {
      const pusher = new Pusher(import.meta.env.VITE_PUSHER_APP_KEY, {
        cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER
      })
      pusher.subscribe('broadcasting')
      pusher.bind('tables-update', (data) => {
        this.$store.dispatch('getStats')
        this.$store.dispatch('getActiveTableOrders')
      })
      pusher.bind('notifications', (data) => {
        this.$store.dispatch('getTasks')
      })
    },
    handleAddButton() {
      this.$router.push(this.$route.path + '/new')
    },
    reloadPage() {
      location.reload()
    }
  }
}
</script>

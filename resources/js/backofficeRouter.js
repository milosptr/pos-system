import { createRouter, createWebHashHistory } from 'vue-router'
import Backoffice from './Backoffice.vue'
import BackofficeOverview from './components/Backoffice/BackofficeOverview.vue'
import BackofficeCategories from './components/Backoffice/BackofficeCategories.vue'
import BackofficeInventory from './components/Backoffice/BackofficeInventory.vue'
import BackofficeUsers from './components/Backoffice/BackofficeUsers.vue'
import BackofficeTables from './components/Backoffice/BackofficeTables.vue'
import BackofficeOrders from './components/Backoffice/BackofficeOrders.vue'
import BackofficeInvoices from './components/Backoffice/BackofficeInvoices.vue'

const routes = [
    {
        path: '/',
        component: Backoffice,
        children: [
            {
                path: '/',
                name: 'overview',
                component: BackofficeOverview
            },
            {
                path: '/categories',
                name: 'categories',
                component: BackofficeCategories
            },
            {
                path: '/inventory',
                name: 'inventory',
                component: BackofficeInventory
            },
            {
                path: '/users',
                name: 'users',
                component: BackofficeUsers
            },
            {
                path: '/tables',
                name: 'tables',
                component: BackofficeTables
            },
            {
                path: '/orders',
                name: 'orders',
                component: BackofficeOrders
            },
            {
                path: '/invoices',
                name: 'invoices',
                component: BackofficeInvoices
            }
        ]
    },

]


const backofficeRouter = createRouter({
    history: createWebHashHistory(),
    routes
})

export default backofficeRouter

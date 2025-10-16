import { defineRouter } from '#q-app/wrappers'
import {
  createRouter,
  createMemoryHistory,
  createWebHistory,
  createWebHashHistory,
} from 'vue-router'
import routes from './routes'
import { useAuthStore } from 'src/stores/auth'

/*
 * If not building with SSR mode, you can
 * directly export the Router instantiation;
 *
 * The function below can be async too; either use
 * async/await or return a Promise which resolves
 * with the Router instance.
 */

export default defineRouter(function (/* { store, ssrContext } */) {
  const createHistory = process.env.SERVER
    ? createMemoryHistory
    : process.env.VUE_ROUTER_MODE === 'history'
      ? createWebHistory
      : createWebHashHistory

  const Router = createRouter({
    scrollBehavior: () => ({ left: 0, top: 0 }),
    routes,

    // Leave this as is and make changes in quasar.conf.js instead!
    // quasar.conf.js -> build -> vueRouterMode
    // quasar.conf.js -> build -> publicPath
    history: createHistory(process.env.VUE_ROUTER_BASE),
  })

  // Navigation guards
  Router.beforeEach((to, from, next) => {
    const authStore = useAuthStore()
    const isAuthenticated = authStore.isAuthenticated

    // Check if route requires authentication
    if (to.matched.some(record => record.meta.requiresAuth)) {
      if (!isAuthenticated) {
        // Redirect to login if not authenticated
        next({
          path: '/login',
          query: { redirect: to.fullPath },
        })
      } else {
        next()
      }
    }
    // Check if route requires guest (login/register pages)
    else if (to.matched.some(record => record.meta.requiresGuest)) {
      if (isAuthenticated) {
        // Redirect to dashboard if already authenticated
        next('/app/dashboard')
      } else {
        next()
      }
    }
    else {
      next()
    }
  })

  return Router
})

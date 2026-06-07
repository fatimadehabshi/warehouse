<script setup>
import { ref } from 'vue'

import logoUrl from './assets/anbaryar-logo.svg'

import HomePage from './components/HomePage.vue'
import AboutPage from './components/AboutPage.vue'
import ContactPage from './components/ContactPage.vue'
import ProductsPage from './components/ProductsPage.vue'
import AuthPage from './components/AuthPage.vue'
import StockMovementsPage from './components/StockMovementsPage.vue'

const currentPage = ref('home')
const isLoggedIn = ref(false)
const currentUser = ref(null)

const goToPage = (page) => {
  const protectedPages = ['products', 'stock-movements']
  if (protectedPages.includes(page) && !isLoggedIn.value) {
    currentPage.value = 'auth'
    return
  }

  currentPage.value = page
}

const handleAuthSuccess = (user) => {
  isLoggedIn.value = true
  currentUser.value = user
  currentPage.value = 'home'
}

const logout = async () => {
  try {
    await fetch('/auth/logout', {
      method: 'POST',
      headers: {
        Accept: 'application/json',
        'X-CSRF-TOKEN': document
          .querySelector('meta[name="csrf-token"]')
          ?.getAttribute('content'),
      },
    })
  } catch (error) {
    console.error(error)
  } finally {
    isLoggedIn.value = false
    currentUser.value = null
    currentPage.value = 'home'
  }
}
</script>

<template>
  <div dir="rtl" class="min-h-screen bg-[#F8FAFC] font-[Vazirmatn]">
    <header class="sticky top-0 z-50 border-b border-slate-100 bg-white/90 backdrop-blur">
      <nav class="mx-auto flex max-w-6xl items-center justify-between px-6 py-4">
        <button class="flex items-center gap-3" @click="goToPage('home')">
          <img :src="logoUrl" alt="لوگوی انباریار" class="h-16 w-16 object-contain" />
        </button>

        <div class="hidden items-center gap-6 text-sm font-medium text-slate-600 md:flex">
          <button class="hover:text-[#219EBC]" @click="goToPage('home')">صفحه اصلی</button>

          <button class="hover:text-[#219EBC]" @click="goToPage('products')">
            لیست کالاها
          </button>

          <button class="hover:text-[#219EBC]" @click="goToPage('stock-movements')">
            رویدادهای انبار 
          </button>

          <button class="hover:text-[#219EBC]" @click="goToPage('about')">درباره ما</button>
          <button class="hover:text-[#219EBC]" @click="goToPage('contact')">تماس با ما</button>
        </div>

        <div class="flex items-center gap-3">
          <div v-if="isLoggedIn" class="hidden text-left text-xs text-slate-500 sm:block">
            <p class="font-bold text-[#023047]">{{ currentUser?.name }}</p>
            <p>{{ currentUser?.email }}</p>
          </div>

          <button
            v-if="!isLoggedIn"
            class="rounded-2xl bg-[#023047] px-5 py-2.5 text-sm font-bold text-white transition hover:bg-[#03415f]"
            @click="goToPage('auth')"
          >
            ورود / ثبت‌نام
          </button>

          <button
            v-else
            class="rounded-2xl bg-[#FB8500] px-5 py-2.5 text-sm font-bold text-white transition hover:bg-[#e67600]"
            @click="logout"
          >
            خروج
          </button>
        </div>
      </nav>
    </header>

    <HomePage
      v-if="currentPage === 'home'"
      :go-to-page="goToPage"
      :logo-url="logoUrl"
      :is-logged-in="isLoggedIn"
    />

    <ProductsPage v-else-if="currentPage === 'products' && isLoggedIn" />

    <StockMovementsPage v-else-if="currentPage === 'stock-movements' && isLoggedIn" />

    <section
      v-else-if="currentPage === 'products' && !isLoggedIn"
      dir="rtl"
      class="bg-[#F8FAFC] px-6 py-16"
    >
      <div class="mx-auto max-w-xl rounded-3xl bg-white p-8 text-center shadow-lg">
        <h1 class="mb-4 text-2xl font-bold text-[#023047]">
          دسترسی به لیست کالاها نیازمند ورود است
        </h1>

        <p class="mb-6 leading-7 text-slate-600">
          برای مشاهده کالاهای انبار، ابتدا وارد حساب کاربری خود شوید یا ثبت‌نام کنید.
        </p>

        <button
          class="rounded-2xl bg-[#023047] px-6 py-3 text-sm font-bold text-white"
          @click="goToPage('auth')"
        >
          ورود / ثبت‌نام
        </button>
      </div>
    </section>

    <AboutPage v-else-if="currentPage === 'about'" />

    <ContactPage v-else-if="currentPage === 'contact'" />

    <AuthPage
      v-else-if="currentPage === 'auth'"
      :after-auth="handleAuthSuccess"
    />

    <footer class="border-t border-slate-100 bg-white px-6 py-6 text-center text-sm text-slate-500">
      ©️ 2026 انباریار - سیستم مدیریت انبار
    </footer>
  </div>
</template>
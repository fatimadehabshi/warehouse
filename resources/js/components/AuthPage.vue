<script setup>
import { ref, reactive } from 'vue'
import logoUrl from '../assets/anbaryar-logo.svg'

const props = defineProps({
    afterAuth: Function,
})
const mode = ref('signin')
const loading = ref(false)
const successMessage = ref('')
const errorMessage = ref('')

const form = reactive({
  name: '',
  email: '',
  password: '',
  warehouseName: '',
})

const switchToSignIn = () => {
  mode.value = 'signin'
  successMessage.value = ''
  errorMessage.value = ''
}

const switchToSignUp = () => {
  mode.value = 'signup'
  successMessage.value = ''
  errorMessage.value = ''
}

const getCsrfToken = () => {
  return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
}

const handleSubmit = async () => {
  loading.value = true
  successMessage.value = ''
  errorMessage.value = ''

  const url = mode.value === 'signin' ? '/auth/login' : '/auth/register'

  const payload =
    mode.value === 'signin'
      ? {
          email: form.email,
          password: form.password,
        }
      : {
          name: form.name,
          email: form.email,
          password: form.password,
        }

  try {
    const response = await fetch(url, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        Accept: 'application/json',
        'X-CSRF-TOKEN': getCsrfToken(),
      },
      body: JSON.stringify(payload),
    })

    const text = await response.text()

    let data = {}

    try {
      data = JSON.parse(text)
    } catch {
      console.log('Laravel returned non-JSON response:')
      console.log(text)
      errorMessage.value = 'Laravel خطای غیر JSON برگرداند. Console مرورگر را چک کن.'
      return
    }

    if (!response.ok) {
      if (data.errors) {
        const firstError = Object.values(data.errors)[0][0]
        errorMessage.value = firstError
      } else {
        errorMessage.value = data.message || 'خطایی رخ داد.'
      }

      return
    }

    successMessage.value = data.message
    props.afterAuth?.(data.user)

    if (mode.value === 'signup') {
      form.name = ''
      form.warehouseName = ''
    }

    form.email = ''
    form.password = ''
  } catch (error) {
    console.error(error)
    errorMessage.value = 'ارتباط با سرور برقرار نشد.'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <main dir="rtl" class="min-h-screen bg-[#F8FAFC] font-[Vazirmatn] text-[#023047]">
    <section class="flex min-h-screen items-center justify-center px-6 py-10">
      <div class="grid w-full max-w-6xl overflow-hidden rounded-3xl bg-white shadow-xl lg:grid-cols-2">

        <!-- Brand Side -->
        <div class="hidden bg-[#023047] p-12 text-white lg:flex lg:flex-col lg:justify-between">
          <div>
            <div class="mb-10 flex items-center gap-4">
              <img
                :src="logoUrl"
                alt="لوگوی انباریار"
                class="h-20 w-20 rounded-2xl object-contain"
              />
            </div>

            <h2 class="mb-4 text-4xl font-bold leading-tight">
              مدیریت ساده و دقیق موجودی انبار
            </h2>

            <p class="max-w-md text-base leading-8 text-slate-200">
              با انباریار کالاها، ورود و خروج، موجودی کم و وضعیت انبار را در یک محیط ساده و منظم مدیریت کنید.
            </p>
          </div>
        </div>

        <!-- Form Side -->
        <div class="flex items-center justify-center p-8 sm:p-12">
          <div class="w-full max-w-md">

            <!-- Mobile Logo -->
            <div class="mb-8 flex items-center gap-4 lg:hidden">
              <img
                :src="logoUrl"
                alt="لوگوی انباریار"
                class="h-20 w-20 rounded-2xl object-contain"
              />
            </div>

            <div class="mb-8">
              <p class="mb-2 text-sm font-medium text-[#219EBC]">
                {{ mode === 'signin' ? 'ورود به پنل مدیریت' : 'ساخت حساب جدید' }}
              </p>

              <h2 class="text-3xl font-bold">
                {{ mode === 'signin' ? 'ورود به حساب کاربری' : 'ثبت‌نام در انباریار' }}
              </h2>

              <p class="mt-3 text-sm leading-7 text-slate-500">
                {{
                  mode === 'signin'
                    ? 'برای مدیریت کالاها و موجودی انبار وارد حساب خود شوید.'
                    : 'اطلاعات اولیه را وارد کنید تا حساب کاربری شما ساخته شود.'
                }}
              </p>
            </div>

            <!-- Tabs -->
            <div class="mb-6 grid grid-cols-2 rounded-2xl bg-slate-100 p-1 text-sm font-semibold">
              <button
                type="button"
                class="rounded-xl px-4 py-3 transition"
                :class="mode === 'signin' ? 'bg-white text-[#023047] shadow-sm' : 'text-slate-500'"
                @click="switchToSignIn"
              >
                ورود
              </button>

              <button
                type="button"
                class="rounded-xl px-4 py-3 transition"
                :class="mode === 'signup' ? 'bg-white text-[#023047] shadow-sm' : 'text-slate-500'"
                @click="switchToSignUp"
              >
                ثبت‌نام
              </button>
            </div>
            
            <div v-if="successMessage" class="mb-5 rounded-2xl bg-green-100 px-4 py-3 text-sm font-medium text-green-700">
                {{ successMessage }}
            </div>

            <div v-if="errorMessage" class="mb-5 rounded-2xl bg-red-100 px-4 py-3 text-sm font-medium text-red-700">
                {{ errorMessage }}
            </div>

            <form class="space-y-5" @submit.prevent="handleSubmit">
              <div v-if="mode === 'signup'">
                <label class="mb-2 block text-sm font-medium">نام و نام خانوادگی</label>
                <input
                  v-model="form.name"
                  type="text"
                  placeholder="مثلاً امیر حسینی"
                  class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none transition placeholder:text-slate-400 focus:border-[#219EBC] focus:ring-4 focus:ring-[#8ECAE6]/40"
                />
              </div>

              <div>
                <label class="mb-2 block text-sm font-medium">ایمیل</label>
                <input
                  v-model="form.email"
                  type="email"
                  placeholder="example@email.com"
                  class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none transition placeholder:text-slate-400 focus:border-[#219EBC] focus:ring-4 focus:ring-[#8ECAE6]/40"
                />
              </div>

              <div>
                <div class="mb-2 flex items-center justify-between">
                  <label class="block text-sm font-medium">رمز عبور</label>

                  <a
                    v-if="mode === 'signin'"
                    href="#"
                    class="text-xs font-medium text-[#219EBC] hover:text-[#023047]"
                  >
                    فراموشی رمز؟
                  </a>
                </div>

                <input
                  v-model="form.password"
                  type="password"
                  placeholder="رمز عبور خود را وارد کنید"
                  class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none transition placeholder:text-slate-400 focus:border-[#219EBC] focus:ring-4 focus:ring-[#8ECAE6]/40"
                />
              </div>

              <div v-if="mode === 'signup'">
                <label class="mb-2 block text-sm font-medium">نام انبار / کسب‌وکار</label>
                <input
                  v-model="form.warehouseName"
                  type="text"
                  placeholder="مثلاً انبار مرکزی"
                  class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none transition placeholder:text-slate-400 focus:border-[#219EBC] focus:ring-4 focus:ring-[#8ECAE6]/40"
                />
              </div>

              <button
                type="submit"
                :disabled="loading"
                class="w-full rounded-2xl bg-[#023047] px-5 py-3.5 text-sm font-bold text-white transition hover:bg-[#03415f] focus:outline-none focus:ring-4 focus:ring-[#8ECAE6]/50 disabled:cursor-not-allowed disabled:opacity-60"
                >
                {{
                    loading
                    ? 'در حال پردازش...'
                    : mode === 'signin'
                        ? 'ورود به انباریار'
                        : 'ساخت حساب کاربری'
                }}
              </button>
            </form>

            <p class="mt-6 text-center text-sm text-slate-500">
              {{
                mode === 'signin'
                  ? 'هنوز حساب کاربری ندارید؟'
                  : 'قبلاً ثبت‌نام کرده‌اید؟'
              }}

              <button
                v-if="mode === 'signin'"
                type="button"
                class="font-semibold text-[#219EBC] hover:text-[#023047]"
                @click="switchToSignUp"
              >
                ثبت‌نام کنید
              </button>

              <button
                v-else
                type="button"
                class="font-semibold text-[#219EBC] hover:text-[#023047]"
                @click="switchToSignIn"
              >
                وارد شوید
              </button>
            </p>
          </div>
        </div>
      </div>
    </section>
  </main>
</template>
<script setup>
import { onMounted, reactive, ref } from 'vue'

const products = ref([])
const loading = ref(false)
const saving = ref(false)
const editingProductId = ref(null)
const successMessage = ref('')
const errorMessage = ref('')

const form = reactive({
  code: '',
  name: '',
  category: '',
  quantity: 0,
})

const getCsrfToken = () => {
  return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
}

const resetForm = () => {
  form.code = ''
  form.name = ''
  form.category = ''
  form.quantity = 0
  editingProductId.value = null
}

const showSuccess = (message) => {
  successMessage.value = message
  errorMessage.value = ''
}

const showError = (message) => {
  errorMessage.value = message
  successMessage.value = ''
}

const fetchProducts = async () => {
  loading.value = true

  try {
    const response = await fetch('/products', {
      headers: {
        Accept: 'application/json',
      },
    })

    const data = await response.json()

    if (!response.ok) {
      showError(data.message || 'خطا در دریافت لیست کالاها.')
      return
    }

    products.value = data.products
  } catch (error) {
    console.error(error)
    showError('ارتباط با سرور برقرار نشد.')
  } finally {
    loading.value = false
  }
}

const handleSubmit = async () => {
  saving.value = true
  successMessage.value = ''
  errorMessage.value = ''

  const isEditing = editingProductId.value !== null
  const url = isEditing ? `/products/${editingProductId.value}` : '/products'
  const method = isEditing ? 'PUT' : 'POST'

  try {
    const response = await fetch(url, {
      method,
      headers: {
        'Content-Type': 'application/json',
        Accept: 'application/json',
        'X-CSRF-TOKEN': getCsrfToken(),
      },
      body: JSON.stringify({
        code: form.code,
        name: form.name,
        category: form.category,
        quantity: Number(form.quantity),
      }),
    })

    const data = await response.json()

    if (!response.ok) {
      if (data.errors) {
        const firstError = Object.values(data.errors)[0][0]
        showError(firstError)
      } else {
        showError(data.message || 'خطایی رخ داد.')
      }

      return
    }

    showSuccess(data.message)
    resetForm()
    await fetchProducts()
  } catch (error) {
    console.error(error)
    showError('ارتباط با سرور برقرار نشد.')
  } finally {
    saving.value = false
  }
}

const startEdit = (product) => {
  editingProductId.value = product.id
  form.code = product.code
  form.name = product.name
  form.category = product.category || ''
  form.quantity = product.quantity
  successMessage.value = ''
  errorMessage.value = ''
}

const deleteProduct = async (product) => {
  const confirmed = confirm(`آیا از حذف کالای «${product.name}» مطمئن هستید؟`)

  if (!confirmed) {
    return
  }

  try {
    const response = await fetch(`/products/${product.id}`, {
      method: 'DELETE',
      headers: {
        Accept: 'application/json',
        'X-CSRF-TOKEN': getCsrfToken(),
      },
    })

    const data = await response.json()

    if (!response.ok) {
      showError(data.message || 'خطا در حذف کالا.')
      return
    }

    showSuccess(data.message)
    await fetchProducts()
  } catch (error) {
    console.error(error)
    showError('ارتباط با سرور برقرار نشد.')
  }
}

const statusText = (quantity) => {
  if (quantity === 0) {
    return 'ناموجود'
  }

  if (quantity <= 5) {
    return 'موجودی کم'
  }

  return 'موجود'
}

const statusClass = (quantity) => {
  if (quantity === 0) {
    return 'bg-red-100 text-red-700'
  }

  if (quantity <= 5) {
    return 'bg-[#FFF4D6] text-[#FB8500]'
  }

  return 'bg-green-100 text-green-700'
}

onMounted(() => {
  fetchProducts()
})
</script>

<template>
  <section dir="rtl" class="bg-[#F8FAFC] px-6 py-12">
    <div class="mx-auto max-w-6xl">
      <div class="mb-8">
        <p class="mb-2 text-sm font-bold text-[#219EBC]">مدیریت کالاها</p>
        <h1 class="text-3xl font-bold text-[#023047]">لیست کالاهای انبار</h1>
        <p class="mt-3 text-sm text-slate-500">
          در این صفحه می‌توانید کالاهای واقعی ذخیره‌شده در دیتابیس را مشاهده، اضافه، ویرایش و حذف کنید.
        </p>
      </div>

      <div v-if="successMessage" class="mb-5 rounded-2xl bg-green-100 px-4 py-3 text-sm font-medium text-green-700">
        {{ successMessage }}
      </div>

      <div v-if="errorMessage" class="mb-5 rounded-2xl bg-red-100 px-4 py-3 text-sm font-medium text-red-700">
        {{ errorMessage }}
      </div>

      <div class="mb-8 rounded-3xl bg-white p-6 shadow-lg">
        <div class="mb-5 flex items-center justify-between">
          <h2 class="text-xl font-bold text-[#023047]">
            {{ editingProductId ? 'ویرایش کالا' : 'افزودن کالای جدید' }}
          </h2>

          <button
            v-if="editingProductId"
            type="button"
            class="rounded-xl bg-slate-100 px-4 py-2 text-sm font-bold text-slate-600"
            @click="resetForm"
          >
            لغو ویرایش
          </button>
        </div>

        <form class="grid gap-4 md:grid-cols-4" @submit.prevent="handleSubmit">
          <div>
            <label class="mb-2 block text-sm font-medium text-[#023047]">کد کالا</label>
            <input
              v-model="form.code"
              type="text"
              placeholder="مثلاً PRD-001"
              class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm outline-none focus:border-[#219EBC] focus:ring-4 focus:ring-[#8ECAE6]/40"
            />
          </div>

          <div>
            <label class="mb-2 block text-sm font-medium text-[#023047]">نام کالا</label>
            <input
              v-model="form.name"
              type="text"
              placeholder="مثلاً لپ‌تاپ"
              class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm outline-none focus:border-[#219EBC] focus:ring-4 focus:ring-[#8ECAE6]/40"
            />
          </div>

          <div>
            <label class="mb-2 block text-sm font-medium text-[#023047]">دسته‌بندی</label>
            <input
              v-model="form.category"
              type="text"
              placeholder="مثلاً الکترونیک"
              class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm outline-none focus:border-[#219EBC] focus:ring-4 focus:ring-[#8ECAE6]/40"
            />
          </div>

          <div>
            <label class="mb-2 block text-sm font-medium text-[#023047]">موجودی</label>
            <input
              v-model="form.quantity"
              type="number"
              min="0"
              class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm outline-none focus:border-[#219EBC] focus:ring-4 focus:ring-[#8ECAE6]/40"
            />
          </div>

          <div class="md:col-span-4">
            <button
              type="submit"
              :disabled="saving"
              class="rounded-2xl bg-[#023047] px-6 py-3 text-sm font-bold text-white transition hover:bg-[#03415f] disabled:cursor-not-allowed disabled:opacity-60"
            >
              {{
                saving
                  ? 'در حال ذخیره...'
                  : editingProductId
                    ? 'ذخیره تغییرات'
                    : 'افزودن کالا'
              }}
            </button>
          </div>
        </form>
      </div>

      <div class="overflow-hidden rounded-3xl bg-white shadow-lg">
        <div v-if="loading" class="p-8 text-center text-sm text-slate-500">
          در حال دریافت لیست کالاها...
        </div>

        <div v-else-if="products.length === 0" class="p-8 text-center">
          <h3 class="mb-2 text-lg font-bold text-[#023047]">هنوز کالایی ثبت نشده است</h3>
          <p class="text-sm text-slate-500">از فرم بالا اولین کالا را به انبار اضافه کنید.</p>
        </div>

        <div v-else class="overflow-x-auto">
          <table class="w-full min-w-[900px] text-right">
            <thead class="bg-slate-50 text-sm text-slate-500">
              <tr>
                <th class="px-6 py-4">کد کالا</th>
                <th class="px-6 py-4">نام کالا</th>
                <th class="px-6 py-4">دسته‌بندی</th>
                <th class="px-6 py-4">موجودی</th>
                <th class="px-6 py-4">وضعیت</th>
                <th class="px-6 py-4">عملیات</th>
              </tr>
            </thead>

            <tbody class="divide-y divide-slate-100 text-sm">
              <tr v-for="product in products" :key="product.id" class="hover:bg-slate-50">
                <td class="px-6 py-4 font-medium text-[#023047]">{{ product.code }}</td>
                <td class="px-6 py-4 text-slate-700">{{ product.name }}</td>
                <td class="px-6 py-4 text-slate-600">{{ product.category || '-' }}</td>
                <td class="px-6 py-4 text-slate-700">{{ product.quantity }}</td>
                <td class="px-6 py-4">
                  <span class="rounded-full px-3 py-1 text-xs font-bold" :class="statusClass(product.quantity)">
                    {{ statusText(product.quantity) }}
                  </span>
                </td>
                <td class="px-6 py-4">
                  <div class="flex gap-2">
                    <button
                      class="rounded-xl bg-[#8ECAE6]/40 px-3 py-2 text-xs font-bold text-[#023047]"
                      @click="startEdit(product)"
                    >
                      ویرایش
                    </button>

                    <button
                      class="rounded-xl bg-red-100 px-3 py-2 text-xs font-bold text-red-700"
                      @click="deleteProduct(product)"
                    >
                      حذف
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </section>
</template>
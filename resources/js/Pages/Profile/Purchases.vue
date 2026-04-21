<template>

    <Head title="Mis compras" />

    <Header />
    <section class="max-w-6xl mx-auto h-screen mt-24 p-4 md:p-8">
        <el-breadcrumb separator="/">
            <el-breadcrumb-item :to="{ path: '/' }">Inicio</el-breadcrumb-item>
            <el-breadcrumb-item>Mis compras</el-breadcrumb-item>
        </el-breadcrumb>
        <section class="mx-auto mt-10">
            <h1 class="text-2xl md:text-3xl font-bold mb-6">Mis compras</h1>

            <!-- Empty state -->
            <div v-if="rows.length === 0" class="rounded-xl border border-dashed p-10 text-center text-slate-600">
                <p class="text-lg">Aún no tienes compras registradas.</p>
                <p class="text-sm mt-1">Cuando completes una reserva, aparecerá aquí.</p>
            </div>

            <!-- Table -->
            <div v-else class="overflow-x-auto rounded-xl border">
                <table class="min-w-[800px] w-full text-left text-sm">
                    <thead class="bg-slate-50 text-slate-600">
                        <tr class="[&>th]:px-4 [&>th]:py-3">
                            <th>Fecha</th>
                            <th>Hotel</th>
                            <th>Folio proveedor</th>
                            <th>Habitación</th>
                            <th>Fechas</th>
                            <th>N/R/A</th>
                            <th>Total</th>
                            <th>Estado</th>
                            <th>Comprobante</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <tr v-for="r in rows" :key="r.id" class="[&>td]:px-4 [&>td]:py-3">
                            <td>{{ fmtDate(r.created_at) }}</td>
                            <td>{{ hotelName(r.hotel_code) }}</td>
                            <td class="font-mono">{{ r.provider_folio || '-' }}</td>
                            <td><span class="font-medium">{{ r.room_type_code }}</span></td>
                            <td>{{ fmtDate(r.checkin) }} → {{ fmtDate(r.checkout) }}</td>
                            <td>{{ r.nights }} / {{ r.rooms }} / {{ r.adults }}</td>
                            <td class="font-semibold">{{ formatMXN(r.amount_cents, r.currency) }}</td>
                            <td>
                                <span
                                    class="inline-flex items-center gap-1 rounded-full px-2 py-1 text-xs font-medium ring-1"
                                    :class="statusBadgeClass(r.status)">
                                    {{ r.status }}
                                </span>
                            </td>
                            <td>
                                <a v-if="r.stripe_checkout_url" :href="r.stripe_checkout_url" target="_blank"
                                    rel="noopener" class="text-blue-600 hover:underline">Ver checkout</a>
                                <span v-else class="text-slate-400">—</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="purchases?.links?.length" class="flex flex-wrap items-center gap-2 mt-6">
                <Link v-for="link in purchases.links" :key="(link.url || '') + link.label" preserve-state
                    preserve-scroll :href="link.url || '#'" class="px-3 py-1 rounded border text-sm" :class="[
                        link.active ? 'bg-slate-900 text-white border-slate-900' : 'bg-white text-slate-700 hover:bg-slate-50',
                        !link.url ? 'pointer-events-none opacity-50' : ''
                    ]" v-html="link.label" />
            </div>
        </section>
    </section>

    <Footer />
</template>

<script>
import Footer from '@/Components/Footer.vue';
import Header from '@/Components/Header.vue';
import WhatsappButton from '@/Components/WhatsappButton.vue';
import { Head, Link } from '@inertiajs/vue3'

export default {
    name: 'Purchases',
    components: { Header, Head, Link, WhatsappButton, Footer },
    props: {
        purchases: { type: Object, required: true } // Eloquent pagination (data, links, etc.)
    },
    computed: {
        rows() {
            return this.purchases?.data ?? []
        }
    },
    methods: {
        formatMXN(cents, currency = 'MXN') {
            const amount = (Number(cents || 0) / 100)
            try {
                return new Intl.NumberFormat('es-MX', {
                    style: 'currency',
                    currency: (currency || 'MXN').toUpperCase(),
                }).format(amount)
            } catch {
                return `$${amount.toFixed(2)} ${currency || 'MXN'}`
            }
        },
        fmtDate(d) {
            if (!d) return '-'
            const dt = new Date(d)
            return isNaN(dt) ? d : dt.toLocaleDateString('es-MX')
        },
        hotelName(hotelCode) {
            const names = {
                torreon: 'Nuve Torreón',
                gomez: 'Nuve Gomez',
            }

            return names[hotelCode] || hotelCode || 'Hotel'
        },
        statusBadgeClass(status) {
            switch ((status || '').toLowerCase()) {
                case 'paid': return 'bg-green-100 text-green-700 ring-green-600/20'
                case 'awaiting_payment': return 'bg-amber-100 text-amber-700 ring-amber-600/20'
                case 'expired': return 'bg-gray-100 text-gray-700 ring-gray-500/20'
                case 'cancelled': return 'bg-rose-100 text-rose-700 ring-rose-600/20'
                case 'failed': return 'bg-red-100 text-red-700 ring-red-600/20'
                default: return 'bg-slate-100 text-slate-700 ring-slate-600/20'
            }
        },
    }
}
</script>

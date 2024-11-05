<script setup>
    import { Link } from '@inertiajs/vue3';
    defineProps({
        informations: Array
    })
</script>
<template>
    <div v-if="informations.links.length > 3">
        <div class="flex justify-between items-center px-4 py-3">
            <div class="text-sm text-slate-500">
                {{ informations.total }}件中　<b>{{ informations.from }}～{{ informations.to }}</b>　件を表示
            </div>
            <div class="flex space-x-1">
                <template v-for="(link, index) in informations.links" :key="index">
                    <div v-if="link.url === null"
                         class="mr-1 mb-1 px-4 py-3 text-sm leading-4 text-gray-400 border rounded"
                         v-html="link.label" />
                    <Link v-else
                          class="mr-1 mb-1 px-4 py-3 text-sm leading-4 border rounded hover:bg-white focus:border-indigo-500 focus:text-indigo-500"
                          :class="{ 'bg-blue-700 text-white': link.active }" :href="link.url" v-html="link.label" />
                </template>
            </div>
        </div>
    </div>
</template>

<script setup>
    import { createApp } from 'vue';
    import App from "../App.vue";
    import { router } from '@inertiajs/vue3';
    import { ref, onMounted, reactive } from 'vue';
    import { Head, Link } from '@inertiajs/vue3';
    import axios from "axios";
    import $ from 'jquery';

    import DatePicker from 'primevue/datepicker';
    import Button from 'primevue/button';
    import InputText from 'primevue/inputtext';
    import Select from 'primevue/select';
    import Fluid from 'primevue/fluid';

    import DataTable from 'primevue/datatable';
    import Column from 'primevue/column';
    import ColumnGroup from 'primevue/columngroup'; // optional
    import Row from 'primevue/row'; // optional
    import { useToast } from "primevue/usetoast";

    onMounted(() => {
        tableRequest()
    })

    let informations = ref(null)
    const loadingDatatable = ref(false)
    let totalRecords = ref(0)
    let rows = ref(100)
    let from = ref(0)
    let to = ref(0)
    let total = ref(0)
    let sortField = ref('')
    let sortOrder = ref(null)
    let expandedRows = reactive({
        value : null
    })

    let messages = reactive({
        createForm: {
            information_title: { show: false, severity: '', content: '' },
            keisai_ymd: { show: false, severity: '', content: '' },
            enable_start_ymd: { show: false, severity: '', content: '' },
            enable_end_ymd: { show: false, severity: '', content: '' },
            information_naiyo: { show: false, severity: '', content: '' },
        },
        editForm: {
            information_title: { show: false, severity: '', content: '' },
            keisai_ymd: { show: false, severity: '', content: '' },
            enable_start_ymd: { show: false, severity: '', content: '' },
            enable_end_ymd: { show: false, severity: '', content: '' },
            information_naiyo: { show: false, severity: '', content: '' },
        },
    })
    const addErrorMessages = (form, field, msg) => {
        messages[form][field] = {
            show: true, severity: 'error', content: msg
        };
    };
    const removeMessages = (form, field) => {
        messages[form][field] = {
            show: false, severity: '', content: ''
        };
    };

    const fieldWithMessage = [
        'information_title',
        'keisai_ymd',
        'enable_start_ymd',
        'enable_end_ymd',
        'information_naiyo'
    ]

    const toast = useToast();

    const visibleModal = reactive({
        createForm: false,
        editForm: false,
        deleteConfirmation: false,
        loadingScreen: false,
    })

    const kbn_search_select = [
        {'name': 'ー', 'value': 2},
        {'name': '重要', 'value': 0},
        {'name': '情報', 'value': 1},
    ]

    const perPageOptions = [
        100,
        50,
        25,
        10,
    ]

    const axiosSearchForm = reactive({
        information_title : null,
        information_kbn : 2,
        keisai_ymd : null,
        enable_start_ymd : null,
        enable_end_ymd : null,
    })

    const axiosSearchParams = reactive({
        information_title : null,
        information_kbn : 2,
        keisai_ymd : null,
        enable_start_ymd : null,
        enable_end_ymd : null,
        page: 0,
        sortField: sortField,
        sortOrder: sortOrder,
        rows: rows
    })

    const searchInformation = () => {
        if(axiosSearchForm.enable_start_ymd && axiosSearchForm.enable_end_ymd && axiosSearchForm.enable_start_ymd > axiosSearchForm.enable_end_ymd)
            toast.add({
                severity: 'error', summary: 'エラー', detail: '適用機開始日は終了日より後に設定できません', life: 3000
            })
        else{
            axiosSearchParams.information_title = axiosSearchForm.information_title
            axiosSearchParams.information_kbn = axiosSearchForm.information_kbn
            axiosSearchParams.keisai_ymd = axiosSearchForm.keisai_ymd
            axiosSearchParams.enable_start_ymd = axiosSearchForm.enable_start_ymd
            axiosSearchParams.enable_end_ymd = axiosSearchForm.enable_end_ymd
            tableRequest()
        }
    }

    const kbn_form = [
        {'name': '重要', 'value': 0},
        {'name': '情報', 'value': 1},
    ]

    const axiosCreateParams = reactive({
        information_title : null,
        information_kbn : 0,
        keisai_ymd : null,
        enable_start_ymd : null,
        enable_end_ymd : null,
        information_naiyo: null
    })

    const validationCreateForm = reactive({
        information_title: true,
        keisai_ymd: true,
        enable_start_ymd: true,
        enable_end_ymd: true,
        information_naiyo: true
    })

    const showCreateInformationForm = () => {
        axiosCreateParams.information_title = null;
        axiosCreateParams.information_kbn = 0;
        axiosCreateParams.keisai_ymd = null;
        axiosCreateParams.enable_start_ymd = null;
        axiosCreateParams.enable_end_ymd = null;
        axiosCreateParams.information_naiyo = null;

        validationCreateForm.information_title = true
        validationCreateForm.keisai_ymd = true
        validationCreateForm.enable_start_ymd = true
        validationCreateForm.enable_end_ymd = true
        validationCreateForm.information_naiyo = true

        fieldWithMessage.forEach((value) => {
            removeMessages('createForm', value)
        })

        visibleModal.createForm = true
    }

    const createInformation = () => {
        let validated = true

        if(!axiosCreateParams.information_title) {
            validated = false
            validationCreateForm.information_title = false
            addErrorMessages('createForm', 'information_title', 'フィールドが必須です。')
        } else {
            validationCreateForm.information_title = true
            removeMessages('createForm', 'information_title')
        }

        if(!axiosCreateParams.keisai_ymd) {
            validated = false
            validationCreateForm.keisai_ymd = false
            addErrorMessages('createForm', 'keisai_ymd', 'フィールドが必須です。')
        } else if(axiosCreateParams.keisai_ymd > axiosCreateParams.enable_start_ymd) {
            validated = false
            validationCreateForm.keisai_ymd = false
            addErrorMessages('createForm', 'keisai_ymd', '掲載日は適用期間より後に設定出来ません。')
        } else {
            validationCreateForm.keisai_ymd = true
            removeMessages('createForm', 'keisai_ymd')
        }

        if(!axiosCreateParams.enable_start_ymd) {
            validated = false
            validationCreateForm.enable_start_ymd = false
            addErrorMessages('createForm', 'enable_start_ymd', 'フィールドが必須です。')
        } else if(axiosCreateParams.enable_start_ymd < axiosCreateParams.keisai_ymd) {
            validated = false
            validationCreateForm.enable_start_ymd = false
            addErrorMessages('createForm', 'enable_start_ymd', '適用期間開始日は掲載日より前に設定出来ません。')
        } else if(axiosCreateParams.enable_start_ymd > axiosCreateParams.enable_end_ymd) {
            validated = false
            validationCreateForm.enable_start_ymd = false
            addErrorMessages('createForm', 'enable_start_ymd', '適用期間開始日は適用期間終了日より後に設定出来ません。')
        } else {
            validationCreateForm.enable_start_ymd = true
            removeMessages('createForm', 'enable_start_ymd')
        }

        if(!axiosCreateParams.enable_end_ymd) {
            validated = false
            validationCreateForm.enable_end_ymd = false
            addErrorMessages('createForm', 'enable_end_ymd', 'フィールドが必須です。')
        } else if(axiosCreateParams.enable_end_ymd < axiosCreateParams.enable_start_ymd) {
            validated = false
            validationCreateForm.enable_end_ymd = false
            addErrorMessages('createForm', 'enable_end_ymd', '適用期間終了日は適用期間開始日より前に設定出来ません。')
        } else {
            validationCreateForm.enable_end_ymd = true
            removeMessages('createForm', 'enable_end_ymd')
        }

        if(!axiosCreateParams.information_naiyo) {
            validated = false
            validationCreateForm.information_naiyo = false
            addErrorMessages('createForm', 'information_naiyo', 'フィールドが必須です。')
        } else {
            validationCreateForm.information_naiyo = true
            removeMessages('createForm', 'information_naiyo')
        }

        if(validated){
            buttonLoading.createForm = true
            axiosCreateParams.keisai_ymd = new Date(axiosCreateParams.keisai_ymd.getTime() - axiosCreateParams.keisai_ymd.getTimezoneOffset() * 60000);
            axiosCreateParams.enable_start_ymd = new Date(axiosCreateParams.enable_start_ymd.getTime() - axiosCreateParams.enable_start_ymd.getTimezoneOffset() * 60000);
            axiosCreateParams.enable_end_ymd = new Date(axiosCreateParams.enable_end_ymd.getTime() - axiosCreateParams.enable_end_ymd.getTimezoneOffset() * 60000);
            axios.post('/api/information', {
                params: axiosCreateParams
            }).then(res => {
                buttonLoading.createForm = false;
                if(res.data.status == 'error'){
                    toast.add({
                        severity: 'error', summary: 'エラー', detail: res.data.message, life: 3000
                    })
                } else if(res.data.status == 'success') {
                    axiosCreateParams.information_title = null;
                    axiosCreateParams.information_kbn = 0;
                    axiosCreateParams.keisai_ymd = null;
                    axiosCreateParams.enable_start_ymd = null;
                    axiosCreateParams.enable_end_ymd = null;
                    axiosCreateParams.information_naiyo = null;
                    visibleModal.createForm = false;
                    toast.add({
                        severity: 'info', summary: '情報', detail: res.data.message, life: 3000
                    })
                    tableRequest()
                }
            })
        }
    }

    const axiosEditParams = reactive({
        information_id : null,
        information_title : null,
        information_kbn : 0,
        keisai_ymd : null,
        enable_start_ymd : null,
        enable_end_ymd : null,
        information_naiyo: null
    })

    const validationEditForm = reactive({
        information_title: true,
        keisai_ymd: true,
        enable_start_ymd: true,
        enable_end_ymd: true,
        information_naiyo: true
    })

    const showEditInformationForm = () => {
        axiosEditParams.information_id = selectedInformation.value.information_id
        axiosEditParams.information_title = selectedInformation.value.information_title
        axiosEditParams.information_kbn = parseInt(selectedInformation.value.information_kbn)
        axiosEditParams.keisai_ymd = new Date(selectedInformation.value.keisai_ymd)
        axiosEditParams.enable_start_ymd = new Date(selectedInformation.value.enable_start_ymd)
        axiosEditParams.enable_end_ymd = new Date(selectedInformation.value.enable_end_ymd)
        axiosEditParams.information_naiyo = selectedInformation.value.information_naiyo

        validationEditForm.information_title = true
        validationEditForm.keisai_ymd = true
        validationEditForm.enable_start_ymd = true
        validationEditForm.enable_end_ymd = true
        validationEditForm.information_naiyo = true

        fieldWithMessage.forEach((value) => {
            removeMessages('editForm', value)
        })

        visibleModal.editForm = true
    }

    const editInformation = () => {
        let validated = true

        if(!axiosEditParams.information_title) {
            validated = false
            validationEditForm.information_title = false
            addErrorMessages('editForm', 'information_title', 'フィールドが必須です。')
        } else {
            validationEditForm.information_title = true
            removeMessages('editForm', 'information_title')
        }

        if(!axiosEditParams.keisai_ymd) {
            validated = false
            validationEditForm.keisai_ymd = false
            addErrorMessages('editForm', 'keisai_ymd', 'フィールドが必須です。')
        } else if(axiosEditParams.keisai_ymd > axiosEditParams.enable_start_ymd) {
            validated = false
            validationEditForm.keisai_ymd = false
            addErrorMessages('editForm', 'keisai_ymd', '掲載日は適用期間より後に設定出来ません。')
        } else {
            validationEditForm.keisai_ymd = true
            removeMessages('editForm', 'keisai_ymd')
        }

        if(!axiosEditParams.enable_start_ymd) {
            validated = false
            validationEditForm.enable_start_ymd = false
            addErrorMessages('editForm', 'enable_start_ymd', 'フィールドが必須です。')
        } else if(axiosEditParams.enable_start_ymd < axiosEditParams.keisai_ymd) {
            validated = false
            validationEditForm.enable_start_ymd = false
            addErrorMessages('editForm', 'enable_start_ymd', '適用期間開始日は掲載日より前に設定出来ません。')
        } else if(axiosEditParams.enable_start_ymd > axiosEditParams.enable_end_ymd) {
            validated = false
            validationEditForm.enable_start_ymd = false
            addErrorMessages('editForm', 'enable_start_ymd', '適用期間開始日は適用期間終了日より後に設定出来ません。')
        } else {
            validationEditForm.enable_start_ymd = true
            removeMessages('editForm', 'enable_start_ymd')
        }

        if(!axiosEditParams.enable_end_ymd) {
            validated = false
            validationEditForm.enable_end_ymd = false
            addErrorMessages('editForm', 'enable_end_ymd', 'フィールドが必須です。')
        } else if(axiosEditParams.enable_end_ymd < axiosEditParams.enable_start_ymd) {
            validated = false
            validationEditForm.enable_end_ymd = false
            addErrorMessages('editForm', 'enable_end_ymd', '適用期間終了日は適用期間開始日より前に設定出来ません。')
        } else {
            validationEditForm.enable_end_ymd = true
            removeMessages('editForm', 'enable_end_ymd')
        }

        if(!axiosEditParams.information_naiyo) {
            validated = false
            validationEditForm.information_naiyo = false
            addErrorMessages('editForm', 'information_naiyo', 'フィールドが必須です。')
        } else {
            validationEditForm.information_naiyo = true
            removeMessages('editForm', 'information_naiyo')
        }

        if(validated){
            buttonLoading.editForm = true
            axiosEditParams.keisai_ymd = new Date(axiosEditParams.keisai_ymd.getTime() - axiosEditParams.keisai_ymd.getTimezoneOffset() * 60000);
            axiosEditParams.enable_start_ymd = new Date(axiosEditParams.enable_start_ymd.getTime() - axiosEditParams.enable_start_ymd.getTimezoneOffset() * 60000);
            axiosEditParams.enable_end_ymd = new Date(axiosEditParams.enable_end_ymd.getTime() - axiosEditParams.enable_end_ymd.getTimezoneOffset() * 60000);
            axios.put('/api/information', {
                params: axiosEditParams
            }).then(res => {
                buttonLoading.editForm = false;
                if(res.data.status == 'error'){
                    toast.add({
                        severity: 'error', summary: 'エラー', detail: res.data.message, life: 3000
                    })
                } else if(res.data.status == 'success') {
                    axiosEditParams.information_id = null;
                    axiosEditParams.information_title = null;
                    axiosEditParams.information_kbn = 0;
                    axiosEditParams.keisai_ymd = null;
                    axiosEditParams.enable_start_ymd = null;
                    axiosEditParams.enable_end_ymd = null;
                    axiosEditParams.information_naiyo = null;
                    visibleModal.editForm = false;
                    toast.add({
                        severity: 'info', summary: '情報', detail: res.data.message, life: 3000
                    })
                    tableRequest()
                }
            })
        }
    }

    const axiosDeleteParams = reactive({
        information_id: null
    })

    const showDeleteInformationConfirmation = () => {
        visibleModal.deleteConfirmation = true
        axiosDeleteParams.information_id = selectedInformation.value.information_id
    }

    const deleteInformation = () => {
        buttonLoading.deleteConfirmation = true;
        axios.delete('/api/information', {
            params: axiosDeleteParams
        }).then(res => {
            buttonLoading.deleteConfirmation = false;
            if(res.data.status == 'error'){
                toast.add({
                    severity: 'error', summary: 'エラー', detail: res.data.message, life: 3000
                })
            } else if(res.data.status == 'success') {
                axiosDeleteParams.information_id = null;
                visibleModal.deleteConfirmation = false;
                toast.add({
                    severity: 'info', summary: '情報', detail: res.data.message, life: 3000
                })
                tableRequest()
            }
        })
    }

    const buttonLoading = reactive({
        createForm : false,
        editForm : false,
        deleteConfirmation : false,
    })

    const onPage = (event) => {
        axiosSearchParams.rows = event.rows
        axiosSearchParams.page = event.page+1
        tableRequest()
    };

    const onSort = (event) => {
        axiosSearchParams.page = 1

        sortField = event.sortField === 'information_kbn_txt' ? 'information_kbn' : event.sortField
        axiosSearchParams.sortField = sortField

        sortOrder = event.sortOrder
        axiosSearchParams.sortOrder = sortOrder

        tableRequest()
    };

    const tableRequest = () => {
        loadingDatatable.value = true;
        selectedInformation.value = null;

        axios.get('/api/information', {
            params: axiosSearchParams
        }).then(res => {
            loadingDatatable.value = false;
            expandedRows.value = null;
            informations = res.data.data
            totalRecords = res.data.total
            rows = res.data.per_page
            from = res.data.from
            to = res.data.to
            total = res.data.total
        })
    }

    const normalizeKeisaiYmd = () => {
        if(axiosSearchForm.keisai_ymd)
            axiosSearchForm.keisai_ymd = new Date(axiosSearchForm.keisai_ymd.getTime() - axiosSearchForm.keisai_ymd.getTimezoneOffset() * 60000);
    }

    const normalizeEnableStart = () => {
        if (axiosSearchForm.enable_start_ymd)
            axiosSearchForm.enable_start_ymd = new Date(axiosSearchForm.enable_start_ymd.getTime() - axiosSearchForm.enable_start_ymd.getTimezoneOffset() * 60000);
    }

    const normalizeEnableEnd = () => {
        if (axiosSearchForm.enable_end_ymd)
            axiosSearchForm.enable_end_ymd = new Date(axiosSearchForm.enable_end_ymd.getTime() - axiosSearchForm.enable_end_ymd.getTimezoneOffset() * 60000);
    }

    const resetSearch = () => {
        axiosSearchParams.information_title = ''
        axiosSearchParams.information_kbn = 2
        axiosSearchParams.keisai_ymd = ''
        axiosSearchParams.enable_start_ymd = ''
        axiosSearchParams.enable_end_ymd = ''

        axiosSearchForm.information_title = ''
        axiosSearchForm.information_kbn = 2
        axiosSearchForm.keisai_ymd = ''
        axiosSearchForm.enable_start_ymd = ''
        axiosSearchForm.enable_end_ymd = ''

        tableRequest()
    }

    let selectedInformation = reactive({
        value: null
    })

    const onRowExpand = (event) => {
        console.log(event)
    }
</script>

<template>
    <Toast />
    <Head title="顧客一覧" />
    <div id="app">
        <div class="mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <section class="text-gray-600 body-font">
                    <div class="container px-5 py-8 mx-auto">
                        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                            お知らせ一覧
                        </h2>
                        <div class="flex-col pl-4 my-4 w-full mx-auto grid grid-cols-4">
                            <div class="grid grid-cols-2 pb-3">
                                <label for="information_title" class="pl-4 my-auto font-bold">お知らせタイトル</label>
                                <InputText v-model="axiosSearchForm.information_title" class="my-auto"/>
                            </div>
                            <div class="grid grid-cols-2 pb-3">
                                <label for="information_title" class="pl-4 my-auto font-bold">お知らせ区分</label>
                                <Select v-model="axiosSearchForm.information_kbn" :options="kbn_search_select" class="my-auto" optionLabel="name" optionValue="value"/>
                            </div>
                            <div class="col-span-2"></div>
                            <div class="grid grid-cols-2 pb-3">
                                <label for="information_title" class="pl-4 my-auto font-bold">掲載日</label>
                                <DatePicker v-model="axiosSearchForm.keisai_ymd" class="my-auto" :manualInput="false" showIcon showButtonBar fluid iconDisplay="input" dateFormat="yy/mm/dd" @update:modelValue="normalizeKeisaiYmd"/>
                            </div>
                            <div class="grid grid-cols-2 pb-3">
                                <label for="information_title" class="pl-4 my-auto font-bold">適用期間</label>
                                <DatePicker v-model="axiosSearchForm.enable_start_ymd" class="my-auto" :manualInput="false" showIcon showButtonBar fluid iconDisplay="input" dateFormat="yy/mm/dd" @update:modelValue="normalizeEnableStart"/>
                            </div>
                            <div class="grid grid-cols-2 pb-3">
                                <label for="information_title" class="pl-4 my-auto mx-auto font-bold">～</label>
                                <DatePicker v-model="axiosSearchForm.enable_end_ymd" class="my-auto" :manualInput="false" showIcon showButtonBar fluid iconDisplay="input" dateFormat="yy/mm/dd" @update:modelValue="normalizeEnableEnd"/>
                            </div>
                            <div class="grid grid-cols-2 pb-3">
                                <Button label=検索 severity="info" class="p-0 ml-5" @click="searchInformation" />
                                <Button label=リセット severity="danger" class="p-0 ml-5" @click="resetSearch" />
                            </div>
                        </div>


                        <div class="w-full mx-auto overflow-auto">
                            <DataTable
                                :value="informations"
                                stripedRows
                                scrollable
                                scroll-height="50vh"
                                lazy
                                :loading="loadingDatatable"
                                :rows="rows"
                                :rowsPerPageOptions="perPageOptions"
                                paginator
                                :totalRecords="totalRecords"
                                @page="onPage"
                                @sort="onSort"
                                v-model:selection="selectedInformation.value"
                                selectionMode="single"
                                dataKey="information_id"
                                removableSort
                                v-model:expandedRows="expandedRows.value"
                                @rowExpand="onRowExpand"
                            >
                                <template #empty> <div style="text-align: center">データは見つかりませんでした。</div> </template>
                                <template #header>
                                    <div class="flex flex-wrap items-center justify-between gap-2">
                                        <span class="text-xl font-bold">お知らせ一覧</span>
                                        <Button icon="pi pi-refresh" rounded raised @click="tableRequest" severity="contrast"/>
                                    </div>
                                </template>
                                <Column expander style="width: 5rem" />
                                <Column :pt="{columnHeaderContent: 'justify-center', }" bodyStyle="text-align:center" field="information_title" header="お知らせタイトル" sortable></Column>
                                <Column :pt="{columnHeaderContent: 'justify-center', }" bodyStyle="text-align:center" field="information_kbn_txt" header="お知らせ区分" sortable></Column>
                                <Column :pt="{columnHeaderContent: 'justify-center', }" bodyStyle="text-align:center" field="keisai_ymd" header="掲載日" sortable></Column>
                                <Column :pt="{columnHeaderContent: 'justify-center', }" bodyStyle="text-align:center" field="enable_ymd" header="適用期間" sortable></Column>
                                <template #footer> {{total}}件中　{{from}}～{{to}}件　表示中 </template>
                                <template #expansion="informations">
                                    <div class="block font-semibold mb-2">
                                        お知らせ内容：
                                    </div>
                                    <div style="white-space: pre">
                                        {{ informations.data.information_naiyo }}
                                    </div>
                                </template>
                            </DataTable>

                            <div class="grid grid-cols-3 pb-3 mt-2">
                                <Button label=登録 severity="success" class="p-0 mx-20" @click="showCreateInformationForm"/>
                                <Button label=編集 severity="info" class="p-0 mx-20" :disabled="!selectedInformation.value" @click="showEditInformationForm"/>
                                <Button label=削除 severity="danger" class="p-0 mx-20" :disabled="!selectedInformation.value" @click="showDeleteInformationConfirmation"/>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>

    <Dialog v-model:visible="visibleModal.createForm" modal header="お知らせ新規登録" :style="{ width: '50rem' }" :closable="false">
        <div class="flex items-center gap-4 mb-4">
            <label for="username" class="font-semibold w-40">お知らせタイトル</label>
            <div class="flex-auto">
                <div class="flex flex-col">
                    <InputText v-model="axiosCreateParams.information_title" class="flex flex-col" autocomplete="off" :invalid="!validationCreateForm.information_title"/>
                    <Message v-if="messages.createForm.information_title.show" :severity="messages.createForm.information_title.severity" class="mt-4">{{ messages.createForm.information_title.content }}</Message>
                </div>
            </div>
        </div>
        <div class="flex items-center gap-4 mb-8">
            <label for="email" class="font-semibold w-40">お知らせ区分</label>
            <Select class="flex-auto" v-model="axiosCreateParams.information_kbn" :options="kbn_form" optionLabel="name" optionValue="value"/>
        </div>
        <div class="flex items-center gap-4 mb-8">
            <label for="email" class="font-semibold w-40">掲載日</label>
            <div class="flex-auto">
                <div class="flex flex-col">
                    <DatePicker class="flex-auto" v-model="axiosCreateParams.keisai_ymd" :manualInput="false" showIcon showButtonBar iconDisplay="input" dateFormat="yy/mm/dd" :invalid="!validationCreateForm.keisai_ymd"/>
                    <Message v-if="messages.createForm.keisai_ymd.show" :severity="messages.createForm.keisai_ymd.severity" class="mt-4">{{ messages.createForm.keisai_ymd.content }}</Message>
                </div>
            </div>
        </div>
        <div class="flex items-center gap-4 mb-8">
            <label for="email" class="font-semibold w-40">適用期間</label>
            <div class="flex-auto">
                <div class="flex flex-col">
                    <DatePicker class="flex-auto" v-model="axiosCreateParams.enable_start_ymd" :manualInput="false" showIcon showButtonBar iconDisplay="input" dateFormat="yy/mm/dd" :invalid="!validationCreateForm.enable_start_ymd"/>
                    <Message v-if="messages.createForm.enable_start_ymd.show" :severity="messages.createForm.enable_start_ymd.severity" class="mt-4">{{ messages.createForm.enable_start_ymd.content }}</Message>                </div>
            </div>
            <span>～</span>
            <div class="flex-auto">
                <div class="flex flex-col">
                    <DatePicker class="flex-auto" v-model="axiosCreateParams.enable_end_ymd" :manualInput="false" showIcon showButtonBar iconDisplay="input" dateFormat="yy/mm/dd" :invalid="!validationCreateForm.enable_end_ymd"/>
                    <Message v-if="messages.createForm.enable_end_ymd.show" :severity="messages.createForm.enable_end_ymd.severity" class="mt-4">{{ messages.createForm.enable_end_ymd.content }}</Message>
                </div>
            </div>
        </div>
        <div class="flex items-center gap-4 mb-8">
            <label for="email" class="font-semibold w-40">お知らせ内容</label>
            <div class="flex-auto">
                <div class="flex flex-col">
                    <Textarea  v-model="axiosCreateParams.information_naiyo" class="flex-auto" rows="5" cols="30" style="resize: none" :invalid="!validationCreateForm.information_naiyo"/>
                    <Message v-if="messages.createForm.information_naiyo.show" :severity="messages.createForm.information_naiyo.severity" class="mt-4">{{ messages.createForm.information_naiyo.content }}</Message>
                </div>
            </div>
        </div>
        <div class="flex justify-end gap-2">
            <Button type="button" label="キャンセル" severity="secondary" @click="visibleModal.createForm = false" :loading="buttonLoading.createForm"></Button>
            <Button type="button" label="登録" @click="createInformation" :loading="buttonLoading.createForm"></Button>
        </div>
    </Dialog>

    <Dialog v-model:visible="visibleModal.editForm" modal header="お知らせ変更" :style="{ width: '50rem' }" :closable="false">
        <div class="flex items-center gap-4 mb-4">
            <label for="username" class="font-semibold w-40">お知らせタイトル</label>
            <div class="flex-auto">
                <div class="flex flex-col">
                    <InputText v-model="axiosEditParams.information_title" class="flex-auto" autocomplete="off" :invalid="!validationEditForm.information_title"/>
                    <Message v-if="messages.editForm.information_title.show" :severity="messages.editForm.information_title.severity" class="mt-4">{{ messages.editForm.information_title.content }}</Message>
                </div>
            </div>
        </div>
        <div class="flex items-center gap-4 mb-8">
            <label for="email" class="font-semibold w-40">お知らせ区分</label>
            <Select class="flex-auto" v-model="axiosEditParams.information_kbn" :options="kbn_form" optionLabel="name" optionValue="value"/>
        </div>
        <div class="flex items-center gap-4 mb-8">
            <label for="email" class="font-semibold w-40">掲載日</label>
            <div class="flex-auto">
                <div class="flex flex-col">
                    <DatePicker class="flex-auto" v-model="axiosEditParams.keisai_ymd" :manualInput="false" showIcon showButtonBar iconDisplay="input" dateFormat="yy/mm/dd" :invalid="!validationEditForm.keisai_ymd"/>
                    <Message v-if="messages.editForm.keisai_ymd.show" :severity="messages.editForm.keisai_ymd.severity" class="mt-4">{{ messages.editForm.keisai_ymd.content }}</Message>
                </div>
            </div>
        </div>
        <div class="flex items-center gap-4 mb-8">
            <label for="email" class="font-semibold w-40">適用期間</label>
            <div class="flex-auto">
                <div class="flex flex-col">
                    <DatePicker class="flex-auto" v-model="axiosEditParams.enable_start_ymd" :manualInput="false" showIcon showButtonBar iconDisplay="input" dateFormat="yy/mm/dd" :invalid="!validationEditForm.enable_start_ymd"/>
                    <Message v-if="messages.editForm.enable_start_ymd.show" :severity="messages.editForm.enable_start_ymd.severity" class="mt-4">{{ messages.editForm.enable_start_ymd.content }}</Message>
                </div>
            </div>
            <span>～</span>
            <div class="flex-auto">
                <div class="flex flex-col">
                    <DatePicker class="flex-auto" v-model="axiosEditParams.enable_end_ymd" :manualInput="false" showIcon showButtonBar iconDisplay="input" dateFormat="yy/mm/dd" :invalid="!validationEditForm.enable_end_ymd"/>
                    <Message v-if="messages.editForm.enable_end_ymd.show" :severity="messages.editForm.enable_end_ymd.severity" class="mt-4">{{ messages.editForm.enable_end_ymd.content }}</Message>
                </div>
            </div>
        </div>
        <div class="flex items-center gap-4 mb-8">
            <label for="email" class="font-semibold w-40">お知らせ内容</label>
            <div class="flex-auto">
                <div class="flex flex-col">
                    <Textarea  v-model="axiosEditParams.information_naiyo" class="flex-auto" rows="5" cols="30" style="resize: none" :invalid="!validationEditForm.information_naiyo"/>
                    <Message v-if="messages.editForm.information_naiyo.show" :severity="messages.editForm.information_naiyo.severity" class="mt-4">{{ messages.editForm.information_naiyo.content }}</Message>
                </div>
            </div>
        </div>
        <div class="flex justify-end gap-2">
            <Button type="button" label="キャンセル" severity="secondary" @click="visibleModal.editForm = false" :loading="buttonLoading.editForm"></Button>
            <Button type="button" label="変更" @click="editInformation" :loading="buttonLoading.editForm"></Button>
        </div>
    </Dialog>

    <Dialog v-model:visible="visibleModal.deleteConfirmation" modal header="お知らせ削除" :closable="false">
        <span class="text-surface-500 dark:text-surface-400 block mb-8">選択されたお知らせを削除します。本当によろしいですか？</span>
        <div class="flex justify-end gap-2">
            <Button type="button" label="キャンセル" severity="secondary" @click="visibleModal.deleteConfirmation = false" :loading="buttonLoading.deleteConfirmation"></Button>
            <Button type="button" label="はい" @click="deleteInformation" :loading="buttonLoading.deleteConfirmation" severity="danger"></Button>
        </div>
    </Dialog>
</template>

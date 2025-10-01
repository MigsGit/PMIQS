<template>
    <div class="row mt-3" v-show="isEmptyTblEcrManRequirements">
        <!-- Man -->
        <div class="card mb-2">
                <h5 class="mb-0">
                    <button id="" class="btn btn-link collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseMan" aria-expanded="true" aria-controls="collapseMan">
                        Man
                    </button>
                </h5>
            <div id="collapseMan" class="collapse show" data-bs-parent="#accordionMain">
                <div class="card-body">
                    <div class="row">
                        <div class="col-12">
                            <DataTable
                                width="100%" cellspacing="0"
                                class="table mt-2"
                                ref="tblEcrManRequirements"
                                :columns="tblEcrRequirementsColumns"
                                :options="{
                                    paging:false,
                                    serverSide: true,
                                    columnDefs: [
                                        { orderable: false, target: [3] }
                                    ],
                                    language: {
                                        zeroRecords: 'No data available',
                                        emptyTable: 'No data available'
                                    },
                                    ajax: {
                                        url: url,
                                        dataSrc: function (json) {
                                        isEmptyTblEcrManRequirements = json.data && json.data.length > 0;
                                        return json.data;
                                        }
                                    }
                                }"
                            >
                                <thead>
                                    <tr>
                                        <th>Requirement</th>
                                        <th>Details</th>
                                        <th>Evidence</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                            </DataTable>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<script setup>
    import {defineProps,ref,onMounted,defineExpose} from 'vue'
    import DataTable from 'datatables.net-vue3';
    import DataTablesCore from 'datatables.net-bs5';
    DataTable.use(DataTablesCore);
    // const tblEcrManRequirements = ref(null);
    const isEmptyTblEcrManRequirements = ref(null);
    const isEmptyTblEcrMachineRequirements = ref(null);
    const isEmptyTblEcrMaterialRequirements = ref(null);
    const isEmptyTblEcrMethodRequirements = ref(null);
    const isEmptyTblEcrEnvironmentRequirements = ref(null);

    const props = defineProps({
        tblEcrManRequirements: {required: true }, // ref passed from parent
        tblEcrRequirementsColumns: { type: Array, required: true },
        url: { type: String, required: true }
    });

</script>

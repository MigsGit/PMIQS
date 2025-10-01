<template>
   <!-- Approver Summary -->
    <!-- <div class="row mt-3" v-show="isSelectReadonly === true"> -->
    <div class="row mt-3">
        <div class="card mb-2">
                <h5 class="mb-0">
                    <button id="" class="btn btn-link collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapsePmiInternalApprovalSummary" aria-expanded="true" aria-controls="collapsePmiInternalApprovalSummary">
                        ECR Approver Summary
                    </button>
                </h5>
            <div id="collapsePmiInternalApprovalSummary" class="collapse show" data-bs-parent="#accordionMain">
                <div class="card-body">
                    <div class="row">
                        <div class="col-12">
                            <DataTable
                                width="100%" cellspacing="0"
                                class="table mt-2"
                                :ref="tblEcrPmiInternalApproverSummary"
                                :columns="tblEcrPmiInternalApproverSummaryColumns"
                                ajax="api/load_pmi_internal_approval_summary"
                                :options="{
                                    paging:false,
                                    serverSide: true, //Serverside true will load the network
                                    ordering: false,
                                }"
                            >
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Approver Name</th>
                                        <th>Role</th>
                                        <th>Remarks</th>
                                        <th>Status</th>
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
    import {defineProps, ref, onMounted} from 'vue'
    import DataTable from 'datatables.net-vue3';
    import DataTablesCore from 'datatables.net-bs5';
    DataTable.use(DataTablesCore);
    const props = defineProps({
        tblEcrPmiInternalApproverSummary: {
            required: true,
        },
        tblEcrPmiInternalApproverSummaryColumns: {
            required: true,
        },
    })
</script>

<template>
    <div class="container-fluid px-4">
        <h4 class="mt-4">Environment</h4>
        <div class="row">
            <div class="col-md-3 offset-md-4">
                <Multiselect
                    placeholder="-Select an Option-"
                    :close-on-select="true"
                    :searchable="true"
                    :options="commonVar.optCategoryAdminAccess"
                    @change="onChangeAdminAccess($event)"
                />
            </div>
        </div>
        <div class="card-body overflow-auto">
            <div class="container-fluid px-4">
                <div class="card mt-5"  style="width: 100%;">
                    <div class="card-body overflow-auto">
                        <div class="container-fluid px-4">
                            <ol class="breadcrumb mb-4">
                                <li class="breadcrumb-item active">Environment Table</li>
                            </ol>
                            <div class="table-responsive">
                                <!-- :ajax="api/load_ecr_by_status?status=AP" -->
                                <DataTable
                                    width="100%" cellspacing="0"
                                    class="table mt-2"
                                    ref="tblEcrByStatus"
                                    :columns="tblEcrByStatusColumns"
                                    ajax="api/load_ecr_environment_by_status?category=Environment"
                                    :options="{
                                        serverSide: true, //Serverside true will load the network
                                        columnDefs:[
                                            {orderable:false,target:[0]}
                                        ]
                                    }"
                                >
                                    <thead>
                                        <tr>
                                            <th style=""width="5%">Action</th>
                                            <th style=""width="10%">Status</th>
                                            <th style=""width="10%">Attachment</th>
                                            <th style=""width="20%">ECR Ctrl No.</th>
                                            <th style=""width="25%">Details</th>
                                            <th style=""width="10%">Category</th>
                                            <th style=""width="10%">Section</th>
                                            <th style=""width="10%">Customer EC No</th>
                                        </tr>
                                    </thead>
                                </DataTable>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <ModalComponent icon="fa-user" modalDialog="modal-dialog modal-xl" title="SaveEnvironment" @add-event="" ref="modalSaveEnvironment">
        <template #body>
            <div class="row">
                <div class="modal-footer justify-content-end">
                    <button @click="btnEcrRequirement(selectedEcrsId)"type="button" ref= "btnEcrApproved" class="btn btn-primary btn-sm">
                        <font-awesome-icon class="nav-icon" icon="fas fa-check" />&nbsp;ECR Requirements
                    </button>
                </div>
                <div class="card">
                    <div class="card-body overflow-auto">
                        <DataTable
                            width="100%" cellspacing="0"
                            class="table  table-responsive mt-2"
                            ref="tblEcrDetails"
                            :columns="tblEcrDetailColumns"
                            ajax="api/load_ecr_details_by_ecr_id"
                            :options="{
                                serverSide: true, //Serverside true will load the network loadEcrDetailsByEcrId
                                columnDefs:[
                                    {orderable:false,target:[0]}
                                ]
                            }"
                        >
                        <thead>
                            <tr>
                                <th>Action</th>
                                <th> Description Of Change</th>
                                <th> Reason Of Change</th>
                                <th> Type Of Part</th>
                                <th> Change Imp Date</th>
                                <th> Docs Sub Date</th>
                                <th> Docs To Be Sub</th>
                                <th> Customer Approval</th>
                                <th> Remarks</th>
                            </tr>
                        </thead>
                        </DataTable>
                    </div>
                </div>
            </div>
            <!-- Pmi Internal Approver -->
            <!-- <PmiInternalApprover :tblEcrPmiInternalApproverSummary="tblEcrPmiInternalApproverSummary" :tblEcrPmiInternalApproverSummaryColumns = tblEcrPmiInternalApproverSummaryColumns>
            </PmiInternalApprover> -->
            <div class="row mt-3" v-show="isSelectReadonly === true">
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
                                        ref="tblPmiInternalApproverSummary"
                                        :columns="tblPmiInternalApproverSummaryColumns"
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
                                                <th>Role</th>
                                                <th>Approver Name</th>
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
        <template #footer>
            <button @click="btnPmiInternalApproval('DIS')" v-show="isSelectReadonly === true && commonVar.isSessionPmiInternalApprover === true" type="button" ref= "btnPmiInternalDisapproved" class="btn btn-danger btn-sm">
                <font-awesome-icon class="nav-icon" icon="fas fa-thumbs-down" />&nbsp;Disapproved
            </button>
            <button @click="btnPmiInternalApproval('APP')" v-show="isSelectReadonly === true && commonVar.isSessionPmiInternalApprover === true" type="button" ref= "btnPmiInternalApproved" class="btn btn-success btn-sm">
                <font-awesome-icon class="nav-icon" icon="fas fa-thumbs-up" />&nbsp;Approved
            </button>
        </template>
    </ModalComponent>
    <ModalComponent icon="fa-user" modalDialog="modal-dialog modal-lg" title="Ecr Details" @add-event="saveEcrDetails()" ref="modalSaveEcrDetail">
        <template #body>
             <!-- Description of Change / Reason for Change -->
             <EcrChangeComponent :isSelectReadonly="isSelectReadonly" :frmEcrReasonRows="frmEcrReasonRows" :optDescriptionOfChange="ecrVar.optDescriptionOfChange" :optReasonOfChange="ecrVar.optReasonOfChange">
            </EcrChangeComponent>
            <div class="row d-none">
                <div class="input-group flex-nowrap mb-2 input-group-sm">
                    <span class="input-group-text" id="addon-wrapping">ECR Details Id:</span>
                    <input v-model="frmEcrDetails.ecrDetailsId"  type="text" class="form-control form-control-lg" aria-describedby="addon-wrapping">
                </div>
            </div>
            <div class="row">
                <div class="col-sm-6">
                    <div class="input-group flex-nowrap mb-2 input-group-sm">
                        <span class="input-group-text" id="addon-wrapping">Type of Part:</span>
                        <Multiselect
                            v-model="frmEcrDetails.typeOfPart"
                            :options="ecrVar.optTypeOfPart"
                            placeholder="Select an option"
                            :searchable="true"
                            :close-on-select="true"
                        />
                    </div>
                    <div class="input-group flex-nowrap mb-2 input-group-sm">
                        <span class="input-group-text" id="addon-wrapping">Change Imp Date:</span>
                        <input v-model="frmEcrDetails.changeImpDate" type="date" class="form-control form-control-lg" aria-describedby="addon-wrapping">
                    </div>
                    <div class="input-group flex-nowrap mb-2 input-group-sm">
                        <span class="input-group-text" id="addon-wrapping">Docs To Be Submitted</span>
                        <input v-model="frmEcrDetails.docToBeSub" type="text" class="form-control form-control-lg" aria-describedby="addon-wrapping">
                    </div>
                 </div>
                <div class="col-sm-6">
                    <div class="input-group flex-nowrap mb-2 input-group-sm">
                        <span class="input-group-text" id="addon-wrapping">Docs Submission Date:</span>
                        <input v-model="frmEcrDetails.docSubDate"  type="date" class="form-control form-control-lg" aria-describedby="addon-wrapping">
                    </div>
                    <div class="input-group flex-nowrap mb-2 input-group-sm">
                        <span class="input-group-text" id="addon-wrapping">Remarks:</span>
                        <input v-model="frmEcrDetails.remarks"  type="text" class="form-control form-control-lg" aria-describedby="addon-wrapping">
                    </div>
                    <div class="input-group flex-nowrap mb-2 input-group-sm">
                        <span class="input-group-text" id="addon-wrapping">Customer Approval</span>
                        <Multiselect
                            v-model="frmEcrDetails.customerApproval"
                            :options="commonVar.optConditions"
                            placeholder="Select an option"
                            :searchable="true"
                            :close-on-select="true"
                        />
                    </div>
                </div>
            </div>
        </template>
        <template #footer>
            <button type="button" id= "closeBtn" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-success btn-sm"><li class="fas fa-save"></li> Save</button>
        </template>
    </ModalComponent>
    <ModalComponent icon="fa-user" modalDialog="modal-dialog modal-md" title="ECR Approval" ref="modalPmiInternalApproval" @add-event="frmSavePmiInternalApproval()">
        <template #body>
            <div class="row mt-3">
                <div class="col-md-12">
                    <div class="input-group flex-nowrap mb-2 input-group-sm">
                        <span class="input-group-text" id="addon-wrapping">Remarks:</span>
                        <textarea v-model="approvalRemarks" class="form-control form-control-lg" aria-describedby="addon-wrapping">
                        </textarea>
                    </div>
                </div>
            </div>
        </template>
        <template #footer>
            <button type="button" id= "closeBtn" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-success btn-sm"><font-awesome-icon class="nav-icon" icon="fas fa-save" />&nbsp; Save</button>
        </template>
    </ModalComponent>
    <ModalComponent icon="fa-upload" modalDialog="modal-dialog modal-md" title="Upload Environment Reference" ref="modalUploadEnvironmentRef" @add-event="frmUploadEnvironmentRef()">
        <template #body>
            <div class="row mt-3">
                <div class="col-md-12">
                    <div class="input-group flex-nowrap mb-2 input-group-sm">
                        <input @change="changeEnvironmentRef" multiple type="file" accept=".pdf" class="form-control form-control-lg" aria-describedby="addon-wrapping" required>
                    </div>
                </div>
            </div>
        </template>
        <template #footer>
            <button type="button" id= "closeBtn" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-success btn-sm"><font-awesome-icon class="nav-icon" icon="fas fa-save" />&nbsp; Save</button>
        </template>
    </ModalComponent>
    <ModalComponent icon="fa-download" modalDialog="modal-dialog modal-md" title="View Environment Reference" ref="modalViewEnvironmentRef">
        <template #body>
            <div class="row mt-3">
                <table class="table">
                    <thead>
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">
                                PDF Attachment
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- v-for -->
                        <tr v-for="(arrOriginalFilename, index) in arrOriginalFilenames" :key="arrOriginalFilename.index">
                            <th scope="row">{{ index+1 }}</th>
                            <td>
                                <a href="#" class="link-primary" ref="aViewEnvironmentRef" @click="btnLinkViewEnvironmentRef(selectedEcrsIdEncrypted,index)">
                                    {{ arrOriginalFilename }}
                                </a>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </template>
        <template #footer>
        </template>
    </ModalComponent>
    <ModalComponent icon="fa-user" modalDialog="modal-dialog modal-xl" title="ECR Requirements" ref="modalEcrRequirements">
        <template #body>
            <div class="row mt-3 man" v-show="isEmptyTblEcrManRequirements">
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
                                                url: 'api/load_ecr_requirements?category=1',
                                                dataSrc: function (json) {
                                                isEmptyTblEcrManRequirements = json.data && json.data.length > 0;
                                                return json.data;
                                                }
                                            }
                                        }"
                                    >
                                    </DataTable>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row mt-3" v-show="isEmptyTblEcrMaterialRequirements">
                <!-- Material -->
                <div class="card mb-2">
                        <h5 class="mb-0">
                            <button id="" class="btn btn-link" type="button" data-bs-toggle="collapse" data-bs-target="#collapMat" aria-expanded="true" aria-controls="collapMat">
                                Material
                            </button>
                        </h5>
                    <div id="collapMat" class="collapse show" data-bs-parent="#accordionMain">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-12">
                                    <DataTable
                                        width="100%" cellspacing="0"
                                        class="table mt-2"
                                        ref="tblEcrMatRequirements"
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
                                                url: 'api/load_ecr_requirements?category=2',
                                                dataSrc: function (json) {
                                                isEmptyTblEcrMaterialRequirements = json.data && json.data.length > 0;
                                                return json.data;
                                                }
                                            }
                                        }"
                                    >
                                    </DataTable>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row mt-3" v-show="isEmptyTblEcrMachineRequirements">
                <!-- Machine  -->
                <div class="card mb-2">
                        <h5 class="mb-0">
                            <button id="" class="btn btn-link" type="button" data-bs-toggle="collapse" data-bs-target="#collapseMac" aria-expanded="true" aria-controls="collapseMac">
                                Machine
                            </button>
                        </h5>
                    <div id="collapseMac" class="collapse show" data-bs-parent="#accordionMain">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-12">
                                    <DataTable
                                        width="100%" cellspacing="0"
                                        class="table mt-2"
                                        ref="tblEcrMachineRequirements"
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
                                                url: 'api/load_ecr_requirements?category=3',
                                                dataSrc: function (json) {
                                                isEmptyTblEcrMachineRequirements = json.data && json.data.length > 0;
                                                return json.data;
                                                }
                                            }
                                        }"
                                    >
                                    </DataTable>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row mt-3" v-show="isEmptyTblEcrMethodRequirements">
                <!-- Method -->
                <div class="card mb-2">
                        <h5 class="mb-0">
                            <button id="" class="btn btn-link" type="button" data-bs-toggle="collapse" data-bs-target="#collapseMethod" aria-expanded="true" aria-controls="collapseMethod">
                                Method
                            </button>
                        </h5>
                    <div id="collapseMethod" class="collapse show" data-bs-parent="#accordionMain">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-12">
                                    <DataTable
                                        width="100%" cellspacing="0"
                                        class="table mt-2"
                                        ref="tblEcrMethodRequirements"
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
                                                url: 'api/load_ecr_requirements?category=4',
                                                dataSrc: function (json) {
                                                isEmptyTblEcrMethodRequirements = json.data && json.data.length > 0;
                                                return json.data;
                                                }
                                            }
                                        }"
                                    >
                                    </DataTable>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row mt-3 environment" v-show="isEmptyTblEcrEnvironmentRequirements">
                <div class="card mb-2">
                        <h5 class="mb-0">
                            <button id="" class="btn btn-link" type="button" data-bs-toggle="collapse" data-bs-target="#collapseEnvironment" aria-expanded="true" aria-controls="collapseEnvironment">
                                Environment
                            </button>
                        </h5>
                    <div id="collapseEnvironment" class="collapse show" data-bs-parent="#accordionMain">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-12">
                                    <DataTable
                                        width="100%" cellspacing="0"
                                        class="table mt-2"
                                        ref="tblEcrEnvironmentRequirements"
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
                                                url: 'api/load_ecr_requirements?category=5',
                                                dataSrc: function (json) {
                                                isEmptyTblEcrEnvironmentRequirements = json.data && json.data.length > 0;
                                                return json.data;
                                                }
                                            }
                                        }"
                                    >
                                    </DataTable>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row mt-3 Others" v-show="isEmptyTblEcrOthersRequirements">
                <div class="card mb-2">
                        <h5 class="mb-0">
                            <button id="" class="btn btn-link" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOthers" aria-expanded="true" aria-controls="collapseOthers">
                                Others
                            </button>
                        </h5>
                    <div id="collapseOthers" class="collapse show" data-bs-parent="#accordionMain">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-12">
                                    <DataTable
                                        width="100%" cellspacing="0"
                                        class="table mt-2"
                                        ref="tblEcrOthersRequirements"
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
                                                url: 'api/load_ecr_requirements?category=6',
                                                dataSrc: function (json) {
                                                isEmptyTblEcrOthersRequirements = json.data && json.data.length > 0;
                                                return json.data;
                                                }
                                            }
                                        }"
                                    >
                                    </DataTable>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>
        <template #footer>
            <button type="button" id= "closeBtn" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
            <!-- <button type="submit" class="btn btn-success btn-sm"><font-awesome-icon class="nav-icon" icon="fas fa-save" />&nbsp;     Save</button> -->
        </template>
    </ModalComponent>
    <ModalComponent icon="fa-download" modalDialog="modal-dialog modal-md" title="View Ecr Requirement References" ref="modalViewEcrRequirementRef">
        <template #body>
            <div class="row mt-3">
                <table class="table">
                    <thead>
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">
                                PDF Attachment
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- v-for -->
                        <tr v-for="(arrEcrRequirementOriginalFilename, index) in arrEcrRequirementOriginalFilenames" :key="arrEcrRequirementOriginalFilename.index">
                            <th scope="row">{{ index+1 }}</th>
                            <td>
                                <a href="#" class="link-primary" ref="aViewEcrRequirementRef" @click="btnLinkViewEcrRequirementRef(selectedEcrRequirementsIdEncrypted,index)">
                                    {{ arrEcrRequirementOriginalFilename }}
                                </a>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </template>
        <template #footer>
        </template>
    </ModalComponent>
</template>

<script setup> //TODO: PMI APPROVAL Move as PmiApprovalComponent
    import {ref , onMounted,reactive, toRef} from 'vue';
    import ModalComponent from '../../js/components/ModalComponent.vue';
    import EcrChangeComponent from '../components/EcrChangeComponent.vue';
    import PmiInternalApprover from '../components/PmiInternalApprover.vue';
    import useEcr from '../../js/composables/ecr.js';
    import useForm from '../../js/composables/utils/useForm.js'
    import useCommon from '../../js/composables/common.js';
    import DataTable from 'datatables.net-vue3';
    import DataTablesCore from 'datatables.net-bs5';
    DataTable.use(DataTablesCore);

    const { axiosSaveData } = useForm(); // Call the useFetch function
    const {
        modalEcr,
        ecrVar,
        tblEcrDetails,
        frmEcrDetails,
        frmEcrReasonRows,
        descriptionOfChangeParams,
        reasonOfChangeParams,
        typeOfPartParams,
        getDropdownMasterByOpt,
        axiosFetchData,
        getEcrDetailsId,
        saveEcrDetails,

        tblEcrRequirementsColumns,
        tblEcrManRequirements,
        tblEcrMatRequirements,
        tblEcrMachineRequirements,
        tblEcrMethodRequirements,
        tblEcrEnvironmentRequirements,
        tblEcrOthersRequirements,
        selectedEcrRequirementsIdEncrypted,
        arrEcrRequirementOriginalFilenames,
        isEmptyTblEcrManRequirements,
        isEmptyTblEcrMaterialRequirements,
        isEmptyTblEcrMachineRequirements,
        isEmptyTblEcrMethodRequirements,
        isEmptyTblEcrEnvironmentRequirements,
        isEmptyTblEcrOthersRequirements,
        btnLinkViewEcrRequirementRef,
        btnEcrRequirement,
    } = useEcr();
    const {
        modal,
        commonVar,
        getCurrentApprover,
        getCurrentPmiInternalApprover,
        getCategoryAdminAccessOpt,
    } = useCommon();

    // const frmEnvironment = ref({
    //     environmentRef: null,
    // });
    const modalSaveEnvironment = ref(null);
    const modalSaveEcrDetail = ref(null);
    const isSelectReadonly = ref(true);
    const environmentRef = ref(null);
    const modalUploadEnvironmentRef = ref(null);
    const modalViewEnvironmentRef = ref(null);
    const aViewEnvironmentRef = ref(null);
    const selectedEcrsIdEncrypted = ref(null);
    const selectedAdminAccess = ref(null);
    const arrOriginalFilenames = ref([]);
    const modalPmiInternalApproval = ref(null);
    const tblPmiInternalApproverSummary = ref(null);
    const tblEcrByStatus = ref(null);
    const approvalRemarks = ref(null);
    const selectedEcrsId = ref(null);
    const isPmiInternalApproved = ref(null);
    const modalEcrRequirements = ref(null);
    const modalViewEcrRequirementRef = ref(null);
    const tblEcrByStatusColumns = [
        {   data: 'get_actions',
            orderable: false,
            searchable: false,
            createdCell(cell){
                let btnGetEcrId = cell.querySelector('#btnGetEcrId');
                let btnViewEcrById = cell.querySelector('#btnViewEcrById');
                let btnDownloadEnvironmentRef = cell.querySelector('#btnDownloadEnvironmentRef');
                if(btnGetEcrId != null){
                    btnGetEcrId.addEventListener('click',function(){
                        let ecrsId = this.getAttribute('ecr-id');
                        selectedEcrsId.value = ecrsId;
                        let approverParams = {
                            ecrsId : ecrsId
                        }
                        getCurrentPmiInternalApprover(approverParams);
                        tblEcrDetails.value.dt.ajax.url("api/load_ecr_details_by_ecr_id?ecr_id="+ecrsId).draw()
                        tblPmiInternalApproverSummary.value.dt.ajax.url("api/load_pmi_internal_approval_summary?ecrsId="+ecrsId).draw()
                        modal.SaveEnvironment.show();
                    });
                }
                if(btnViewEcrById != null){
                    btnViewEcrById.addEventListener('click',function(){
                        let ecrsId = this.getAttribute('ecr-id');
                        selectedEcrsId.value = ecrsId;
                        let approverParams = {
                            ecrsId : ecrsId
                        }
                        getCurrentPmiInternalApprover(approverParams);
                        tblEcrDetails.value.dt.ajax.url("api/load_ecr_details_by_ecr_id?ecr_id="+ecrsId).draw();

                        tblPmiInternalApproverSummary.value.dt.ajax.url("api/load_pmi_internal_approval_summary?ecrsId="+ecrsId).draw()

                        //Load ECR Requirement by Category and Ecrs Id
                        tblEcrManRequirements.value.dt.ajax.url("api/load_ecr_requirements?category=1&ecrsId="+ecrsId).draw();
                        tblEcrMatRequirements.value.dt.ajax.url("api/load_ecr_requirements?category=2&ecrsId="+ecrsId).draw();
                        tblEcrMachineRequirements.value.dt.ajax.url("api/load_ecr_requirements?category=3&ecrsId="+ecrsId).draw();
                        tblEcrMethodRequirements.value.dt.ajax.url("api/load_ecr_requirements?category=4&ecrsId="+ecrsId).draw();
                        tblEcrEnvironmentRequirements.value.dt.ajax.url("api/load_ecr_requirements?category=5&ecrsId="+ecrsId).draw();
                        tblEcrOthersRequirements.value.dt.ajax.url("api/load_ecr_requirements?category=6&ecrsId="+ecrsId).draw();
                        modalEcr.EcrRequirements.show();
                        modal.SaveEnvironment.show();
                    });
                }
                if(btnDownloadEnvironmentRef != null){
                    btnDownloadEnvironmentRef.addEventListener('click',function(){
                        let ecrsId = this.getAttribute('ecr-id');
                        selectedEcrsId.value = ecrsId;
                        modal.UploadEnvironmentRef.show();
                    });
                }
            }
        } ,
        {   data: 'get_status'} ,
        // {   data: 'get_attachment',
        //     orderable: false,
        //     searchable: false,
        //     createdCell(cell){
        //         let btnViewEnvironmentRef = cell.querySelector('#btnViewEnvironmentRef');
        //         if(btnViewEnvironmentRef != null){
        //             btnViewEnvironmentRef.addEventListener('click',function(){
        //                 let ecrsId = this.getAttribute('ecr-id');
        //                 getEnvironmentRefByEcrsId(ecrsId);
        //             });
        //         }

        //     }
        // } ,
        {   data: 'ecr_no'} ,
        {   data: 'get_details'} ,
        {   data: 'category'} ,
        {   data: 'section'} ,
        {   data: 'customer_ec_no'} ,
    ];
    const tblPmiInternalApproverSummaryColumns = [
        {   data: 'get_count'} ,
        {   data: 'get_role'} ,
        {   data: 'get_approver_name'} ,
        {   data: 'remarks'},
        {   data: 'get_status'} ,
    ];
    const tblEcrDetailColumns = [
        {   data: 'get_actions',
            orderable: false,
            searchable: false,
            createdCell(cell){
                let btnGetEcrDetailsId = cell.querySelector('#btnGetEcrDetailsId');
                if(btnGetEcrDetailsId != null){
                    btnGetEcrDetailsId.addEventListener('click',function(){
                        let ecrDetailsId = this.getAttribute('ecr-details-id');
                        getEcrDetailsId(ecrDetailsId);
                        modalEcr.SaveEcrDetail.show();
                    });
                }
            }
        } ,
        {   data: 'description_of_change'} ,
        {   data: 'reason_of_change'} ,
        {   data: 'type_of_part'} ,
        {   data: 'change_imp_date'} ,
        {   data: 'doc_sub_date'} ,
        {   data: 'doc_to_be_sub'} ,
        {   data: 'get_customer_approval'} ,
        {   data: 'remarks'} ,
    ];

    onMounted( async ()=>{
        modal.SaveEnvironment = new Modal(modalSaveEnvironment.value.modalRef,{ keyboard: false });
        modal.PmiInternalApproval = new Modal(modalPmiInternalApproval.value.modalRef,{ keyboard: false });
        modal.UploadEnvironmentRef = new Modal(modalUploadEnvironmentRef.value.modalRef,{ keyboard: false });
        modal.ViewEnvironmentRef = new Modal(modalViewEnvironmentRef.value.modalRef,{ keyboard: false });
        modalEcr.SaveEcrDetail = new Modal(modalSaveEcrDetail.value.modalRef,{ keyboard: false });
        modalEcr.EcrRequirements = new Modal(modalEcrRequirements.value.modalRef,{ keyboard: false });
        modalEcr.ViewEcrRequirementRef = new Modal(modalViewEcrRequirementRef.value.modalRef,{ keyboard: false });
        await getDropdownMasterByOpt(descriptionOfChangeParams);
        await getDropdownMasterByOpt(reasonOfChangeParams);
        await getDropdownMasterByOpt(typeOfPartParams);
        await getCategoryAdminAccessOpt();
        modalSaveEcrDetail.value.modalRef.addEventListener('hidden.bs.modal', event => {
            resetEcrForm(frmEcrDetails.value);
        });
    })
    // === Functions
    const onChangeAdminAccess = async (selectedParams)=>{
        tblEcrByStatus.value.dt.ajax.url("api/load_ecr_environment_by_status?category=Environment"+"&& adminAccess="+selectedParams).draw();
        selectedAdminAccess.value = selectedParams;
    }
    const resetEcrForm = async (frmElement) => {
        for (const key in frmElement) {
            frmElement[key] = '';
        }
    };
    const btnPmiInternalApproval = async (isEcrApproved) => {
        modal.PmiInternalApproval.show();
        isPmiInternalApproved.value = isEcrApproved;
    }
    const changeEnvironmentRef = async (event)  => {
        environmentRef.value =  Array.from(event.target.files);
    }
    const btnLinkViewEnvironmentRef = async (selectedEcrsIdEncrypted,index) => {
        window.open(`api/view_environment_ref?ecrsId=${selectedEcrsIdEncrypted} && index=${index}`, '_blank');
    }
    const getEnvironmentRefByEcrsId = async (ecrsId) => {
        let apiParams = {
            ecrsId : ecrsId
        }
        axiosFetchData(apiParams,'api/get_environment_ref_by_ecrs_id',function(response){
            let data = response.data;
            let ecrsId = data.ecrsId;
            let originalFilename = data.originalFilename;
            arrOriginalFilenames.value = originalFilename;
            selectedEcrsIdEncrypted.value = ecrsId;
            modal.ViewEnvironmentRef.show();
        });
    }
    const frmSavePmiInternalApproval = async () => {
        let formData = new FormData();
        //Append form data
        [
            ["ecrsId", selectedEcrsId.value],
            ["status", isPmiInternalApproved.value],
            ["remarks", approvalRemarks.value],
        ].forEach(([key, value]) =>
            formData.append(key, value)
        );
        axiosSaveData(formData,'api/save_pmi_internal_approval', (response) =>{
            // tblPmiInternalApproverSummary.value.dt.ajax.url("api/load_pmi_internal_approval_summary?ecr_id="+selectedEcrsId.value).draw()
            tblEcrByStatus.value.dt.ajax.url("api/load_ecr_environment_by_status?category=Environment"+"&& adminAccess="+selectedAdminAccess.value).draw();
            modal.PmiInternalApproval.hide();
            modal.SaveEnvironment.hide();
        });
    }
    const frmUploadEnvironmentRef = async () => {
        let formData = new FormData();
        //Append form data
        environmentRef.value.forEach((file, index) => {
            formData.append('environment_ref[]', file);
        });
        formData.append("ecrsId", selectedEcrsId.value);

        axiosSaveData(formData,'api/upload_environment_ref',(response) =>{
            console.log(response);
        });
    }

</script>



<template>
    <div class="container-fluid px-4">
        <h4 class="mt-4">Methods</h4>
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
        <div class="card mt-5"  style="width: 100%;">
            <div class="card-body overflow-auto">
                <div class="container-fluid px-4">
                    <div class="table-responsive">
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item active">Method Table</li>
                        </ol>
                        <DataTable
                            width="100%" cellspacing="0"
                            class="table mt-2"
                            ref="tblEcrByStatus"
                            :columns="tblEcrByStatusColumns"
                            ajax="api/load_method_ecr_by_status?category=Method"
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
    <ModalComponent icon="fa-user" modalDialog="modal-dialog modal-xl" title="SaveMethod" ref="modalSaveMethod">
        <template #body>
            <div class="modal-footer justify-content-end">
                <button @click="btnEcrRequirement(selectedEcrsId)"type="button" ref= "btnEcrApproved" class="btn btn-primary btn-sm">
                    <font-awesome-icon class="nav-icon" icon="fas fa-check" />&nbsp;ECR Requirements
                </button>
            </div>
            <div class="row">
                <div class="card">
                    <div class="card-body overflow-auto">
                        <DataTable
                            width="100%" cellspacing="0"
                            class="table  table-responsive mt-2"
                            ref="tblEcrDetails"
                            :columns="tblEcrDetailColumns"
                            ajax="api/load_ecr_details_by_ecr_id?ecr_id=4"
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
                                <th> Doc Sub Date</th>
                                <th> Doc To Be Sub</th>
                                <th> Remarks</th>
                            </tr>
                        </thead>
                        </DataTable>
                    </div>
                </div>
            </div>
            <div class="card mb-2" v-show="isModal === 'Edit'">
                <h5 class="mb-0">
                    <button id="" class="btn btn-link" type="button" data-bs-toggle="collapse" data-bs-target="#collapse2" aria-expanded="true" aria-controls="collapse2">
                        Approval:
                    </button>
                </h5>
                <div id="collapse2" class="collapse show" data-bs-parent="#accordionMain">
                    <div class="card-body shadow">
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <div class="input-group flex-nowrap mb-2 input-group-sm">
                                    <span class="input-group-text" id="addon-wrapping">Before:</span>
                                    <input @change="changeMethodRefBefore" multiple type="file" accept=".jpg" class="form-control form-control-lg" aria-describedby="addon-wrapping" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="input-group flex-nowrap mb-2 input-group-sm">
                                    <span class="input-group-text" id="addon-wrapping">After:</span>
                                    <input @change="changeMethodRefAfter" multiple type="file" accept=".jpg" class="form-control form-control-lg" aria-describedby="addon-wrapping" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 overflow-auto">
                                <table class="table table-responsive">
                                    <thead>
                                        <tr>
                                        <th scope="col" style="width: 10%;">Section</th>
                                        <th scope="col" style="width: 30%;">Assessed by</th>
                                        <th scope="col" style="width: 30%;">Checked by</th>
                                        </tr>
                                    </thead>
                                    <!-- @change="onUserChange(qadApprovedByInternalParams)" -->
                                    <!-- @change="onUserChange(prCheckedByParams)" -->
                                        <!-- @change="onUserChange(prApprovedByParams)" -->
                                    <tbody>
                                        <tr class="production">
                                            <td>
                                                Production
                                            </td>
                                            <td>
                                                <Multiselect
                                                    v-model="frmMethod.prdnAssessedBy"
                                                    :close-on-select="true"
                                                    :searchable="true"
                                                    :options="methodVar.prdnAssessedBy"
                                                />
                                            </td>
                                            <td>
                                                <Multiselect
                                                    v-model="frmMethod.prdnCheckedBy"
                                                    :close-on-select="true"
                                                    :searchable="true"
                                                    :options="methodVar.prdnCheckedBy"
                                                />
                                            </td>
                                        </tr>
                                        <tr class="ppc">
                                            <td>
                                                Conformed: PPC
                                            </td>
                                            <td>
                                                <Multiselect
                                                    v-model="frmMethod.ppcAssessedBy"
                                                    :close-on-select="true"
                                                    :searchable="true"
                                                    :options="methodVar.ppcAssessedBy"
                                                />
                                            </td>
                                            <td>
                                                <Multiselect
                                                    v-model="frmMethod.ppcCheckedBy"
                                                    :close-on-select="true"
                                                    :searchable="true"
                                                    :options="methodVar.ppcCheckedBy"
                                                />
                                            </td>
                                        </tr>
                                        <tr class="pro-engineer">
                                            <td>
                                                Process Engineering
                                            </td>
                                            <td>
                                                <Multiselect
                                                    v-model="frmMethod.proEnggAssessedBy"
                                                    :close-on-select="true"
                                                    :searchable="true"
                                                    :options="methodVar.proEnggAssessedBy"
                                                />
                                            </td>
                                            <td>
                                                <Multiselect
                                                    v-model="frmMethod.proEnggCheckedBy"
                                                    :close-on-select="true"
                                                    :searchable="true"
                                                    :options="methodVar.proEnggCheckedBy"
                                                />
                                            </td>

                                        </tr>
                                        <tr class="main-engineer">
                                            <td>
                                                Maintenance Engineering
                                            </td>
                                            <td>
                                                <Multiselect
                                                    v-model="frmMethod.mainEnggAssessedBy"
                                                    :close-on-select="true"
                                                    :searchable="true"
                                                    :options="methodVar.mainEnggAssessedBy"
                                                />
                                            </td>
                                            <td>
                                                <Multiselect
                                                    v-model="frmMethod.mainEnggCheckedBy"
                                                    :close-on-select="true"
                                                    :searchable="true"
                                                    :options="methodVar.mainEnggCheckedBy"
                                                />
                                            </td>

                                        </tr>
                                        <tr class="qc">
                                            <td>
                                                QC
                                            </td>
                                            <td>
                                                <Multiselect
                                                    v-model="frmMethod.qcAssessedBy"
                                                    :close-on-select="true"
                                                    :searchable="true"
                                                    :options="methodVar.qcAssessedBy"
                                                />
                                            </td>
                                            <td>
                                                <Multiselect
                                                    v-model="frmMethod.qcCheckedBy"
                                                    :close-on-select="true"
                                                    :searchable="true"
                                                    :options="methodVar.qcCheckedBy"
                                                />
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row mt-3"  v-show="isModal === 'View' && currentStatus != 'PMIAPP'">
                <div class="card mb-2">
                        <h5 class="mb-0">
                            <button id="" class="btn btn-link collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseMachineApproverSummary" aria-expanded="true" aria-controls="collapseMachineApproverSummary">
                                ECR Approver Summary
                            </button>
                        </h5>
                    <div id="collapseMachineApproverSummary" class="collapse show" data-bs-parent="#accordionMain">
                        <div class="card-header">
                            <h5> Machine Approver </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-12">
                                    <DataTable
                                        width="100%" cellspacing="0"
                                        class="table mt-2"
                                        ref="tblMethodApproverSummary"
                                        :columns="tblMethodApproverSummaryColumns"
                                        ajax="api/load_machine_approver_summary_id"
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
            <div class="row mt-3" v-show="isModal === 'Edit'">
                        <div class="card">
                            <div class="row mt-2">
                                <div class="col-12">
                                    <button @click="btnAddSpecialInspection()" type="button" class="btn btn-primary btn-sm mb-2" style="float: right !important;"><i class="fas fa-plus"></i> Add SA Details</button>
                                </div>
                            </div>
                            <div class="card-body overflow-auto">
                                <DataTable
                                    width="100%" cellspacing="0"
                                    class="table mt-2"
                                    ref="tblSpecialInspection"
                                    :columns="tblSpecialInspectionColumns"
                                    ajax="api/load_special_inspection_by_ecr_id"
                                    :options="{
                                        serverSide: true, //Serverside true will load the network  //ecrsId
                                        columnDefs:[
                                            // {orderable:false,target:[0]}
                                        ]
                                    }"
                                >
                                </DataTable>
                            </div>
                        </div>
            </div>
            <div class="row mt-3" v-show="isModal === 'View' && currentStatus === 'PMIAPP'" >
                <div class="card mb-2">
                        <h5 class="mb-0">
                            <button id="" class="btn btn-link collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapsePmiInternalApprovalSummary" aria-expanded="true" aria-controls="collapsePmiInternalApprovalSummary">
                                ECR Approver Summary
                            </button>
                        </h5>
                    <div id="collapsePmiInternalApprovalSummary" class="collapse show" data-bs-parent="#accordionMain">
                        <div class="card-header">
                            <h3> PMI Approvers </h3>
                        </div>
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
            <button @click="btnApprovedDisapproved('DIS')" v-show="isModal === 'View' && commonVar.isSessionApprover === true" type="button" ref= "btnPmiInternalDisapproved" class="btn btn-danger btn-sm">
                <font-awesome-icon class="nav-icon" icon="fas fa-thumbs-down" />&nbsp;Disapproved
            </button>
            <button @click="btnApprovedDisapproved('APP')" v-show="isModal === 'View' && commonVar.isSessionApprover === true" type="button" ref= "btnPmiInternalApproved" class="btn btn-success btn-sm">Approved</button>
            <button v-show="isModal === 'Edit'" type="button" id= "closeBtn" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Close</button>
            <button @click="saveMethod()" v-show="isModal === 'Edit'" type="submit" class="btn btn-success btn-sm"><li class="fas fa-save"></li> Save</button>
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
                </div>
            </div>
        </template>
        <template #footer>
            <button type="button" id= "closeBtn" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-success btn-sm"><li class="fas fa-save"></li> Save</button>
        </template>
    </ModalComponent>
    <ModalComponent icon="fa-user" modalDialog="modal-dialog modal-lg" title="Special Inspection" @add-event="saveSpecialInspection()" ref="modalSaveSpecialInspection">
        <template #body>
            <ModalSpecialInspectionComponent @click-reload-lqc="reloadLqc()" @click-reload-inspector="reloadInspector()" :commonVar="commonVar" :frmSpecialInspection="frmSpecialInspection">
            </ModalSpecialInspectionComponent>
        </template>
        <template #footer>
            <button type="button" id= "closeBtn" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-success btn-sm"><li class="fas fa-save"></li> Save</button>
        </template>
    </ModalComponent>
    <ModalComponent icon="fa-download" modalDialog="modal-dialog modal-md" title="View Method Reference" ref="modalViewMethodRef">
        <template #body>
            <div class="row mt-3">
                <table class="table">
                    <thead>
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">
                                Before Image Attachment
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- v-for -->
                        <tr v-for="(arrOriginalFilenameBefore, index) in arrOriginalFilenamesBefore" :key="arrOriginalFilenameBefore.index">
                            <th scope="row">{{ index+1 }}</th>
                            <td>
                                <a href="#" class="link-primary" ref="aViewMethodRefBefore" @click="btnLinkViewMethodRefBefore(selectedMethodsId,index)">
                                    {{ arrOriginalFilenameBefore }}
                                </a>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <table class="table">
                    <thead>
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">
                                After Image Attachment
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- v-for -->
                        <tr v-for="(arrOriginalFilenameAfter, index) in arrOriginalFilenamesAfter" :key="arrOriginalFilenameAfter.index">
                            <th scope="row">{{ index+1 }}</th>
                            <td>
                                <a href="#" class="link-primary" ref="aViewMethodRefAfter" @click="btnLinkViewMethodRefAfter(selectedMethodsId,index)">
                                    {{ arrOriginalFilenameAfter }}
                                </a>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <table class="table">
                    <thead>

                        <tr>
                            <!-- <th scope="col">
                                Internal Material
                            </th> -->
                            <th scope="col">
                                External Material
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <!-- <td>
                                <a href="#" class="link-primary" @click="btnLinkDownloadInternalMachine(selectedEcrsId)">
                                    Download Internal Machine
                                </a>
                            </td> -->
                            <td>
                                <a href="#" class="link-primary" @click="btnLinkDownloadExternal(selectedEcrsId)">
                                    Download External Machine
                                </a>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <table class="table">
                    <thead>
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">
                                External Disposition
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- v-for -->
                        <tr v-for="(arrOriginalFilenameExternalDisposition, index) in arrOriginalFilenameExternalDispositions" :key="arrOriginalFilenameExternalDisposition.index">
                            <th scope="row">{{ index+1 }}</th>
                            <td>
                                <a href="#" class="link-primary" ref="aViewExternalDisposition" @click="btnLinkViewExternalDisposition(selectedEcrsId,index)">
                                    {{ arrOriginalFilenameExternalDisposition }}
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
    <ModalComponent icon="fa-user" modalDialog="modal-dialog modal-md" title="Approval" ref="modalApproval">
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
            <button @click = "saveApproval(selectedMethodsId,selectedEcrsId,approvalRemarks,isApprovedDisappproved,currentStatus)" type="button" class="btn btn-success btn-sm"><font-awesome-icon class="nav-icon" icon="fas fa-save" />&nbsp; Save</button>
        </template>
    </ModalComponent>
    <ModalComponent icon="fa-upload" modalDialog="modal-dialog modal-md" title="Upload External Disposition" ref="modalExternalDisposition" @add-event="saveExternalDisposition()">
        <template #body>
            <div class="row mt-3">
                <div class="col-md-12">
                    <div class="input-group flex-nowrap mb-2 input-group-sm">
                        <input @change="changeExternalDisposition" multiple type="file" accept=".pdf" class="form-control form-control-lg" aria-describedby="addon-wrapping" required>
                    </div>
                </div>
            </div>
        </template>
        <template #footer>
            <button type="button" id= "closeBtn" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-success btn-sm"><font-awesome-icon class="nav-icon" icon="fas fa-save" />&nbsp; Save</button>
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

<script setup>
   import {ref , onMounted,reactive, toRef} from 'vue';
    import ModalComponent from '../../js/components/ModalComponent.vue';
    import EcrChangeComponent from '../components/EcrChangeComponent.vue';
    import ModalSpecialInspectionComponent from '../components/ModalSpecialInspectionComponent.vue';
    import useEcr from '../../js/composables/ecr.js';
    import useMethod from '../../js/composables/method.js';
    import useForm from '../../js/composables/utils/useForm.js'
    import useSettings from '../../js/composables/settings.js'
    import DataTable from 'datatables.net-vue3';
    import DataTablesCore from 'datatables.net-bs5';
    import useCommon from '../../js/composables/common.js';
    DataTable.use(DataTablesCore);

    const { axiosSaveData } = useForm(); // Call the useForm function
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
        methodVar,
        frmMethod,
    } = useMethod();
    const {
        modal,
        commonVar,
        externalDisposition,
        tblSpecialInspection,
        tblSpecialInspectionColumns,
        modalSaveSpecialInspection,
        modalExternalDisposition,
        specialInsQcInspectorParams,
        specialInsLqcParams,
        frmSpecialInspection,
        saveSpecialInspection,
        getCurrentApprover,
        getCurrentPmiInternalApprover,
        changeExternalDisposition,
        btnLinkViewExternalDisposition,
        getCategoryAdminAccessOpt,
    } = useCommon();
    const {
        getRapidxUserByIdOpt,
    } = useSettings();
    const modalSaveMethod = ref(null);
    const modalSaveEcrDetail = ref(null);
    const tblEcrByStatus = ref(null);
    const isModal = ref('Edit');
    const isSelectReadonly = ref(true);
    const currentStatus = ref(null);
    const selectedEcrsId = ref(null);
    const tblMethodApproverSummary = ref(null);
    const tblPmiInternalApproverSummary = ref(null);
    const isApprovedDisappproved = ref(null);
    const approvalRemarks = ref(null);
    const modalApproval = ref(null);
    const modalViewMethodRef = ref(null);
    const aViewMethodRefBefore = ref(null);
    const aViewMethodRefAfter = ref(null);
    const arrOriginalFilenamesBefore = ref(null);
    const arrOriginalFilenamesAfter = ref(null);
    const arrOriginalFilenameExternalDispositions = ref(null);
    const selectedMethodsId = ref(null);
    const selectedAdminAccess = ref(null);
    const methodRefBefore = ref(null);
    const methodRefAfter = ref(null);

    const modalEcrRequirements = ref(null);
    const modalViewEcrRequirementRef = ref(null);

    const tblEcrByStatusColumns = [
        {   data: 'get_actions',
            orderable: false,
            searchable: false,
            createdCell(cell){
                let btnGetEcrId = cell.querySelector('#btnGetEcrId');
                let btnViewMethodById = cell.querySelector('#btnViewMethodById');
                let btnViewDispotionById = cell.querySelector('#btnViewDispotionById');
                if(btnGetEcrId != null){
                    btnGetEcrId.addEventListener('click',function(){
                        let ecrsId = this.getAttribute('ecrs-id');
                        let methodsId = this.getAttribute('methods-id');
                        selectedEcrsId.value = ecrsId;
                        selectedMethodsId.value = methodsId;
                        isModal.value = 'Edit';

                        tblEcrDetails.value.dt.ajax.url("api/load_ecr_details_by_ecr_id?ecr_id="+ecrsId).draw();
                        tblSpecialInspection.value.dt.ajax.url("api/load_special_inspection_by_ecr_id?ecrsId="+ecrsId).draw();
                        getRapidxUserByIdOpt(prdnAssessedByParams);
                        getRapidxUserByIdOpt(prdnCheckedByParams);
                        getRapidxUserByIdOpt(ppcAssessedByParams);
                        getRapidxUserByIdOpt(ppcCheckedByParams);
                        getRapidxUserByIdOpt(mainEnggAssessedByParams);
                        getRapidxUserByIdOpt(mainEnggCheckedByParams);
                        getRapidxUserByIdOpt(proEnggAssessedByParams);
                        getRapidxUserByIdOpt(proEnggCheckedByParams);
                        getRapidxUserByIdOpt(qcAssessedByParams);
                        getRapidxUserByIdOpt(qcCheckedByParams);

                        //Load ECR Requirement by Category and Ecrs Id
                        tblEcrManRequirements.value.dt.ajax.url("api/load_ecr_requirements?category=1&ecrsId="+ecrsId).draw();
                        tblEcrMatRequirements.value.dt.ajax.url("api/load_ecr_requirements?category=2&ecrsId="+ecrsId).draw();
                        tblEcrMachineRequirements.value.dt.ajax.url("api/load_ecr_requirements?category=3&ecrsId="+ecrsId).draw();
                        tblEcrMethodRequirements.value.dt.ajax.url("api/load_ecr_requirements?category=4&ecrsId="+ecrsId).draw();
                        tblEcrEnvironmentRequirements.value.dt.ajax.url("api/load_ecr_requirements?category=5&ecrsId="+ecrsId).draw();
                        tblEcrOthersRequirements.value.dt.ajax.url("api/load_ecr_requirements?category=6&ecrsId="+ecrsId).draw();
                        modalEcr.EcrRequirements.show();
                        modal.SaveMethod.show();
                    });
                }
                if(btnViewMethodById != null){ //madi krstevski
                    btnViewMethodById.addEventListener('click',function(){
                        let ecrsId = this.getAttribute('ecrs-id');
                        let methodsId = this.getAttribute('methods-id');
                        let methodStatus = this.getAttribute('method-status');
                        let methodApproverParams = {
                            selectedId : methodsId,
                            approvalType : 'methodApproval'
                        }
                        let pmiApproverParams = {
                            selectedId : ecrsId,
                            approvalType : 'pmiApproval'
                        }
                        selectedEcrsId.value = ecrsId;
                        selectedMethodsId.value = methodsId;
                        isModal.value = 'View';
                        currentStatus.value = methodStatus;

                        if( methodStatus === 'PMIAPP' || methodStatus === 'OK'){
                            getCurrentApprover(pmiApproverParams);
                            tblPmiInternalApproverSummary.value.dt.ajax.url("api/load_pmi_internal_approval_summary?ecrsId="+ecrsId).draw()
                        }
                        if( methodStatus != 'PMIAPP'){
                            getCurrentApprover(methodApproverParams);
                        }
                        tblMethodApproverSummary.value.dt.ajax.url("api/load_method_approver_summary_material_id?methodsId="+methodsId).draw();
                        tblEcrDetails.value.dt.ajax.url("api/load_ecr_details_by_ecr_id?ecr_id="+ecrsId).draw();
                        //Load ECR Requirement by Category and Ecrs Id
                        tblEcrManRequirements.value.dt.ajax.url("api/load_ecr_requirements?category=1&ecrsId="+ecrsId).draw();
                        tblEcrMatRequirements.value.dt.ajax.url("api/load_ecr_requirements?category=2&ecrsId="+ecrsId).draw();
                        tblEcrMachineRequirements.value.dt.ajax.url("api/load_ecr_requirements?category=3&ecrsId="+ecrsId).draw();
                        tblEcrMethodRequirements.value.dt.ajax.url("api/load_ecr_requirements?category=4&ecrsId="+ecrsId).draw();
                        tblEcrEnvironmentRequirements.value.dt.ajax.url("api/load_ecr_requirements?category=5&ecrsId="+ecrsId).draw();
                        tblEcrOthersRequirements.value.dt.ajax.url("api/load_ecr_requirements?category=6&ecrsId="+ecrsId).draw();
                        modalEcr.EcrRequirements.show();
                        modal.SaveMethod.show();
                    });
                }
                if(btnViewDispotionById != null){
                    btnViewDispotionById.addEventListener('click',function(){
                        let ecrsId = this.getAttribute('ecrs-id');
                        selectedEcrsId.value = ecrsId;
                        modal.ExternalDisposition.show();
                    });
                }
            }
        } ,
        {   data: 'get_status'} ,
        {   data: 'get_attachment',
            orderable: false,
            searchable: false,
            createdCell(cell){
                let btnViewMethodRef = cell.querySelector('#btnViewMethodRef');
                if(btnViewMethodRef != null){
                    btnViewMethodRef.addEventListener('click',function(){
                        let methodsId = this.getAttribute('methods-id');
                        let ecrsId = this.getAttribute('ecrs-id');
                        selectedEcrsId.value = ecrsId;

                        getMethodRefByEcrsId(methodsId);
                    });
                }
            }
        } ,
        {   data: 'ecr_no'} ,
        {   data: 'get_details'} ,
        {   data: 'category'} ,
        {   data: 'section'} ,
        {   data: 'customer_ec_no'} ,
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
        {   data: 'remarks'} ,
    ];
    const tblMethodApproverSummaryColumns = [
        {   data: 'get_count'} ,
        {   data: 'get_role'} ,
        {   data: 'get_approver_name'} ,
        {   data: 'remarks'},
        {   data: 'get_status'} ,
    ];
    const tblPmiInternalApproverSummaryColumns = [
        {   data: 'get_count'} ,
        {   data: 'get_role'} ,
        {   data: 'get_approver_name'} ,
        {   data: 'remarks'},
        {   data: 'get_status'} ,
    ];
    //Users Params
    const prdnAssessedByParams = {
        globalVar: methodVar.prdnAssessedBy,
        formModel: toRef(frmMethod.value,'prdnAssessedBy'),
        selectedVal: 237,
    };
    const prdnCheckedByParams = {
        globalVar: methodVar.prdnCheckedBy,
        formModel: toRef(frmMethod.value,'prdnCheckedBy'),
        selectedVal: 237,
    };
    const ppcAssessedByParams = {
        globalVar: methodVar.ppcAssessedBy,
        formModel: toRef(frmMethod.value,'ppcAssessedBy'),
        selectedVal: 237,
    };
    const ppcCheckedByParams = {
        globalVar: methodVar.ppcCheckedBy,
        formModel: toRef(frmMethod.value,'ppcCheckedBy'),
        selectedVal: 237,
    };
    const mainEnggAssessedByParams = {
        globalVar: methodVar.mainEnggAssessedBy,
        formModel: toRef(frmMethod.value,'mainEnggAssessedBy'),
        selectedVal: 237,
    };
    const mainEnggCheckedByParams = {
        globalVar: methodVar.mainEnggCheckedBy,
        formModel: toRef(frmMethod.value,'mainEnggCheckedBy'),
        selectedVal: 237,
    };
    const proEnggAssessedByParams = {
        globalVar: methodVar.proEnggAssessedBy,
        formModel: toRef(frmMethod.value,'proEnggAssessedBy'),
        selectedVal: 237,
    };
    const proEnggCheckedByParams = {
        globalVar: methodVar.proEnggCheckedBy,
        formModel: toRef(frmMethod.value,'proEnggCheckedBy'),
        selectedVal:237,
    };
    const qcAssessedByParams = {
        globalVar: methodVar.qcAssessedBy,
        formModel: toRef(frmMethod.value,'qcAssessedBy'),
        selectedVal: 237,
    };
    const qcCheckedByParams = {
        globalVar: methodVar.qcCheckedBy,
        formModel: toRef(frmMethod.value,'qcCheckedBy'),
        selectedVal:237,
    };
    onMounted( async ()=>{
        modal.SaveMethod = new Modal(modalSaveMethod.value.modalRef,{ keyboard: false });
        modal.SaveSpecialInspection = new Modal(modalSaveSpecialInspection.value.modalRef,{ keyboard: false });
        modal.ViewMethodRef = new Modal(modalViewMethodRef.value.modalRef,{ keyboard: false });
        modal.Approval = new Modal(modalApproval.value.modalRef,{ keyboard: false });
        modalEcr.SaveEcrDetail = new Modal(modalSaveEcrDetail.value.modalRef,{ keyboard: false });
        modalEcr.EcrRequirements = new Modal(modalEcrRequirements.value.modalRef,{ keyboard: false });
        modalEcr.ViewEcrRequirementRef = new Modal(modalViewEcrRequirementRef.value.modalRef,{ keyboard: false });
        modalSaveMethod.value.modalRef.addEventListener('hidden.bs.modal', event => {
            resetEcrForm(frmMethod.value);
        });
        modalSaveEcrDetail.value.modalRef.addEventListener('hidden.bs.modal', event => {
            resetEcrForm(frmEcrDetails.value);
        });
        modalSaveSpecialInspection.value.modalRef.addEventListener('hidden.bs.modal', event => {
            frmSpecialInspection.value.ecrsId;
        });
        modal.ExternalDisposition = new Modal(modalExternalDisposition.value.modalRef,{ keyboard: false });
        await getDropdownMasterByOpt(descriptionOfChangeParams);
        await getDropdownMasterByOpt(reasonOfChangeParams);
        await getDropdownMasterByOpt(typeOfPartParams);
        await getRapidxUserByIdOpt(specialInsQcInspectorParams);
        await getCategoryAdminAccessOpt();
    })
    // === Functions
    const reloadInspector = async ()=>{
        await getRapidxUserByIdOpt(specialInsQcInspectorParams);
    }
    const reloadLqc = async ()=>{
        await getRapidxUserByIdOpt(specialInsLqcParams,);
    }
    const onChangeAdminAccess = async (selectedParams)=>{
        tblEcrByStatus.value.dt.ajax.url("api/load_method_ecr_by_status?category=Method"+"&& adminAccess="+selectedParams).draw();
        selectedAdminAccess.value = selectedParams;
    }
    const resetEcrForm = async (frmElement) => {
        for (const key in frmElement) {
            frmElement[key] = '';
        }
    };
    const btnLinkDownloadExternal = async () => {
        let params = {
            selectedId : selectedEcrsId.value,
        };
        var queryString = $.param(params);
        window.location.href="api/download_excel_by_ecrs_id?" + queryString;
    }
    const btnApprovedDisapproved = async (decision) => {
        isApprovedDisappproved.value = decision;
        modal.Approval.show();
    }
    const btnLinkViewMethodRefBefore = async (selectedMethodsId,index) => {
        console.log('selectedMethodsId',selectedMethodsId);
        console.log('index',index);
        window.open(`api/view_method_ref?methodsId=${selectedMethodsId} && index=${index} && imageType=before`, '_blank');
    }
    const btnLinkViewMethodRefAfter = async (selectedMethodsId,index) => {
        window.open(`api/view_method_ref?methodsId=${selectedMethodsId} && index=${index} && imageType=after`, '_blank');
    }
    const getMethodRefByEcrsId = async (methodsId) => {
        let apiParams = {
            methodsId : methodsId,
            ecrsId : selectedEcrsId.value,
        }
        axiosFetchData(apiParams,'api/get_method_ref_by_id',function(response){

            let data = response.data[0];
            let methodsId = data.methodsId;
            let ecrsId = data.ecrsId;
            arrOriginalFilenamesBefore.value = data.originalFilenameBefore;
            arrOriginalFilenamesAfter.value = data.originalFilenameAfter;
            arrOriginalFilenameExternalDispositions.value = data.originalFilenameExternalDisposition;
            selectedMethodsId.value = methodsId;
            selectedEcrsId.value = ecrsId;
            modal.ViewMethodRef.show();
        });
    }
    const btnAddSpecialInspection = async () => {
        frmSpecialInspection.value.ecrsId = selectedEcrsId;
        modal.SaveSpecialInspection.show();
    }
    const changeMethodRefBefore = async (event) => {
        methodRefBefore.value =  Array.from(event.target.files);
    }
    const changeMethodRefAfter = async (event) => {
        methodRefAfter.value =  Array.from(event.target.files);
    }
    const saveMethod = async () => {
        let formData = new FormData();

        //Append form data
        [
            ["ecrsId", selectedEcrsId.value],
            ["methodsId", selectedMethodsId.value],
            ["prdnAssessedBy", frmMethod.value.prdnAssessedBy],
            ["prdnCheckedBy", frmMethod.value.prdnCheckedBy],
            ["ppcAssessedBy", frmMethod.value.ppcAssessedBy],
            ["ppcCheckedBy", frmMethod.value.ppcCheckedBy],
            ["qcAssessedBy", frmMethod.value.qcAssessedBy],
            ["qcCheckedBy", frmMethod.value.qcCheckedBy],
            ["proEnggAssessedBy", frmMethod.value.proEnggAssessedBy],
            ["proEnggCheckedBy", frmMethod.value.proEnggCheckedBy],
            ["mainEnggAssessedBy", frmMethod.value.mainEnggAssessedBy],
            ["mainEnggCheckedBy", frmMethod.value.mainEnggCheckedBy],
        ].forEach(([key, value]) =>
            formData.append(key, value)
        );
        methodRefBefore.value.forEach((file, index) => {
            formData.append('methodRefBefore[]', file);
        });
        methodRefAfter.value.forEach((file, index) => {
            formData.append('methodRefAfter[]', file);
        });
        axiosSaveData(formData,'api/save_method',(response) =>{
            console.log(response);
            modal.SaveMethod.hide();
            tblEcrByStatus.value.dt.ajax.url("api/load_method_ecr_by_status?category=Method"+"&& adminAccess="+selectedAdminAccess.value).draw();
        });
    }
    const saveApproval = async (selectedId,selectedEcrsId,remarks,isApprovedDisappproved,approvalType = null) => {

        if(approvalType === 'PMIAPP'){
            let apiParams = {
                ecrsId : selectedEcrsId,
                status : isApprovedDisappproved,
                remarks : remarks,
            }
            axiosFetchData(apiParams,'api/save_pmi_internal_approval',function(response){
                tblEcrByStatus.value.dt.ajax.url("api/load_method_ecr_by_status?category=Method"+"&& adminAccess="+selectedAdminAccess.value).draw();
                modal.Approval.hide();
                modal.SaveMethod.hide();
            });
            return;
        }
        let apiParams = {
            selectedId : selectedId,
            status : isApprovedDisappproved,
            remarks : remarks,
        }
        axiosFetchData(apiParams,'api/save_method_approval',function(response){
            console.log(response);
            tblEcrByStatus.value.dt.ajax.url("api/load_method_ecr_by_status?category=Method"+"&& adminAccess="+selectedAdminAccess.value).draw();
            modal.Approval.hide();
            modal.SaveMethod.hide();
        });
    }
    const saveExternalDisposition = async () => {
        let formData = new FormData();
        externalDisposition.value.forEach((file, index) => {
            formData.append('externalDisposition[]', file);
        });
        formData.append("ecrsId", selectedEcrsId.value);

        axiosSaveData(formData,'api/save_external_disposition',(response) =>{
            modal.ExternalDisposition.hide();
            tblEcrByStatus.value.dt.ajax.url("api/load_method_ecr_by_status?category=Method"+"&& adminAccess="+selectedAdminAccess.value).draw();
        });
    }
</script>



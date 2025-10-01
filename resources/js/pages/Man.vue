<template>
    <div class="container-fluid px-4">
        <h4 class="mt-4">Man</h4>
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
                    <ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item active">Man Table</li>
                    </ol>
                    <div class="table-responsive">
                        <!-- :ajax="api/load_ecr_by_status?status=AP" -->
                        <DataTable
                            width="100%" cellspacing="0"
                            class="table mt-2"
                            ref="tblEcrByStatus"
                            :columns="ecrColumns"
                            ajax="api/load_ecr_man_by_status?category=Man"
                            :options="{
                                serverSide: true, //Serverside true will load the network
                                columnDefs:[
                                    // {orderable:false,target:[0]}
                                ]
                            }"
                        >
                            <thead>
                                <tr>
                                    <th>
                                        <font-awesome-icon class="nav-icon" icon="fa-cogs" />
                                    </th>
                                    <th style=""width="10%">Status</th>
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
    <ModalComponent icon="fa-user" modalDialog="modal-dialog modal-xl" title="Man" @add-event="" ref="modalSaveMan">
        <template #body>
            <div class="row">
                <div class="card">
                    <div class="card-body overflow-auto">
                        <DataTable
                        width="100%" cellspacing="0"
                        class="table  table-responsive mt-2"
                        ref="tblEcrDetails"
                        :columns="tblEcrDetailColumns"
                        ajax="api/load_ecr_details_by_ecr_id"
                        :options="{
                            serverSide: true, //Serverside true will load the network
                            columnDefs:[
                                // {orderable:false,target:[0]}
                            ]
                        }"
                    >
                        <thead>
                            <tr>
                                <th>
                                    <font-awesome-icon class="nav-icon" icon="fa-cogs" />
                                </th>
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
            <div class="row mt-3">
                <div class="card">
                    <div class="row mt-2">
                        <div class="col-12">
                            <!-- v-if="currentStatus === 'RUP'"          -->
                            <button @click="addManDetails()" type="button" class="btn btn-primary btn-sm mb-2" style="float: right !important;"><i class="fas fa-plus"></i> Add Man Details</button>
                        </div>
                    </div>
                    <div class="card-body overflow-auto">
                        <DataTable
                        width="100%" cellspacing="0"
                        class="table mt-2"
                        ref="tblManDetails"
                        :columns="tblManColumns"
                        ajax="api/load_man_by_ecr_id"
                        :options="{
                            serverSide: true, //Serverside true will load the network  //ecrsId
                            columnDefs:[
                                // {orderable:false,target:[0]}
                            ]
                        }"
                    >
                        <thead>
                            <tr>
                                <th>
                                    <font-awesome-icon class="nav-icon" icon="fa-cogs" />
                                </th>
                               <th> First Assign </th>
                               <th> Long Interval </th>
                               <th> Change </th>
                               <th> Process Name </th>
                               <th> Working Time </th>
                               <th> Qc Inspector /Operator </th>
                               <th> Trainer </th>
                               <th> Trainer SampleSize </th>
                               <th> Trainer Result </th>
                               <th> Lqc Supervisor </th>
                               <th> Lqc SampleSize </th>
                               <th> Lqc Result </th>
                               <th> Process Change Factor </th>
                            </tr>
                        </thead>
                        </DataTable>
                    </div>
                </div>
            </div>
            <div class="row mt-3">
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
                        <!-- <thead>
                            <tr>
                                <th>
                                    <font-awesome-icon class="nav-icon" icon="fa-cogs" />
                                </th>
                                <th> Product Detail </th>
                                <th> Lot Qty </th>
                                <th> Samples </th>
                                <th> Mod </th>
                                <th> Mod Qty </th>
                                <th> Judgement </th>
                                <th> Inspection Date </th>
                                <th> Inspector </th>
                                <th> Remarks </th>
                            </tr>
                        </thead> -->
                        </DataTable>
                    </div>
                </div>
            </div>
            <div class="row mt-3"  v-show="isModal === 'View' && currentStatus != 'PMIAPP'">
                <div class="card mb-2">
                        <h5 class="mb-0">
                            <button id="" class="btn btn-link collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseManApproverSummary" aria-expanded="true" aria-controls="collapseManApproverSummary">
                                ECR Approver Summary
                            </button>
                        </h5>
                    <div id="collapseManApproverSummary" class="collapse show" data-bs-parent="#accordionMain">
                        <div class="card-header">
                            <h5> Man Approver </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-12">
                                    <DataTable
                                        width="100%" cellspacing="0"
                                        class="table mt-2"
                                        ref="tblManApproverSummary"
                                        :columns="tblManApproverSummaryColumns"
                                        ajax="api/load_man_approver_summary_ecrs_id"
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
            <div class="row mt-3" v-show="isModal === 'View' && currentStatus === 'PMIAPP'">
            <!-- <div class="row mt-3"> -->
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
        </template>
    </ModalComponent>
    <ModalComponent icon="fa-user" modalDialog="modal-dialog modal-lg" title="Ecr Details" @add-event="saveEcrDetails()" ref="modalSaveEcrDetail">
        <template #body>
             <!-- Description of Change / Reason for Change -->
             <EcrChangeComponent :isSelectReadonly="isSelectReadonly" :frmEcrReasonRows="frmEcrReasonRows" :optDescriptionOfChange="ecrVar.optDescriptionOfChange" :optReasonOfChange="ecrVar.optReasonOfChange">
            </EcrChangeComponent>
            <div class="row d-none">
                <div class="input-group flex-nowrap mb-2 input-group-sm d-none">
                    <span class="input-group-text" id="addon-wrapping">ECR Details Id:</span>
                    <input v-model="frmEcrDetails.ecrDetailsId"  type="hidden" class="form-control form-control-lg" aria-describedby="addon-wrapping" readonly>
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
                        <button @click="reloadDropdown()" class="btn btn-outline-warning btn-sm" type="button" data-item-process="remove">
                            <font-awesome-icon class="nav-icon" icon="refresh" />
                        </button>
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
    <ModalComponent icon="fa-user" modalDialog="modal-dialog modal-lg" title="Man Details" @add-event="saveManDetails()" ref="modalSaveManDetails">
        <template #body>
            <div class="row d-none">
                <div class="input-group flex-nowrap mb-2 input-group-sm">
                    <span class="input-group-text" id="addon-wrapping">ECR Id:</span>
                    <input v-model="frmMan.ecrsId" type="text" class="form-control form-control-lg" aria-describedby="addon-wrapping" readonly>
                </div>
                <div class="input-group flex-nowrap mb-2 input-group-sm">
                    <span class="input-group-text" id="addon-wrapping">Man Id:</span>
                    <input  v-model="frmMan.manId"  type="text" class="form-control form-control-lg" aria-describedby="addon-wrapping" readonly>
                </div>
            </div>
            <div class="row">

                <div class="col-sm-6">
                    <div class="input-group flex-nowrap mb-2 input-group-sm">
                        <span class="input-group-text" id="addon-wrapping">F:</span>
                        <Multiselect
                            v-model="frmMan.firstAssign"
                            :options="commonVar.optYesNo"
                            placeholder="Select an option"
                            :searchable="true"
                            :close-on-select="true"
                        />
                    </div>
                    <div class="input-group flex-nowrap mb-2 input-group-sm">
                        <span class="input-group-text" id="addon-wrapping">L:</span>
                        <Multiselect
                            v-model="frmMan.longInterval"
                            :options="commonVar.optYesNo"
                            placeholder="Select an option"
                            :searchable="true"
                            :close-on-select="true"
                        />
                    </div>
                    <div class="input-group flex-nowrap mb-2 input-group-sm">
                        <span class="input-group-text" id="addon-wrapping">C:</span>
                        <Multiselect
                            v-model="frmMan.change"
                            :options="commonVar.optYesNo"
                            placeholder="Select an option"
                            :searchable="true"
                            :close-on-select="true"
                        />
                    </div>
                    <div class="input-group flex-nowrap mb-2 input-group-sm">
                        <span class="input-group-text" id="addon-wrapping">Process Name:</span>
                        <input v-model="frmMan.processName" type="date" class="form-control form-control-lg" aria-describedby="addon-wrapping">
                    </div>
                    <div class="input-group flex-nowrap mb-2 input-group-sm">
                        <span class="input-group-text" id="addon-wrapping">Working Time</span>
                        <input v-model="frmMan.workingTime" type="time" class="form-control form-control-lg" aria-describedby="addon-wrapping">
                    </div>
                    <div class="input-group flex-nowrap mb-2 input-group-sm">
                        <span class="input-group-text" id="addon-wrapping">Qc Inspector/ Operator:</span>
                        <Multiselect
                            v-model="frmMan.qcInspectorOperator"
                            :options="commonVar.optUserMaster"
                            placeholder="Select an option"
                            :searchable="true"
                            :close-on-select="true"
                        />
                        <button @click="reloadRapidxUserDropdown()" class="btn btn-outline-warning btn-sm" type="button" data-item-process="remove">
                            <font-awesome-icon class="nav-icon" icon="refresh" />
                        </button>
                    </div>
                    <div class="input-group flex-nowrap mb-2 input-group-sm">
                        <span class="input-group-text" id="addon-wrapping">Process Change Factor:</span>
                        <textarea v-model="frmMan.processChangeFactor" class="form-control form-control-lg" aria-describedby="addon-wrapping">
                        </textarea>
                    </div>
                 </div>
                <div class="col-sm-6">
                    <!-- Unnecessary value binding used alongside v-model. It will interfere with v-model's behavior.  v-if="currentStatus === 'RUP'"-->
                    <div  class="input-group flex-nowrap mb-2 input-group-sm">
                        <span class="input-group-text text-danger" id="addon-wrapping">Update Approver? {{ currentStatus }}</span>
                        <Multiselect
                            v-model="frmMan.isUpdateManApprover"
                            :options="commonVar.optYesNo"
                            placeholder="Select an option"
                            :searchable="true"
                            :close-on-select="true"
                        />
                    </div>
                    <div class="input-group flex-nowrap mb-2 input-group-sm">
                        <span class="input-group-text" id="addon-wrapping">Trainers:</span>
                        <Multiselect
                            v-model="frmMan.trainer"
                            :options="commonVar.optUserMaster"
                            placeholder="Select an option"
                            :searchable="true"
                            :close-on-select="true"
                        />
                        <button @click="reloadRapidxUserDropdown()" class="btn btn-outline-warning btn-sm" type="button" data-item-process="remove">
                            <font-awesome-icon class="nav-icon" icon="refresh" />
                        </button>
                    </div>
                    <div class="input-group flex-nowrap mb-2 input-group-sm">
                        <span class="input-group-text" id="addon-wrapping">Trainer Sample Size:</span>
                        <input v-model="frmMan.trainerSampleSize" type="number" class="form-control form-control-lg" aria-describedby="addon-wrapping">
                    </div>
                    <div class="input-group flex-nowrap mb-2 input-group-sm">
                        <span class="input-group-text" id="addon-wrapping">Trainer Result:</span>
                        <Multiselect
                            v-model="frmMan.trainerResult"
                            :options="commonVar.optResult"
                            placeholder="Select an option"
                            :searchable="true"
                            :close-on-select="true"
                        />
                    </div>
                    <div class="input-group flex-nowrap mb-2 input-group-sm">
                        <span class="input-group-text" id="addon-wrapping">LQC Supervisor:</span>
                        <Multiselect
                            v-model="frmMan.lqcSupervisor"
                            :options="commonVar.optUserMaster"
                            placeholder="Select an option"
                            :searchable="true"
                            :close-on-select="true"
                        />
                        <button @click="reloadRapidxUserDropdown()" class="btn btn-outline-warning btn-sm" type="button" data-item-process="remove">
                            <font-awesome-icon class="nav-icon" icon="refresh" />
                        </button>
                    </div>
                    <div class="input-group flex-nowrap mb-2 input-group-sm">
                        <span class="input-group-text" id="addon-wrapping">LQC Sample Size:</span>
                        <input v-model="frmMan.lqcSampleSize" type="number" class="form-control form-control-lg" aria-describedby="addon-wrapping">
                    </div>
                    <div class="input-group flex-nowrap mb-2 input-group-sm">
                        <span class="input-group-text" id="addon-wrapping">LQC Result:</span>
                        <Multiselect
                            v-model="frmMan.lqcResult"
                            :options="commonVar.optJudgment"
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
    <ModalComponent icon="fa-user" modalDialog="modal-dialog modal-lg" title="Man Checklist" ref="modalManChecklist">
        <template #body>
            <div class="row mt-3">
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
                                        ref="tblManChecklist"
                                        :columns="tblManChecklistColumns"
                                        ajax="api/load_man_checklist?dropdown_masters_id=7"
                                        :options="{
                                            searching:false,
                                            paging:false,
                                            serverSide: true, //Serverside true will load the network
                                            columnDefs:[
                                                {
                                                    orderable:false,target:[0,1],
                                                }
                                            ]
                                        }"
                                    >
                                        <thead>
                                            <tr>
                                                <th style="width:80%">Requirement</th>
                                                <th style="width:20%">
                                                    <font-awesome-icon class="nav-icon" icon="fa-cogs" />
                                                </th>
                                            </tr>
                                        </thead>
                                    </DataTable>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Material -->
                <div class="card mb-2">
                        <h5 class="mb-0">
                            <button id="" class="btn btn-link collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseMat" aria-expanded="true" aria-controls="collapseMat">
                                Material
                            </button>
                        </h5>
                    <div id="collapseMat" class="collapse show" data-bs-parent="#accordionMain">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-12">
                                    <DataTable
                                        width="100%" cellspacing="0"
                                        class="table mt-2"
                                        ref="tblMatChecklist"
                                        :columns="tblManChecklistColumns"
                                        ajax="api/load_man_checklist?dropdown_masters_id=8"
                                        :options="{
                                            searching:false,
                                            paging:false,
                                            serverSide: true, //Serverside true will load the network
                                            ordering:false,
                                        }"
                                    >
                                        <thead>
                                            <tr>
                                                <th style="width:80%">Requirement</th>
                                                <th style="width:20%">
                                                    <font-awesome-icon class="nav-icon" icon="fa-cogs" />
                                                </th>
                                            </tr>
                                        </thead>
                                    </DataTable>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Method -->
                <div class="card mb-2">
                        <h5 class="mb-0">
                            <button id="" class="btn btn-link collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseMethod" aria-expanded="true" aria-controls="collapseMethod">
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
                                        ref="tblMatChecklist"
                                        :columns="tblManChecklistColumns"
                                        ajax="api/load_man_checklist?dropdown_masters_id=9"
                                        :options="{
                                            searching:false,
                                            paging:false,
                                            serverSide: true, //Serverside true will load the network
                                            ordering:false,
                                        }"
                                    >
                                        <thead>
                                            <tr>
                                                <th style="width:80%">Requirement</th>
                                                <th style="width:20%">
                                                    <font-awesome-icon class="nav-icon" icon="fa-cogs" />
                                                </th>
                                            </tr>
                                        </thead>
                                    </DataTable>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Machine -->
                <div class="card mb-2">
                        <h5 class="mb-0">
                            <button id="" class="btn btn-link collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseMachine" aria-expanded="true" aria-controls="collapseMachine">
                                Machine
                            </button>
                        </h5>
                    <div id="collapseMachine" class="collapse show" data-bs-parent="#accordionMain">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-12">
                                    <DataTable
                                        width="100%" cellspacing="0"
                                        class="table mt-2"
                                        ref="tblMatChecklist"
                                        :columns="tblManChecklistColumns"
                                        ajax="api/load_man_checklist?dropdown_masters_id=10"
                                        :options="{
                                            searching:false,
                                            paging:false,
                                            serverSide: true, //Serverside true will load the network
                                            ordering:false,
                                        }"
                                    >
                                        <thead>
                                            <tr>
                                                <th style="width:80%">Requirement</th>
                                                <th style="width:20%">
                                                    <font-awesome-icon class="nav-icon" icon="fa-cogs" />
                                                </th>
                                            </tr>
                                        </thead>
                                    </DataTable>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Quality -->
                <div class="card mb-2">
                        <h5 class="mb-0">
                            <button id="" class="btn btn-link collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseQuality" aria-expanded="true" aria-controls="collapseQuality">
                                Quality of the Product
                            </button>
                        </h5>
                    <div id="collapseQuality" class="collapse show" data-bs-parent="#accordionMain">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-12">
                                    <DataTable
                                        width="100%" cellspacing="0"
                                        class="table mt-2"
                                        ref="tblMatChecklist"
                                        :columns="tblManChecklistColumns"
                                        ajax="api/load_man_checklist?dropdown_masters_id=11"
                                        :options="{
                                            searching:false,
                                            paging:false,
                                            serverSide: true, //Serverside true will load the network
                                            ordering:false,
                                        }"
                                    >
                                        <thead>
                                            <tr>
                                                <th style="width:80%">Requirement</th>
                                                <th style="width:20%">
                                                    <font-awesome-icon class="nav-icon" icon="fa-cogs" />
                                                </th>
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
            <button type="button" id= "closeBtn" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
            <!-- <button type="submit" class="btn btn-success btn-sm"><font-awesome-icon class="nav-icon" icon="fas fa-save" />&nbsp;     Save</button> -->
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
            <button @click = "saveApproval(currentManDetailsId,selectedEcrsId,approvalRemarks,isApprovedDisappproved,currentStatus)" type="button" class="btn btn-success btn-sm"><font-awesome-icon class="nav-icon" icon="fas fa-save" />&nbsp; Save</button>
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
    import {ref , onMounted,reactive, toRef,watch} from 'vue';
    import ModalComponent from '../../js/components/ModalComponent.vue';
    import ModalSpecialInspectionComponent from '../components/ModalSpecialInspectionComponent.vue';
    import EcrChangeComponent from '../components/EcrChangeComponent.vue';
    import useEcr from '../../js/composables/ecr.js';
    import useMan from '../../js/composables/man.js';
    import useSettings from '../../js/composables/settings.js'
    import useForm from '../../js/composables/utils/useForm.js'
    import DataTable from 'datatables.net-vue3';
    import DataTablesCore from 'datatables.net-bs5';
    import useCommon from '../../js/composables/common.js';

    DataTable.use(DataTablesCore)
    const { axiosSaveData } = useForm(); // Call the useFetch function

    //composables export function
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
        frmMan,
    } = useMan();
    const {
        modal,
        commonVar,
        tblSpecialInspection,
        tblSpecialInspectionColumns,
        modalSaveSpecialInspection,
        specialInsQcInspectorParams,
        specialInsLqcParams,
        frmSpecialInspection,
        saveSpecialInspection,
        getCurrentApprover,
        getCurrentPmiInternalApprover,
        getCategoryAdminAccessOpt,
        resetEcrForm,
    } = useCommon();
    const {
        getRapidxUserByIdOpt,
    } = useSettings();

    //ref state
    const tblEcrByStatus = ref(null);
    const tblManChecklist = ref(null);
    const tblMatChecklist = ref(null);
    const isSelectReadonly  = ref(true);
    const currentStatus = ref('Edit');
    const selectedEcrsId = ref(null);
    const selectedAdminAccess = ref(null);
    const tblManDetails = ref(null);
    const modalSaveMan = ref(null);
    const modalSaveEcrDetail = ref(null);
    const modalSaveManDetails = ref(null);
    const modalManChecklist = ref(null);
    const modalApproval = ref(null);
    const modalEcrRequirements = ref(null);
    const currentManDetailsId = ref(null);
    const tblPmiInternalApproverSummary = ref(null);
    const tblManApproverSummary = ref(null);

    const isModal = ref(null);
    const isApprovedDisappproved  = ref(true);
    const approvalRemarks = ref(null);

    const ecrColumns = [
        {   data: 'get_actions',
            orderable: false,
            searchable: false,
            createdCell(cell){
                let btnGetEcrId = cell.querySelector('#btnGetEcrId');
                if(btnGetEcrId != null){
                    btnGetEcrId.addEventListener('click',function(){
                        let ecrsId = this.getAttribute('ecrs-id');
                        let manDetailsId = this.getAttribute('man-details-id');
                        selectedEcrsId.value = ecrsId;
                        currentManDetailsId.value = manDetailsId;
                        frmMan.value.ecrsId = ecrsId;

                        isModal.value = 'Edit';
                        tblEcrDetails.value.dt.ajax.url("api/load_ecr_details_by_ecr_id?ecr_id="+ecrsId).draw();
                        tblManDetails.value.dt.ajax.url("api/load_man_by_ecr_id?ecrsId="+ecrsId).draw();
                        tblSpecialInspection.value.dt.ajax.url("api/load_special_inspection_by_ecr_id?ecrsId="+ecrsId).draw()

                        modal.SaveMan.show();
                    });

                }
                let btnViewManById = cell.querySelector('#btnViewManById');
                if(btnViewManById != null){
                    btnViewManById.addEventListener('click',function(){
                        let ecrsId = this.getAttribute('ecrs-id');
                        let manStatus = this.getAttribute('man-status');
                        let manDetailsId = this.getAttribute('man-details-id');

                        let manApproverParams = {
                            selectedId : ecrsId,
                            approvalType : 'manApproval'
                        }
                        let pmiApproverParams = {
                            selectedId : ecrsId,
                            approvalType : 'pmiApproval'
                        }
                        selectedEcrsId.value = ecrsId;
                        frmMan.value.ecrsId = ecrsId;
                        currentManDetailsId.value = manDetailsId;
                        frmSpecialInspection.value.ecrsId = ecrsId;
                        currentStatus.value = manStatus;
                        isModal.value = 'View';
                        tblEcrDetails.value.dt.ajax.url("api/load_ecr_details_by_ecr_id?ecr_id="+ecrsId).draw();
                        tblManDetails.value.dt.ajax.url("api/load_man_by_ecr_id?ecrsId="+ecrsId).draw();
                        tblSpecialInspection.value.dt.ajax.url("api/load_special_inspection_by_ecr_id?ecrsId="+ecrsId).draw()
                        if( manStatus != 'PMIAPP'){
                            getCurrentApprover(manApproverParams);
                            tblManApproverSummary.value.dt.ajax.url("api/load_man_approver_summary_ecrs_id?ecrsId="+ecrsId).draw();
                        }
                        if( manStatus === 'PMIAPP'){
                            getCurrentApprover(pmiApproverParams);
                            tblPmiInternalApproverSummary.value.dt.ajax.url("api/load_pmi_internal_approval_summary?ecrsId="+ecrsId).draw()
                        }
                        modal.SaveMan.show();

                        //Load ECR Requirement by Category and Ecrs Id
                        tblEcrManRequirements.value.dt.ajax.url("api/load_ecr_requirements?category=1&ecrsId="+ecrsId).draw();
                        tblEcrMatRequirements.value.dt.ajax.url("api/load_ecr_requirements?category=2&ecrsId="+ecrsId).draw();
                        tblEcrMachineRequirements.value.dt.ajax.url("api/load_ecr_requirements?category=3&ecrsId="+ecrsId).draw();
                        tblEcrMethodRequirements.value.dt.ajax.url("api/load_ecr_requirements?category=4&ecrsId="+ecrsId).draw();
                        tblEcrEnvironmentRequirements.value.dt.ajax.url("api/load_ecr_requirements?category=5&ecrsId="+ecrsId).draw();
                        tblEcrOthersRequirements.value.dt.ajax.url("api/load_ecr_requirements?category=6&ecrsId="+ecrsId).draw();
                        modalEcr.EcrRequirements.show();
                    });
                }
            }
        } ,
        {   data: 'get_status'} ,
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
    const tblManColumns = [
        {   data: 'get_actions',
            orderable: false,
            searchable: false,
            createdCell(cell){
                let btnManDetailsId = cell.querySelector('#btnManDetailsId');
                if(btnManDetailsId != null){
                    btnManDetailsId.addEventListener('click',function(){
                        let manDetailsId = this.getAttribute('man-details-id');
                        let ecrsId = this.getAttribute('ecrs-id');
                        selectedEcrsId.value = ecrsId;
                        frmMan.value.ecrsId = ecrsId;
                        getManById(manDetailsId);
                        modal.SaveManDetails.show();
                    });
                }
                let btnManChecklistId = cell.querySelector('#btnManChecklistId');
                if(btnManChecklistId != null){
                    btnManChecklistId.addEventListener('click',function(){
                        let manDetailsId = this.getAttribute('man-details-id');
                        let ecrsId = this.getAttribute('ecrs-id');
                        selectedEcrsId.value = ecrsId;
                        frmMan.value.ecrsId = ecrsId;
                        currentManDetailsId.value = manDetailsId;
                        tblManChecklist.value.dt.ajax.url("api/load_man_checklist?dropdown_masters_id=7 && manDetailsId="+manDetailsId).draw();
                        tblMatChecklist.value.dt.ajax.url("api/load_man_checklist?dropdown_masters_id=8 && manDetailsId="+manDetailsId).draw();
                        tblMatChecklist.value.dt.ajax.url("api/load_man_checklist?dropdown_masters_id=9 && manDetailsId="+manDetailsId).draw();
                        tblMatChecklist.value.dt.ajax.url("api/load_man_checklist?dropdown_masters_id=10 && manDetailsId="+manDetailsId).draw();
                        tblMatChecklist.value.dt.ajax.url("api/load_man_checklist?dropdown_masters_id=11 && manDetailsId="+manDetailsId).draw();
                        modal.ManChecklist.show();
                    });
                }
            }
        } ,
        {   data: 'first_assign'} ,
        {   data: 'long_interval'} ,
        {   data: 'change'} ,
        {   data: 'process_name'} ,
        {   data: 'working_time'} ,
        {   data: 'qc_inspector_operator'} ,
        {   data: 'trainer'} ,
        {   data: 'trainer_sample_size'} ,
        {   data: 'trainer_result'} ,
        {   data: 'lqc_supervisor'} ,
        {   data: 'lqc_sample_size'} ,
        {   data: 'lqc_result'} ,
        {   data: 'process_change_factor'} ,
    ];
    const tblManChecklistColumns = [
        {   data: 'dropdown_masters_details'} ,
        {   data: 'get_actions',
            createdCell(cell){
                let btnChangeManChecklistDecision = cell.querySelector('#btnChangeManChecklistDecision');
                if(btnChangeManChecklistDecision != null){
                    btnChangeManChecklistDecision.addEventListener('change',function(){
                        let manChecklistsId = this.getAttribute('man-checklists-id');
                        let manChecklistValue = this.value;
                        let dropdownMasterDetailsId = this.getAttribute('dropdown-master-details-id');
                        let params = {
                            manChecklistsId : manChecklistsId,
                            manChecklistValue : manChecklistValue,
                            dropdownMasterDetailsId : dropdownMasterDetailsId,
                            btnChangeManChecklistDecisionClass: this.classList,
                        }
                        changeManChecklistDecision(params);
                    });
                }
            }
        }
    ];

    const tblPmiInternalApproverSummaryColumns = [
        {   data: 'get_count'} ,
        {   data: 'get_role'} ,
        {   data: 'get_approver_name'} ,
        {   data: 'remarks'},
        {   data: 'get_status'} ,
    ];
    const tblManApproverSummaryColumns = [
        {   data: 'get_count'} ,
        {   data: 'get_role'} ,
        {   data: 'get_approver_name'} ,
        {   data: 'remarks'},
        {   data: 'get_status'} ,
    ];

    const trainerParams = {
        globalVar: commonVar.optUserMaster,
        formModel: toRef(frmMan.value,'qcInspectorOperator'),
        selectedVal: '',
    };
    const qcSupervisor1Params = {
        globalVar: commonVar.optUserMaster,
        formModel: toRef(frmMan.value,'qcInspectorOperator'),
        selectedVal: '',
    };

    onMounted( async ()=>{
        modal.SaveMan = new Modal(modalSaveMan.value.modalRef,{ keyboard: false });
        modalEcr.SaveEcrDetail = new Modal(modalSaveEcrDetail.value.modalRef,{ keyboard: false });
        modal.SaveManDetails = new Modal(modalSaveManDetails.value.modalRef,{ keyboard: false });
        modal.ManChecklist = new Modal(modalManChecklist.value.modalRef,{ keyboard: false });
        modal.SaveSpecialInspection = new Modal(modalSaveSpecialInspection.value.modalRef,{ keyboard: false });
        modal.Approval = new Modal(modalApproval.value.modalRef,{ keyboard: false });
        modalEcr.EcrRequirements = new Modal(modalEcrRequirements.value.modalRef,{ keyboard: false });
        modalSaveManDetails.value.modalRef.addEventListener('hidden.bs.modal', event => {
            resetEcrForm(frmMan.value);
        })

        modalSaveSpecialInspection.value.modalRef.addEventListener('hidden.bs.modal', event => {
            frmSpecialInspection.value.ecrsId;
        });
        modalSaveEcrDetail.value.modalRef.addEventListener('hidden.bs.modal', event => {
            resetEcrForm(frmEcrDetails.value);
        });
        await getDropdownMasterByOpt(descriptionOfChangeParams);
        await getDropdownMasterByOpt(reasonOfChangeParams);
        await getDropdownMasterByOpt(typeOfPartParams);
        await getRapidxUserByIdOpt(specialInsQcInspectorParams);
        await getCategoryAdminAccessOpt();
    })

    watch(
        () => commonVar.rapidxUserDeptGroup,
        async (newVal) => {
            if (!newVal) return;

            let rapidxUserOpt = {
                globalVar: commonVar.optUserMaster,

            };
            await getRapidxUserByIdOpt(rapidxUserOpt);
        }
    )
    // === Functions
    const reloadInspector = async ()=>{
        await getRapidxUserByIdOpt(specialInsQcInspectorParams);
    }
    const reloadLqc = async ()=>{
        await getRapidxUserByIdOpt(specialInsLqcParams,);
    }
    const reloadDropdown = async () => {
        await getDropdownMasterByOpt(typeOfPartParams);
    }
    const reloadRapidxUserDropdown = async () => {
        let rapidxUserOpt = {
            globalVar: commonVar.optUserMaster,
        };
        await getRapidxUserByIdOpt(rapidxUserOpt);
    }
    const onChangeAdminAccess = async (selectedParams)=>{
        tblEcrByStatus.value.dt.ajax.url("api/load_ecr_man_by_status?category=Man"+"&& adminAccess="+selectedParams).draw();
        selectedAdminAccess.value = selectedParams;
    }
    const btnApprovedDisapproved = async (decision) => {
        isApprovedDisappproved.value = decision;
        modal.Approval.show();
    }
    const addManDetails = async () => {
        frmMan.value.ecrsId = selectedEcrsId.value;
        modal.SaveManDetails.show();
    }
    const btnAddSpecialInspection = async () => {
        frmSpecialInspection.value.ecrsId = selectedEcrsId.value;
        modal.SaveSpecialInspection.show();
    }
    const getManById = async (manId) =>
    {
        let apiParams = {
            manId : manId
        }
        axiosFetchData(apiParams,'api/get_man_by_id',function(response){
            let data = response.data;
            let man = data.man;
            frmMan.value.manId = man.id;
            frmMan.value.firstAssign = man.first_assign;
            frmMan.value.longInterval = man.long_interval;
            frmMan.value.change = man.change;
            frmMan.value.processName = man.process_name;
            frmMan.value.workingTime = man.working_time;
            frmMan.value.qcInspectorOperator = man.qc_inspector_operator;
            frmMan.value.trainer = man.trainer;
            frmMan.value.trainerSampleSize = man.trainer_sample_size;
            frmMan.value.trainerResult = man.trainer_result;
            frmMan.value.lqcSupervisor = man.lqc_supervisor;
            frmMan.value.lqcSampleSize = man.lqc_sample_size;
            frmMan.value.lqcResult = man.lqc_result;
            frmMan.value.processChangeFactor = man.process_change_factor;
        });
    }
    const changeManChecklistDecision = async (params)=>{
        let apiParams = {
            manChecklistsId : params.manChecklistsId,
            manChecklistValue : params.manChecklistValue,
            dropdownMasterDetailsId : params.dropdownMasterDetailsId,
            manDetailsId : currentManDetailsId.value,
        }
        console.log(apiParams);

        axiosFetchData(apiParams,'api/man_checklist_decision_change',function(response){
            params.btnChangeManChecklistDecisionClass.remove("is-invalid");
            tblManChecklist.value.dt.ajax.url("api/load_man_checklist?dropdown_masters_id=7 && manDetailsId="+currentManDetailsId.value).draw();
            tblMatChecklist.value.dt.ajax.url("api/load_man_checklist?dropdown_masters_id=8 && manDetailsId="+currentManDetailsId.value).draw();
        });
    }
    const saveManDetails = async () => {
    //     alert(frmMan.value.isUpdateManApprover);
    //         return;
        if(frmMan.value.isUpdateManApprover === 'YES'){
            Swal.fire({
                title: 'Confirmation',
                text: 'Please double check your details, the Approval will RESET !',
                icon: 'warning',
                allowOutsideClick: false,
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes'
            }).then((result) => {
                if (result.isConfirmed) {
                    saveManDetailsWithConfirmation();
                    tblManApproverSummary.value.dt.ajax.url("api/load_man_approver_summary_ecrs_id?ecrsId="+selectedEcrsId.value).draw();
                }
            })
            return;
        }
        await saveManDetailsWithConfirmation();
    }
    const saveManDetailsWithConfirmation = async () => {
        let formData = new FormData();
        //Append form data
        [
            ["ecrs_id", frmMan.value.ecrsId],
            ["man_id", frmMan.value.manId ?? ''],
            ["is_update_man_approver", frmMan.value.isUpdateManApprover],
            ["first_assign", frmMan.value.firstAssign],
            ["long_interval", frmMan.value.longInterval],
            ["change", frmMan.value.change],
            ["process_name", frmMan.value.processName],
            ["working_time", frmMan.value.workingTime],
            ["trainer", frmMan.value.trainer],
            ["qc_inspector_operator", frmMan.value.qcInspectorOperator],
            ["trainer_sample_size", frmMan.value.trainerSampleSize],
            ["trainer_result", frmMan.value.trainerResult],
            ["lqc_supervisor", frmMan.value.lqcSupervisor],
            ["lqc_sample_size", frmMan.value.lqcSampleSize],
            ["lqc_result", frmMan.value.lqcResult],
            ["process_change_factor", frmMan.value.processChangeFactor],
        ].forEach(([key, value]) =>
            formData.append(key, value)
        );
        axiosSaveData(formData,'api/save_man', (response) =>{
            modal.SaveManDetails.hide();
            tblManDetails.value.dt.ajax.url("api/load_man_by_ecr_id?ecrsId="+frmMan.value.ecrsId).draw();
        });

    }
    const saveApproval = async (selectedId=null,selectedEcrsId,remarks,isApprovedDisappproved,approvalType = null) => {

        if(approvalType === 'PMIAPP'){ //Based on Ecr Id
            let apiParams = {
                ecrsId : selectedEcrsId,
                status : isApprovedDisappproved,
                remarks : remarks,
            }
            axiosFetchData(apiParams,'api/save_pmi_internal_approval',function(response){
                modal.Approval.hide();
                modal.SaveMachine.hide();
                tblEcrByStatus.value.dt.draw();
            });
            return;
        }
        let apiParams = { //Based on Ecr Id because the APPROVED ECR save the man approval by ECR id
            selectedId : selectedEcrsId,
            status : isApprovedDisappproved,
            remarks : remarks,
        }
        axiosFetchData(apiParams,'api/save_man_approval',function(response){
            tblEcrByStatus.value.dt.ajax.url("api/load_ecr_man_by_status?category=Man"+"&& adminAccess="+selectedAdminAccess.value).draw();
            modal.Approval.hide();
            modal.SaveMan.hide();
        });
    }
</script>



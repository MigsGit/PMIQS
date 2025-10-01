<template>
    <div class="container-fluid px-4">
        <h4 class="mt-4">ENGINEERING CHANGE REQUEST</h4>
        <div class="row">
            <div class="col-md-3 offset-md-4">
                <Multiselect
                        placeholder="-Select an Option-"
                    :close-on-select="true"
                    :searchable="true"
                    :options="commonVar.optAdminAccess"
                    @change="onChangeAdminAccess($event)"
                />
            </div>
        </div>
        <div class="card mt-5" style="width: 100%;">
            <ul class="nav nav-tabs" id="myTab" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active .menuTab" id="Pending-tab" data-bs-toggle="tab" href="#menu1" role="tab" aria-controls="menu1" aria-selected="true">For Approval</a>
                </li>
                <li class="nav-item d-none">
                <!-- <li v-show="commonVar.rapidxUserDeptGroup ==='ISS' || commonVar.rapidxUserDeptGroup ==='QA'" class="nav-item"> -->
                    <a class="nav-link .menuTab" id="Completed-tab" data-bs-toggle="tab" href="#menu2" role="tab" aria-controls="menu2" aria-selected="false">QA Approval</a>
                </li>
            </ul>
            <div class="tab-content mt-2" id="myTabContent">
                <div class="tab-pane fade show active" id="menu1" role="tabpanel" aria-labelledby="menu1-tab">
                    <div class="container-fluid px-4">
                        <button @click="btnEcr"type="button" class="btn btn-primary btn-sm mb-2" style="float: right !important;"><i class="fas fa-plus"></i> Create ECR</button>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item active">Engineering Change Request</li>
                        </ol>
                        <div class="table-responsive">
                        <DataTable
                            width="100%" cellspacing="0"
                            class="table mt-2"
                            ref="tblEcr"
                            :columns="tblEcrColumns"
                            ajax="api/load_ecr?status=IA,DIS,QA"
                            :options="{
                                serverSide: true, //Serverside true will load the network
                                columnDefs:[
                                    {orderable:false,target:[0]}
                                ],
                                language: {
                                    zeroRecords: 'No data available',
                                    emptyTable: 'No data available'
                                }
                            }"
                        >
                            <thead>
                                <tr>
                                    <th style=""width="5%">Action</th>
                                    <th style=""width="10%">Status</th>
                                    <th style=""width="10%">Attachment</th>
                                    <th style=""width="10%">ECR Ctrl No.</th>
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
                <div v-show="commonVar.rapidxUserDeptGroup ==='ISS' || commonVar.rapidxUserDeptGroup ==='QA'" class="tab-pane fade" id="menu2" role="tabpanel" aria-labelledby="menu1-tab">
                    <div class="container-fluid px-4">
                            <ol class="breadcrumb mb-4">
                                <li class="breadcrumb-item active">QA Approval</li>
                            </ol>
                            <div class="table-responsive">
                            <DataTable
                                width="100%" cellspacing="0"
                                class="table mt-2"
                                ref="tblEcrQa"
                                :columns="tblEcrColumns"
                                ajax="api/load_ecr?status=QA"
                                :options="{
                                    serverSide: true, //Serverside true will load the network
                                    columnDefs:[
                                        {orderable:false,target:[0]}
                                    ],
                                    language: {
                                        zeroRecords: 'No data available',
                                        emptyTable: 'No data available'
                                    }
                                }"
                            >
                                <thead>
                                    <tr>
                                        <th style=""width="5%">Action</th>
                                        <th style=""width="10%">Status</th>
                                        <th style=""width="10%">Attachment</th>
                                        <th style=""width="10%">ECR Ctrl No.</th>
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
    <ModalComponent icon="fa-user" modalDialog="modal-dialog modal-lg" :title="modalTitle+' '+'ECR'" @add-event="frmSaveEcr()" ref="modalSaveEcr">
        <template #body>
                <div class="row d-none">
                    <div class="input flex-nowrap mb-2 input-group-sm">
                        <input  v-model="frmEcr.ecrsId" type="text" class="form-control form-control" aria-describedby="addon-wrapping" readonly>
                    </div>
                    <div class="input flex-nowrap mb-2 input-group-sm">
                        <input  v-model="frmEcr.departmentGroup" type="text" class="form-control form-control" aria-describedby="addon-wrapping" readonly>
                    </div>

                </div>
                <div class="row">
                    <div class="col-sm-6">
                        <div class="input-group flex-nowrap mb-2 input-group-sm">
                            <span class="input-group-text" id="addon-wrapping">Ecr Ctrl No:</span>
                            <input v-model="frmEcr.ecrNo" type="text" class="form-control form-control" aria-describedby="addon-wrapping" readonly>
                        </div>
                        <div class="input-group flex-nowrap mb-2 input-group-sm">
                            <span class="input-group-text" id="addon-wrapping">Category:</span>
                            <select v-model="frmEcr.category" class="form-select form-select-sm" aria-describedby="addon-wrapping">
                                <option value="" selected disabled>Select</option>
                                <option value="Man">Man</option>
                                <option value="Material">Material</option>
                                <option value="Machine">Machine</option>
                                <option value="Method">Method</option>
                                <option value="Environment">Environment</option>
                            </select>
                        </div>
                        <div class="input-group flex-nowrap mb-2 input-group-sm">
                            <span class="input-group-text" id="addon-wrapping">Customer Name:</span>
                            <input v-model="frmEcr.customerName" type="text" class="form-control form-control" aria-describedby="addon-wrapping">
                        </div>
                        <div class="input-group flex-nowrap mb-2 input-group-sm">
                            <span class="input-group-text" id="addon-wrapping">Part Name:</span>
                            <input v-model="frmEcr.partName" type="text" class="form-control" aria-describedby="addon-wrapping">
                        </div>
                        <div class="input-group flex-nowrap mb-2 input-group-sm">
                            <span class="input-group-text" id="addon-wrapping">Product Line:</span>
                            <input v-model="frmEcr.productLine" type="text" class="form-control" aria-describedby="addon-wrapping">
                        </div>
                        <div class="input-group flex-nowrap mb-2 input-group-sm">
                            <span class="input-group-text" id="addon-wrapping">Section:</span>
                            <input v-model="frmEcr.section" type="text" class="form-control" aria-describedby="addon-wrapping">
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="input-group flex-nowrap mb-2 input-group-sm">
                            <span class="input-group-text" id="addon-wrapping">Internal / External:</span>
                            <select v-model="frmEcr.internalExternal" class="form-select form-select-sm" aria-describedby="addon-wrapping">
                                <option value="" selected disabled>Select</option>
                                <option value="Internal">Internal</option>
                                <option value="External">External</option>
                            </select>
                        </div>
                        <div class="input-group flex-nowrap mb-2 input-group-sm">
                            <span class="input-group-text" id="addon-wrapping">Part Number:</span>
                            <input v-model="frmEcr.partNumber" type="text" class="form-control" aria-describedby="addon-wrapping">
                        </div>
                        <div class="input-group flex-nowrap mb-2 input-group-sm">
                            <span class="input-group-text" id="addon-wrapping">Device Name:</span>
                            <input v-model="frmEcr.deviceName" type="text" class="form-control" aria-describedby="addon-wrapping">
                        </div>
                        <div class="input-group flex-nowrap mb-2 input-group-sm">
                            <span class="input-group-text" id="addon-wrapping">Customer EC No. (If any):</span>
                            <input v-model="frmEcr.customerEcNo" type="text" class="form-control" aria-describedby="addon-wrapping">
                        </div>
                        <div class="input-group flex-nowrap mb-2 input-group-sm">
                            <span class="input-group-text" id="addon-wrapping">Date of Request:</span>
                            <input v-model="frmEcr.dateOfRequest" type="date" class="form-control" aria-describedby="addon-wrapping">
                        </div>
                        <div class="input-group flex-nowrap mb-2 input-group-sm">
                            <span class="input-group-text" id="addon-wrapping">Attachment (Optional)</span>
                            <input @change="changeEcrRef" multiple type="file" accept=".pdf" class="form-control form-control-lg" aria-describedby="addon-wrapping">
                        </div>
                    </div>
                </div>
                  <!-- Others Disposition -->
                  <div class="card mb-2 d-none">
                        <h5 class="mb-0">
                            <button class="btn btn-link" type="button" data-bs-toggle="collapse" data-bs-target="#collapseExternal" aria-expanded="true" aria-controls="collapseExternal">
                                External Field
                            </button>
                        </h5>
                    <div id="collapseExternal" class="collapse show" data-bs-parent="#accordionMain">
                        <div class="card-body shadow">
                            <div class="row">
                                <div class="col-12">
                                    <div class="input-group flex-nowrap mb-2 input-group-sm">
                                        <span class="input-group-text" id="addon-wrapping">Document Affected:</span>
                                        <Multiselect
                                            v-model="frmEcr.documentAffectedExternal"
                                            :close-on-select="true"
                                            :searchable="true"
                                            :options="ecrVar.documentAffectedExternal"
                                            :disabled="isSelectReadonly"
                                        />
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="input-group flex-nowrap mb-2 input-group-sm">
                                        <span class="input-group-text" id="addon-wrapping">Target of Implementation:</span>
                                        <input type="date" class="form-control" aria-describedby="addon-wrapping">
                                    </div>
                                    <div class="input-group flex-nowrap mb-2 input-group-sm">
                                        <span class="input-group-text" id="addon-wrapping">Actual Sample Attached:</span>
                                        <input type="text" class="form-control" aria-describedby="addon-wrapping">
                                        <input type="text" class="form-control" aria-describedby="addon-wrapping" placeholder="Qty(pcs.)">
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="input-group flex-nowrap mb-2 input-group-sm">
                                        <span class="input-group-text" id="addon-wrapping">With Attachment:</span>
                                        <input type="date" class="form-control" aria-describedby="addon-wrapping">
                                    </div>
                                    <div class="input-group flex-nowrap mb-2 input-group-sm">
                                        <span class="input-group-text" id="addon-wrapping">Title of Attachment:</span>
                                        <input type="text" class="form-control" aria-describedby="addon-wrapping">
                                    </div>
                                </div>
                                <div class="col-12">
                                    <!-- @click="btnAddEcrOtherDispoRows()"  -->
                                    <button type="button" class="btn btn-primary btn-sm mb-2" style="float: right !important;"><i class="fas fa-plus"></i> Add Document Affected</button>
                                </div>
                                <div class="col-12">
                                    <table class="table table-responsive">
                                        <thead>
                                            <tr>
                                            <th scope="col">#</th>
                                            <th scope="col" style="width: 75%;"> Document Affected</th>
                                            <th scope="col">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- <tr  v-for="(frmEcrOtherDispoRow, index) in frmEcrOtherDispoRows" :key="frmEcrOtherDispoRow.index"> -->
                                            <tr>
                                                <td>
                                                   <!-- {{ index+1 }} -->
                                                   1
                                                </td>
                                                <td>
                                                    <input type="text" class="form-control" aria-describedby="addon-wrapping">
                                                </td>
                                                <td>
                                                    <!-- @click="btnRemoveEcrOtherDispoRows(index)" -->
                                                    <button  class="btn btn-outline-danger btn-sm" type="button" data-item-process="add">
                                                        <font-awesome-icon class="nav-icon" icon="fas fa-trash" />
                                                    </button>

                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <EcrChangeComponent :isSelectReadonly="isSelectReadonly" @remove-ecr-reason-rows-event="removeEcrReasonRows(index)" @add-ecr-reason-rows-event="addEcrReasonRows()" :frmEcrReasonRows="frmEcrReasonRows"   @reload-ecr-requirements="reloadEcrRequirements()" :optDescriptionOfChange="ecrVar.optDescriptionOfChange" :optReasonOfChange="ecrVar.optReasonOfChange">
                </EcrChangeComponent>
                <!-- Others Disposition -->
                <div v-show="isSelectReadonly === false" class="card mb-2">
                        <h5 class="mb-0">
                            <button class="btn btn-link" type="button" data-bs-toggle="collapse" data-bs-target="#collapse2" aria-expanded="true" aria-controls="collapse2">
                                Others Disposition
                            </button>
                        </h5>
                    <div id="collapse2" class="collapse show" data-bs-parent="#accordionMain">
                        <div class="card-body shadow">
                            <div class="row">
                                <div class="col-12">
                                    <button @click="btnAddEcrOtherDispoRows()"  type="button" class="btn btn-primary btn-sm mb-2" style="float: right !important;"><i class="fas fa-plus"></i> Add Validator</button>
                                </div>
                                <div class="col-12">
                                    <table class="table table-responsive">
                                        <thead>
                                            <tr>
                                            <th scope="col">#</th>
                                            <th scope="col" style="width: 40%;">Requested By</th>
                                            <th scope="col" style="width: 40%;">Reviewed By / Engineering</th>
                                            <th class="d-none" scope="col" style="width: 10%;">Reviewed By / Section Heads</th>
                                            <th scope="col">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr  v-for="(frmEcrOtherDispoRow, index) in frmEcrOtherDispoRows" :key="frmEcrOtherDispoRow.index">
                                                <td>
                                                   {{ index+1 }}
                                                </td>
                                                <td>
                                                    <Multiselect
                                                        v-model="frmEcrOtherDispoRow.requestedBy"
                                                        :close-on-select="true"
                                                        :searchable="true"
                                                        :options="ecrVar.requestedBy"
                                                        :disabled="isSelectReadonly"
                                                    />
                                                    {{frmEcrOtherDispoRows.remarks}}
                                                </td>
                                                <td>
                                                    <Multiselect
                                                        v-model="frmEcrOtherDispoRow.technicalEvaluation"
                                                        :close-on-select="true"
                                                        :searchable="true"
                                                        :options="ecrVar.technicalEvaluation"
                                                        :disabled="isSelectReadonly"
                                                    />
                                                </td>
                                                <td class="d-none">
                                                    <Multiselect
                                                        v-model="frmEcrOtherDispoRow.reviewedBy"
                                                        :close-on-select="true"
                                                        :searchable="true"
                                                        :options="ecrVar.reviewedBy"
                                                        :disabled="isSelectReadonly"
                                                    />

                                                </td>
                                                <td class="d-flex justify-content-between">
                                                    <button @click="btnRemoveEcrOtherDispoRows(index)" class="btn btn-outline-danger btn-sm mr-3" type="button" data-item-process="add">
                                                        <font-awesome-icon class="nav-icon" icon="fas fa-trash" />
                                                    </button>
                                                    <button @click="reloadApprovers(commonVar.rapidxUserDeptGroup)" class="btn btn-outline-warning btn-sm" type="button" data-item-process="remove">
                                                        <font-awesome-icon class="nav-icon" icon="refresh" />
                                                    </button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                 <!-- QA Dispositions -->
                 <div class="card mb-2 h-100" v-show="isSelectReadonly === false">
                        <h5 class="mb-0">
                            <button class="btn btn-link" type="button" data-bs-toggle="collapse" data-bs-target="#collapse3" aria-expanded="true" aria-controls="collapse3">
                                QA Dispositions
                            </button>
                        </h5>
                    <div id="collapse3" class="collapse show" data-bs-parent="#accordionMain">
                        <div class="card-body shadow">
                            <div class="row">
                                <!-- style="height: 200px;-->
                                <div class="col-12">
                                    <span class="input-group-text" id="addon-wrapping">Agreed By: </span>
                                </div>
                                <div class="col-12">
                                    <table class="table table-responsive">
                                        <thead>
                                            <tr>
                                            <th scope="col" style="width: 10%;">#</th>
                                            <th scope="col" style="width: 40%;">Quality Engineer</th>
                                            <th scope="col" style="width: 40%;">QA Manager</th>
                                            <th scope="col" style="width: 10%;">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr >
                                                <td>
                                                1
                                                </td>
                                                <td>
                                                    <Multiselect
                                                        v-model="frmEcrQadRows.qadCheckedBy"
                                                        :close-on-select="true"
                                                        :searchable="true"
                                                        :options="ecrVar.optQadCheckedBy"
                                                        :disabled="isSelectReadonly"
                                                        />
                                                    </td>
                                                <td>
                                                    <Multiselect
                                                        v-model="frmEcrQadRows.qadApprovedByInternal"
                                                        :close-on-select="true"
                                                        :searchable="true"
                                                        :options="ecrVar.optQadApprovedByInternal"
                                                        :disabled="isSelectReadonly"
                                                        />
                                                </td>
                                                <td>
                                                    <button @click="reloadQaApprover()" class="btn btn-outline-warning btn-sm" type="button" data-item-process="remove">
                                                        <font-awesome-icon class="nav-icon" icon="refresh"/>
                                                    </button>
                                                        <!-- fa fa-circle-o-notch fa-spin refresh-->
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- PMI Approvers -->
                <div class="card mb-2" v-show="isSelectReadonly === false">
                        <h5 class="mb-0">
                            <button id="" class="btn btn-link" type="button" data-bs-toggle="collapse" data-bs-target="#collapse4" aria-expanded="true" aria-controls="collapse4">
                                PMI Approvers (Please fill up, for the 4M Approval Only.)
                            </button>
                        </h5>
                    <div id="collapse4" class="collapse show" data-bs-parent="#accordionMain">
                        <div class="card-body shadow">
                            <div class="row">
                                <div class="col-12">
                                    <button @click="btnAddEcrPmiApproverRows"type="button" class="btn btn-primary btn-sm mb-2" style="float: right !important;"><i class="fas fa-plus"></i> Add PMI Approvers</button>
                                </div>
                                <div class="col-12">
                                    <table class="table table-responsive">
                                        <thead>
                                            <tr>
                                            <th scope="col">#</th>
                                            <th scope="col" style="width: 25%;">Prepared By</th>
                                            <th scope="col" style="width: 25%;">Checked By</th>
                                            <th scope="col" style="width: 30%;">Approved By</th>
                                            <th scope="col">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>

                                            <tr  v-for="(frmEcrPmiApproverRow,index) in frmEcrPmiApproverRows" :key="frmEcrPmiApproverRows.index">
                                                <td>
                                                    {{ index+1 }}
                                                </td>
                                                <td>
                                                    <Multiselect
                                                        v-model="frmEcrPmiApproverRow.preparedBy"
                                                        :close-on-select="true"
                                                        :searchable="true"
                                                        :options="ecrVar.preparedBy"
                                                        :disabled="isSelectReadonly"


                                                    />

                                                </td>
                                                <td>
                                                    <Multiselect
                                                        v-model="frmEcrPmiApproverRow.checkedBy"
                                                        :close-on-select="true"
                                                        :searchable="true"
                                                        :options="ecrVar.checkedBy"
                                                        :disabled="isSelectReadonly"
                                                    />
                                                </td>
                                                <td>
                                                    <Multiselect
                                                        v-model="frmEcrPmiApproverRow.approvedBy"
                                                        :close-on-select="true"
                                                        :searchable="true"
                                                        :options="ecrVar.approvedBy"
                                                        :disabled="isSelectReadonly"
                                                    />
                                                </td>
                                                <td class="d-flex justify-content-between">
                                                    <button @click="btnRemoveEcrPmiApproverRows(index)" class="btn btn-outline-danger btn-sm" type="button" data-item-process="add">
                                                        <font-awesome-icon class="nav-icon" icon="fas fa-trash" />
                                                    </button>
                                                    <button @click="reloadPmiApprover(commonVar.rapidxUserDeptGroup)" class="btn btn-outline-warning btn-sm" type="button" data-item-process="remove">
                                                        <font-awesome-icon class="nav-icon" icon="refresh" />
                                                    </button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- External PMI Approvers -->
                <div class="card mb-2" v-show="isSelectReadonly === false && frmEcr.internalExternal ==='External'">
                        <h5 class="mb-0">
                            <button class="btn btn-link" type="button" data-bs-toggle="collapse" data-bs-target="#collapsePmiExternal" aria-expanded="true" aria-controls="collapsePmiExternal">
                                PMI External Approvers
                            </button>
                        </h5>
                    <div id="collapsePmiExternal" class="collapse show" data-bs-parent="#accordionMain">
                        <div class="card-body shadow">
                            <div class="row">
                                <div class="col-12">
                                    <button @click="btnAddEcrPmiExternalApproverRows"  type="button" class="btn btn-primary btn-sm mb-2" style="float: right !important;"><i class="fas fa-plus"></i> Add PMI External Approvers</button>
                                </div>
                                <div class="col-12">
                                    <table class="table table-responsive">
                                        <thead>
                                            <tr>
                                            <th scope="col">#</th>
                                            <th scope="col" style="width: 30%;">Qc Head</th>
                                            <th scope="col" style="width: 30%;">Operation Head</th>
                                            <th scope="col" style="width: 30%;">QA Head</th>
                                            <th scope="col">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>

                                            <tr  v-for="(frmEcrPmiExternalApproverRow,index) in frmEcrPmiExternalApproverRows" :key="frmEcrPmiExternalApproverRows.index">
                                                <td>
                                                    {{ index+1 }}
                                                </td>
                                                <td>
                                                    <Multiselect
                                                        v-model="frmEcrPmiExternalApproverRow.preparedBy"
                                                        :close-on-select="true"
                                                        :searchable="true"
                                                        :options="ecrVar.preparedBy"
                                                        :disabled="isSelectReadonly"
                                                    />

                                                </td>
                                                <td>
                                                    <Multiselect
                                                        v-model="frmEcrPmiExternalApproverRow.checkedBy"
                                                        :close-on-select="true"
                                                        :searchable="true"
                                                        :options="ecrVar.checkedBy"
                                                        :disabled="isSelectReadonly"
                                                    />
                                                </td>
                                                <td>
                                                    <Multiselect
                                                        v-model="frmEcrPmiExternalApproverRow.approvedBy"
                                                        :close-on-select="true"
                                                        :searchable="true"
                                                        :options="ecrVar.approvedBy"
                                                    />
                                                </td>
                                                <td>
                                                    <button @click="btnRemoveEcrPmiExternalApproverRows" class="btn btn-danger btn-sm" type="button" data-item-process="add">
                                                        <font-awesome-icon class="nav-icon" icon="fas fa-trash" />
                                                    </button>
                                                    <button @click="reloadPmiExternalApprover(commonVar.rapidxUserDeptGroup)" class="btn btn-outline-warning btn-sm" type="button" data-item-process="remove">
                                                        <font-awesome-icon class="nav-icon" icon="refresh" />
                                                    </button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Approver Summary -->
                <div class="row mt-3" v-show="isSelectReadonly === true">
                    <div class="card mb-2">
                            <h5 class="mb-0">
                                <button id="" class="btn btn-link collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseApprovalSummary" aria-expanded="true" aria-controls="collapseApprovalSummary">
                                    ECR Approver Summary
                                </button>
                            </h5>
                        <div id="collapseApprovalSummary" class="collapse show" data-bs-parent="#accordionMain">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-12">
                                        <DataTable
                                            width="100%" cellspacing="0"
                                            class="table mt-2"
                                            ref="tblEcrApproverSummary"
                                            :columns="tblEcrApproverSummaryColumns"
                                            ajax="api/load_ecr_approval_summary?ecrs_id="
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
                <div class="row">
                    <div class="modal-footer justify-content-end">
                        <button v-show="modalTitle === 'View'" @click="btnEcrRequirement(frmEcr.ecrsId)"type="button" ref= "btnEcrApproved" class="btn btn-primary btn-sm">
                            <font-awesome-icon class="nav-icon" icon="fas fa-check" />&nbsp;ECR Requirements
                        </button>
                    </div>
                </div>
            </template>
            <template #footer>
                <button v-show="isSelectReadonly === false" type="button" id= "closeBtn" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                <button v-show="isSelectReadonly === false" type="submit" class="btn btn-success btn-sm"><font-awesome-icon class="nav-icon" icon="fas fa-save" />&nbsp;     Save</button>
                <button @click="btnEcrApproval('DIS')" v-show="isSelectReadonly === true && commonVar.isSessionApprover === true" type="button" ref= "btnEcrDisapproved" class="btn btn-danger btn-sm">
                    <font-awesome-icon class="nav-icon" icon="fas fa-thumbs-down" />&nbsp;Disapproved
                </button>
                <button @click="btnEcrApproval('APP')" v-show="isSelectReadonly === true && commonVar.isSessionApprover === true" type="button" ref= "btnEcrApproved" class="btn btn-success btn-sm">
                    <font-awesome-icon class="nav-icon" icon="fas fa-thumbs-up" />&nbsp;Approved
                </button>

            </template>
    </ModalComponent>
    <ModalComponent icon="fa-user" modalDialog="modal-dialog modal-lg" title="ECR Requirements" ref="modalEcrRequirements">
        <template #body>
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
                                                url: 'api/load_ecr_requirements?category=1',
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
            <div class="row mt-3" v-show="isEmptyTblEcrEnvironmentRequirements">
                <!-- Method -->
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
        <template #footer>
            <button type="button" id= "closeBtn" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
            <!-- <button type="submit" class="btn btn-success btn-sm"><font-awesome-icon class="nav-icon" icon="fas fa-save" />&nbsp;     Save</button> -->
        </template>
    </ModalComponent>
    <ModalComponent icon="fa-user" modalDialog="modal-dialog modal-md" title="ECR Approval" ref="modalEcrApproval" @add-event="frmSaveEcrApproval()">
        <template #body>
            <div class="row mt-3">
                <div class="col-md-12">
                    <div class="input-group flex-nowrap mb-2 input-group-sm">
                        <span class="input-group-text" id="addon-wrapping">Remarks:</span>
                        <textarea v-model="frmEcr.approvalRemarks" class="form-control form-control-lg" aria-describedby="addon-wrapping">
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
    <ModalComponent icon="fa-download" modalDialog="modal-dialog modal-md" title="View Ecr Reference" ref="modalViewEcrRef">
        <template #body>
            <div class="row mt-3">
                <table class="table" v-show="arrOriginalFilenames">
                    <thead>
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(arrOriginalFilename, index) in arrOriginalFilenames" :key="arrOriginalFilename.index">
                            <th scope="row">{{ index+1 }}</th>
                            <td>
                                <a href="#" class="link-primary" ref="aViewEcrRef" @click="btnLinkViewEcrRef(selectedEcrsIdEncrypted,index)">
                                    {{ arrOriginalFilename }}
                                </a>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <table class="table" v-show="currentStatus=== 'OK'" >
                    <thead>

                        <tr>
                            <!-- <th class="d-none"  scope="col">
                                Internal Ecr
                            </th> -->
                            <th scope="col">
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <a href="#" class="link-primary" @click="btnLinkDownloadEcr(selectedEcrsIdEncrypted)">
                                    Download Ecr Excel Export
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
    import ModalComponent from '../components/ModalComponent.vue';
    import EcrChangeComponent from '../components/EcrChangeComponent.vue';
    import useCommon from '../composables/common.js';
    import useEcr from '../composables/ecr.js';
    import useForm from '../composables/utils/useForm.js'
    import useSettings from '../composables/settings.js';
    import DataTable from 'datatables.net-vue3';
    import DataTablesCore from 'datatables.net-bs5';
    DataTable.use(DataTablesCore);

    const { axiosSaveData } = useForm(); // Call the useFetch function
    //composables export function
    const {
        modalEcr,
        ecrVar,
        frmEcr,
        frmEcrReasonRows,
        frmEcrQadRows,
        frmEcrOtherDispoRows,
        frmEcrPmiApproverRows,
        frmEcrPmiExternalApproverRows,
        tblEcrManRequirements,
        tblEcrMatRequirements,
        tblEcrMachineRequirements,
        tblEcrMethodRequirements,
        tblEcrEnvironmentRequirements,
        isEmptyTblEcrManRequirements,
        isEmptyTblEcrMachineRequirements,
        isEmptyTblEcrMaterialRequirements,
        isEmptyTblEcrMethodRequirements,
        isEmptyTblEcrEnvironmentRequirements,
        isEmptyTblEcrOthersRequirements,
        descriptionOfChangeParams,
        reasonOfChangeParams,
        resetArrEcrRows,
        axiosFetchData,
        getEcrById,
        addEcrReasonRows,
        removeEcrReasonRows,
    } = useEcr();
    const {
        getRapidxUserByIdOpt,
        getDropdownMasterByOpt,
        onUserChange,
    } = useSettings();
    const {
        rapidxUserDeptGroup,
        modal,
        commonVar,
        getCurrentApprover,
        getCurrentPmiInternalApprover,
        getAdminAccessOpt,
    } = useCommon();

    // const item = ref();
    //ref state
    const modalSaveEcr = ref(null);
    const modalTitle = ref('Add');
    const modalEcrRequirements = ref(null);
    const modalEcrApproval = ref(null);
    const modalViewEcrRef = ref(null);
    const isSelectReadonly = ref(null);
    const currentStatus = ref(null);
    const selectedEcrsIdEncrypted = ref(null);
    const ecrRef = ref(null);
    const tblEcr = ref(null);
    const tblEcrQa = ref(null);
    const tblEcrApproverSummary = ref(null);
    const tblEcrOthersRequirements = ref(null);


    const btnEcrApproved = ref(null);
    const btnEcrDisapproved = ref(null);
    const isApproved = ref(null);
    const currentEcrsId = ref(null);
    const selectedAdminAccess = ref(null);
    const arrOriginalFilenames = ref(null);
    const arrFilteredDocumentName = ref(null);


    //Table Column btnViewEcrRef
    const tblEcrColumns = [
        {   data: 'get_actions',
            orderable: false,
            searchable: false,
            createdCell(cell){
                let btnGetEcrId = cell.querySelector('#btnGetEcrId');
                let btnViewEcrId = cell.querySelector('#btnViewEcrId');

                if(btnGetEcrId !=null){
                    btnGetEcrId.addEventListener('click',function(){
                        let ecrsId = this.getAttribute('ecr-id');
                        modalTitle.value = "Edit";
                        isSelectReadonly.value = false;
                        currentEcrsId.value = ecrsId;
                        getEcrById(ecrsId);
                        tblEcrApproverSummary.value.dt.ajax.url("api/load_ecr_details_by_ecr_id?ecrs_id="+ecrsId).draw();

                        tblEcrApproverSummary.value.dt.ajax.url("api/load_ecr_approval_summary?ecrsId="+ecrsId).draw();
                        tblEcrManRequirements.value.dt.ajax.url("api/load_ecr_requirements?category=1&ecrsId="+currentEcrsId.value).draw();
                        tblEcrMatRequirements.value.dt.ajax.url("api/load_ecr_requirements?category=2&ecrsId="+currentEcrsId.value).draw();
                        tblEcrMachineRequirements.value.dt.ajax.url("api/load_ecr_requirements?category=3&ecrsId="+currentEcrsId.value).draw();
                        tblEcrMethodRequirements.value.dt.ajax.url("api/load_ecr_requirements?category=4&ecrsId="+currentEcrsId.value).draw();
                        tblEcrEnvironmentRequirements.value.dt.ajax.url("api/load_ecr_requirements?category=5&ecrsId="+currentEcrsId.value).draw();
                        tblEcrOthersRequirements.value.dt.ajax.url("api/load_ecr_requirements?category=6&ecrsId="+currentEcrsId.value).draw();
                    });
                }
                if(btnViewEcrId !=null){
                    btnViewEcrId.addEventListener('click',function(){
                        let ecrsId = this.getAttribute('ecr-id');
                        let ecrStatus = this.getAttribute('ecr-status');
                        let approverParams = {
                            selectedId : ecrsId,
                            approvalType : 'ecrApproval'
                        }
                        modalTitle.value = "View";
                        isSelectReadonly.value = true;
                        currentEcrsId.value = ecrsId;
                        currentStatus.value = ecrStatus;
                        getEcrById(ecrsId);
                        getCurrentApprover(approverParams);
                        tblEcrApproverSummary.value.dt.ajax.url("api/load_ecr_approval_summary?ecrsId="+ecrsId).draw();
                        tblEcrManRequirements.value.dt.ajax.url("api/load_ecr_requirements?category=1&ecrsId="+currentEcrsId.value).draw();
                        tblEcrMatRequirements.value.dt.ajax.url("api/load_ecr_requirements?category=2&ecrsId="+currentEcrsId.value).draw();
                        tblEcrMachineRequirements.value.dt.ajax.url("api/load_ecr_requirements?category=3&ecrsId="+currentEcrsId.value).draw();
                        tblEcrMethodRequirements.value.dt.ajax.url("api/load_ecr_requirements?category=4&ecrsId="+currentEcrsId.value).draw();
                        tblEcrEnvironmentRequirements.value.dt.ajax.url("api/load_ecr_requirements?category=5&ecrsId="+currentEcrsId.value).draw();
                        tblEcrOthersRequirements.value.dt.ajax.url("api/load_ecr_requirements?category=6&ecrsId="+currentEcrsId.value).draw();
                    });
                }
            }
        } ,
        {   data: 'get_status'} ,
        {   data: 'get_attachment',
            orderable: false,
            searchable: false,
            createdCell(cell){
                let btnViewEcrRef = cell.querySelector('#btnViewEcrRef');
                if(btnViewEcrRef != null){
                    btnViewEcrRef.addEventListener('click',function(){
                        let ecrsIdEncrypted = this.getAttribute('ecrs-id-encrypted');
                        let ecrStatus = this.getAttribute('ecr-status');
                        currentStatus.value = ecrStatus;
                        let params = {
                            ecrsId : ecrsIdEncrypted,
                            ecrStatus : ecrStatus,
                        };
                        getEcrRefDownload(params)
                        modal.ViewEcrRef.show();
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
    const tblEcrRequirementsColumns = [
        {   data: 'requirement'} ,
        {   data: 'details'} ,
        {   data: 'evidence'} ,
        {   data: 'get_actions',
            createdCell(cell){
                let btnChangeEcrReqDecision = cell.querySelector('#btnChangeEcrReqDecision');
                if(btnChangeEcrReqDecision != null){
                    btnChangeEcrReqDecision.addEventListener('change',function(){
                        let ecrReqId = this.getAttribute('ecr-requirements-id');
                        let ecrReqValue = this.value;
                        let classificationRequirementId = this.getAttribute('classification-requirement-id');
                        let ecrReqDecisionParams = {
                            ecrReqId : ecrReqId,
                            ecrReqValue : ecrReqValue,
                            classificationRequirementId : classificationRequirementId,
                            btnChangeEcrReqDecisionClass: this.classList,
                        }

                        ecrReqDecisionChange(ecrReqDecisionParams);
                    });
                }
            }
        }
    ];
    const tblEcrApproverSummaryColumns = [
        {   data: 'get_count'} ,
        {   data: 'get_approver_name'} ,
        {   data: 'get_role'} ,
        {   data: 'remarks'},
        {   data: 'get_status'} ,
    ];
    // === Vue hooks
    onMounted( async ()=>{
        //ModalRef inside the ModalComponent.vue
        //Do not name the Modal it is same new Modal js class


        modalEcr.SaveEcr = new Modal(modalSaveEcr.value.modalRef,{ keyboard: false });
        modalEcr.EcrRequirements = new Modal(modalEcrRequirements.value.modalRef,{ keyboard: false });
        modal.EcrApproval = new Modal(modalEcrApproval.value.modalRef,{ keyboard: false });
        modal.ViewEcrRef = new Modal(modalViewEcrRef.value.modalRef,{ keyboard: false });
        modalSaveEcr.value.modalRef.addEventListener('hidden.bs.modal', event => {
            resetEcrForm(frmEcr.value);
            frmEcrReasonRows.value = [];
            frmEcrQadRows.value = [];
            frmEcrPmiApproverRows.value = [];
            frmEcrOtherDispoRows.value = [];
            frmEcrPmiExternalApproverRows.value = [];
            frmEcrQadRows.value.qadCheckedBy =  '0';
            frmEcrQadRows.value.qadApprovedByInternal =  '0';
            frmEcrReasonRows.value.push({
                descriptionOfChange: '',
                reasonOfChange: '',
            });
            frmEcrOtherDispoRows.value.push({
                requestedBy: '0',
                technicalEvaluation: '0',
                reviewedBy: '0',
            });
            frmEcrPmiApproverRows.value.push({
                preparedBy: '0',
                checkedBy: '0',
                approvedBy: '0',
            });
            frmEcrPmiExternalApproverRows.value.push({
                preparedBy: '0',
                checkedBy: '0',
                approvedBy: '0',
            });
        });
        const btnChangeEcrReqDecision = toRef(btnChangeEcrReqDecision);
        await getDropdownMasterByOpt(descriptionOfChangeParams);
        await getDropdownMasterByOpt(reasonOfChangeParams);
        await getAdminAccessOpt();
        $('#collapse1').addClass('show');
    })

    watch(
        () => commonVar.rapidxUserDeptGroup,
        async (newVal) => {
            if (!newVal) return;
            frmEcr.value.departmentGroup = commonVar.rapidxUserDeptGroup;
            //constant object params
            let otherDispoRequestedByParams = {
                globalVar: ecrVar.requestedBy,
                formModel: toRef(frmEcrOtherDispoRows.value[0], 'requestedBy'),
                // rapidxUserDeptGroup: newVal,
                selectedVal: '0',
            };
            let otherDispoTechnicalEvaluationParams = {
                globalVar: ecrVar.technicalEvaluation,
                formModel: toRef(frmEcrOtherDispoRows.value[0],'technicalEvaluation'),
                // rapidxUserDeptGroup: newVal,
                selectedVal: '0',
            };
            let otherDispoReviewedByParams = {
                globalVar: ecrVar.reviewedBy,
                formModel: toRef(frmEcrOtherDispoRows.value[0],'reviewedBy'),
                // rapidxUserDeptGroup: newVal,
                isApprover: true,
                selectedVal: '0',
            };
            let qadCheckedByParams = {
                globalVar: ecrVar.optQadCheckedBy,
                formModel: toRef(frmEcrQadRows.value,'qadCheckedBy'),
                rapidxUserDeptGroup: 'QAD',
                selectedVal: '0',
            };
            let qadApprovedByInternalParams = {
                globalVar: ecrVar.optQadApprovedByInternal,
                formModel: toRef(frmEcrQadRows.value,'qadApprovedByInternal'),
                rapidxUserDeptGroup: 'QAD',
                selectedVal: '0',
            };
            let pmiApproverPreparedByParams = {
                globalVar: ecrVar.preparedBy,
                formModel: toRef(frmEcrPmiApproverRows.value[0],'preparedBy'),
                selectedVal: '0',
            };
            let pmiApproverCheckedByParams = {
                globalVar: ecrVar.checkedBy,
                formModel: toRef(frmEcrPmiApproverRows.value[0],'checkedBy'),
                selectedVal: '0',
            };
            let pmiApproverApprovedByParams = {
                globalVar: ecrVar.approvedBy,
                formModel: toRef(frmEcrPmiApproverRows.value[0],'approvedBy'),
                selectedVal: '0',
            };
            let pmiExternalApproverPreparedByParams = {
                globalVar: ecrVar.preparedBy,
                formModel: toRef(frmEcrPmiExternalApproverRows.value[0],'preparedBy'),
                selectedVal: '0',
            };
            let pmiExternalApproverCheckedByParams = {
                globalVar: ecrVar.checkedBy,
                formModel: toRef(frmEcrPmiExternalApproverRows.value[0],'checkedBy'),
                selectedVal: '0',
            };
            let pmiExternalApproverApprovedByParams = {
                globalVar: ecrVar.approvedBy,
                formModel: toRef(frmEcrPmiExternalApproverRows.value[0],'approvedBy'),
                selectedVal: '0',
            };
            await Promise.all([
                getRapidxUserByIdOpt(otherDispoRequestedByParams),
                getRapidxUserByIdOpt(otherDispoTechnicalEvaluationParams),
                getRapidxUserByIdOpt(otherDispoReviewedByParams),
                getRapidxUserByIdOpt(qadCheckedByParams),
                getRapidxUserByIdOpt(qadApprovedByInternalParams),
                getRapidxUserByIdOpt(pmiApproverPreparedByParams),
                getRapidxUserByIdOpt(pmiApproverCheckedByParams),
                getRapidxUserByIdOpt(pmiApproverApprovedByParams),
                getRapidxUserByIdOpt(pmiExternalApproverPreparedByParams),
                getRapidxUserByIdOpt(pmiExternalApproverCheckedByParams),
                getRapidxUserByIdOpt(pmiExternalApproverApprovedByParams),
            ]);
        },
        { immediate: true }
    );

    //Functions

    const getEcrRefDownload = async (params)  => {
        let apiParams = {
            ecrsId : params.ecrsId,
            ecrStatus : params.ecrStatus,
        }
        axiosFetchData(apiParams,'api/get_ecr_ref_download',function(response){
            let data = response.data;
            arrOriginalFilenames.value=[];
            arrFilteredDocumentName.value=[];
            selectedEcrsIdEncrypted.value=[];
            if(data.originalFilename[0] != ""){
                arrOriginalFilenames.value = data.originalFilename;
                arrFilteredDocumentName.value = data.filteredDocumentName;
            }
            selectedEcrsIdEncrypted.value = data.ersIdEncryted;
        });
    }
    const btnLinkViewEcrRef = async (selectedEcrsIdEncrypted,index)  => {
        window.open(`api/view_ecr_ref?ecrsId=${selectedEcrsIdEncrypted} && index=${index}`, '_blank');
    }
    const btnLinkDownloadEcr = async (selectedEcrsIdEncrypted)  => {
        let params = {
            ecrsId : selectedEcrsIdEncrypted,
        };
        let queryString = $.param(params);
        window.location.href="api/download_ecr_excel_by_ecrs_id?" + queryString;
    }

    const changeEcrRef = async (event)  => {
        ecrRef.value =  Array.from(event.target.files) ?? [];
    }
    const resetEcrForm = async (frmElement) => {
        for (const key in frmElement) {
            frmElement[key] = '';
        }
    };
    const btnEcr = async () => {

        modalEcr.SaveEcr.show();
        isSelectReadonly.value = false;
        await generateControlNumber();

    }
    const reloadApprovers = async (rapidxUserDeptGroup) => {
        let otherDispoRequestedByParams = {
            globalVar: ecrVar.requestedBy,
            rapidxUserDeptGroup: rapidxUserDeptGroup,
            // isApprover: true,
        };
        let otherDispoTechnicalEvaluationParams = {
            globalVar: ecrVar.technicalEvaluation,
        };
        let otherDispoReviewedByParams = {
            globalVar: ecrVar.reviewedBy,
        };
        await Promise.all([
            getRapidxUserByIdOpt(otherDispoRequestedByParams),
            getRapidxUserByIdOpt(otherDispoTechnicalEvaluationParams),
            getRapidxUserByIdOpt(otherDispoReviewedByParams),
        ]);
    }
    const reloadQaApprover = async () => {
        let qadCheckedByParams = {
            globalVar: ecrVar.optQadCheckedBy,
            rapidxUserDeptGroup: 'QAD',
        };
        let qadApprovedByInternalParams = {
            globalVar: ecrVar.optQadApprovedByInternal,
            rapidxUserDeptGroup: 'QAD',
        };
        await Promise.all([
            getRapidxUserByIdOpt(qadCheckedByParams),
            getRapidxUserByIdOpt(qadApprovedByInternalParams),
        ]);
    }
    const reloadPmiApprover = async (rapidxUserDeptGroup) => {
        let pmiApproverPreparedByParams = {
            globalVar: ecrVar.preparedBy,
            rapidxUserDeptGroup: rapidxUserDeptGroup,
        };
        let pmiApproverCheckedByParams = {
            globalVar: ecrVar.checkedBy,
            rapidxUserDeptGroup: rapidxUserDeptGroup,
        };
        let pmiApproverApprovedByParams = {
            globalVar: ecrVar.approvedBy,
            rapidxUserDeptGroup: rapidxUserDeptGroup,
            isApprover: true,
        };
        await Promise.all([
            getRapidxUserByIdOpt(pmiApproverPreparedByParams),
            getRapidxUserByIdOpt(pmiApproverCheckedByParams),
            getRapidxUserByIdOpt(pmiApproverApprovedByParams),
        ]);
    }
    const reloadPmiExternalApprover = async () => {
        let pmiExternalApproverPreparedByParams = {
            globalVar: ecrVar.preparedBy,
            rapidxUserDeptGroup: rapidxUserDeptGroup,
        };
        let pmiExternalApproverCheckedByParams = {
            globalVar: ecrVar.checkedBy,
            rapidxUserDeptGroup: rapidxUserDeptGroup,
        };
        let pmiExternalApproverApprovedByParams = {
            globalVar: ecrVar.approvedBy,
            rapidxUserDeptGroup: rapidxUserDeptGroup,
        };
        await Promise.all([
            getRapidxUserByIdOpt(pmiExternalApproverPreparedByParams),
            getRapidxUserByIdOpt(pmiExternalApproverCheckedByParams),
            getRapidxUserByIdOpt(pmiExternalApproverApprovedByParams),
        ]);
    }
    const reloadEcrRequirements = async () => {
        let descriptionOfChangeParams ={
            tblReference : 'ecr_doc',
            globalVar: ecrVar.optDescriptionOfChange,
        };
        let reasonOfChangeParams = {
            tblReference : 'ecr_roc',
            globalVar: ecrVar.optReasonOfChange,
        };
        await getDropdownMasterByOpt(descriptionOfChangeParams);
        await getDropdownMasterByOpt(reasonOfChangeParams);
    }

    const generateControlNumber = async () => {
        let apiParams = {};
        axiosFetchData(apiParams,'api/generate_control_number',function(response){
             frmEcr.value.ecrNo = response.data.currentCtrlNo;
        });
    }
    const onChangeAdminAccess = async (selectedParams)=>{
        tblEcr.value.dt.ajax.url("api/load_ecr?status=IA,DIS,QA"+"&& adminAccess="+selectedParams).draw();
        tblEcrQa.value.dt.ajax.url("api/load_ecr?status=QA"+"&& adminAccess="+selectedParams).draw();
        selectedAdminAccess.value = selectedParams;
    }
    const ecrReqDecisionChange = async (ecrReqDecisionParams)=>{
        let apiParams = {
            ecr_req_id : ecrReqDecisionParams.ecrReqId,
            ecr_req_value : ecrReqDecisionParams.ecrReqValue,
            classification_requirement_id : ecrReqDecisionParams.classificationRequirementId,
            ecrsId : currentEcrsId.value,
        }
        axiosFetchData(apiParams,'api/ecr_req_decision_change',function(response){
            ecrReqDecisionParams.btnChangeEcrReqDecisionClass.remove("is-invalid");

            tblEcrManRequirements.value.dt.ajax.url("api/load_ecr_requirements?category=1&ecrsId="+currentEcrsId.value).draw();
            tblEcrMatRequirements.value.dt.ajax.url("api/load_ecr_requirements?category=2&ecrsId="+currentEcrsId.value).draw();
            tblEcrMachineRequirements.value.dt.ajax.url("api/load_ecr_requirements?category=3&ecrsId="+currentEcrsId.value).draw();
            tblEcrMethodRequirements.value.dt.ajax.url("api/load_ecr_requirements?category=4&ecrsId="+currentEcrsId.value).draw();
            tblEcrEnvironmentRequirements.value.dt.ajax.url("api/load_ecr_requirements?category=5&ecrsId="+currentEcrsId.value).draw();
            tblEcrOthersRequirements.value.dt.ajax.url("api/load_ecr_requirements?category=6&ecrsId="+currentEcrsId.value).draw();
        });
    }
    const btnAddEcrOtherDispoRows = async () => {
        frmEcrOtherDispoRows.value.push({
            requestedBy: '0',
            technicalEvaluation: '0',
            reviewedBy: '0',
        });
    }
    const btnRemoveEcrOtherDispoRows = async (index) => {
        frmEcrOtherDispoRows.value.splice(index,1);
    }
    const btnAddEcrPmiApproverRows = async () => {
        frmEcrPmiApproverRows.value.push({
            preparedBy: '0',
            checkedBy: '0',
            approvedBy: '0',
        });
    }
    const btnRemoveEcrPmiApproverRows = async (index) => {
        frmEcrPmiApproverRows.value.splice(index,1);
    }
    const btnAddEcrPmiExternalApproverRows = async () => {
        frmEcrPmiExternalApproverRows.value.push({
                preparedBy: '0',
                checkedBy: '0',
                approvedBy: '0',
            });
    }
    const btnRemoveEcrPmiExternalApproverRows = async (index) => {
        frmEcrPmiExternalApproverRows.value.splice(index,1);
    }
    const btnEcrApproval = async (isEcrApproved) => {
        modal.EcrApproval.show();
        isApproved.value = isEcrApproved;
    }
    const btnEcrRequirement = async (ecrsId) => {
        console.log('tblEcrManRequirements',tblEcrManRequirements);
        tblEcrManRequirements.value.dt.ajax.url("api/load_ecr_requirements?category=1&ecrsId="+ecrsId).draw();
        tblEcrMatRequirements.value.dt.ajax.url("api/load_ecr_requirements?category=2&ecrsId="+ecrsId).draw();
        tblEcrMachineRequirements.value.dt.ajax.url("api/load_ecr_requirements?category=3&ecrsId="+ecrsId).draw();
        tblEcrMethodRequirements.value.dt.ajax.url("api/load_ecr_requirements?category=4&ecrsId="+ecrsId).draw();
        tblEcrEnvironmentRequirements.value.dt.ajax.url("api/load_ecr_requirements?category=5&ecrsId="+ecrsId).draw();
        tblEcrOthersRequirements.value.dt.ajax.url("api/load_ecr_requirements?category=6&ecrsId="+ecrsId).draw();

        modalEcr.EcrRequirements.show();
    }
    const frmSaveEcrApproval = async () => {
        let formData = new FormData();
        //Append form data
        [
            ["ecrs_id", frmEcr.value.ecrsId],
            ["status", isApproved.value],
            ["remarks", frmEcr.value.approvalRemarks],
        ].forEach(([key, value]) =>
            formData.append(key, value)
        );
        axiosSaveData(formData,'api/save_ecr_approval', (response) =>{
            tblEcrApproverSummary.value.dt.draw();
            tblEcr.value.dt.ajax.url("api/load_ecr?status=IA,DIS,QA").load();
            tblEcrQa.value.dt.ajax.url("api/load_ecr?status=QA").load();
            modal.EcrApproval.hide();
            modalEcr.SaveEcr.hide();
        });
    }
    const frmSaveEcr = async () => {
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
                let formData = new FormData();
                if(ecrRef.value){
                    ecrRef.value.forEach((file, index) => {
                        formData.append('ecr_ref[]', file);
                    });
                }

                //Append form data
                [
                    ["ecrs_id", frmEcr.value.ecrsId],
                    ["ecr_no", frmEcr.value.ecrNo],
                    ["category", frmEcr.value.category],
                    ["customer_name", frmEcr.value.customerName],
                    ["part_name", frmEcr.value.partName],
                    ["product_line", frmEcr.value.productLine],
                    ["section", frmEcr.value.section],
                    ["internal_external", frmEcr.value.internalExternal],
                    ["part_no", frmEcr.value.partNumber],
                    ["device_name", frmEcr.value.deviceName],
                    ["customer_ec_no", frmEcr.value.customerEcNo],
                    ["date_of_request", frmEcr.value.dateOfRequest],
                ].forEach(([key, value]) =>
                    formData.append(key, value)
                );

                for (let index = 0; index < frmEcrReasonRows.value.length; index++) {
                    const descriptionOfChange = frmEcrReasonRows.value[index].descriptionOfChange;
                    const reasonOfChange = frmEcrReasonRows.value[index].reasonOfChange;
                    [
                        ["description_of_change[]", descriptionOfChange],
                        ["reason_of_change[]", reasonOfChange],
                    ].forEach(([key, value]) =>
                        formData.append(key, value)
                    );
                }

                [
                    ["qad_approved_by_internal", frmEcrQadRows.value.qadApprovedByInternal],
                    ["qad_checked_by", frmEcrQadRows.value.qadCheckedBy],
                ].forEach(([key, value]) =>
                    formData.append(key, value)
                );

                for (let index = 0; index < frmEcrOtherDispoRows.value.length; index++) {
                    const requestedBy = frmEcrOtherDispoRows.value[index].requestedBy;
                    const technicalEvaluation = frmEcrOtherDispoRows.value[index].technicalEvaluation;
                    const reviewedBy = frmEcrOtherDispoRows.value[index].reviewedBy;
                    [
                        ["requested_by[]", requestedBy],
                        ["technical_evaluation[]", technicalEvaluation],
                        ["reviewed_by[]", reviewedBy],
                    ].forEach(([key, value]) =>
                        formData.append(key, value)
                    );
                }
                for (let index = 0; index < frmEcrPmiApproverRows.value.length; index++) {
                    const preparedBy = frmEcrPmiApproverRows.value[index].preparedBy;
                    const checkedBy = frmEcrPmiApproverRows.value[index].checkedBy;
                    const approvedBy = frmEcrPmiApproverRows.value[index].approvedBy;
                    [
                        ["prepared_by[]", preparedBy],
                        ["checked_by[]", checkedBy],
                        ["approved_by[]", approvedBy],
                    ].forEach(([key, value]) =>
                        formData.append(key, value)
                    );
                }
                for (let index = 0; index < frmEcrPmiExternalApproverRows.value.length; index++) {
                    const externalPreparedBy = frmEcrPmiExternalApproverRows.value[index].preparedBy;
                    const externalPheckedBy = frmEcrPmiExternalApproverRows.value[index].checkedBy;
                    const externalPpprovedBy = frmEcrPmiExternalApproverRows.value[index].approvedBy;
                    [
                        ["external_prepared_by[]", externalPreparedBy],
                        ["external_checked_by[]", externalPheckedBy],
                        ["external_approved_by[]", externalPpprovedBy],
                    ].forEach(([key, value]) =>
                        formData.append(key, value)
                    );
                }
                //TODO: Save Successfully
                axiosSaveData(formData,'api/save_ecr', (response) =>{
                    tblEcr.value.dt.ajax.url("api/load_ecr?status=IA,DIS,QA && adminAccess="+selectedAdminAccess.value).load();
                    tblEcrQa.value.dt.ajax.url("api/load_ecr?status=QA && adminAccess="+selectedAdminAccess.value).load();
                    modalEcr.SaveEcr.hide();
                });
            }
        })

    }

</script>

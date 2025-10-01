<template>
    <div class="container-fluid px-4">
        <h4 class="mt-4">Material</h4>
        <div class="card">
            <div class="card-header">
                <h5>Filter</h5>
            </div>
            <div class="card-body">
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
            </div>
        </div>
        <div class="card mt-5"  style="width: 100%;">
            <div class="card-body overflow-auto">
                <div class="container-fluid px-4">
                    <ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item active">Material Table</li>
                    </ol>
                    <div class="table-responsive">
                        <DataTable
                            width="100%" cellspacing="0"
                            class="table mt-2"
                            ref="tblEcrByCategoryStatus"
                            :columns="tblEcrByCategoryStatusColumns"
                            ajax="api/load_ecr_material_by_status?category=Material"
                            :options="{
                                serverSide: true, //Serverside true will load the network
                                columnDefs:[
                                    // {orderable:false,target:[0]}
                                ]
                            }"
                        >
                            <thead>
                                <tr>
                                    <th style=""width="5%">Action</th>
                                    <th style=""width="10%">Status</th>
                                    <!-- <th style=""width="10%">Attachment</th> -->
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
    <ModalComponent icon="fa-user" modalDialog="modal-dialog modal-xl" title="Material" @add-event="" ref="modalSaveMaterial">
        <template #body>
            <div class="row">
                <div class="modal-footer justify-content-end">
                    <button @click="btnEcrRequirement(frmMaterial.ecrsId)"type="button" ref= "btnEcrApproved" class="btn btn-primary btn-sm">
                        <font-awesome-icon class="nav-icon" icon="fas fa-check" />&nbsp;ECR Requirements
                    </button>
                </div>
            </div>
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
            <div class="row mt-3">
                <div class="card">
                    <div class="card-body">
                        <div class="row d-none">
                            <div class="input-group flex-nowrap mb-2 input-group-sm">
                                <span class="input-group-text" id="addon-wrapping">ECR Id:</span>
                            <input :disabled="isModalView" v-model="frmMaterial.ecrsId" type="text" class="form-control form-control-lg" aria-describedby="addon-wrapping" readonly>
                            </div>
                            <div class="input-group flex-nowrap mb-2 input-group-sm">
                                <span class="input-group-text" id="addon-wrapping">Material Id:</span>
                                <input :disabled="isModalView" v-model="frmMaterial.materialId"  type="text" class="form-control form-control-lg" aria-describedby="addon-wrapping" readonly>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="input-group flex-nowrap mb-2 input-group-sm">
                                    <span class="input-group-text" id="addon-wrapping">Parts/Direct Material:</span>
                                    <Multiselect
                                        :disabled="isModalView"
                                        v-model="frmMaterial.pdMaterial"
                                        :options="commonVar.optCheck"
                                        placeholder="Select an option"
                                        :searchable="true"
                                        :close-on-select="true"
                                    />
                                </div>
                                <div class="input-group flex-nowrap mb-2 input-group-sm">
                                    <span class="input-group-text" id="addon-wrapping">MSDS:</span>
                                    <Multiselect
                                        :disabled="isModalView"
                                        v-model="frmMaterial.msds"
                                        :options="commonVar.optYesNo"
                                        placeholder="Select an option"
                                        :searchable="true"
                                        :close-on-select="true"
                                    />
                                </div>
                                <div class="input-group flex-nowrap mb-2 input-group-sm">
                                    <span class="input-group-text" id="addon-wrapping">ICP:</span>
                                    <Multiselect
                                        :disabled="isModalView"
                                        v-model="frmMaterial.icp"
                                        :options="commonVar.optYesNo"
                                        placeholder="Select an option"
                                        :searchable="true"
                                        :close-on-select="true"
                                    />
                                </div>
                                <div class="input-group flex-nowrap mb-2 input-group-sm">
                                    <span class="input-group-text" id="addon-wrapping">GP:</span>
                                    <input :disabled="isModalView" v-model="frmMaterial.gp" type="date" class="form-control form-control-lg" aria-describedby="addon-wrapping">
                                </div>
                                <div class="input-group flex-nowrap mb-2 input-group-sm">
                                    <span class="input-group-text" id="addon-wrapping">Qoutation</span>
                                    <Multiselect
                                        :disabled="isModalView"
                                        v-model="frmMaterial.qoutation"
                                        :options="commonVar.optCheck"
                                        placeholder="Select an option"
                                        :searchable="true"
                                        :close-on-select="true"
                                    />
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="input-group flex-nowrap mb-2 input-group-sm">
                                    <span class="input-group-text" id="addon-wrapping">Supplier:</span>
                                    <Multiselect
                                        :disabled="isModalView"
                                        v-model="frmMaterial.materialSupplier"
                                        :options="materialVar.materialSupplier"
                                        placeholder="Select an option"
                                        :searchable="true"
                                        :close-on-select="true"
                                    />
                                    <button @click="reloadDropdownMaster('materialSupplierParams')" class="btn btn-outline-warning btn-sm" type="button" data-item-process="remove">
                                        <font-awesome-icon class="nav-icon" icon="refresh" />
                                    </button>
                                </div>
                                <div class="input-group flex-nowrap mb-2 input-group-sm">
                                    <span class="input-group-text" id="addon-wrapping">Product Color:</span>
                                    <Multiselect
                                        :disabled="isModalView"
                                        v-model="frmMaterial.materialColor"
                                        :options="materialVar.materialColor"
                                        placeholder="Select an option"
                                        :searchable="true"
                                        :close-on-select="true"
                                    />
                                    <button @click="reloadDropdownMaster('materialColorParams')" class="btn btn-outline-warning btn-sm" type="button" data-item-process="remove">
                                        <font-awesome-icon class="nav-icon" icon="refresh" />
                                    </button>
                                </div>
                                <div class="input-group flex-nowrap mb-2 input-group-sm">
                                    <span class="input-group-text" id="addon-wrapping">ROHS:</span>
                                    <Multiselect
                                        :disabled="isModalView"
                                        v-model="frmMaterial.rohs"
                                        :options="commonVar.optResult"
                                        placeholder="Select an option"
                                        :searchable="true"
                                        :close-on-select="true"
                                    />
                                </div>
                                <div class="input-group flex-nowrap mb-2 input-group-sm">
                                    <span class="input-group-text" id="addon-wrapping">Material Sample:</span>
                                    <Multiselect
                                        :disabled="isModalView"
                                        v-model="frmMaterial.materialSample"
                                        :options="commonVar.optCheck"
                                        placeholder="Select an option"
                                        :searchable="true"
                                        :close-on-select="true"
                                    />
                                </div>
                                <div class="input-group flex-nowrap mb-2 input-group-sm">
                                    <span class="input-group-text" id="addon-wrapping">COC:</span>
                                    <Multiselect
                                        :disabled="isModalView"
                                        v-model="frmMaterial.coc"
                                        :options="commonVar.optCheck"
                                        placeholder="Select an option"
                                        :searchable="true"
                                        :close-on-select="true"
                                    />
                                </div>
                            </div>
                        </div>
                        <!-- Material Approval -->
                        <div class="card mb-2" v-show="isModalMaterial === 'Edit'">
                            <h5 class="mb-0">
                                <button id="" class="btn btn-link" type="button" data-bs-toggle="collapse" data-bs-target="#collapse2" aria-expanded="true" aria-controls="collapse2">
                                    Approval:
                                </button>
                            </h5>
                            <div id="collapse2" class="collapse show" data-bs-parent="#accordionMain">
                                <div class="card-body shadow">
                                    <div class="row">
                                        <div class="col-12 overflow-auto">
                                            <table class="table table-responsive">
                                                <thead>
                                                    <tr>
                                                    <th scope="col" style="width: 10%;">Section</th>
                                                    <th scope="col" style="width: 25%;">Prepared by</th>
                                                    <th scope="col" style="width: 25%;">Checked by</th>
                                                    <th scope="col" style="width: 30%;">Approved By</th>
                                                    <th scope="col" style="width: 10%;">Action</th>
                                                    </tr>
                                                </thead>

                                                <tbody>
                                                    <tr class="production" v-show="isInternalExternal == 'External'">
                                                        <td>
                                                            Production
                                                        </td>
                                                        <td>
                                                            <Multiselect
                                                                :disabled="isModalView"
                                                                v-model="frmMaterial.prdnPreparedBy"
                                                                :close-on-select="true"
                                                                :searchable="true"
                                                                :options="materialVar.prdnPreparedBy"
                                                            />
                                                        </td>
                                                        <td>
                                                            <Multiselect
                                                                :disabled="isModalView"
                                                                v-model="frmMaterial.prdnCheckedBy"
                                                                :close-on-select="true"
                                                                :searchable="true"
                                                                :options="materialVar.prdnCheckedBy"
                                                            />

                                                        </td>
                                                        <td>
                                                            <Multiselect
                                                                :disabled="isModalView"
                                                                v-model="frmMaterial.prdnApprovedBy"
                                                                :close-on-select="true"
                                                                :searchable="true"
                                                                :options="materialVar.prdnApprovedBy"
                                                            />
                                                        </td>
                                                        <td>
                                                            <button @click="reloadRapidxUserMaster('prdn')" class="btn btn-outline-warning btn-sm" type="button" data-item-process="refresh">
                                                                <font-awesome-icon class="nav-icon" icon="refresh" />
                                                            </button>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td>
                                                            Purchasing
                                                        </td>
                                                        <td>

                                                            <Multiselect
                                                                :disabled="isModalView"
                                                                v-model="frmMaterial.prPreparedBy"
                                                                :close-on-select="true"
                                                                :searchable="true"
                                                                :options="materialVar.prPreparedBy"
                                                            />
                                                        </td>
                                                        <td>
                                                            <Multiselect
                                                                :disabled="isModalView"
                                                                v-model="frmMaterial.prCheckedBy"
                                                                :close-on-select="true"
                                                                :searchable="true"
                                                                :options="materialVar.prCheckedBy"
                                                            />
                                                        </td>
                                                        <td>
                                                            <Multiselect
                                                                :disabled="isModalView"
                                                                v-model="frmMaterial.prApprovedBy"
                                                                :close-on-select="true"
                                                                :searchable="true"
                                                                :options="materialVar.prApprovedBy"
                                                            />
                                                        </td>
                                                        <td>
                                                            <button @click="reloadRapidxUserMaster('purchasing')" class="btn btn-outline-warning btn-sm" type="button" data-item-process="refresh">
                                                                <font-awesome-icon class="nav-icon" icon="refresh" />
                                                            </button>
                                                        </td>
                                                    </tr>
                                                    <tr >
                                                        <td>
                                                            Conformed: PPC
                                                        </td>
                                                        <td>
                                                            <Multiselect
                                                                :disabled="isModalView"
                                                                v-model="frmMaterial.ppcPreparedBy"
                                                                :close-on-select="true"
                                                                :searchable="true"
                                                                :options="materialVar.ppcPreparedBy"
                                                            />
                                                        </td>
                                                        <td>
                                                            <Multiselect
                                                                :disabled="isModalView"
                                                                v-model="frmMaterial.ppcCheckedBy"
                                                                :close-on-select="true"
                                                                :searchable="true"
                                                                :options="materialVar.ppcCheckedBy"
                                                            />
                                                        </td>
                                                        <td>
                                                            <Multiselect
                                                                :disabled="isModalView"
                                                                v-model="frmMaterial.ppcApprovedBy"
                                                                :close-on-select="true"
                                                                :searchable="true"
                                                                :options="materialVar.ppcApprovedBy"
                                                            />
                                                        </td>
                                                        <td>
                                                            <button @click="reloadRapidxUserMaster('ppc')" class="btn btn-outline-warning btn-sm" type="button" data-item-process="refresh">
                                                                <font-awesome-icon class="nav-icon" icon="refresh" />
                                                            </button>
                                                        </td>
                                                    </tr>
                                                    <tr class="EMS">
                                                        <td>
                                                            EMS
                                                        </td>
                                                        <td>
                                                            <Multiselect
                                                                :disabled="isModalView"
                                                                v-model="frmMaterial.emsPreparedBy"
                                                                :close-on-select="true"
                                                                :searchable="true"
                                                                :options="materialVar.emsPreparedBy"
                                                            />
                                                        </td>
                                                        <td>
                                                            <Multiselect
                                                                :disabled="isModalView"
                                                                v-model="frmMaterial.emsCheckedBy"
                                                                :close-on-select="true"
                                                                :searchable="true"
                                                                :options="materialVar.emsCheckedBy"
                                                            />
                                                        </td>
                                                        <td>
                                                            <Multiselect
                                                                :disabled="isModalView"
                                                                v-model="frmMaterial.emsApprovedBy"
                                                                :close-on-select="true"
                                                                :searchable="true"
                                                                :options="materialVar.emsApprovedBy"
                                                            />
                                                        </td>
                                                        <td>
                                                            <button @click="reloadRapidxUserMaster('ems')" class="btn btn-outline-warning btn-sm" type="button" data-item-process="refresh">
                                                                <font-awesome-icon class="nav-icon" icon="refresh" />
                                                            </button>
                                                        </td>
                                                    </tr>
                                                    <tr class="QC">
                                                        <td>
                                                            QC
                                                        </td>
                                                        <td>
                                                            <Multiselect
                                                                :disabled="isModalView"
                                                                v-model="frmMaterial.qcPreparedBy"
                                                                :close-on-select="true"
                                                                :searchable="true"
                                                                :options="materialVar.qcPreparedBy"
                                                            />
                                                        </td>
                                                        <td>
                                                            <Multiselect
                                                                :disabled="isModalView"
                                                                v-model="frmMaterial.qcCheckedBy"
                                                                :close-on-select="true"
                                                                :searchable="true"
                                                                :options="materialVar.qcCheckedBy"
                                                            />
                                                        </td>
                                                        <td>
                                                            <Multiselect
                                                                :disabled="isModalView"
                                                                v-model="frmMaterial.qcApprovedBy"
                                                                :close-on-select="true"
                                                                :searchable="true"
                                                                :options="materialVar.qcApprovedBy"
                                                            />
                                                        </td>
                                                        <td>
                                                            <button @click="reloadRapidxUserMaster('qc')" class="btn btn-outline-warning btn-sm" type="button" data-item-process="refresh">
                                                                <font-awesome-icon class="nav-icon" icon="refresh" />
                                                            </button>
                                                        </td>
                                                    </tr>
                                                    <tr class="pro-engineer" v-show="isInternalExternal == 'External'">
                                                        <td>
                                                            Process Engineering
                                                        </td>
                                                        <td>
                                                            <Multiselect
                                                                :disabled="isModalView"
                                                                v-model="frmMaterial.proEnggPreparedBy"
                                                                :close-on-select="true"
                                                                :searchable="true"
                                                                :options="materialVar.proEnggPreparedBy"
                                                            />
                                                        </td>
                                                        <td>
                                                            <Multiselect
                                                                :disabled="isModalView"
                                                                v-model="frmMaterial.proEnggCheckedBy"
                                                                :close-on-select="true"
                                                                :searchable="true"
                                                                :options="materialVar.proEnggCheckedBy"
                                                            />
                                                        </td>
                                                        <td>
                                                            <Multiselect
                                                                :disabled="isModalView"
                                                                v-model="frmMaterial.proEnggApprovedBy"
                                                                :close-on-select="true"
                                                                :searchable="true"
                                                                :options="materialVar.proEnggApprovedBy"
                                                            />
                                                        </td>
                                                        <td>
                                                            <button @click="reloadRapidxUserMaster('proEngg')" class="btn btn-outline-warning btn-sm" type="button" data-item-process="refresh">
                                                                <font-awesome-icon class="nav-icon" icon="refresh" />
                                                            </button>
                                                        </td>
                                                    </tr>
                                                    <tr class="main-engineer" v-show="isInternalExternal == 'External'">
                                                        <td>
                                                            Maintenance Engineering
                                                        </td>
                                                        <td>
                                                            <Multiselect
                                                                :disabled="isModalView"
                                                                v-model="frmMaterial.mainEnggPreparedBy"
                                                                :close-on-select="true"
                                                                :searchable="true"
                                                                :options="materialVar.mainEnggPreparedBy"
                                                            />
                                                        </td>
                                                        <td>
                                                            <Multiselect
                                                                :disabled="isModalView"
                                                                v-model="frmMaterial.mainEnggCheckedBy"
                                                                :close-on-select="true"
                                                                :searchable="true"
                                                                :options="materialVar.mainEnggCheckedBy"
                                                            />
                                                        </td>
                                                        <td>
                                                            <Multiselect
                                                                :disabled="isModalView"
                                                                v-model="frmMaterial.mainEnggApprovedBy"
                                                                :close-on-select="true"
                                                                :searchable="true"
                                                                :options="materialVar.mainEnggApprovedBy"
                                                            />
                                                        </td>
                                                        <td>
                                                            <button @click="reloadRapidxUserMaster('mainEngg')" class="btn btn-outline-warning btn-sm" type="button" data-item-process="refresh">
                                                                <font-awesome-icon class="nav-icon" icon="refresh" />
                                                            </button>
                                                        </td>
                                                    </tr>
                                                    <tr class="engineer" v-show="isInternalExternal == 'Internal'">
                                                        <td>
                                                            Engineering
                                                        </td>
                                                        <td>
                                                            <Multiselect
                                                                :disabled="isModalView"
                                                                v-model="frmMaterial.enggPreparedBy"
                                                                :close-on-select="true"
                                                                :searchable="true"
                                                                :options="materialVar.enggPreparedBy"
                                                            />
                                                        </td>
                                                        <td>
                                                            <Multiselect
                                                                :disabled="isModalView"
                                                                v-model="frmMaterial.enggCheckedBy"
                                                                :close-on-select="true"
                                                                :searchable="true"
                                                                :options="materialVar.enggCheckedBy"
                                                            />
                                                        </td>
                                                        <td>
                                                            <Multiselect
                                                                :disabled="isModalView"
                                                                v-model="frmMaterial.enggApprovedBy"
                                                                :close-on-select="true"
                                                                :searchable="true"
                                                                :options="materialVar.enggApprovedBy"
                                                            />
                                                        </td>
                                                        <td>
                                                            <button @click="reloadRapidxUserMaster('engg')" class="btn btn-outline-warning btn-sm" type="button" data-item-process="refresh">
                                                                <font-awesome-icon class="nav-icon" icon="refresh" />
                                                            </button>
                                                        </td>
                                                    </tr>
                                                    <tr class="QA">
                                                        <td>
                                                            QA
                                                        </td>
                                                        <td>
                                                            <Multiselect
                                                                :disabled="isModalView"
                                                                v-model="frmMaterial.qaPreparedBy"
                                                                :close-on-select="true"
                                                                :searchable="true"
                                                                :options="materialVar.qaPreparedBy"
                                                            />
                                                        </td>
                                                        <td>
                                                            <Multiselect
                                                                :disabled="isModalView"
                                                                v-model="frmMaterial.qaCheckedBy"
                                                                :close-on-select="true"
                                                                :searchable="true"
                                                                :options="materialVar.qaCheckedBy"
                                                            />
                                                        </td>
                                                        <td>
                                                            <Multiselect
                                                                :disabled="isModalView"
                                                                v-model="frmMaterial.qaApprovedBy"
                                                                :close-on-select="true"
                                                                :searchable="true"
                                                                :options="materialVar.qaApprovedBy"
                                                            />
                                                        </td>
                                                        <td>
                                                            <button @click="reloadRapidxUserMaster('qa')" class="btn btn-outline-warning btn-sm" type="button" data-item-process="refresh">
                                                                <font-awesome-icon class="nav-icon" icon="refresh" />
                                                            </button>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <!-- TODO ECR | 4M Requirement View Manager Ni-an
                                <div class="card-footer justify-content-end">
                                    <button type="button" id= "closeBtn" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                                    <button type="button" class="btn btn-success btn-sm"><font-awesome-icon class="nav-icon" icon="fas fa-eye" />&nbsp;     View</button>
                                </div> -->
                            </div>
                        </div>
                        <div class="row" v-show="isModalMaterial === 'View'">
                            <div class="card">
                                <div class="card-header">
                                    <h3> Material Approvers</h3>
                                </div>
                                <div class="card-body overflow-auto">
                                    <DataTable
                                        width="100%" cellspacing="0"
                                        class="table mt-2"
                                        ref="tblMaterialApproval"
                                        :columns="tblMaterialApprovalColumns"
                                        ajax="api/load_material_approval_by_meterial_id"
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
            <div class="row mt-3" v-show="isModalMaterial === 'View' && currentStatus === 'PMIAPP' || currentStatus === 'OK'" >
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
            <button @click="btnApprovedDisapproved('DIS')" v-show="isModalMaterial === 'View' && commonVar.isSessionApprover === true && currentStatus !='PMIAPP' "  type="button" ref= "btnPmiInternalDisapproved" class="btn btn-danger btn-sm">
                <font-awesome-icon class="nav-icon" icon="fas fa-thumbs-down" />&nbsp;Disapproved
            </button>
            <button @click="btnApprovedDisapproved('APP')" v-show="isModalMaterial === 'View' && commonVar.isSessionApprover === true" type="button" ref= "btnPmiInternalApproved" class="btn btn-success btn-sm">Approved</button>
            <button  v-show="isModalMaterial === 'Edit'"  type="button" id= "closeBtn" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Close</button>
            <button @click="saveMaterial()"  v-show="isModalMaterial === 'Edit'"  type="submit" class="btn btn-success btn-sm"><li class="fas fa-save"></li> Save</button>
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
                    <input :disabled="isModalView" v-model="frmEcrDetails.ecrDetailsId"  type="text" class="form-control form-control-lg" aria-describedby="addon-wrapping">
                </div>
            </div>
            <div class="row">
                <div class="col-sm-6">
                    <div class="input-group flex-nowrap mb-2 input-group-sm">
                        <span class="input-group-text" id="addon-wrapping">Type of Part:</span>
                        <Multiselect
                                                                :disabled="isModalView"
                            v-model="frmEcrDetails.typeOfPart"
                            :options="ecrVar.optTypeOfPart"
                            placeholder="Select an option"
                            :searchable="true"
                            :close-on-select="true"
                        />
                        <button @click="reloadDropdownMaster('typeOfPartParams')" class="btn btn-outline-warning btn-sm" type="button" data-item-process="remove">
                            <font-awesome-icon class="nav-icon" icon="refresh" />
                        </button>
                    </div>
                    <div class="input-group flex-nowrap mb-2 input-group-sm">
                        <span class="input-group-text" id="addon-wrapping">Change Imp Date:</span>
                        <input :disabled="isModalView" v-model="frmEcrDetails.changeImpDate" type="date" class="form-control form-control-lg" aria-describedby="addon-wrapping">
                    </div>
                    <div class="input-group flex-nowrap mb-2 input-group-sm">
                        <span class="input-group-text" id="addon-wrapping">Docs To Be Submitted</span>
                        <input :disabled="isModalView" v-model="frmEcrDetails.docToBeSub" type="text" class="form-control form-control-lg" aria-describedby="addon-wrapping">
                    </div>
                 </div>
                <div class="col-sm-6">
                    <div class="input-group flex-nowrap mb-2 input-group-sm">
                        <span class="input-group-text" id="addon-wrapping">Docs Submission Date:</span>
                        <input :disabled="isModalView" v-model="frmEcrDetails.docSubDate"  type="date" class="form-control form-control-lg" aria-describedby="addon-wrapping">
                    </div>
                    <div class="input-group flex-nowrap mb-2 input-group-sm">
                        <span class="input-group-text" id="addon-wrapping">Remarks:</span>
                        <input :disabled="isModalView" v-model="frmEcrDetails.remarks"  type="text" class="form-control form-control-lg" aria-describedby="addon-wrapping">
                    </div>
                </div>
            </div>
        </template>
        <template #footer>
            <button type="button" id= "closeBtn" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
            <button type="submit" v-show="isModalMaterial === 'Edit'" class="btn btn-success btn-sm"><li class="fas fa-save"></li> Save</button>
        </template>
    </ModalComponent>
    <ModalComponent icon="fa-upload" modalDialog="modal-dialog modal-md" title="Upload Material Reference" ref="modalUploadMaterialRef" @add-event="frmUploadMaterialRef()">
        <template #body>
            <div class="row mt-3">
                <div class="col-md-12">
                    <div class="input-group flex-nowrap mb-2 input-group-sm">
                        <input @change="changeMaterialRef" multiple type="file" accept=".pdf" class="form-control form-control-lg" aria-describedby="addon-wrapping" required>
                    </div>
                </div>
            </div>
        </template>
        <template #footer>
            <button type="button" id= "closeBtn" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-success btn-sm"><font-awesome-icon class="nav-icon" icon="fas fa-save" />&nbsp; Save</button>
        </template>
    </ModalComponent>
    <ModalComponent icon="fa-download" modalDialog="modal-dialog modal-md" title="View Material Reference" ref="modalViewMaterialRef">
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
                                <a href="#" class="link-primary" ref="aViewMaterialRef" @click="btnLinkViewMaterialRef(selectedEcrsIdEncrypted,index)">
                                    {{ arrOriginalFilename }}
                                </a>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <table class="table">
                    <thead>

                        <tr>
                            <!-- <th class="d-none"  scope="col">
                                Internal Material
                            </th> -->
                            <th scope="col">
                                External Material
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <!-- <td  class="d-none">
                                <a href="#" class="link-primary" @click="btnLinkDownloadInternalMaterial(selectedEcrsId)">
                                    Download Internal Material
                                </a>
                            </td> -->
                            <td>
                                <a href="#" class="link-primary" @click="btnLinkDownloadExternalMaterial(selectedEcrsId)">
                                    Download External Material
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
            <button @click = "saveApproval(selectedMaterialsId,selectedEcrsId,approvalRemarks,isApprovedDisappproved,currentStatus)" type="button" class="btn btn-success btn-sm"><font-awesome-icon class="nav-icon" icon="fas fa-save" />&nbsp; Save</button>
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
    import useCommon from '../composables/common.js';
    import useForm from '../composables/utils/useForm.js'
    import useEcr from '../composables/ecr.js';
    import useMaterial from '../composables/material.js';
    import useSettings from '../composables/settings.js';
    import DataTable from 'datatables.net-vue3';
    import DataTablesCore from 'datatables.net-bs5';
    DataTable.use(DataTablesCore)

    const {
        modalEcr,
        ecrVar,
        tblEcrDetails,
        frmEcrDetails,
        frmEcrReasonRows,
        descriptionOfChangeParams,
        typeOfPartParams,
        reasonOfChangeParams,
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
     // getDropdownMasterByOpt,
     // getRapidxUserByIdOpt,

    const {
        materialVar,
        frmMaterial,
    } = useMaterial();

    const {
        axiosSaveData
    } = useForm();

    const {
        getRapidxUserByIdOpt,
        getDropdownMasterByOpt,
        onUserChange,
    } = useSettings();

    const {
        modal,
        commonVar,
        getCurrentApprover,
        getCurrentPmiInternalApprover,
        getCategoryAdminAccessOpt,
    } = useCommon();

    const modalSaveEcrDetail = ref(null);
    const modalSaveMaterial = ref(null);
    const modalUploadMaterialRef = ref(null);
    const modalViewMaterialRef = ref(null);
    const modalApproval = ref(null);

    const isModalMaterial = ref(null);
    const isModalView = ref(true);
    const selectedEcrsId = ref(null);
    const currentStatus = ref(null);
    const selectedEcrsIdEncrypted = ref(null);
    const selectedAdminAccess = ref(null);
    const arrOriginalFilenames = ref(null);
    const materialRef = ref(null);
    const isInternalExternal = ref(null);
    const isSelectReadonly  = ref(true);
    const tblEcrByCategoryStatus = ref(null);
    const tblMaterialApproval = ref(null);
    const tblPmiInternalApproverSummary = ref(null);
    const isApprovedDisappproved = ref(null);
    const approvalRemarks = ref(null);
    const selectedMaterialsId = ref(null);
    //Ecr Req
    const modalEcrRequirements = ref(null);
    const modalViewEcrRequirementRef = ref(null);

    //Columns
     const tblEcrByCategoryStatusColumns = [
        {   data: 'get_actions',
            orderable: false,
            searchable: false,
            createdCell(cell){
                let btnGetEcrId = cell.querySelector('#btnGetEcrId');
                let btnDownloadMaterialRef = cell.querySelector('#btnDownloadMaterialRef');
                let btnViewMaterialById = cell.querySelector('#btnViewMaterialById');
                if(btnGetEcrId != null){
                    btnGetEcrId.addEventListener('click',function(){
                        let ecrsId = this.getAttribute('ecrs-id');
                        frmMaterial.value.ecrsId = ecrsId;
                        isModalMaterial.value = 'Edit';
                        isModalView.value = false;
                        tblEcrDetails.value.dt.ajax.url("api/load_ecr_details_by_ecr_id?ecr_id="+ecrsId).draw();
                        getMaterialEcrById(ecrsId);
                        getRapidxUserByIdOpt(prdnPreparedByParams);
                        getRapidxUserByIdOpt(prdnCheckedByParams);
                        getRapidxUserByIdOpt(prdnApprovedByParams);
                        getRapidxUserByIdOpt(prPreparedByParams);
                        getRapidxUserByIdOpt(prCheckedByParams);
                        getRapidxUserByIdOpt(prApprovedByParams);
                        getRapidxUserByIdOpt(ppcPreparedByParams);
                        getRapidxUserByIdOpt(ppcCheckedByParams);
                        getRapidxUserByIdOpt(ppcApprovedByParams);
                        getRapidxUserByIdOpt(emsPreparedByParams);
                        getRapidxUserByIdOpt(emsCheckedByParams);
                        getRapidxUserByIdOpt(emsApprovedByParams);
                        getRapidxUserByIdOpt(qcPreparedByParams);
                        getRapidxUserByIdOpt(qcCheckedByParams);
                        getRapidxUserByIdOpt(qcApprovedByParams);
                        getRapidxUserByIdOpt(proEnggPreparedByParams);
                        getRapidxUserByIdOpt(proEnggCheckedByParams);
                        getRapidxUserByIdOpt(proEnggApprovedByParams);
                        getRapidxUserByIdOpt(mainEnggPreparedByParams);
                        getRapidxUserByIdOpt(mainEnggCheckedByParams);
                        getRapidxUserByIdOpt(mainEnggApprovedByParams);
                        getRapidxUserByIdOpt(enggPreparedByParams);
                        getRapidxUserByIdOpt(enggCheckedByParams);
                        getRapidxUserByIdOpt(enggApprovedByParams);
                        getRapidxUserByIdOpt(qaPreparedByParams);
                        getRapidxUserByIdOpt(qaCheckedByParams);
                        getRapidxUserByIdOpt(qaApprovedByParams);
                        modal.SaveMaterial.show();
                    });
                }
                if(btnDownloadMaterialRef != null){
                    btnDownloadMaterialRef.addEventListener('click',function(){
                        let ecrsId = this.getAttribute('ecrs-id');
                        selectedEcrsId.value = ecrsId;
                        modal.UploadMaterialRef.show();
                    });
                }
                if(btnViewMaterialById != null){
                    btnViewMaterialById.addEventListener('click',function(){
                        let ecrsId = this.getAttribute('ecrs-id');
                        let materialsId = this.getAttribute('materials-id');
                        let materialStatus = this.getAttribute('material-status');
                        let materialApproverParams = {
                            selectedId : materialsId,
                            approvalType : 'materialApproval'
                        }
                        let pmiApproverParams = {
                            selectedId : ecrsId,
                            approvalType : 'pmiApproval'
                        }
                        selectedEcrsId.value = ecrsId;
                        selectedMaterialsId.value = materialsId;
                        currentStatus.value = materialStatus;
                        isModalMaterial.value = 'View';
                        isModalView.value = true;
                        getMaterialEcrById(ecrsId);
                        tblEcrDetails.value.dt.ajax.url("api/load_ecr_details_by_ecr_id?ecr_id="+ecrsId).draw();
                        getCurrentApprover(materialApproverParams);
                        tblMaterialApproval.value.dt.ajax.url("api/load_material_approval_by_meterial_id?materialsId="+materialsId).draw();
                        if( materialStatus === 'PMIAPP' || materialStatus === 'OK'){
                            getCurrentApprover(pmiApproverParams);
                            tblPmiInternalApproverSummary.value.dt.ajax.url("api/load_pmi_internal_approval_summary?ecrsId="+ecrsId).draw()
                        }

                    });
                }
            }
        , title: 'Requirement'} ,
        {   data: 'get_status'} ,
        // {   data: 'get_attachment',
        //     orderable: false,
        //     searchable: false,
        //     createdCell(cell){
        //         let btnViewMaterialRef = cell.querySelector('#btnViewMaterialRef');
        //         if(btnViewMaterialRef != null){
        //             btnViewMaterialRef.addEventListener('click',function(){
        //                 let ecrsId = this.getAttribute('ecrs-id');
        //                 getMaterialRefByEcrsId(ecrsId);
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
    const tblMaterialApprovalColumns = [
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

    //Params
    const materialSupplierParams = {
        tblReference : 'material_supplier',
        globalVar: materialVar.materialSupplier,
        formModel: toRef(frmMaterial.value,'materialSupplier'),
        selectedVal: '',
    };
    const materialColorParams = {
        tblReference : 'material_color',
        globalVar: materialVar.materialColor,
        formModel: toRef(frmMaterial.value,'materialColor'),
        selectedVal: '',
    };
    //User Params
    const prdnPreparedByParams = {
        globalVar: materialVar.prdnPreparedBy,
        formModel: toRef(frmMaterial.value,'prdnPreparedBy'),
        selectedVal: "",
    };
    const prdnCheckedByParams = {
        globalVar: materialVar.prdnCheckedBy,
        formModel: toRef(frmMaterial.value,'prdnCheckedBy'),
        selectedVal: "",
    };
    const prdnApprovedByParams = {
        globalVar: materialVar.prdnApprovedBy,
        formModel: toRef(frmMaterial.value,'prdnApprovedBy'),
        selectedVal: "",
    };
    const prPreparedByParams = {
        globalVar: materialVar.prPreparedBy,
        formModel: toRef(frmMaterial.value,'prPreparedBy'),
        selectedVal: "0",
    };
    const prCheckedByParams = {
        globalVar: materialVar.prCheckedBy,
        formModel: toRef(frmMaterial.value,'prCheckedBy'),
        selectedVal: "0",
    };
    const prApprovedByParams = {
        globalVar: materialVar.prApprovedBy,
        formModel: toRef(frmMaterial.value,'prApprovedBy'),
        selectedVal: "0",
    };
    const ppcPreparedByParams = {
        globalVar: materialVar.ppcPreparedBy,
        formModel: toRef(frmMaterial.value,'ppcPreparedBy'),
        selectedVal: "0",
    };
    const ppcCheckedByParams = {
        globalVar: materialVar.ppcCheckedBy,
        formModel: toRef(frmMaterial.value,'ppcCheckedBy'),
        selectedVal: "0",
    };
    const ppcApprovedByParams = {
        globalVar: materialVar.ppcApprovedBy,
        formModel: toRef(frmMaterial.value,'ppcApprovedBy'),
        selectedVal: "0",
    };
    const emsPreparedByParams = {
        globalVar: materialVar.emsPreparedBy,
        formModel: toRef(frmMaterial.value,'emsPreparedBy'),
        selectedVal: "0",
    };
    const emsCheckedByParams = {
        globalVar: materialVar.emsCheckedBy,
        formModel: toRef(frmMaterial.value,'emsCheckedBy'),
        selectedVal: "0",
    };
    const emsApprovedByParams = {
        globalVar: materialVar.emsApprovedBy,
        formModel: toRef(frmMaterial.value,'emsApprovedBy'),
        selectedVal: "0",
    };
    const qcPreparedByParams = {
        globalVar: materialVar.qcPreparedBy,
        formModel: toRef(frmMaterial.value,'qcPreparedBy'),
        selectedVal: "0",
    };
    const qcCheckedByParams = {
        globalVar: materialVar.qcCheckedBy,
        formModel: toRef(frmMaterial.value,'qcCheckedBy'),
        selectedVal: "0",
    };
    const qcApprovedByParams = {
        globalVar: materialVar.qcApprovedBy,
        formModel: toRef(frmMaterial.value,'qcApprovedBy'),
        selectedVal: "0",
    };
    const proEnggPreparedByParams = {
        globalVar: materialVar.proEnggPreparedBy,
        formModel: toRef(frmMaterial.value,'proEnggPreparedBy'),
        selectedVal: "0",
    };
    const proEnggCheckedByParams = {
        globalVar: materialVar.proEnggCheckedBy,
        formModel: toRef(frmMaterial.value,'proEnggCheckedBy'),
        selectedVal: "0",
    };
    const proEnggApprovedByParams = {
        globalVar: materialVar.proEnggApprovedBy,
        formModel: toRef(frmMaterial.value,'proEnggApprovedBy'),
        selectedVal: "0",
    };
    const mainEnggPreparedByParams = {
        globalVar: materialVar.mainEnggPreparedBy,
        formModel: toRef(frmMaterial.value,'mainEnggPreparedBy'),
        selectedVal: "0",
    };
    const mainEnggCheckedByParams = {
        globalVar: materialVar.mainEnggCheckedBy,
        formModel: toRef(frmMaterial.value,'mainEnggCheckedBy'),
        selectedVal: "0",
    };
    const mainEnggApprovedByParams = {
        globalVar: materialVar.mainEnggApprovedBy,
        formModel: toRef(frmMaterial.value,'mainEnggApprovedBy'),
        selectedVal: "0",
    };
    const enggPreparedByParams = {
        globalVar: materialVar.enggPreparedBy,
        formModel: toRef(frmMaterial.value,'enggPreparedBy'),
        selectedVal: "0",
    };
    const enggCheckedByParams = {
        globalVar: materialVar.enggCheckedBy,
        formModel: toRef(frmMaterial.value,'enggCheckedBy'),
        selectedVal: "0",
    };
    const enggApprovedByParams = {
        globalVar: materialVar.enggApprovedBy,
        formModel: toRef(frmMaterial.value,'enggApprovedBy'),
        selectedVal: "0",
    };
    const qaPreparedByParams = {
        globalVar: materialVar.qaPreparedBy,
        formModel: toRef(frmMaterial.value,'qaPreparedBy'),
        selectedVal: "0",
    };
    const qaCheckedByParams = {
        globalVar: materialVar.qaCheckedBy,
        formModel: toRef(frmMaterial.value,'qaCheckedBy'),
        selectedVal: "0",
    };
    const qaApprovedByParams = {
        globalVar: materialVar.qaApprovedBy,
        formModel: toRef(frmMaterial.value,'qaApprovedBy'),
        selectedVal: "0",
    };

    onMounted( async ()=>{
        modal.SaveMaterial = new Modal(modalSaveMaterial.value.modalRef,{ keyboard: false });
        modal.UploadMaterialRef = new Modal(modalUploadMaterialRef.value.modalRef,{ keyboard: false });
        modal.ViewMaterialRef = new Modal(modalViewMaterialRef.value.modalRef,{ keyboard: false });
        modal.Approval = new Modal(modalApproval.value.modalRef,{ keyboard: false });
        modalEcr.SaveEcrDetail = new Modal(modalSaveEcrDetail.value.modalRef,{ keyboard: false });
        modalEcr.EcrRequirements = new Modal(modalEcrRequirements.value.modalRef,{ keyboard: false });
        modalEcr.ViewEcrRequirementRef = new Modal(modalViewEcrRequirementRef.value.modalRef,{ keyboard: false });

        modalSaveMaterial.value.modalRef.addEventListener('hidden.bs.modal', event => {
            resetEcrForm(frmMaterial.value);
        });
        modalSaveEcrDetail.value.modalRef.addEventListener('hidden.bs.modal', event => {
            resetEcrForm(frmEcrDetails.value);
        });
        await getDropdownMasterByOpt(descriptionOfChangeParams);
        await getDropdownMasterByOpt(reasonOfChangeParams);
        await getDropdownMasterByOpt(typeOfPartParams);
        await getDropdownMasterByOpt(materialSupplierParams);
        await getDropdownMasterByOpt(materialColorParams);
        await getCategoryAdminAccessOpt();
    })

    const reloadDropdownMaster = async (dropdownMastersDetails) => {
        let stringToVal = '';
        switch (dropdownMastersDetails) {
            case 'typeOfPartParams':

                stringToVal = eval(dropdownMastersDetails);
                break;
            case 'materialSupplierParams':
                stringToVal = eval(dropdownMastersDetails);
                break;
            case 'materialColorParams':
                stringToVal = eval(dropdownMastersDetails);
                break;
            default:
                stringToVal = '';
                break;
        }
        if(stringToVal === ''){
            alert('Invalid Dropdown Master Details')
        }
        await getDropdownMasterByOpt(stringToVal);
    }
    const reloadRapidxUserMaster = async (dropdownRapidxUserMaster,rapidxUserDeptGroup=null) => {
        switch (dropdownRapidxUserMaster) {
            case 'prdn':
                await getRapidxUserByIdOpt(prdnPreparedByParams);
                await getRapidxUserByIdOpt(prdnCheckedByParams);
                await getRapidxUserByIdOpt(prdnApprovedByParams);
                break;
            case 'purchasing':
                    getRapidxUserByIdOpt(prPreparedByParams);
                    getRapidxUserByIdOpt(prCheckedByParams);
                    getRapidxUserByIdOpt(prApprovedByParams);
                break;
            case 'ppc':
                getRapidxUserByIdOpt(ppcPreparedByParams);
                getRapidxUserByIdOpt(ppcCheckedByParams);
                getRapidxUserByIdOpt(ppcApprovedByParams);
                break;
            case 'ems':
                getRapidxUserByIdOpt(emsPreparedByParams);
                getRapidxUserByIdOpt(emsCheckedByParams);
                getRapidxUserByIdOpt(emsApprovedByParams);
                break;
            case 'qc':
                getRapidxUserByIdOpt(qcPreparedByParams);
                getRapidxUserByIdOpt(qcCheckedByParams);
                getRapidxUserByIdOpt(qcApprovedByParams);
                break;
            case 'proEngg':
                getRapidxUserByIdOpt(proEnggPreparedByParams);
                getRapidxUserByIdOpt(proEnggCheckedByParams);
                getRapidxUserByIdOpt(proEnggApprovedByParams);
                break;
            case 'mainEngg':
                getRapidxUserByIdOpt(mainEnggPreparedByParams);
                getRapidxUserByIdOpt(mainEnggCheckedByParams);
                getRapidxUserByIdOpt(mainEnggApprovedByParams);
                break;
            case 'engg':
                getRapidxUserByIdOpt(enggPreparedByParams);
                getRapidxUserByIdOpt(enggCheckedByParams);
                getRapidxUserByIdOpt(enggApprovedByParams);
                break;
            case 'qa':
                getRapidxUserByIdOpt(qaPreparedByParams);
                getRapidxUserByIdOpt(qaCheckedByParams);
                getRapidxUserByIdOpt(qaApprovedByParams);
                break;
            default:
                alert('Invalid Dropdown Master Details');
                break;
        }

    }
    // Override default classes for small size and readonly styles
    const multiselectSm =ref ({
        container: 'text-sm rounded border-gray-300 bg-gray-100 opacity-70 cursor-not-allowed',
        input: 'text-sm',
        singleLabel: 'text-sm',
        search: 'hidden', // Hide input if readonly
    });

    //Functions
    const onChangeAdminAccess = async (selectedParams)=>{
        tblEcrByCategoryStatus.value.dt.ajax.url("api/load_ecr_material_by_status?category=Material"+"&& adminAccess="+selectedParams).draw();
        selectedAdminAccess.value = selectedParams;
    }
    const resetEcrForm = async (frmElement) => {
        for (const key in frmElement) {
            frmElement[key] = '';
        }
    };
    const btnApprovedDisapproved = async (decision) => {
        isApprovedDisappproved.value = decision;
        modal.Approval.show();
    }
    const saveApproval = async (selectedId,selectedEcrsId,remarks,isApprovedDisappproved,approvalType = null) => {
        let apiParams = {
            selectedId : selectedId,
            status : isApprovedDisappproved,
            remarks : remarks,
        }
        if(approvalType === 'PMIAPP'){
            let apiParams = {
                ecrsId : selectedEcrsId,
                status : isApprovedDisappproved,
                remarks : remarks,
            }
            axiosFetchData(apiParams,'api/save_pmi_internal_approval',function(response){
                modal.Approval.hide();
                modal.SaveMaterial.hide();
                tblEcrByCategoryStatus.value.dt.ajax.url("api/load_ecr_material_by_status?category=Material"+"&& adminAccess="+selectedAdminAccess.value).draw();
            });
            return;
        }

        axiosFetchData(apiParams,'api/save_material_approval',function(response){
            tblEcrByCategoryStatus.value.dt.ajax.url("api/load_ecr_material_by_status?category=Material"+"&& adminAccess="+selectedAdminAccess.value).draw();
            modal.Approval.hide();
            modal.SaveMaterial.hide();
        });
    }
    const changeMaterialRef = async (event)  => {
        materialRef.value =  Array.from(event.target.files);
    }
    const getMaterialRefByEcrsId = async (ecrsId) => {
        let apiParams = {
            ecrsId : ecrsId
        }
        axiosFetchData(apiParams,'api/get_material_ref_by_ecrs_id',function(response){
            let data = response.data;
            let ecrsId = data.ecrsId;
            let originalFilename = data.originalFilename;
            arrOriginalFilenames.value = originalFilename;
            selectedEcrsIdEncrypted.value = ecrsId;
            modal.ViewMaterialRef.show();
        });
    }

    const btnLinkViewMaterialRef = async (selectedEcrsIdEncrypted,index) => {
        window.open(`api/view_material_ref?ecrsId=${selectedEcrsIdEncrypted} && index=${index}`, '_blank');
    }
    const btnLinkViewExternalDisposition = async (selectedEcrsId,index) => {
        window.open(`api/view_external_disposition?ecrsId=${selectedEcrsId} && index=${index} && imageType=after`, '_blank');
    }

    const getMaterialEcrById = async (ecrId) => {
        let apiParams = {
            ecrId : ecrId
        }
        axiosFetchData(apiParams,'api/get_material_ecr_by_id',function(response){
            let data = response.data;
            let material = data.material[0];
            let internalExternal = data.internalExternal;
            let materialApprovalCollection = data.materialApprovalCollection;
            //Load ECR Requirement by Category and Ecrs Id
            tblEcrManRequirements.value.dt.ajax.url("api/load_ecr_requirements?category=1&ecrsId="+material.ecrs_id).draw();
            tblEcrMatRequirements.value.dt.ajax.url("api/load_ecr_requirements?category=2&ecrsId="+material.ecrs_id).draw();
            tblEcrMachineRequirements.value.dt.ajax.url("api/load_ecr_requirements?category=3&ecrsId="+material.ecrs_id).draw();
            tblEcrMethodRequirements.value.dt.ajax.url("api/load_ecr_requirements?category=4&ecrsId="+material.ecrs_id).draw();
            tblEcrEnvironmentRequirements.value.dt.ajax.url("api/load_ecr_requirements?category=5&ecrsId="+material.ecrs_id).draw();
            tblEcrOthersRequirements.value.dt.ajax.url("api/load_ecr_requirements?category=6&ecrsId="+material.ecrs_id).draw();
            modalEcr.EcrRequirements.show();

            console.log(material.ecrs_id);
            frmMaterial.value.ecrsId = material.ecrs_id;
            frmMaterial.value.materialId = material.id;
            frmMaterial.value.pdMaterial = material.pd_material;
            frmMaterial.value.msds = material.msds;
            frmMaterial.value.icp = material.icp;
            frmMaterial.value.gp = material.gp;
            frmMaterial.value.qoutation = material.qoutation;
            frmMaterial.value.materialSupplier = material.material_supplier;
            frmMaterial.value.materialColor = material.material_color;
            frmMaterial.value.rohs = material.rohs;
            frmMaterial.value.materialSample = material.material_sample;
            frmMaterial.value.coc = material.coc;
            frmMaterial.value.remarks = material.remarks;
            isInternalExternal.value = internalExternal;

            let prPreparedBy = materialApprovalCollection.PURPB[0].rapidx_user_id ?? "0";
            let prCheckedBy = materialApprovalCollection.PURCB[0].rapidx_user_id ?? "0";
            let prApprovedBy = materialApprovalCollection.PURAB[0].rapidx_user_id ?? "0";
            let ppcPreparedBy = materialApprovalCollection.PPCPB[0].rapidx_user_id ?? "0";
            let ppcCheckedBy = materialApprovalCollection.PPCCB[0].rapidx_user_id ?? "0";
            let ppcApprovedBy = materialApprovalCollection.PPCAB[0].rapidx_user_id ?? "0";
            let emsPreparedBy = materialApprovalCollection.EMSPB[0].rapidx_user_id ?? "0";
            let emsCheckedBy = materialApprovalCollection.EMSCB[0].rapidx_user_id ?? "0";
            let emsApprovedBy = materialApprovalCollection.EMSAB[0].rapidx_user_id ?? "0";
            let qcPreparedBy = materialApprovalCollection.LQCPB[0].rapidx_user_id ?? "0";
            let qcCheckedBy = materialApprovalCollection.LQCCB[0].rapidx_user_id ?? "0";
            let qcApprovedBy = materialApprovalCollection.LQCAB[0].rapidx_user_id ?? "0";
            let qaPreparedBy = materialApprovalCollection.QAPB[0].rapidx_user_id ?? "0";
            let qaCheckedBy = materialApprovalCollection.QACB[0].rapidx_user_id ?? "0";
            let qaApprovedBy = materialApprovalCollection.QAAB[0].rapidx_user_id ?? "0";
            setTimeout(() => {  //Cannot display data immediately, need to wait for the DOM to be
                frmMaterial.value.prPreparedBy = prPreparedBy
                frmMaterial.value.prCheckedBy = prCheckedBy
                frmMaterial.value.prApprovedBy = prApprovedBy
                frmMaterial.value.ppcPreparedBy = ppcPreparedBy
                frmMaterial.value.ppcCheckedBy = ppcCheckedBy
                frmMaterial.value.ppcApprovedBy = ppcApprovedBy
                frmMaterial.value.emsPreparedBy = emsPreparedBy
                frmMaterial.value.emsCheckedBy = emsCheckedBy
                frmMaterial.value.emsApprovedBy = emsApprovedBy
                frmMaterial.value.qcPreparedBy = qcPreparedBy
                frmMaterial.value.qcCheckedBy = qcCheckedBy
                frmMaterial.value.qcApprovedBy = qcApprovedBy
                frmMaterial.value.qaPreparedBy = qaPreparedBy
                frmMaterial.value.qaCheckedBy = qaCheckedBy
                frmMaterial.value.qaApprovedBy = qaApprovedBy
            }, 500);
            if (internalExternal === "External"){
                setTimeout(() => {  //Cannot display data immediately, need to wait for the DOM to be updated
                    let prdnPreparedBy = materialApprovalCollection.PRDNPB[0].rapidx_user_id ?? "0";
                    let prdnCheckedBy = materialApprovalCollection.PRDNCB[0].rapidx_user_id ?? "0";
                    let prdnApprovedBy = materialApprovalCollection.PRDNAP[0].rapidx_user_id ?? "0";
                    let mainEnggPreparedBy = materialApprovalCollection.MENGPB[0].rapidx_user_id ?? "0";
                    let mainEnggCheckedBy = materialApprovalCollection.MENGCB[0].rapidx_user_id ?? "0";
                    let mainEnggApprovedBy = materialApprovalCollection.MENGAB[0].rapidx_user_id ?? "0";
                    let proEnggPreparedBy = materialApprovalCollection.PENGPB[0].rapidx_user_id ?? "0";
                    let proEnggCheckedBy = materialApprovalCollection.PENGCB[0].rapidx_user_id ?? "0";
                    let proEnggApprovedBy = materialApprovalCollection.PENGAB[0].rapidx_user_id ?? "0";
                    frmMaterial.value.prdnPreparedBy = prdnPreparedBy;
                    frmMaterial.value.prdnCheckedBy = prdnCheckedBy;
                    frmMaterial.value.prdnApprovedBy = prdnApprovedBy;
                    frmMaterial.value.mainEnggPreparedBy = mainEnggPreparedBy;
                    frmMaterial.value.mainEnggCheckedBy = mainEnggCheckedBy;
                    frmMaterial.value.mainEnggApprovedBy = mainEnggApprovedBy;
                    frmMaterial.value.proEnggPreparedBy = proEnggPreparedBy;
                    frmMaterial.value.proEnggCheckedBy = proEnggCheckedBy;
                    frmMaterial.value.proEnggApprovedBy = proEnggApprovedBy;
                    frmMaterial.value.engPreparedBy = '0'
                    frmMaterial.value.engCheckedBy = '0'
                    frmMaterial.value.engApprovedBy = '0'

                }, 500);
            }
            if (internalExternal === "Internal"){
                setTimeout(() => {  //Cannot display data immediately, need to wait for the DOM to be updated
                    let engPreparedBy = materialApprovalCollection.ENGPB[0].rapidx_user_id ?? "0";
                    let engCheckedBy = materialApprovalCollection.ENGCB[0].rapidx_user_id ?? "0";
                    let engApprovedBy = materialApprovalCollection.ENGAB[0].rapidx_user_id ?? "0";

                    frmMaterial.value.prdnPreparedBy = '0';
                    frmMaterial.value.prdnCheckedBy = '0';
                    frmMaterial.value.prdnApprovedBy = '0';
                    frmMaterial.value.mengPreparedBy = '0';
                    frmMaterial.value.mengCheckedBy = '0';
                    frmMaterial.value.mengApprovedBy = '0';
                    frmMaterial.value.pengPreparedBy = '0';
                    frmMaterial.value.pengCheckedBy = '0';
                    frmMaterial.value.pengApprovedBy = '0';
                    frmMaterial.value.engPreparedBy = engPreparedBy;
                    frmMaterial.value.engCheckedBy = engCheckedBy;
                    frmMaterial.value.engApprovedBy = engApprovedBy;
                }, 500);
            }
            modal.SaveMaterial.show();
        });
    }
    const saveMaterial = async () =>{
        let formData = new FormData();
        //Append form data
        [
            ["ecrs_id", frmMaterial.value.ecrsId],
            ["material_id", frmMaterial.value.materialId],
            ["pd_material", frmMaterial.value.pdMaterial],
            ["msds", frmMaterial.value.msds],
            ["icp", frmMaterial.value.icp],
            ["gp", frmMaterial.value.gp],
            ["qoutation", frmMaterial.value.qoutation],
            ["material_supplier", frmMaterial.value.materialSupplier],
            ["material_color", frmMaterial.value.materialColor],
            ["rohs", frmMaterial.value.rohs],
            ["material_sample", frmMaterial.value.materialSample],
            ["coc", frmMaterial.value.coc],
            ["prdn_prepared_by", frmMaterial.value.prdnPreparedBy],
            ["prdn_checked_by", frmMaterial.value.prdnCheckedBy],
            ["prdn_approved_by", frmMaterial.value.prdnApprovedBy],
            ["pr_prepared_by", frmMaterial.value.prPreparedBy],
            ["pr_checked_by", frmMaterial.value.prCheckedBy],
            ["pr_approved_by", frmMaterial.value.prApprovedBy],
            ["ppc_prepared_by", frmMaterial.value.ppcPreparedBy],
            ["ppc_checked_by", frmMaterial.value.ppcCheckedBy],
            ["ppc_approved_by", frmMaterial.value.ppcApprovedBy],
            ["ems_prepared_by", frmMaterial.value.emsPreparedBy],
            ["ems_checked_by", frmMaterial.value.emsCheckedBy],
            ["ems_approved_by", frmMaterial.value.emsApprovedBy],
            ["qc_prepared_by", frmMaterial.value.qcPreparedBy],
            ["qc_checked_by", frmMaterial.value.qcCheckedBy],
            ["qc_approved_by", frmMaterial.value.qcApprovedBy],
            ["pro_engg_prepared_by", frmMaterial.value.proEnggPreparedBy],
            ["pro_engg_checked_by", frmMaterial.value.proEnggCheckedBy],
            ["pro_engg_approved_by", frmMaterial.value.proEnggApprovedBy],
            ["main_engg_prepared_by", frmMaterial.value.mainEnggPreparedBy],
            ["main_engg_checked_by", frmMaterial.value.mainEnggCheckedBy],
            ["main_engg_approved_by", frmMaterial.value.mainEnggApprovedBy],
            ["engg_prepared_by", frmMaterial.value.enggPreparedBy],
            ["engg_checked_by", frmMaterial.value.enggCheckedBy],
            ["engg_approved_by", frmMaterial.value.enggApprovedBy],
            ["qa_prepared_by", frmMaterial.value.qaPreparedBy],
            ["qa_checked_by", frmMaterial.value.qaCheckedBy],
            ["qa_approved_by", frmMaterial.value.qaApprovedBy],

        ].forEach(([key, value]) =>
            formData.append(key, value)
        );
        axiosSaveData(formData,'api/save_material', (response) =>{
            tblEcrByCategoryStatus.value.dt.ajax.url("api/load_ecr_material_by_status?category=Material"+"&& adminAccess="+selectedAdminAccess.value).draw();
            modal.SaveMaterial.hide();

        });
    }
    const frmUploadMaterialRef = async () => {
        let formData = new FormData();
        materialRef.value.forEach((file, index) => {
            formData.append('material_ref[]', file);
        });
        formData.append("ecrsId", selectedEcrsId.value);

        axiosSaveData(formData,'api/upload_material_ref',(response) =>{
            console.log(response);
        });
    }
</script>

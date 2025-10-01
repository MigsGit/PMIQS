<template>
    <div class="container-fluid px-4">
        <h4 class="mt-4">Ecr Requirement Master</h4>
        <div class="row justify-content-between">
            <div class="col-md-3">
                <Multiselect
                    v-model="slctDropdownMasterByCategory"
                    :close-on-select="true"
                    :searchable="true"
                    :options="optDropdownMasterByCategory"
                    @change="onDropdownMasterByCategory($event)"
                />
            </div>
        </div>

        <div class="card mt-3"  style="width: 100%;">
            <div class="row justify-content-end">
                <div class="col-md-2 mt-3">
                    <button v-if="slctDropdownMasterByCategory!= ''" @click="btnAddDropdownMasterDetails" type="button" class="btn btn-primary btn-sm mb-2"><i class="fas fa-plus"></i> Add Dropdown Details</button>
                </div>
            </div>
            <div class="card-body overflow-auto">
                <div class="table-responsive">
                    <DataTable
                        width="100%" cellspacing="0"
                        class="table mt-2"
                        ref="tblDropdownMasterDetails"
                        :columns="tblDropdownMasterDetailsColumns"
                        ajax="api/load_classification_requirements"
                        :options="{
                            serverSide: true, //Serverside true will load the network
                            columnDefs:[
                                {orderable:false,target:[0]}
                            ]
                        }"
                    >
                        <thead>
                            <tr>
                                <th style="width:10%">Action</th>
                                <th style="width:10%">Status</th>
                                <th>Requirement</th>
                                <th>Details </th>
                                <th>Evidence</th>
                            </tr>
                        </thead>
                    </DataTable>
                </div>
            </div>
        </div>
    </div>
    <ModalComponent icon="fa-user" modalDialog="modal-dialog modal-md" title="Ecr Requirement Master Details" @add-event="saveEcrRequirementDetails()" ref="modalSaveEcrRequirementDetails">
        <template #body>
                <div class="row">
                    <div class="input flex-nowrap mb-2 input-group-sm">
                            <input v-model="frmEcrRequirementDetails.ecrRequirementDetailsId" type="number" class="form-control form-control" aria-describedby="addon-wrapping" readonly>
                    </div>
                    <div class="col-sm-12">
                        <div class="input-group flex-nowrap mb-2 input-group-sm">
                            <span class="input-group-text" id="addon-wrapping">Ecr Category:</span>
                            <Multiselect
                                :disabled="isDisabledCategory"
                                v-model="frmEcrRequirementDetails.category"
                                :close-on-select="true"
                                :searchable="true"
                                :options="optDropdownMasterByCategory"
                            />
                        </div>
                    </div>
                    <div class="col-sm-12">
                        <div class="input-group flex-nowrap mb-2 input-group-sm">
                            <span class="input-group-text" id="addon-wrapping">Requirement:</span>
                            <input  v-model="frmEcrRequirementDetails.requirement" type="text" class="form-control form-control" aria-describedby="addon-wrapping">
                        </div>
                    </div>
                    <div class="col-sm-12">
                        <div class="input-group flex-nowrap mb-2 input-group-sm">
                            <span class="input-group-text" id="addon-wrapping">Details:</span>
                            <textarea v-model="frmEcrRequirementDetails.details"class="form-control form-control" aria-describedby="addon-wrapping">
                            </textarea>
                        </div>
                    </div>
                    <div class="col-sm-12">
                        <div class="input-group flex-nowrap mb-2 input-group-sm">
                            <span class="input-group-text" id="addon-wrapping">Evidence:</span>
                            <textarea v-model="frmEcrRequirementDetails.evidence"class="form-control form-control" aria-describedby="addon-wrapping">
                            </textarea>
                        </div>
                    </div>
                </div>
            </template>
            <template #footer>
                <button type="button" id= "closeBtn" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-success btn-sm"><font-awesome-icon class="nav-icon" icon="fas fa-save" />&nbsp;     Save</button>
            </template>
    </ModalComponent>
</template>

<script setup>
    import {
        onMounted,
        ref,
        toRef,
    } from 'vue'
    import ModalComponent from '../components/ModalComponent.vue';
    import useSettings from '../composables/settings.js';
    import useCommon from '../composables/common.js';
    import useForm from '../composables/utils/useForm.js'
    import DataTable from 'datatables.net-vue3';
    import DataTablesCore from 'datatables.net-bs5';
    DataTable.use(DataTablesCore);

    //Ref State
    const modalSaveEcrRequirementDetails = ref(null);
    const optDropdownMaster = ref([]);
    const optDropdownMasterByCategory = ref([]);
    const slctDropdownMaster = ref(null);
    const slctDropdownMasterByCategory = ref(null);
    const btnDropdownMasterDetails = ref(null);
    const tblDropdownMasterDetails = ref(null);
    const isDisabledCategory = ref(true);

    //Constant Object
    const {
        modal,
        frmDropdownMasterDetails,
        frmEcrRequirementDetails,
        axiosFetchData,
        getDropdownMasterByOpt
    } = useSettings();
    const {
        resetEcrForm,
    } = useCommon();
    const { axiosSaveData } = useForm(); // Call the useFetch function
    const selectedCategory = ref(null);
    const tblDropdownMasterDetailsColumns = [
        {   data: 'get_action',
            createdCell(cell){
                let btnDropdownMasterDetails = cell.querySelector('#btnDropdownMasterDetails');
                let btnDelClassificationRequirements = cell.querySelector('#btnDelClassificationRequirements');
                if(btnDropdownMasterDetails != null){
                    btnDropdownMasterDetails.addEventListener('click',function(){
                        let classificationRequirementsId = this.getAttribute('dropdown-master-details-id');
                        getEcrRequirementDetailsById(classificationRequirementsId);
                    });
                }
                if(btnDelClassificationRequirements != null){
                    btnDelClassificationRequirements.addEventListener('click',function(){
                        let classificationRequirementsId = this.getAttribute('dropdown-master-details-id');
                        Swal.fire({
                            title: 'Confirmation',
                            text: 'Are you sure to delete this data !',
                            icon: 'warning',
                            allowOutsideClick: false,
                            showCancelButton: true,
                            confirmButtonColor: '#3085d6',
                            cancelButtonColor: '#d33',
                            confirmButtonText: 'Yes'
                        }).then((result) => {
                            delClassificationRequirements(classificationRequirementsId)
                        })
                    });
                }
            }
        },
        {   data: 'get_status'} ,
        {   data: 'requirement'} ,
        {   data: 'details'} ,
        {   data: 'evidence'} ,
    ];

    const dropdownMasterByCategoryParams = {
        globalVar: optDropdownMasterByCategory,
        formModel: slctDropdownMasterByCategory,
        selectedVal: '',
    };
    const ecrDropdownMasterByCategoryParams = {
        globalVar: optDropdownMasterByCategory,
        formModel: toRef(frmEcrRequirementDetails.value,'category'),
        selectedVal: '',
    };


    // optDropdownMasterByCategory
    onMounted(async () => {
        modal.SaveEcrRequirementDetails = new Modal(modalSaveEcrRequirementDetails.value.modalRef,{ keyboard: false });
        await getDropdownMasterCategory(dropdownMasterByCategoryParams);
        await getDropdownMasterCategory(ecrDropdownMasterByCategoryParams);
    })
    //Functions
    const getEcrRequirementDetailsById = async (dropdownMasterDetailsId) => {
        let apiParams = {
            dropdownMasterDetailsId : dropdownMasterDetailsId
        }
        axiosFetchData(apiParams,'api/get_ecr_requirement_details_by_id',function(response){
            let data = response.data;
            let classificationRequirement = data.classificationRequirement;
            frmEcrRequirementDetails.value.ecrRequirementDetailsId = classificationRequirement.id,
            frmEcrRequirementDetails.value.category = classificationRequirement.classifications_id,
            frmEcrRequirementDetails.value.requirement = classificationRequirement.requirement,
            frmEcrRequirementDetails.value.details = classificationRequirement.details,
            frmEcrRequirementDetails.value.evidence = classificationRequirement.evidence,
            modal.SaveEcrRequirementDetails.show();
        });
    }
    const getDropdownMasterCategory = async (params) =>{
        let apiParams = {}
        axiosFetchData(apiParams,'api/get_ecr_requirement_master_category',function(response){
            let data = response.data;
            let dropdownMaster = data.dropdownMaster;
            params.globalVar.value.splice(0, params.globalVar.value.length,
                { value: '', label: '-Select Category-', disabled:true }, // Push "" option at the start
                    ...dropdownMaster.map((value) => {
                    return {
                        value: value.id,
                        label: value.category
                    }
                }),
            );
            params.formModel.value = params.selectedVal; //selectedValue after the reading data
        });
    }
    const onDropdownMasterByCategory = async (category) => {
        frmEcrRequirementDetails.value.category = category;
        tblDropdownMasterDetails.value.dt.ajax.url('api/load_classification_requirements?dropDownMastersId='+category).draw();
        // frmDropdownMasterDetails.value.dropdownMastersId = dropDownMastersId;
    }

    const btnAddDropdownMasterDetails = async () => {
        modal.SaveEcrRequirementDetails.show();
    }


    const saveEcrRequirementDetails = async () => {
        let formData = new FormData();
        //Append form data
        [
             ["ecr_requirement_details_id", frmEcrRequirementDetails.value.ecrRequirementDetailsId],
             ["classifications_id", frmEcrRequirementDetails.value.category],
             ["requirement", frmEcrRequirementDetails.value.requirement ],
             ["details", frmEcrRequirementDetails.value.details ],
             ["evidence", frmEcrRequirementDetails.value.evidence ],
        ].forEach(([key, value]) =>
            formData.append(key, value)
        );
        axiosSaveData(formData,'api/save_ecr_requirement_details', (response) =>{
            modal.SaveEcrRequirementDetails.hide();
            tblDropdownMasterDetails.value.dt.ajax.url('api/load_classification_requirements?dropDownMastersId='+frmEcrRequirementDetails.value.category).draw();
        });
    }
    const delClassificationRequirements = async (classificationRequirementsId) => {
        let formData = new FormData();
        //Append form data
        [
             ["classificationRequirementsId", classificationRequirementsId],
        ].forEach(([key, value]) =>
            formData.append(key, value)
        );

        axiosSaveData(formData,'api/del_classification_requirements', (response) =>{
            tblDropdownMasterDetails.value.dt.ajax.url('api/load_classification_requirements?dropDownMastersId='+frmEcrRequirementDetails.value.category).draw();
        });
    }
</script>


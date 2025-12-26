<template>
    <div class="container-fluid px-4">
        <div class="row mt-3">
            <div class="col-6 shadow">
                <div class="row">
                    <div class="col-sm-2">
                        <button @click="btnSavePdfEmailFormat" type="submit" style="float: right !important;" class="btn btn-primary"> <font-awesome-icon class="nav-icon" icon="fas fa-envelope" />&nbsp; Update Recipients </button>
                    </div>
                    <div class="col-sm-10">
                        <button @click="btnForApproval" type="submit" style="float: right !important;" class="btn btn-info"> <font-awesome-icon class="nav-icon" icon="fas fa-thumbs-up" />&nbsp; For Approval </button>
                    </div>
                </div>
                <div class="row">
                    <div class="col-6">
                        <h4>Items & Description Details</h4>
                    </div>
                </div>
                <div class="row mt-3">
                <div class="col-6">
                    <div class="input-group flex-nowrap mb-2 input-group-sm">
                        <span class="input-group-text" id="addon-wrapping">Control No. :</span>
                        <input v-model="frmItem.controlNo" type="text" class="form-control" id="inlineFormInputGroup" readonly>
                    </div>
                </div>
                <div class="col-6">
                    <div class="input-group flex-nowrap mb-2 input-group-sm">
                        <span ref="status" class="badge badge-sm rounded-pill bg-primary" id="addon-wrapping">FOR UPDATE</span>
                    </div>
                </div>
                </div>
                <div class="row">
                    <div class="col-6">
                        <div class="input-group flex-nowrap mb-2 input-group-sm">
                            <span class="input-group-text" id="addon-wrapping">Category. :</span>
                            <Multiselect
                                v-model="frmItem.category"
                                :close-on-select="true"
                                :searchable="true"
                                :options="commonVar.category"
                            />
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="input-group flex-nowrap mb-2 input-group-sm">
                            <span class="input-group-text" id="addon-wrapping">Division. :</span>
                            <Multiselect
                                v-model="frmItem.division"
                                :close-on-select="true"
                                :searchable="true"
                                :options="commonVar.division"
                            />
                        </div>

                    </div>
                </div>
                <div class="row">
                    <div class="col-6">
                        <div class="input-group flex-nowrap mb-2 input-group-sm">
                            <span class="input-group-text" id="addon-wrapping">Remarks. :</span>
                            <textarea v-model="frmItem.remarks" type="text" class="form-control" id="inlineFormInputGroup" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="input-group flex-nowrap mb-2 input-group-sm">
                            <span class="input-group-text" id="addon-wrapping">Created by :</span>
                            <input v-model="frmItem.createdBy" type="text" class="form-control" id="inlineFormInputGroup" readonly>
                        </div>
                    </div>
                </div>
                <!-- Item Description -->
                <div class="col-12">
                </div>
                <div class="col-12 mb-3">
                    <div class="row itemDesc " v-for="(rowSaveItem, indexItem) in rowSaveItems" :key="rowSaveItem.itemNo">
                        <div class="card mb-2">
                                <h5 class="mb-0">
                                    <button id="" class="btn btn-link collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseMan" aria-expanded="true" aria-controls="collapseMan">
                                        Item No. {{ rowSaveItem.itemNo }}

                                    </button>
                                </h5>
                            <div id="collapseMan" class="collapse show" data-bs-parent="#accordionMain">
                                <div class="card-body overflow-auto">
                                    <div class="row">
                                        <div class="col-12">
                                            <table class="table table-striped">
                                                <thead>
                                                    <tr>
                                                        <th scope="col">#</th>
                                                        <th scope="col" >PartCode/Type</th>
                                                        <th scope="col">Description/ItemName</th>
                                                        <th scope="col" v-show="frmItem.category === 'RM'">Length</th>
                                                        <th scope="col" v-show="frmItem.category === 'RM'">Width</th>
                                                        <th scope="col" v-show="frmItem.category === 'RM'">Height</th>
                                                        <th scope="col" v-show="frmItem.category === 'RM'">Type</th>
                                                        <th scope="col" v-show="frmItem.category === 'RM'">Thickness</th>
                                                        <th scope="col" v-show="frmItem.category === 'RM'">Width</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr v-for="(rowSaveDescription, indexDescription) in rowSaveItem.rows" :key="rowSaveDescription.indexDescription">
                                                        <td>
                                                            <span>{{ indexDescription+1 }}</span>
                                                            <input v-model="rowSaveDescription.descItemNo" type="hidden" class="form-control" id="inlineFormInputGroup" placeholder="Partcode/Type" readonly>
                                                        </td>
                                                        <td>
                                                            <input v-model="rowSaveDescription.partcodeType" type="text" class="form-control" id="inlineFormInputGroup" placeholder="PartCode/Type">
                                                        </td>
                                                        <td>
                                                            <input v-model="rowSaveDescription.descriptionItemName" type="text" class="form-control" id="inlineFormInputGroup" placeholder="Description/Item Name">
                                                        </td>

                                                        <td v-show="frmItem.category === 'RM'">
                                                            <input v-model="rowSaveDescription.matSpecsLength" type="number" min=0 class="form-control" id="inlineFormInputGroup">
                                                        </td>
                                                        <td v-show="frmItem.category === 'RM'">
                                                            <input v-model="rowSaveDescription.matSpecsWidth" type="number" min="0" class="form-control" id="inlineFormInputGroup">
                                                        </td>
                                                        <td v-show="frmItem.category === 'RM'">
                                                            <input v-model="rowSaveDescription.matSpecsHeight" type="number" min="0" class="form-control" id="inlineFormInputGroup">
                                                        </td>
                                                        <td v-show="frmItem.category === 'RM'">
                                                            <input v-model="rowSaveDescription.matRawType" type="text" class="form-control" id="inlineFormInputGroup">
                                                        </td v-show="frmItem.category === 'RM'">
                                                        <td v-show="frmItem.category === 'RM'">
                                                            <input v-model="rowSaveDescription.matRawThickness" type="number" min="0" class="form-control" id="inlineFormInputGroup">
                                                        </td>
                                                        <td v-show="frmItem.category === 'RM'">
                                                            <input v-model="rowSaveDescription.matRawWidth" type="number" min="0" class="form-control" id="inlineFormInputGroup">
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Approvers -->
                <!-- v-show="isSelectReadonly === true" -->
                <div class="row mt-3">
                    <div class="card mb-2">
                            <h5 class="mb-0">
                                <button id="" class="btn btn-link collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseApprovalSummary" aria-expanded="true" aria-controls="collapseApprovalSummary">
                                    ECR Approver Summary
                                </button>
                            </h5>
                        <div id="collapseApprovalSummary" class="collapse show" data-bs-parent="#accordionMain">
                            <div class="card-body overflow-auto">
                                <div class="row">
                                    <div class="col-12">
                                        <DataTable
                                            width="100%" cellspacing="0"
                                            class="table mt-2"
                                            ref="tblPmApproverSummary"
                                            :columns="tblPmApproverSummaryColumns"
                                            ajax="api/load_pm_approval_summary?itemsId"
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
            </div>
            <div class="col-6 shadow">
                <div class="row">
                    <div class="col-6">
                        <h4>Classification Details</h4>
                       {{ pmItemStatusParam }}
                    </div>

                    <div class="col-6 mb-3">
                        <button v-show="pmItemStatusParam === 'FORUP'" @click="formSaveClassificationQty" type="submit" style="float: right !important;" class="btn btn-success"><font-awesome-icon class="nav-icon" icon="fas fa-save" />&nbsp;Save </button>
                    </div>
                </div>
                 <!-- Classification Cards -->
                    <div v-for="(card, cardIndex) in cardSaveClassifications" :key="cardIndex" class="card mb-3">
                   <div class="card-header">
                       <h5>Description / ItemName: {{ card.descriptionPartName }}</h5>
                       <h5>Part Code / Type: {{ card.descriptionPartCode}}</h5>
                    </div>
                        <div class="card-body overflow-auto">
                        <table class="table table-bordered">
                        <thead>
                            <tr>
                            <th>#</th>
                            <th>Classification</th>
                            <th>Quantity</th>
                            <th style="width: 30%;">UOM</th>
                            <th>Unit Price</th>
                            <th style="width: 20%;">Remarks</th>
                            <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(rowSaveClassifications, rowIndex) in card.rows" :key="rowIndex">
                            <td>{{ rowIndex + 1 }}</td>
                            <td>
                                <input
                                type="text"
                                class="form-control"
                                v-model="rowSaveClassifications.classification"
                                placeholder="Enter classification"
                                />
                                <input
                                type="text"
                                class="form-control"
                                v-model="rowSaveClassifications.descriptionsId"
                                placeholder="Enter classification"
                                />
                            </td>
                            <td>
                                <input
                                type="number"
                                class="form-control"
                                v-model="rowSaveClassifications.qty"
                                placeholder="-Select an Option-"
                                selected=""
                                />
                            </td>
                            <td>
                                <Multiselect
                                v-model="rowSaveClassifications.uom"
                                :close-on-select="true"
                                :searchable="true"
                                :options="commonVar.uom"
                                placeholder="-Select an Option-"
                            />
                                <!-- nmodify -->
                                <!-- <input
                                type="number"
                                class="form-control"
                                v-model="rowSaveClassifications.uom"
                                placeholder="Enter quantity"
                                /> -->
                            </td>
                            <td>
                                <input
                                type="text"
                                class="form-control"
                                v-model="rowSaveClassifications.unitPrice"
                                placeholder="Enter unit price"
                                />
                            </td>
                            <td>
                                <input
                                type="text"
                                class="form-control"
                                v-model="rowSaveClassifications.remarks"
                                placeholder="Enter remarks"
                                />
                            </td>
                            <td>
                                <!-- <center> -->
                                    <button @click="removeRowFromCard(cardIndex, rowIndex)" class="btn btn-danger mt-3">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                <!-- </center> -->
                            </td>
                            </tr>
                        </tbody>
                        </table>
                        <button @click="addRowClassification(cardIndex,card.rows)" class="btn btn-primary">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                    </div>
            </div>
        </div>
    </div>
    <!-- @add-event="formSaveApproval"  -->
    <ModalComponent icon="fa-download" modalDialog="modal-dialog modal-md" title="For Your Approval" ref="modalSaveApproval">
        <template #body>
            <div class="row mt-3">
                <div class="input-group flex-nowrap mb-2 input-group-sm">
                    <span class="input-group-text" id="addon-wrapping">Remarks:</span>
                   <textarea class="form-control" v-model="approverRemarks" id="" row="5"></textarea>
                </div>
            </div>
        </template>
        <template #footer>
            <button @click="saveForApproval('DIS')" type="submit" class="btn btn-danger btn-sm"><font-awesome-icon class="nav-icon" icon="fas fa-thumbs-down" />&nbsp;     Disapproved</button>
            <button @click="saveForApproval('APP')" type="submit" class="btn btn-success btn-sm"><font-awesome-icon class="nav-icon" icon="fas fa-thumbs-up" />&nbsp;     Approved</button>
        </template>
    </ModalComponent>
    <ModalComponent  @add-event="formSavePdfEmailFormat" icon="fa-envelope" modalDialog="modal-dialog modal-xl" title="Recipients & Email Details" ref="modalSavePdfEmailFormat">
        <template #body>
            <div class="row mt-3">
                <div class="col-sm-6">
                    <div class="input-group flex-nowrap mb-2 input-group-sm">
                        <span class="input-group-text" id="addon-wrapping">To:</span>
                        <Multiselect
                            v-model="frmPdfEmailFormat.pdfToGroup"
                            :close-on-select="true"
                            :searchable="true"
                            :options="settingsVar.pdfToGroup"
                            @change=onChangePdfToGroup($event)
                            placeholder="-Select an Option-">
                        </Multiselect>
                    </div>
                </div>
                <div class="col-sm-6 d-none">
                    <div class="input-group flex-nowrap mb-2 input-group-sm">
                        <span class="input-group-text" id="addon-wrapping">Attention:</span>
                        <Multiselect
                            :close-on-select="false"
                            :searchable="true"
                            placeholder="-Select an Option-"
                            mode="tags"
                            v-model="frmPdfEmailFormat.pdfAttn"
                            :options="settingsVar.pdfAttn"
                        />
                    </div>
                </div>
                <div class="col-sm-6 d-none">
                    <div class="input-group flex-nowrap mb-2 input-group-sm">
                        <span class="input-group-text" id="addon-wrapping">CC:</span>
                        <Multiselect
                            :close-on-select="false"
                            :searchable="true"
                            placeholder="-Select an Option-"
                            mode="tags"
                            v-model="frmPdfEmailFormat.pdfCc"
                            :options="settingsVar.pdfCc"
                        />
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="input-group flex-nowrap mb-2 input-group-sm">
                        <span class="input-group-text" id="addon-wrapping">Attention:</span>
                        <textarea class="form-control" v-model="frmPdfEmailFormat.pdfAttnName" row="5">
                        </textarea>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="input-group flex-nowrap mb-2 input-group-sm">
                        <span class="input-group-text" id="addon-wrapping">CC:</span>
                        <textarea class="form-control" v-model="frmPdfEmailFormat.pdfCcName" row="5">
                        </textarea>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="input-group flex-nowrap mb-2 input-group-sm">
                        <span class="input-group-text" id="addon-wrapping">Subject:</span>
                        <textarea class="form-control" v-model="frmPdfEmailFormat.pdfSubject" row="5">
                        </textarea>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="input-group flex-nowrap mb-2 input-group-sm">
                        <span class="input-group-text" id="addon-wrapping">Additional Message:</span>
                        <textarea class="form-control" v-model="frmPdfEmailFormat.pdfAdditionalMsg" row="5">
                        </textarea>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="input-group flex-nowrap mb-2 input-group-sm">
                        <span class="input-group-text" id="addon-wrapping">Terms and Condition:</span>
                        <textarea class="form-control" v-model="frmPdfEmailFormat.pdfTermsCondition" row="5">
                        </textarea>
                    </div>
                </div>
            </div>
        </template>
        <template #footer>
            <button type="button" id= "closeBtn" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-success btn-sm"><font-awesome-icon class="nav-icon" icon="fas fa-save" />&nbsp;     Saved</button>
        </template>
    </ModalComponent>
</template>
<script setup>
    import {
        onMounted,
        ref,
        reactive,
        toRef,
        watch,
    } from 'vue'
    import DataTable from 'datatables.net-vue3';
    import DataTablesCore from 'datatables.net-bs5';
    import useFetch from '../composables/utils/useFetch';
    import useForm from '../composables/utils/useForm';
    import useCommon from '../composables/common';
    import useSettings from '../composables/settings';
    import useProductMaterial from '../composables/productmaterial';
    import ModalComponent from '../components/ModalComponent.vue';
    import Router from '../routes';
    import { useRoute } from 'vue-router';
    const route = useRoute();
    const itemsIdParam = ref(route.params.itemsId); // Retrieve itemsId Route route params
    const pmItemStatusParam = ref(route.params.pmItemStatus); // Retrieve paramsPmItemStatus from route params
    const {
        axiosFetchData,
    } = useFetch();
    const {
        axiosSaveData,
    } = useForm();
    const {
        modalCommon,
        commonVar,
    } = useCommon();
    const {
        getPdfToGroup,
        settingsVar,
        frmPdfEmailFormat,
        getPdfEmailFormat,
    } = useSettings();
    const {
        modalPm,
        pmVar,
        tblProductMaterial,
        frmItem,
        cardSaveClassifications,
        rowSaveClassifications,
        rowSaveDescriptions,
        rowSaveItems,
        getItemsById,
        onChangeDivision,
    } = useProductMaterial();
    DataTable.use(DataTablesCore);
    const selectedItemsId = ref(null);
    const approverRemarks = ref(null);
    const modalSaveApproval = ref(null);
    const modalSavePdfEmailFormat = ref(null);
    const tblPmApproverSummary = ref(null);
    const productMaterialColumns = [
        {   data : 'getActions',
             orderable: false,
            searchable: false,
            createdCell(cell){
                let btnGetMaterialById = cell.querySelector('#btnGetMaterialById');
                if(btnGetMaterialById !=null){
                    btnGetMaterialById.addEventListener('click',function(){
                        let itemsId = this.getAttribute('items-id')
                        let itemParams = {
                            itemsId : itemsId
                        }
                        selectedItemsId.value = itemsId;
                    });
                }
            }
        },
        { data : 'controlNo' },
        { data : 'category' },
        { data : 'createdBy' },
        { data : 'remarks' },
    ];
    const tblPmApproverSummaryColumns = [
        {   data: 'getCount'} ,
        {   data: 'getApproverName'} ,
        {   data: 'getRole'} ,
        {   data: 'getRemarks'},
        {   data: 'getStatus'} ,
    ];
    onMounted ( async () =>{
        modalPm.SaveApproval = new Modal(modalSaveApproval.value.modalRef,{ keyboard: false });
        modalPm.SavePdfEmailFormat = new Modal(modalSavePdfEmailFormat.value.modalRef,{ keyboard: false });
        frmItem.value.status = "FOR UPDATE";
        let itemParams = {
            itemsId : itemsIdParam.value,
            isClassificationQtyExist : true,
        }
        await getItemsById(itemParams);
        selectedItemsId.value = itemsIdParam.value;
        tblPmApproverSummary.value.dt.ajax.url("api/load_pm_approval_summary?itemsId="+selectedItemsId.value).draw();
    })
    const btnSavePdfEmailFormat = () => {
        let pdfToGroupParams = {
            itemsId : selectedItemsId.value,
            globalVarPdfToGroup: settingsVar.pdfToGroup,
            frmModelPdfToGroup: toRef(frmPdfEmailFormat.value,'pdfToGroup'),
            globalVarPdfAttn: settingsVar.pdfAttn,
            frmModelPdfAttn: toRef(frmPdfEmailFormat.value,'pdfAttn'),
            globalVarPdfCc: settingsVar.pdfCc,
            frmModelPdfCc: toRef(frmPdfEmailFormat.value,'pdfCc'),

            selectedVal: '',
        };
        getPdfToGroup(pdfToGroupParams);
        getPdfEmailFormat(pdfToGroupParams);
        modalPm.SavePdfEmailFormat.show();

    }
    const onChangePdfToGroup = (customer) => {
        let pdfToGroupParams = {
            globalVarPdfToGroup: settingsVar.pdfToGroup,
            frmModelPdfToGroup: toRef(frmPdfEmailFormat.value,'pdfToGroup'),
            globalVarPdfAttn: settingsVar.pdfAttn,
            frmModelPdfAttn: toRef(frmPdfEmailFormat.value,'pdfAttn'),
            globalVarPdfCc: settingsVar.pdfCc,
            frmModelPdfCc: toRef(frmPdfEmailFormat.value,'pdfCc'),
            customer: customer,
        };
        getPdfToGroup(pdfToGroupParams);
    }
    const btnForApproval = () => {
        modalPm.SaveApproval.show();
    }
    const saveForApproval = (approverDecision) => {
        let formData =  new FormData();
        formData.append('selectedItemsId', selectedItemsId.value) //selectedItemsId
        formData.append('approverRemarks', approverRemarks.value)
        formData.append('approverDecision', approverDecision)
        axiosSaveData(formData,'api/save_for_approval', (response) =>{
            console.log(response);
            // Router.push({ name: 'ProductMaterial'});
        });

        // Swal.fire({
        //     title: 'Confirmation',
        //     text: 'Please double check your details, the Approval will RESET !',
        //     icon: 'warning',
        //     allowOutsideClick: false,
        //     showCancelButton: true,
        //     confirmButtonColor: '#3085d6',
        //     cancelButtonColor: '#d33',
        //     confirmButtonText: 'Yes'
        // }).then((result) => {
        //     if (result.isConfirmed) {
        //     }
        // });

    }
    // Add a new row to a specific card
    const addRowClassification = (cardIndex,card) => {
      cardSaveClassifications.value[cardIndex].rows.push({
            descriptionsId: card[0].descriptionsId,
            classification: '',
            qty: 0,
            uom: 'pcs',
            unitPrice: 0,
            remarks: '',
      });
    };
    // Remove a row from a specific card
    const removeRowFromCard = (cardIndex, rowIndex) => {
      cardSaveClassifications.value[cardIndex].rows.splice(rowIndex, 1);
    };
    const formSaveClassificationQty = async () => {
        let formData =  new FormData();
        for (let index = 0; index < cardSaveClassifications.value.length; index++) {
            const elementCardSaveClassifications = cardSaveClassifications.value[index];

            for (let index = 0; index < elementCardSaveClassifications.rows.length; index++) {
                const elementRowSaveDescription = elementCardSaveClassifications.rows[index];

                const descriptionsId = elementRowSaveDescription.descriptionsId;
                const classification = elementRowSaveDescription.classification;
                const qty = elementRowSaveDescription.qty;
                const uom = elementRowSaveDescription.uom;
                const unitPrice = elementRowSaveDescription.unitPrice;
                const remarks = elementRowSaveDescription.remarks;
                [
                    ["descriptionsId[]", descriptionsId],
                    ["classification[]", classification],
                    ["qty[]", qty],
                    ["uom[]", uom],
                    ["unitPrice[]", unitPrice],
                    ["remarks[]", remarks],
                ].forEach(([key, value]) =>
                    formData.append(key, value)
                );
            }
        }
        axiosSaveData(formData,'api/save_classification_qty', (response) =>{
            Router.push({ name: 'ProductMaterial'});
        });
    }
    const formSavePdfEmailFormat = async () => {
        let formData =  new FormData();
        formData.append('selectedItemsId', selectedItemsId.value) //selectedItemsId
        formData.append('pdfToGroup', frmPdfEmailFormat.value.pdfToGroup)
        formData.append('pdfAttnName', frmPdfEmailFormat.value.pdfAttnName)
        formData.append('pdfCcName', frmPdfEmailFormat.value.pdfCcName)
        formData.append('pdfSubject', frmPdfEmailFormat.value.pdfSubject)
        formData.append('pdfAdditionalMsg', frmPdfEmailFormat.value.pdfAdditionalMsg)
        formData.append('pdfTermsCondition', frmPdfEmailFormat.value.pdfTermsCondition)
        axiosSaveData(formData,'api/save_pdf_email_format', (response) =>{
            // modalPm.SavePdfEmailFormat.hide();
        });
    }


</script>
<style lang="scss" scoped>

</style>


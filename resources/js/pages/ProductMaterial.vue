<template>
    <div class="container-fluid px-4">
        <h4 class="mt-4">Material / Product Quotation</h4>
        <div class="card mt-3"  style="width: 100%;">
            <div class="card-body overflow-auto">
                <div class="table-responsive">
                    <div class="row">
                        <div class="col-6 mb-3">
                            <button @click="btnAddUser" type="button" ref= "btnAddUser" class="btn btn-primary btn-sm">
                                <font-awesome-icon class="nav-icon" icon="fas fa-file" />&nbsp; Add New
                            </button>
                        </div>
                    </div>
                    <DataTable
                        width="100%" cellspacing="0"
                        class="table mt-2"
                        ref="tblProductMaterial"
                        ajax="api/load_product_material"
                        :columns="productMaterialColumns"
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
                                <th>Control Number</th>
                                <th>Category</th>
                                <th>Created By</th>
                                <th>Remarks</th>
                            </tr>
                        </thead>
                    </DataTable>
                </div>
            </div>
        </div>
    </div>
    <ModalComponent icon="fa-file" modalDialog="modal-dialog modal-md" title="Add User" ref="modalAddUser">
        <template #body>
            <div class="row mt-3">
                <div class="row">
                    <div class="input-group flex-nowrap mb-2 input-group-sm">
                        <span class="input-group-text" id="addon-wrapping">Full Name:</span>
                        <Multiselect
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
            <button type="submit" class="btn btn-success btn-sm"><font-awesome-icon class="nav-icon" icon="fas fa-save" />&nbsp;  Save</button>
        </template>
    </ModalComponent>
    <ModalComponent icon="fa-download" modalDialog="modal-dialog modal-xl" title="Quotations" ref="modalQuotations">
        <template #body>
            <div class="row mt-3">
                <div class="row">
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
                                :change="onChangeDivision(selectedItemsId)"
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
                <!--  -->
                <div class="col-12">
                    <button @click="addRowSaveItem"  type="button" class="btn btn-primary btn-sm" style="float: right !important;"><i class="fas fa-plus"></i> Add Items</button>
                    <br><br>
                </div>
                <div class="col-12">
                        <!-- <Multiselect
                            placeholder="-Select an Option-"
                        :close-on-select="true"
                        :searchable="true"
                        :options="commonVar.optAdminAccess"
                        @change="onChangeAdminAccess($event)"
                    /> -->
                    <!-- <input :value="selectedItemsId" v-model="pmItemsId" type="text" class="form-control" id="inlineFormInputGroup"> -->

                    <div class="row itemDesc" v-for="(rowSaveItem, indexItem) in rowSaveItems" :key="rowSaveItem.itemNo">
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
                                            <div class="col-12">
                                                <button @click="addRowSaveDescription(indexItem,rowSaveItem.itemNo)" type="button" class="btn btn-primary btn-sm" style="float: right !important;"><i class="fas fa-plus"></i> Add Descriptions</button>
                                                <br><br>

                                            </div>
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
                                                    <th scope="col">Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr v-for="(rowSaveDescription, indexDescription) in rowSaveItem.rows" :key="rowSaveDescription.indexDescription">
                                                        <td>
                                                            <span>{{ indexDescription+1 }}</span>
                                                            <input v-model="rowSaveDescription.descItemNo" type="" class="form-control" id="inlineFormInputGroup" readonly>
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

                                                        <td>
                                                            <button @click="removeRowSaveDescription(indexItem, indexDescription)" class="btn btn-danger btn-sm" type="button" data-item-process="add">
                                                                <li class="fa fa-trash"></li>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                            <button @click="saveDescItemNo(rowSaveItem.itemNo)" type="button" class="btn btn-success btn-sm" style="float: right !important;"><i class="fas fa-save"></i> Save Item No. {{ rowSaveItem.itemNo }}</button>
                                                <br><br>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="card mb-2">
                            <h5 class="mb-0">
                                <button id="" class="btn btn-link collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseApprovalSummary" aria-expanded="true" aria-controls="collapseMan">
                                   Approval Summary
                                </button>
                            </h5>
                        <div id="collapseApprovalSummary" class="collapse show" data-bs-parent="#accordionMain">
                            <div class="card-body overflow-auto">
                                <div class="row">
                                    <div class="col-12 overflow-auto">
                                        <table class="table table-responsive">
                                            <thead>
                                                <tr>
                                                <th scope="col" style="width: 25%;">Role</th>
                                                <th scope="col" style="width: 25%;">Approver Name</th>
                                                <th scope="col" style="width: 10%;">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr class="production">
                                                    <td>
                                                        Prepared By
                                                    </td>
                                                    <td>
                                                        <Multiselect
                                                            :disabled="isModalView"
                                                            v-model="frmItem.preparedBy"
                                                            :close-on-select="true"
                                                            :searchable="true"
                                                            :options="settingsVar.preparedBy"
                                                        />
                                                    </td>
                                                    <td>

                                                    </td>
                                                </tr>
                                                <tr class="production">
                                                    <td>
                                                        Checked By
                                                    </td>
                                                    <td>
                                                        <Multiselect
                                                            :disabled="isModalView"
                                                            v-model="frmItem.checkedBy"
                                                            :close-on-select="true"
                                                            :searchable="true"
                                                            :options="settingsVar.checkedBy"
                                                        />
                                                    </td>
                                                    <td>

                                                    </td>
                                                </tr>
                                                <tr class="production">
                                                    <td>
                                                        Noted By
                                                    </td>
                                                    <td>
                                                        <Multiselect
                                                            :disabled="isModalView"
                                                            v-model="frmItem.notedBy"
                                                            :close-on-select="true"
                                                            :searchable="true"
                                                            :options="settingsVar.notedBy"
                                                        />
                                                    </td>
                                                    <td>

                                                    </td>
                                                </tr>
                                                <tr class="production">
                                                    <td>
                                                        Approved By
                                                    </td>
                                                    <td>
                                                        <Multiselect
                                                            :disabled="isModalView"
                                                            v-model="frmItem.approvedByOne"
                                                            :close-on-select="true"
                                                            :searchable="true"
                                                            :options="settingsVar.approvedByOne"
                                                        />
                                                    </td>
                                                    <td>
                                                        <!-- <button @click="reloadRapidxUserMaster('prdn')" class="btn btn-outline-warning btn-sm" type="button" data-item-process="refresh">
                                                            <font-awesome-icon class="nav-icon" icon="refresh" />
                                                        </button> -->
                                                    </td>
                                                </tr>
                                                <tr class="production">
                                                    <td>
                                                        Approved By
                                                    </td>
                                                    <td>
                                                        <Multiselect
                                                            :disabled="isModalView"
                                                            v-model="frmItem.approvedByTwo"
                                                            :close-on-select="true"
                                                            :searchable="true"
                                                            :options="settingsVar.approvedByTwo"
                                                        />
                                                    </td>
                                                    <td>
                                                        <!-- <button @click="reloadRapidxUserMaster('prdn')" class="btn btn-outline-warning btn-sm" type="button" data-item-process="refresh">
                                                            <font-awesome-icon class="nav-icon" icon="refresh" />
                                                        </button> -->
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
        </template>
        <template #footer>
            <button type="button" id= "closeBtn" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
            <button  @click="formSaveItem" type="submit" class="btn btn-success btn-sm"><font-awesome-icon class="nav-icon" icon="fas fa-save" />&nbsp;     Save</button>
        </template>
    </ModalComponent>
</template>

<script setup>
     import {
        onMounted,
        ref,
        reactive,
        toRef,
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
    // import Router from
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
        modalPm,
        pmVar,
        frmItem,
        cardSaveClassifications,
        rowSaveClassifications,
        rowSaveDescriptions,
        rowSaveItems,
        getDescriptionByItemsId,
        getItemsById,
        onChangeDivision,
    } = useProductMaterial();

    const {
        settingsVar,
        getRapidxUserByIdOpt,
    } = useSettings();

    DataTable.use(DataTablesCore);
    const selectedItemsId = ref(null);
    const modalQuotations = ref(null);
    const isModalView = ref(false);

    const preparedByParams = {
        globalVar: settingsVar.preparedBy,
        formModel: toRef(frmItem.value,'preparedBy'),
        selectedVal: "0",
    };
    const checkedByParams = {
        globalVar: settingsVar.checkedBy,
        formModel: toRef(frmItem.value,'checkedBy'),
        selectedVal: "0",
    };
    const notedByParams = {
        globalVar: settingsVar.notedBy,
        formModel: toRef(frmItem.value,'notedBy'),
        selectedVal: "0",
    };
    const approvedByOneParams = {
        globalVar: settingsVar.approvedByOne,
        formModel: toRef(frmItem.value,'approvedByOne'),
        selectedVal: "0",
    };
    const approvedByTwoParams = {
        globalVar: settingsVar.approvedByTwo,
        formModel: toRef(frmItem.value,'approvedByTwo'),
        selectedVal: "0",
    };
    const productMaterialColumns = [
        {   data : 'getActions',
             orderable: false,
            searchable: false,
            createdCell(cell){
                let btnGetMaterialById = cell.querySelector('#btnGetMaterialById');
                let btnGetClassificationQtyByItemsId = cell.querySelector('#btnGetClassificationQtyByItemsId');
                if(btnGetMaterialById !=null){
                    btnGetMaterialById.addEventListener('click',function(){
                        let itemsId = this.getAttribute('items-id')
                        let pmItemStatus = this.getAttribute('pm-item-status')
                        let itemParams = {
                            itemsId : itemsId
                        }
                        // Router.push({ name: 'ClassificationQty', params: { itemsId } });
                        getItemsById(itemParams);
                        selectedItemsId.value = itemsId;
                    });
                }
                if(btnGetClassificationQtyByItemsId !=null){
                    btnGetClassificationQtyByItemsId.addEventListener('click',function(){
                        let itemsId = this.getAttribute('items-id')
                        let pmItemStatus = this.getAttribute('pm-item-status')
                        let itemParams = {
                            itemsId : itemsId,
                        }
                        Router.push({ name: 'ClassificationQty',
                            params: {
                                itemsId,
                                pmItemStatus,
                            },
                        });
                        // getItemsById(itemParams);
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
    onMounted ( async () =>{
        modalPm.Quotations = new Modal(modalQuotations.value.modalRef,{ keyboard: false });
        // modalPm.Quotations.show();
        frmItem.value.status = "FOR UPDATE";
        getRapidxUserByIdOpt(preparedByParams);
        getRapidxUserByIdOpt(checkedByParams);
        getRapidxUserByIdOpt(notedByParams);
        getRapidxUserByIdOpt(approvedByOneParams);
        getRapidxUserByIdOpt(approvedByTwoParams);

    })
    const addRowSaveItem = () => {
        const newItemNo = rowSaveItems.value.length + 1;
        rowSaveItems.value.push( {
            itemNo: newItemNo,
            descriptionsId: 0,
            rows : [{
                descItemNo: newItemNo,
                partcodeType: 'N/A',
                descriptionItemName: "N/A",
                matSpecsLength: 0,
                matSpecsWidth: 0,
                matSpecsHeight: 0,
                matRawType: 'N/A',
                matRawThickness: 0,
                matRawWidth: 0,
            }]
        })
    }
    const addRowSaveDescription = (itemNoIndex,newItemNo) => {
        rowSaveItems.value[itemNoIndex].rows.push( {
            descItemNo: newItemNo,
            partcodeType: 'N/A',
            descriptionItemName: "N/A",
            matSpecsLength: 0,
            matSpecsWidth: 0,
            matSpecsHeight: 0,
            matRawType: 'N/A',
            matRawThickness: 0,
            matRawWidth: 0,
        })
    }
    const removeRowSaveDescription =   (indexItem, indexDescription) => {
        rowSaveItems.value[indexItem].rows.splice(indexDescription, 1);
    }
    const formSaveItem = async () => {
        let formData =  new FormData();
        formData.append('itemsId', selectedItemsId.value);
        formData.append('controlNo', frmItem.value.controlNo);
        formData.append('division', frmItem.value.division);
        formData.append('category', frmItem.value.category);
        formData.append('remarks', frmItem.value.remarks);

        formData.append('preparedBy', frmItem.value.preparedBy);
        formData.append('checkedBy', frmItem.value.checkedBy);
        formData.append('notedBy', frmItem.value.notedBy);
        formData.append('approvedByOne', frmItem.value.approvedByOne);
        formData.append('approvedByTwo', frmItem.value.approvedByTwo);


        for (let index = 0; index < rowSaveItems.value.length; index++) {
            const elementRowSaveItems = rowSaveItems.value[index];

            for (let index = 0; index < elementRowSaveItems.rows.length; index++) {
                const elementRowSaveDescription = elementRowSaveItems.rows[index];
                const descItemNo = elementRowSaveDescription.descItemNo;
                const partcodeType = elementRowSaveDescription.partcodeType;
                const descriptionItemName = elementRowSaveDescription.descriptionItemName;

                const matSpecsLength = elementRowSaveDescription.matSpecsLength;
                const matSpecsWidth = elementRowSaveDescription.matSpecsWidth;
                const matSpecsHeight = elementRowSaveDescription.matSpecsHeight;
                const matRawType = elementRowSaveDescription.matRawType;
                const matRawThickness = elementRowSaveDescription.matRawThickness;
                const matRawWidth = elementRowSaveDescription.matRawWidth;

                [
                    ["itemNo[]", descItemNo],
                    ["partcodeType[]", partcodeType],
                    ["descriptionItemName[]", descriptionItemName],
                    ["matSpecsLength[]", matSpecsLength],
                    ["matSpecsWidth[]", matSpecsWidth],
                    ["matSpecsHeight[]", matSpecsHeight],
                    ["matRawType[]", matRawType],
                    ["matRawThickness[]", matRawThickness],
                    ["matRawWidth[]", matRawWidth],
                ].forEach(([key, value]) =>
                    formData.append(key, value)
                );
            }
        }
        axiosSaveData(formData,'api/save_item', (response) =>{
            console.log(response);
        });
    }
    const saveDescItemNo = async (selectedItemNo) => {
        let formData =  new FormData();
        let descItemByselectedItemNo = rowSaveItems.value.find(({ itemNo
        }) => itemNo === selectedItemNo); //Find the selected itemNo Object
        formData.append('itemsId', selectedItemsId.value);
        formData.append('selectedItemNo', selectedItemNo);
        for (let index = 0; index < descItemByselectedItemNo.rows.length; index++) {
            const elementRowSaveDescription = descItemByselectedItemNo.rows[index];
            const descItemNo = elementRowSaveDescription.descItemNo;
            const partcodeType = elementRowSaveDescription.partcodeType;
            const descriptionItemName = elementRowSaveDescription.descriptionItemName;

            const matSpecsLength = elementRowSaveDescription.matSpecsLength;
            const matSpecsWidth = elementRowSaveDescription.matSpecsWidth;
            const matSpecsHeight = elementRowSaveDescription.matSpecsHeight;
            const matRawType = elementRowSaveDescription.matRawType;
            const matRawThickness = elementRowSaveDescription.matRawThickness;
            const matRawWidth = elementRowSaveDescription.matRawWidth;
            [
                ["itemNo[]", descItemNo],
                ["partcodeType[]", partcodeType],
                ["descriptionItemName[]", descriptionItemName],
                ["matSpecsLength[]", matSpecsLength],
                ["matSpecsWidth[]", matSpecsWidth],
                ["matSpecsHeight[]", matSpecsHeight],
                ["matRawType[]", matRawType],
                ["matRawThickness[]", matRawThickness],
                ["matRawWidth[]", matRawWidth],
            ].forEach(([key, value]) =>
                formData.append(key, value)
            );
        }
        axiosSaveData(formData,'api/save_item_no', (response) =>{
            console.log(response);
        });
    }
</script>
<template>
    <div class="container-fluid px-4">
        <div class="row mt-3">
            <div class="col-6 shadow">
                <h4>Items & Description Details</h4>
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
                    <!--  -->
                    <div class="col-12">
                    </div>
                    <div class="col-12 mb-3">
                        <div class="row itemDesc" v-for="(rowSaveItem, indexItem) in rowSaveItems" :key="rowSaveItem.itemNo">
                            <div class="card mb-2">
                                    <h5 class="mb-0">
                                        <button id="" class="btn btn-link collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseMan" aria-expanded="true" aria-controls="collapseMan">
                                            Item No. {{ rowSaveItem.itemNo }}

                                        </button>
                                    </h5>
                                <div id="collapseMan" class="collapse show" data-bs-parent="#accordionMain">
                                    <div class="card-body">
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
            </div>
            <div class="col-6 shadow">
                <div class="row">
                    <div class="col-6">
                        <h4>Classification Details</h4>
                    </div>
                    <div class="col-6 mb-3">
                        <button @click="formSaveClassificationQty" type="submit" style="float: right !important;" class="btn btn-success"><font-awesome-icon class="nav-icon" icon="fas fa-save" />&nbsp;Save</button>
                    </div>
                </div>
                 <!-- Classification Cards -->
                    <div v-for="(card, cardIndex) in cardSaveClassifications" :key="cardIndex" class="card mb-3">
                   <div class="card-header">
                       <h5>Description / ItemName: {{ card.descriptionPartName }}</h5>
                       <h5>Part Code / Type: {{ card.descriptionPartCode}}</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered">
                        <thead>
                            <tr>
                            <th>#</th>
                            <th>Classification</th>
                            <th>Quantity</th>
                            <th>UOM</th>
                            <th>Unit Price</th>
                            <th>Remarks</th>
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
                                placeholder="Enter quantity"
                                />
                            </td>
                            <td>
                                <input
                                type="number"
                                class="form-control"
                                v-model="rowSaveClassifications.uom"
                                placeholder="Enter quantity"
                                />
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
    import useProductMaterial from '../composables/productmaterial';
    import ModalComponent from '../components/ModalComponent.vue';
    import Router from '../routes';
    import { useRoute } from 'vue-router';
    const route = useRoute();
    const itemsIdFrom = ref(route.params.itemsId); // Retrieve itemsId from route params
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
    onMounted ( async () =>{
        console.log(rowSaveDescriptions);

        frmItem.value.status = "FOR UPDATE";
        let itemParams = {
            itemsId : itemsIdFrom.value,
            isClassificationQtyExist : true,
        }
        await getItemsById(itemParams);
        selectedItemsId.value = itemsIdFrom.value;
    })
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
</script>
<style lang="scss" scoped>

</style>


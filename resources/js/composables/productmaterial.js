import { ref, inject,reactive } from 'vue'
import useFetch from './utils/useFetch';
export default function useProductMaterial()
{
    const { axiosFetchData } = useFetch();
    const frmItem = ref({
        controlNo: '',
        category: '',
        remarks: '',
    });
    const pmVar = ref({
        controlNo: '',
        ecrApprovalCurrentCount: 0,
    });
    const rowSaveClassifications = ref();
    const tblProductMaterial = ref(null);
    const cardSaveClassifications = ref([]);
    const rowSaveDescriptions = ref();
    const modalPm = {};
    const rowSaveItems = ref([
        {
            itemNo: 1,
            descriptionsId: 0,
            rows: [{
                descItemNo: 1,
                partcodeType: 'N/A',
                descriptionItemName: "N/A",
                matSpecsLength: 0,
                matSpecsWidth: 0,
                matSpecsHeight: 0,
                matRawType: 'N/A',
                matRawThickness: 0,
                matRawWidth: 0,
            }]
        }
    ]);
    const  getItemsById = (params) => {
        rowSaveItems.value = [];
        let apiParams = {
            itemsId : params.itemsId
        }
        axiosFetchData(apiParams,'api/get_items_by_id',function(response){
            let data = response.data;
            if (data.descriptionCount > 0) {
                //description
                let arrFlatDescription = [];
                let itemCollection = data.itemCollection[0];
                let pmApprovals = data.pmApprovals;
                frmItem.value.controlNo = itemCollection.controlNo;
                frmItem.value.category = itemCollection.category;
                frmItem.value.division = itemCollection.division;
                frmItem.value.status = itemCollection.status;
                frmItem.value.remarks = itemCollection.remarks;
                frmItem.value.createdBy = data.createdBy;
                pmVar.value.ecrApprovalCurrentCount = data.ecrApprovalCurrentCount;
                if(pmApprovals[0] != undefined){
                    frmItem.value.preparedBy = pmApprovals[0].rapidx_user_rapidx_user_id.id ?? '0'
                    frmItem.value.checkedBy = pmApprovals[1].rapidx_user_rapidx_user_id.id ?? '0';
                    frmItem.value.notedBy =  pmApprovals[2].rapidx_user_rapidx_user_id.id ?? '0';
                    frmItem.value.approvedByOne =  pmApprovals[3].rapidx_user_rapidx_user_id.id ?? '0';
                    frmItem.value.approvedByTwo =  pmApprovals[4].rapidx_user_rapidx_user_id.id ?? '0';
                }
                for (let index = 1; index <= data.descriptionCount; index++) {
                    const elementDescription = data.description[index];
                    let rows = [];
                    // Populate rows for the current Item No
                    if (elementDescription) {
                        for (let indexRow = 0; indexRow < elementDescription.length; indexRow++) {
                            // console.log(elementDescription.matSpecsLength);

                            const elementDescriptionRow = elementDescription[indexRow];
                            rows.push({
                                descItemNo: elementDescriptionRow.itemNo,
                                partcodeType: elementDescriptionRow.partCode || 'N/A',
                                descriptionItemName: elementDescriptionRow.descriptionPartName || 'N/A',
                                //Raw Mats
                                matSpecsLength: elementDescriptionRow.matSpecsLength,
                                matSpecsWidth: elementDescriptionRow.matSpecsWidth,
                                matSpecsHeight: elementDescriptionRow.matSpecsHeight,
                                matRawType: elementDescriptionRow.matRawType,
                                matRawThickness: elementDescriptionRow.matRawThickness,
                                matRawWidth: elementDescriptionRow.matRawWidth,
                            });
                        }

                    }
                    if(elementDescription){
                        // Add the card data to rowSaveItems
                        rowSaveItems.value.push({
                            itemNo: index,
                            descriptionsId: elementDescription[0].id,
                            rows: rows.length > 0 ? rows : [{
                                // descItemNo: index,
                                // partcodeType: elementDescription.partcodeType,
                                // descriptionItemName: elementDescription.descriptionItemName,
                                // //Raw Mats
                                // matSpecsLength: elementDescription.matSpecsLength,
                                // matSpecsWidth: elementDescription.matSpecsWidth,
                                // matSpecsHeight: elementDescription.matSpecsHeight,
                                // matRawType: elementDescription.matRawType,
                                // matRawThickness: elementDescription.matRawThickness,
                                // matRawWidth: elementDescription.matRawWidth,
                            }],
                        });

                        if (Array.isArray(elementDescription)) arrFlatDescription.push(...elementDescription);
                    }

                }
                if (arrFlatDescription) {
                    for (let indexRowDescriptions = 0; indexRowDescriptions < arrFlatDescription.length; indexRowDescriptions++) {

                            const elementClassifications = arrFlatDescription[indexRowDescriptions].classifications ?? false;
                            const elementDescription = arrFlatDescription[indexRowDescriptions];

                            let rows = [];
                            if (elementClassifications) {
                                for (let indexRowClassifications = 0; indexRowClassifications < elementClassifications.length; indexRowClassifications++) {
                                    let arrClassifications = elementClassifications[indexRowClassifications]
                                    rows.push({
                                        descriptionsId: arrClassifications.descriptionsId,
                                        classification: arrClassifications.classification,
                                        //Product
                                        qty: arrClassifications.qty,
                                        uom: arrClassifications.uom,
                                        unitPrice: arrClassifications.unitPrice,
                                        remarks: arrClassifications.remarks,
                                    });
                                }
                            }

                            // Push the description and its classifications into cardSaveClassifications
                            cardSaveClassifications.value.push({
                                descriptionPartName: elementDescription.descriptionPartName,
                                descriptionPartCode: elementDescription.partCode,
                                rows: rows.length > 0 ? rows : [
                                    {
                                        descriptionsId: elementDescription.id,
                                        classification: 'N/A',
                                        qty: 0,
                                        uom: '',
                                        unitPrice: 0,
                                        remarks: '',
                                    },
                                ],
                            });
                    }
                }

            }
            if(params.isClassificationQtyExist != true){
                modalPm.Quotations.show();
            }
        });
    }
    const onChangeDivision = (selectedItemsId=null) => {
        if(selectedItemsId === null){
            let apiParams = {
                division : frmItem.value.division
            }
            axiosFetchData(apiParams,'api/generate_control_number',function(response){
                let data = response.data;
                frmItem.value.controlNo = data.currentCtrlNo;
            });
        }
    }

    return {
        modalPm,
        pmVar,
        tblProductMaterial,
        frmItem,
        rowSaveDescriptions,
        rowSaveItems,
        rowSaveClassifications,
        cardSaveClassifications,
        getItemsById,
        onChangeDivision,
    }
};

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
    });
    const rowSaveClassifications = ref();
    const cardSaveClassifications = ref(
        [
            {
                descriptionId: 1,
                rows: [
                    {   classification: 'N/A',
                        qty: 0,
                        qty: "pcs",
                        unitPrice: "pcs",

                        remarks: "",
                    }
                ]
            }
        ]
    );
    const rowSaveDescriptions = ref();
    const modalPm = {};
    const rowSaveItems = ref([
        {
            itemNo: 1,
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

    const getDescriptionByItemsId = () => {
        let apiParams = {

        }

        axiosFetchData(apiParams,'api/get_description_by_items_id',function(response){
            console.log(response);
        });
    }

    const  getItemsById = (params) => {
        rowSaveItems.value = [];
        let apiParams = {
            itemsId : params.itemsId
        }
        axiosFetchData(apiParams,'api/get_items_by_id',function(response){
            let data = response.data;
            if (data.descriptionCount > 0) {
                let itemCollection = data.itemCollection[0];
                frmItem.value.controlNo = itemCollection.controlNo;
                frmItem.value.category = itemCollection.category;
                frmItem.value.status = itemCollection.status;
                frmItem.value.remarks = itemCollection.remarks;
                for (let index = 1; index <= data.descriptionCount; index++) {
                    const elementDescription = data.description[index];
                    let rows = [];
                    // Populate rows for the current Item No
                    if (elementDescription) {
                        for (let indexRow = 0; indexRow < elementDescription.length; indexRow++) {
                            const elementDescriptionRow = elementDescription[indexRow];
                            rows.push({
                                descItemNo: elementDescriptionRow.itemNo,
                                partcodeType: elementDescriptionRow.partCode || 'N/A',
                                descriptionItemName: elementDescriptionRow.descriptionPartName || 'N/A',
                            });
                        }
                    }

                    // Add the card data to rowSaveItems
                    rowSaveItems.value.push({
                        itemNo: index,
                        rows: rows.length > 0 ? rows : [{
                            descItemNo: index,
                            partcodeType: 'N/A',
                            descriptionItemName: 'N/A',
                        }],
                    });
                }
            }
            modalPm.Quotations.show();
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
        frmItem,
        rowSaveDescriptions,
        rowSaveItems,
        rowSaveClassifications,
        cardSaveClassifications,
        getDescriptionByItemsId,
        getItemsById,
        onChangeDivision,
    }
};

import { ref, inject,reactive } from 'vue'
import useFetch from './utils/useFetch';
export default function useProductMaterial()
{
    const { axiosFetchData } = useFetch();
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
                descriptionItemName: "N/A"
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
            modalPm.Quotations.show();
            if (data.descriptionCount > 0) {
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
        });
    }

    return {
        modalPm,
        rowSaveDescriptions,
        rowSaveItems,
        rowSaveClassifications,
        cardSaveClassifications,
        getDescriptionByItemsId,
        getItemsById,
    }
};

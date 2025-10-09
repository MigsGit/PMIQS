import { ref, inject,reactive } from 'vue'
import useFetch from './utils/useFetch';
export default function useProductMaterial()
{
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

    return {
        rowSaveDescriptions,
        rowSaveItems,
        rowSaveClassifications,
        cardSaveClassifications,
    }
};

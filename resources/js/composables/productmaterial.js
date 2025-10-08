import { ref, inject,reactive } from 'vue'
import useFetch from './utils/useFetch';
export default function useProductMaterial()
{
    const rowSaveDescriptions = ref([
        {
            itemNo: 1,
            rows: [
                {   partcodeType: 'N/A',
                    descriptionItemName: "N/A"
                }
            ]
        }
    ]);


    const rowSaveItems = ref([
        {
            itemNo: 1,
            rows: [
                {   partcodeType: 'N/A',
                    descriptionItemName: "N/A"
                }
            ]
        }
    ]);

    return {
        rowSaveDescriptions,
        rowSaveItems,
    }
};

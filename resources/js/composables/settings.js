import { ref, inject,reactive,nextTick,toRef } from 'vue'
import useFetch from './utils/useFetch';
import useForm from "./utils/useForm";

export default function useSettings(){
    const { axiosFetchData } = useFetch(); // Call  the useFetch function
    const settingsVar = reactive({
        optRapidxUser: [],
        userRoles : [
            {"value":"PREPBY","label":"Prepared By"},
            {"value":"CHCKBY","label":"Checked By"},
            {"value":"NOTEDBY","label":"Noted By"},
            {"value":"APPBY","label":"Approved By"},
        ],
        preparedBy:[],
        checkedBy:[],
        notedBy:[],
        approvedByOne:[],
        approvedByTwo:[],
        userRoles : [
            {"value":"PREPBY","label":"Prepared By"},
            {"value":"CHCKBY","label":"Checked By"},
            {"value":"NOTEDBY","label":"Noted By"},
            {"value":"APPBY","label":"Approved By"},
        ],
    });
    const frmDropdownMasterDetails = ref({
        dropdownMastersId : '',
        dropdownMasterDetailsId : '',
        dropdownMastersDetails : '',
        remarks : '',
    });
    const frmEcrRequirementDetails = ref({
        ecrRequirementDetailsId : '',
        requirement : '',
        details : '',
        evidence : '',
    });
    const modalSettings ={}

    const getDropdownMasterByOpt = async (params) => {
        let apiParams = {
            tblReference : params.tblReference
        }

        await axiosFetchData(apiParams, `api/get_dropdown_master_by_opt`, (response) => { //url
            let data = response.data;
            let dropdownMasterByOpt = data.dropdownMasterByOpt;
             /*
                Multiple option element base on globalVar
                This only reassigns the local globalVar.
                It does NOT modify the original ecrVar.optDropdownMasterDetails, because in Vue's reactive, reassigning won't trigger reactivity.
                You must mutate the array (not replace it) so Vue detects and updates it reactively.
                Use .splice() to update its contents.
            */
            params.globalVar.splice(0, params.globalVar.length,
                { value: '', label: '-Select an option-', disabled:true }, // Push "" option at the start
                // { value: 0, label: 'N/A' }, // Push "N/A" option at the start
                    ...dropdownMasterByOpt.map((value) => {
                    return {
                        value: value.id,
                        label: value.dropdown_masters_details
                    }
                }),
            );

            if(params.selectedVal != undefined ){
                params.formModel.value = params.selectedVal; //Make sure the data type is correct | String or Array
            }
        });
    }
    const getRapidxUserByIdOpt = async (params) => {

        let apiParams = {
            rapidxUserDeptGroup : params.rapidxUserDeptGroup ?? '',
            isApprover : params.isApprover ?? '',
        }
        //Multiselect, needs to pass reactive state of ARRAY, import vueselect with default css, check the data to the component by using console.log
        await axiosFetchData(apiParams, `api/get_rapidx_user_by_id_opt`, (response) => { //url
            let data = response.data;

            let rapidxUserById = data.rapidxUserById;
            params.globalVar.splice(0, params.globalVar.length,
                { value: '', label: '-Select an option-', disabled:true }, // Push "" option at the start
                { value: 0, label: 'N/A' }, // Push "N/A" option at the start
                    ...rapidxUserById.map((value) => {
                    return {
                        value: value.id,
                        label: value.name
                    }
                }),
            );
            if(params.selectedVal != undefined ){
                params.formModel.value = params.selectedVal; //Make sure the data type is correct | String or Array
            }
        });
    }
    const getNoModuleRapidxUserByIdOpt = async (params) => {
        alert('getNoModuleRapidxUserByIdOpt')
        let apiParams = {
            rapidxUserDeptGroup : params.rapidxUserDeptGroup ?? '',
            isApprover : params.isApprover ?? '',
        }
        //Multiselect, needs to pass reactive state of ARRAY, import vueselect with default css, check the data to the component by using console.log
        await axiosFetchData(apiParams, `api/get_no_module_rapidx_user_by_id_opt`, (response) => { //url
            let data = response.data;

            let rapidxUserById = data.rapidxUserById;
            params.globalVar.splice(0, params.globalVar.length,
                { value: '', label: '-Select an option-', disabled:true }, // Push "" option at the start
                { value: 0, label: 'N/A' }, // Push "N/A" option at the start
                    ...rapidxUserById.map((value) => {
                    return {
                        value: value.id,
                        label: value.name
                    }
                }),
            );
            params.formModel.value = params.selectedVal; //Make sure the data type is correct | String or Array
        });
    }

    const onUserChange = async (selectedParams)=>{
        await getRapidxUserByIdOpt(selectedParams);
    }
    return {
        modalSettings,
        settingsVar,
        frmDropdownMasterDetails,
        frmEcrRequirementDetails,
        axiosFetchData,
        getDropdownMasterByOpt,
        getRapidxUserByIdOpt,
        getNoModuleRapidxUserByIdOpt,
        onUserChange,
    }
}

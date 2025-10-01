import { ref, inject,reactive,nextTick,toRef } from 'vue'
import useFetch from './utils/useFetch';

export default function useMethod(){
    const machineVar = reactive({
        prdnAssessedBy : [],
        prdnCheckedBy : [],
        ppcAssessedBy : [],
        ppcCheckedBy : [],
        proEnggAssessedBy : [],
        proEnggCheckedBy : [],
        mainEnggAssessedBy : [],
        mainEnggCheckedBy : [],
        qcAssessedBy : [],
        qcCheckedBy : [],
    });
    const frmMachine = ref ({
        ecrsId : "",
        prdnAssessedBy : "",
        prdnCheckedBy : "",
        ppcAssessedBy : "",
        ppcCheckedBy : "",
        proEnggAssessedBy : "",
        proEnggCheckedBy : "",
        mainEnggAssessedBy : "",
        mainEnggCheckedBy : "",
        qcAssessedBy : "",
        qcCheckedBy : "",

    });
    return {
        machineVar,
        frmMachine,
    };
}

import { ref, inject,reactive,nextTick,toRef } from 'vue'
import useFetch from './utils/useFetch';

export default function useMan(){ //get_special_inspection_by_id
    const frmMan = ref ({
        firstAssign : '',
        longInterval : '',
        pdMaterial : '',
        processName : '',
        workingTime : '',
        trainer : '',
        qcInspectorOperator : '',
        trainerSampleSize : '',
        trainerResult : '',
        lqcSupervisor : '',
        lqcSampleSize : '',
        lqcResult : '',
        processChangeFactor : '',

    });
    return {
        frmMan,
    };
}
